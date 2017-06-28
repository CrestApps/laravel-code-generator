<?php
namespace CrestApps\CodeGenerator\DatabaseParsers;

use DB;
use App;
use CrestApps\CodeGenerator\DatabaseParsers\ParserBase;
use CrestApps\CodeGenerator\Models\Field;
use CrestApps\CodeGenerator\Models\ForeignConstraint;
use CrestApps\CodeGenerator\Support\FieldTransformer;
use CrestApps\CodeGenerator\Support\Config;

class MysqlParser extends ParserBase
{
    /**
     * List of the foreign constraints.
     *
     * @var array
     */
    protected $constrains;

    /**
     * Gets column meta info from the information schema.
     *
     * @return array
    */
    protected function getColumn()
    {
        return DB::select('SELECT
		                   COLUMN_NAME
		                  ,COLUMN_DEFAULT
		                  ,UPPER(IS_NULLABLE)  AS IS_NULLABLE
		                  ,LOWER(DATA_TYPE) AS DATA_TYPE
		                  ,CHARACTER_MAXIMUM_LENGTH
		                  ,UPPER(COLUMN_KEY) AS COLUMN_KEY
		                  ,UPPER(EXTRA) AS EXTRA
		                  ,COLUMN_COMMENT
		                  ,COLUMN_TYPE
		                  FROM INFORMATION_SCHEMA.COLUMNS
		                  WHERE TABLE_NAME = ? AND TABLE_SCHEMA = ? ',
                          [$this->tableName, $this->databaseName]);
    }

    /**
     * Gets foreign key constraint info for a giving column name.
     *
     * @return mix (null|object)
    */
    protected function getConstraint($foreign)
    {
        foreach ($this->getConstraints() as $constraint) {
            if ($constraint->foreign == $foreign) {
                return (object) $constraint;
            }
        }

        return null;
    }

    /**
     * Gets foreign key constraints info from the information schema.
     *
     * @return array
    */
    protected function getConstraints()
    {
        if (is_null($this->constrains)) {
            $this->constrains = DB::select('SELECT 
                                            r.referenced_table_name AS `references`
                                           ,r.CONSTRAINT_NAME AS `name`
                                           ,r.UPDATE_RULE AS `onUpdate`
                                           ,r.DELETE_RULE AS `onDelete`
                                           ,u.referenced_column_name AS `on`
                                           ,u.column_name AS `foreign`
                                           FROM INFORMATION_SCHEMA.REFERENTIAL_CONSTRAINTS AS r
                                           INNER JOIN information_schema.key_column_usage AS u ON u.CONSTRAINT_NAME = r.CONSTRAINT_NAME
                                                                                               AND u.table_schema = r.constraint_schema
                                                                                               AND u.table_name = r.table_name
                                           WHERE u.table_name = ? AND u.constraint_schema = ?;',
                                          [$this->tableName, $this->databaseName]);
        }

        return $this->constrains;
    }
    
    /**
     * Gets the field after transfering it from a giving query object.
     *
     * @param object $column
     *
     * @return CrestApps\CodeGenerator\Model\Field;
     */
    protected function getTransfredFields(array $columns)
    {
        $collection = [];

        foreach ($columns as $column) {
            $properties['name'] = $column->COLUMN_NAME;
            $properties['labels'] = $this->getLabel($column->COLUMN_NAME);
            $properties['is-nullable'] = ($column->IS_NULLABLE == 'YES');
            $properties['data-value'] = $column->COLUMN_DEFAULT;
            $properties['data-type'] = $this->getDataType($column->DATA_TYPE);
            $properties['data-type-params'] = $this->getPrecision($column->CHARACTER_MAXIMUM_LENGTH, $column->DATA_TYPE, $column->COLUMN_TYPE);
            $properties['is-primary'] = ($column->COLUMN_KEY == 'PRIMARY KEY');
            $properties['is-index'] = ($column->COLUMN_KEY == 'MUL');
            $properties['is-unique'] = ($column->COLUMN_KEY == 'UNI');
            $properties['is-auto-increment'] = ($column->EXTRA == 'AUTO_INCREMENT');
            $properties['comment'] = $column->COLUMN_COMMENT ?: null;
            $properties['options'] = $this->getHtmlOptions($column->DATA_TYPE, $column->COLUMN_TYPE);
            $properties['is-unsigned'] = (strpos($column->COLUMN_TYPE, 'unsigned') !== false);
            $properties['html-type'] = $this->getHtmlType($column->DATA_TYPE);
            $properties['foreign-constraint'] = $this->getForeignConstraint($column->COLUMN_NAME);

            if (intval($column->CHARACTER_MAXIMUM_LENGTH) > 255
                || in_array($column->DATA_TYPE, ['varbinary','blob','mediumblob','longblob','text','mediumtext','longtext'])) {
                $properties['is-on-index'] = false;
            }

            $collection[] = $properties;
        }
        $localeGroup = str_plural(strtolower($this->tableName));
        $fields = FieldTransformer::fromArray($collection, $localeGroup);

        return $fields;
    }

