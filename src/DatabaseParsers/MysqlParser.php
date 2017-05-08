<?php
namespace CrestApps\CodeGenerator\DatabaseParsers;

use DB;
use App;
use CrestApps\CodeGenerator\DatabaseParsers\ParserBase;
use CrestApps\CodeGenerator\Models\Field;
use CrestApps\CodeGenerator\Models\ForeignConstraint;
use CrestApps\CodeGenerator\Support\FieldTransformer;

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
		                  ,IS_NULLABLE
		                  ,CASE WHEN COLUMN_TYPE = \'tinyint(1)\' THEN \'boolean\' ELSE DATA_TYPE END AS DATA_TYPE
		                  ,CHARACTER_MAXIMUM_LENGTH
		                  ,COLUMN_KEY
		                  ,EXTRA
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
        $fields = [];

        foreach ($columns as $column) {
            $field = new Field($column->COLUMN_NAME);

            $this->setIsNullable($field, $column->IS_NULLABLE)
                 ->setMaxLength($field, $column->CHARACTER_MAXIMUM_LENGTH)
                 ->setDefault($field, $column->COLUMN_DEFAULT)
                 ->setDataType($field, $column->DATA_TYPE)
                 ->setKey($field, $column->COLUMN_KEY, $column->EXTRA)
                 ->setLabel($field, $this->getLabelName($column->COLUMN_NAME))
                 ->setComment($field, $column->COLUMN_COMMENT)
                 ->setOptions($field, $column)
                 ->setUnsigned($field, $column->COLUMN_TYPE)
                 ->setHtmlType($field, $column->DATA_TYPE)
                 ->setIsOnViews($field)
                 ->setForeignConstraint($field)
                 ->setForeignRelation($field);

            $fields[] = $field;
        }

        return $fields;
    }

    /**
     * Set the foreign constrain for the giving field.
     *
     * @param CrestApps\CodeGenerator\Models\Field $field
     *
     * @return $this
     */
    protected function setForeignConstraint(Field & $field)
    {
        $raw = $this->getConstraint($field->name);

        if (!is_null($raw)) {
            $constraint = new ForeignConstraint(
                                strtolower($raw->foreign),
                                strtolower($raw->references),
                                strtolower($raw->on),
                                strtolower($raw->onDelete),
                                strtolower($raw->onUpdate)
                            );
            
            $field->setForeignConstraint($constraint);
        }

        return $this;
    }

    /**
     * Set the options for a giving field.
     *
     * @param CrestApps\CodeGenerator\Models\Field $field
     * @param object $column
     *
     * @return $this
     */
    protected function setOptions(Field & $field, $column)
    {
        if ($column->DATA_TYPE == 'boolean') {
            return $this->addOptions($field, $this->getBooleanOptions());
        }

        if (($options = $this->getOptions($column->COLUMN_TYPE)) !== null) {
            return $this->addOptions($field, $options);
        }

        return $this;
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
     * Checks if the request requires languages.
     *
     * @return bool
    */
    protected function hasLanguages()
    {
        return ! empty($this->languages);
    }

    /**
     * Adds the options for a giving field.
     *
     * @param CrestApps\CodeGenerator\Models\Field $field
     * @param array $options
     *
     * @return $this
    */
    protected function addOptions(Field & $field, array $options)
    {
        if (! $this->hasLanguages()) {
            return $this->addOptionsFor($field, $options, true, $this->locale);
        }

        foreach ($this->languages as $language) {
            $labels = FieldTransformer::transferOptionsToLabels($field, $options, $language, $this->tableName);
            foreach ($labels as $label) {
                $labelTitle = $this->getLabelName($label->text);
                $field->addOption($labelTitle, $this->tableName, $label->isPlain, $label->lang, $label->value);
            }
        }

        return $this;
    }

    /**
     * Adds options for a giving field.
     *
     * @param CrestApps\CodeGenerator\Models\Field $field
     * @param array $options
     * @param bool $isPlain
     * @param string $locale
     *
     * @return $this
    */
    protected function addOptionsFor(Field & $field, array $options, $isPlain, $locale)
    {
        foreach ($options as $value => $option) {
            $name = is_array($option) ? current($option) : $option;
            $field->addOption($this->getLabelName($name), $this->tableName, $isPlain, $locale, $value);
        }

        return $this;
    }

    /**
     * Parses out the options from a giving type
     *
     * @param string $type
     *
     * @return mix (null|array)
    */
    protected function getOptions($type)
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
                    $finals[$option][$language] = $option;
                }
                continue;
            }

            $finals[$option] = $option;
        }

        return $finals;
    }
}
