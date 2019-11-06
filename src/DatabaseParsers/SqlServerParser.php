<?php
namespace CrestApps\CodeGenerator\DatabaseParser;

use App;
use CrestApps\CodeGenerator\Models\Field;
use CrestApps\CodeGenerator\Models\FieldOptimizer;
use CrestApps\CodeGenerator\Support\DatabaseParser\ParserBase;
use DB;
use Exception;

class SqlServerParser extends ParserBase
{
    /**
     * The table name.
     *
     * @var array
     */
    protected $tableName;

    /**
     * The databasename
     *
     * @var array
     */
    protected $databaseName;

    /**
     * The locale value
     *
     * @var array
     */
    protected $locale;

    /**
     * The final fields.
     *
     * @var array
     */
    protected $fields;

    /**
     * Creates a new field instance.
     *
     * @param string $tableName
     * @param string $databaseName
     *
     * @return void
     */
    public function __construct($tableName, $databaseName)
    {
        $this->tableName = $tableName;
        $this->databaseName = $databaseName;
        $this->locale = App::getLocale();
    }

    /**
     * Gets the final fields.
     *
     * @return array
     */
    public function getFields()
    {
        if (is_null($this->fields)) {
            $columns = $this->getColumn();

            if (empty($columns)) {
                throw new Exception('The table ' . $this->tableName . ' was not found in the ' . $this->databaseName . ' database.');
            }

            $this->fields = $this->transfer($columns);
        }

        return $this->fields;
    }

    /**
     * Gets column meta info from the information schema.
     *
     * @return array
     */
    protected function getColumn()
    {
        return DB::select(
            'SELECT
							 c.COLUMN_NAME
							,c.COLUMN_DEFAULT
							,c.IS_NULLABLE
							,c.DATA_TYPE
							,c.CHARACTER_MAXIMUM_LENGTH
							,pk.CONSTRAINT_TYPE AS EXTRA
							FROM INFORMATION_SCHEMA.COLUMNS AS c
							LEFT JOIN (
							    SELECT ku.TABLE_CATALOG,ku.TABLE_SCHEMA,ku.TABLE_NAME,ku.COLUMN_NAME, tc.CONSTRAINT_TYPE
							    FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS AS tc
							    INNER JOIN INFORMATION_SCHEMA.KEY_COLUMN_USAGE AS ku ON tc.CONSTRAINT_NAME = ku.CONSTRAINT_NAME
							) AS pk ON  c.TABLE_CATALOG = pk.TABLE_CATALOG
							            AND c.TABLE_SCHEMA = pk.TABLE_SCHEMA
							            AND c.TABLE_NAME = pk.TABLE_NAME
							            AND c.COLUMN_NAME = pk.COLUMN_NAME
							WHERE c.TABLE_NAME = ? AND c.TABLE_CATALOG = ? ',
            [$this->tableName, $this->databaseName]
        );
    }

    /**
     * Gets array of field after transfering each column meta into field.
     *
     * @param array $columns
     *
     * @return array
     */
    protected function transfer(array $columns)
    {
        $fields = [];

        foreach ($columns as $column) {
            $field = new Field($column->COLUMN_NAME);

            $this->setIsNullable($field, $column->IS_NULLABLE)
                ->setMaxLength($field, $column->CHARACTER_MAXIMUM_LENGTH)
                ->setDefault($field, $column->COLUMN_DEFAULT)
                ->setDataType($field, $column->DATA_TYPE)
                ->setKey($field, $column->COLUMN_KEY, $column->EXTRA)
                ->setLabel($field, $column->COLUMN_NAME)
                ->setHtmlType($field, $column->DATA_TYPE);

            $optimizer = new FieldOptimizer($field);

            $fields[] = $optimizer->optimize()->getField();
        }

        return $fields;
    }

    /**
     * Set the unsiged flag for a given field.
     *
     * @param CrestApps\CodeGenerator\Models\Field $field
     * @param string $type
     *
     * @return $this
     */
    protected function setUnsigned(Field &$field, $type)
    {
        if (strpos($type, 'unsigned') !== false) {
            $field->isUnsigned = true;
            $field->validationRules[] = sprintf('min:%s', 0);
        }

        return $this;
    }

    /**
     * Set the html type for a given field.
     *
     * @param CrestApps\CodeGenerator\Models\Field $field
     * @param string $type
     *
     * @return $this
     */
    protected function setHtmlType(Field &$field, $type)
    {
        $map = $this->getMap();

        if (array_key_exists($type, $map)) {
            $field->htmlType = $map[$type];
        }

        return $this;
    }

    /**
     * Set the data type for a given field.
     *
     * @param CrestApps\CodeGenerator\Models\Field $field
     * @param string $type
     *
     * @return $this
     */
    protected function setDataType(Field &$field, $type)
    {
        $map = $this->dataTypeMap();

        if (array_key_exists($type, $map)) {
            $field->dataType = $map[$type];
        }

        return $this;
    }

    /**
     * Set the nullable for a given field.
     *
     * @param CrestApps\CodeGenerator\Models\Field $field
     * @param string $nullable
     *
     * @return $this
     */
    protected function setIsNullable(Field &$field, $nullable)
    {
        $field->isNullable = (strtoupper($nullable) == 'YES');

        return $this;
    }

    /**
     * Set the max length for a given field.
     *
     * @param CrestApps\CodeGenerator\Models\Field $field
     * @param string $length
     *
     * @return $this
     */
    protected function setMaxLength(Field &$field, $length)
    {
        if (($value = intval($length)) > 0) {
            $field->validationRules[] = sprintf('max:%s', $value);
            $field->methodParams[] = $value;
        }

        return $this;
    }

    /**
     * Set the default value for a given field.
     *
     * @param CrestApps\CodeGenerator\Models\Field $field
     * @param string $default
     *
     * @return $this
     */
    protected function setDefault(Field &$field, $default)
    {
        if (!empty($default)) {
            $field->dataValue = $default;
        }

        return $this;
    }

    /**
     * Set the labels for a given field.
     *
     * @param CrestApps\CodeGenerator\Models\Field $field
     * @param string $name
     *
     * @return $this
     */
    protected function setLabel(Field &$field, $name)
    {
        $field->addLabel($this->getLabelName($name), $this->tableName, true, $this->locale);

        return $this;
    }

    /**
     * Set the keys for a given field.
     *
     * @param CrestApps\CodeGenerator\Models\Field $field
     * @param string $key
     * @param string $extra
     *
     * @return $this
     */
    protected function setKey(Field &$field, $key, $extra)
    {
        $key = strtoupper($key);

        if ($key == 'PRIMARY KEY') {
            $field->isPrimary = true;
        }

        if ($key == 'MUL') {
            $field->isIndex = true;
        }

        if ($key == 'UNI') {
            $field->isUnique = true;
        }

        if (strtolower($extra) == 'auto_increment') {
            $field->isAutoIncrement = true;
        }

        return $this;
    }

    /**
     * Gets a labe field's label from a given name.
     *
     * @return string
     */
    protected function getLabelName($name)
    {
        return trim(ucwords(str_replace(['-', '_'], ' ', $name)));
    }

    /**
     * Gets the eloquent method to html
     *
     * @return array
     */
    protected function getMap()
    {
        return config('codegenerator.eloquent_type_to_html_type');
    }

    /**
     * Gets the eloquent type to method collection
     *
     * @return array
     */
    public function dataTypeMap()
    {
        return config('codegenerator.eloquent_type_to_method');
    }
}