    /**
     * Gets the type params
     *
     * @param string $length
     * @param string $dataType
     * @param string $columnType
     *
     * @return $this
    */
    protected function getPrecision($length, $dataType, $columnType)
    {
        if (in_array($dataType, ['decimal','double','float','real'])) {
            $match = [];

            preg_match('#\((.*?)\)#', $columnType, $match);

            if (!isset($match[1])) {
                return null;
            }

            return explode(',', $match[1]);
        }

        if (intval($length) > 0) {
            return [$length];
        }

        return [];
    }

    /**
     * Gets the data type for a giving field.
     *
     * @param string $type
     *
     * @return $this
    */
    protected function getDataType($type)
    {
        $map = Config::dataTypeMap();

        if (!array_key_exists($type, $map)) {
            throw new Exception("The type " . $type . " is not mapped in the 'eloquent_type_to_method' key in the config file.");
        }

        return $map[$type];
    }

    /**
     * Gets the foreign constrain for the giving field.
     *
     * @param string $name
     *
     * @return $this
     */
    protected function getForeignConstraint($name)
    {
        $raw = $this->getConstraint($name);

        if (is_null($raw)) {
            return null;
        }

        return [
            'field'      => strtolower($raw->foreign),
            'references' => strtolower($raw->references),
            'on'         => strtolower($raw->on),
            'on-delete'  => strtolower($raw->onDelete),
            'on-update'  => strtolower($raw->onUpdate)
        ];
    }

    /**
     * Set the options for a giving field.
     *
     * @param CrestApps\CodeGenerator\Models\Field $field
     * @param string $type
     *
     * @return array
     */
    protected function getHtmlOptions($dataType, $columnType)
    {
        if ($dataType == 'tinyint(1)') {
            return $this->getBooleanOptions();
        }

        if (($options = $this->getEnumOptions($columnType)) !== null) {
            return $options;
        }

        return [];
    }

    /**
     * Get boolean options
     *
     * @return array
     */
    protected function getBooleanOptions()
    {
        $options = [];
        if (! $this->hasLanguages()) {
            return $this->booleanOptions;
        }

        foreach ($this->booleanOptions as $key => $title) {
            foreach ($this->languages as $language) {
                $options[$key][$language] = $title;
            }
        }

        return $options;
    }

    /**
     * Parses out the options from a giving type
     *
     * @param string $type
     *
     * @return mix (null|array)
    */
    protected function getEnumOptions($type)
    {
        $match = [];

        preg_match('#enum\((.*?)\)#', $type, $match);

        if (!isset($match[1])) {
            return null;
        }

        $options =  array_map(function ($option) {
            return trim($option, "'");
        }, explode(',', $match[1]));

        $finals = [];

        foreach ($options as $option) {
            if ($this->hasLanguages()) {
                foreach ($this->languages as $language) {
                    $finals[$language][$option] = $option;
                }
                continue;
            }

            $finals[$option] = $option;
        }

        return $finals;
    }
}
