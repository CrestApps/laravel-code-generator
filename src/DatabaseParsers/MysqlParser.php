<?php
namespace CrestApps\CodeGenerator\DatabaseParsers;

use App;
use CrestApps\CodeGenerator\DatabaseParsers\ParserBase;
use CrestApps\CodeGenerator\Models\Field;
use CrestApps\CodeGenerator\Models\ForeignConstraint;
use CrestApps\CodeGenerator\Models\ForeignRelationship;
use CrestApps\CodeGenerator\Models\Index;
use CrestApps\CodeGenerator\Support\Config;
use CrestApps\CodeGenerator\Support\FieldTransformer;
use CrestApps\CodeGenerator\Support\Str;
use CrestApps\CodeGenerator\Traits\LanguageTrait;
use CrestApps\CodeGenerator\Traits\ModelTrait;
use DB;
use Exception;

class MysqlParser extends ParserBase
{
    use ModelTrait, LanguageTrait;

    /**
     * List of the foreign constraints.
     *
     * @var array
     */
    protected $constrains;

    /**
     * List of the foreign relations.
     *
     * @var array
     */
    protected $relations;

    /**
     * List of the data types that hold large data.
     * This will be used to eliminate the column from the index view
     *
     * @var array
     */
    protected $largeDataTypes = ['varbinary', 'blob', 'mediumblob', 'longblob', 'text', 'mediumtext', 'longtext'];

    /**
     * Gets columns meta info from the information schema.
     *
     * @return array
     */
    protected function getColumns()
    {
        return DB::select(
            'SELECT
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
            [$this->tableName, $this->databaseName]
        );
    }

    /**
     * Gets foreign key constraint info for a given column name.
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
            $this->constrains = DB::select(
                'SELECT
                    r.referenced_table_name AS `references`
                   ,r.CONSTRAINT_NAME AS `name`
                   ,r.UPDATE_RULE AS `onUpdate`
                   ,r.DELETE_RULE AS `onDelete`
                   ,u.referenced_column_name AS `on`
                   ,u.column_name AS `foreign`
                   ,CASE WHEN u.TABLE_NAME = r.referenced_table_name THEN 1 ELSE 0 END selfReferences
                   FROM INFORMATION_SCHEMA.REFERENTIAL_CONSTRAINTS AS r
                   INNER JOIN information_schema.key_column_usage AS u ON u.CONSTRAINT_NAME = r.CONSTRAINT_NAME
                                                                       AND u.table_schema = r.constraint_schema
                                                                       AND u.table_name = r.table_name
                   WHERE u.table_name = ? AND u.constraint_schema = ?;',
                [$this->tableName, $this->databaseName]
            );
        }

        return $this->constrains;
    }

    protected function getRawIndexes()
    {
        $result = DB::select(
            'SELECT
              INDEX_NAME AS name
             ,COUNT(1) AS TotalColumns
             ,GROUP_CONCAT(DISTINCT COLUMN_NAME ORDER BY SEQ_IN_INDEX ASC SEPARATOR \'|||\') AS columns
             FROM INFORMATION_SCHEMA.STATISTICS AS s
             WHERE TABLE_NAME = ? AND TABLE_SCHEMA = ?
             GROUP BY INDEX_NAME
             HAVING COUNT(1) > 1;',
            [$this->tableName, $this->databaseName]
        );

        return $result;
    }

    /**
     * Get all available relations
     *
     * @return array of CrestApps\CodeGenerator\Models\ForeignRelationship;
     */
    protected function getRelations()
    {
        $relations = [];
        $rawRelations = $this->getRawRelations();
        foreach ($rawRelations as $rawRelation) {
            $relations[] = $this->getRealtion($rawRelation->foreignTable, $rawRelation->foreignKey, $rawRelation->localKey, $rawRelation->selfReferences);
        }

        return $relations;
    }

    /**
     * Gets the raw relations from the database.
     *
     * @return array
     */
    protected function getRawRelations()
    {
        if (is_null($this->relations)) {
            $this->relations = DB::select(
                'SELECT DISTINCT
                 u.referenced_column_name AS `localKey`
                ,u.column_name AS `foreignKey`
                ,r.table_name AS `foreignTable`
                ,CASE WHEN u.TABLE_NAME = r.referenced_table_name THEN 1 ELSE 0 END selfReferences
                FROM INFORMATION_SCHEMA.REFERENTIAL_CONSTRAINTS AS r
                INNER JOIN information_schema.key_column_usage AS u ON u.CONSTRAINT_NAME = r.CONSTRAINT_NAME
                                                                   AND u.table_schema = r.constraint_schema
                                                                   AND u.table_name = r.table_name
                WHERE u.referenced_table_name = ? AND u.constraint_schema = ?;',
                [$this->tableName, $this->databaseName]
            );
        }

        return $this->relations;
    }

    /**
     * Gets a query to check the relation type.
     *
     * @return string
     */
    protected function getRelationTypeQuery($tableName, $columnName)
    {
        return ' SELECT `' . $columnName . '` AS id, COUNT(1) AS total ' .
            ' FROM `' . $tableName . '` ' .
            ' GROUP BY `' . $columnName . '` ' .
            ' HAVING COUNT(1) > 1 ' .
            ' LIMIT 1 ';
    }

    /**
     * Get a corresponding relation to a given table name, foreign column and local column.
     *
     * @return CrestApps\CodeGenerator\Models\ForeignRelationship
     */
    protected function getRealtion($foreignTableName, $foreignColumn, $localColumn, $selfReferences)
    {
        $modelName = $this->getModelName($foreignTableName);
        $model = self::guessModelFullName($modelName, self::getModelsPath());

        $params = [
            $model,
            $foreignColumn,
            $localColumn,
        ];

        $relationName = ($selfReferences ? 'child_' : '');

        if ($this->isOneToMany($foreignTableName, $foreignColumn)) {
            return new ForeignRelationship('hasMany', $params, camel_case($relationName . Str::plural($foreignTableName)));
        }

        return new ForeignRelationship('hasOne', $params, camel_case($relationName . Str::singular($foreignTableName)));
    }

    /**
     * Checks of the table has one-to-many relations
     *
     * @return bool
     */
    protected function isOneToMany($tableName, $columnName)
    {
        $query = $this->getRelationTypeQuery($tableName, $columnName);
        $result = DB::select($query);

        return isset($result[0]);
    }

    /**
     * Get all available indexed
     *
     * @return array of CrestApps\CodeGenerator\Models\Index;
     */
    protected function getIndexes()
    {
        $indexes = [];
        $rawIndexes = $this->getRawIndexes();

        foreach ($rawIndexes as $rawIndex) {
            $index = new Index($rawIndex->name);
            $index->addColumns(explode('|||', $rawIndex->columns));

            $indexes[] = $index;
        }

        return $indexes;
    }

    /**
     * Gets the field after transfering it from a given query object.
     *
     * @param object $column
     *
     * @return CrestApps\CodeGenerator\Model\Field;
     */
    protected function getTransfredFields(array $columns)
    {
        $collection = [];

        foreach ($columns as $column) {
            // While constructing the array for each field
            // there is no need to set translations for options
            // or even labels. This step is handled using the FieldTransformer
            $properties['name'] = $column->COLUMN_NAME;
            $properties['is-nullable'] = ($column->IS_NULLABLE == 'YES');
            $properties['data-value'] = $column->COLUMN_DEFAULT;
            $properties['data-type'] = $this->getDataType($column->DATA_TYPE, $column->COLUMN_TYPE);
            $properties['data-type-params'] = $this->getPrecision($column->CHARACTER_MAXIMUM_LENGTH, $column->DATA_TYPE, $column->COLUMN_TYPE);
            $properties['is-primary'] = in_array($column->COLUMN_KEY, ['PRIMARY KEY', 'PRI']);
            $properties['is-index'] = ($column->COLUMN_KEY == 'MUL');
            $properties['is-unique'] = ($column->COLUMN_KEY == 'UNI');
            $properties['is-auto-increment'] = ($column->EXTRA == 'AUTO_INCREMENT');
            $properties['comment'] = $column->COLUMN_COMMENT ?: null;
            $properties['options'] = $this->getHtmlOptions($column->DATA_TYPE, $column->COLUMN_TYPE);
            $properties['is-unsigned'] = (strpos($column->COLUMN_TYPE, 'unsigned') !== false);

            $constraint = $this->getForeignConstraint($column->COLUMN_NAME);

            $properties['foreign-constraint'] = !is_null($constraint) ? $constraint->toArray() : null;

            if (intval($column->CHARACTER_MAXIMUM_LENGTH) > 255
                || in_array($column->DATA_TYPE, $this->largeDataTypes)) {
                $properties['is-on-index'] = false;
            }

            $collection[] = $properties;
        }

        $localeGroup = self::makeLocaleGroup($this->tableName);

        $fields = FieldTransformer::fromArray($collection, $localeGroup, $this->languages);

        // At this point we constructed the fields collection with the default html-type
        // We need to set the html-type using the config::getEloquentToHtmlMap() setting
        $this->setHtmlType($fields);

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
        if (in_array($dataType, ['decimal', 'double', 'float', 'real'])) {
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
     * Gets the data type for a given field.
     *
     * @param string $type
     * @param string $columnType
     *
     * @return $this
     */
    protected function getDataType($type, $columnType)
    {
        $map = Config::dataTypeMap();

        if (in_array($columnType, ['bit', 'tinyint(1)'])) {
            return 'boolean';
        }

        if (!array_key_exists($type, $map)) {
            throw new Exception("The type " . $type . " is not mapped in the 'eloquent_type_to_method' key in the config file.");
        }

        return $map[$type];
    }

    /**
     * Gets the foreign constrain for the given field.
     *
     * @param string $name
     *
     * @return null || CrestApps\CodeGenerator\Models\ForeignConstraint
     */
    protected function getForeignConstraint($name)
    {
        $raw = $this->getConstraint($name);

        if (is_null($raw)) {
            return null;
        }

        return new ForeignConstraint(
            $raw->foreign,
            $raw->references,
            $raw->on,
            strtolower($raw->onDelete),
            strtolower($raw->onUpdate),
            null,
            $raw->selfReferences
        );
    }

    /**
     * Set the options for a given field.
     *
     * @param CrestApps\CodeGenerator\Models\Field $field
     * @param string $type
     *
     * @return array
     */
    protected function getHtmlOptions($dataType, $columnType)
    {
        if (($options = $this->getEnumOptions($columnType)) !== null) {
            return $options;
        }

        return [];
    }

    /**
     * Parses out the options from a given type
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

        $options = array_map(function ($option) {
            return trim($option, "'");
        }, explode(',', $match[1]));

        $finals = [];

        foreach ($options as $option) {
            $finals[$option] = $option;
        }

        return $finals;
    }
}
