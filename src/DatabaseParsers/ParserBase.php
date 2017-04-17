<?php
namespace CrestApps\CodeGenerator\DatabaseParsers;

use DB;
use App;
use Exception;
use CrestApps\CodeGenerator\Models\Field;
use CrestApps\CodeGenerator\Models\FieldMapper;
use CrestApps\CodeGenerator\Support\FieldsOptimizer;

abstract class ParserBase
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
     * The langugaes to create labels form.
     *
     * @var array
     */
    protected $langugaes;

    /**
     * Creates a new field instance.
     *
     * @param string $tableName
     * @param string $databaseName
     * @param array $langugaes
     *
     * @return void
     */
    public function __construct($tableName, $databaseName, array $languages = [])
    {
        $this->tableName = $tableName;
        $this->databaseName = $databaseName;
        $this->languages = $languages;
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
     * Gets array of field after transfering each column meta into field.
     *
     * @param array $columns
     *
     * @return array
    */
    protected function transfer(array $columns)
    {
        $fields = array_map(function ($field) {
            return new FieldMapper($field);
        }, $this->getTransfredFields($columns));

        $optimizer = new FieldsOptimizer($fields);

        return $optimizer->optimize()->getFields();
    }

    /**
     * Set the unsiged flag for a giving field.
     *
     * @param CrestApps\CodeGenerator\Models\Field $field
     * @param string $type
     *
     * @return $this
    */
    protected function setUnsigned(Field & $field, $type)
    {
        if (strpos($type, 'unsigned') !== false) {
            $field->isUnsigned = true;
            $field->validationRules[] = sprintf('min:%s', 0);
        }

        return $this;
    }

    /**
     * Set the html type for a giving field.
     *
     * @param CrestApps\CodeGenerator\Models\Field $field
     * @param string $type
     *
     * @return $this
    */
    protected function setHtmlType(Field & $field, $type)
    {
        $map = $this->getMap();

        if (array_key_exists($type, $map)) {
            $field->htmlType = $map[$type];
        }

        return $this;
    }

    /**
     * Set the data type for a giving field.
     *
     * @param CrestApps\CodeGenerator\Models\Field $field
     * @param string $type
     *
     * @return $this
    */
    protected function setDataType(Field & $field, $type)
    {
        $map = $this->dataTypeMap();

        if (array_key_exists($type, $map)) {
            $field->dataType = $map[$type];
        }

        return $this;
    }

    /**
     * Sets the nullable property for a giving field.
     *
     * @param CrestApps\CodeGenerator\Models\Field $field
     * @param string $nullable
     *
     * @return $this
    */
    protected function setIsNullable(Field & $field, $nullable)
    {
        $field->isNullable = (strtoupper($nullable) == 'YES');

        return $this;
    }

    /**
     * Sets the max length property for a giving field.
     *
     * @param CrestApps\CodeGenerator\Models\Field $field
     * @param string $length
     *
     * @return $this
    */
    protected function setMaxLength(Field & $field, $length)
    {
        if (($value = intval($length)) > 0) {
            $field->validationRules[] = sprintf('max:%s', $value);
            $field->methodParams[] = $value;
        }

        return $this;
    }

    /**
     * Sets the data value property for a giving field.
     *
     * @param CrestApps\CodeGenerator\Models\Field $field
     * @param string $default
     *
     * @return $this
    */
    protected function setDefault(Field & $field, $default)
    {
        if (!empty($default)) {
            $field->dataValue = $default;
        }

        return $this;
    }

    /**
     * Sets the labels for a giving field.
     *
     * @param CrestApps\CodeGenerator\Models\Field $field
     * @param string $label
     *
     * @return $this
    */
    protected function setLabel(Field & $field, $label)
    {
        if (empty($this->languages)) {
            $field->addLabel($label, $this->tableName, true, $this->locale);
            return $this;
        }
        
        foreach ($this->languages as $language) {
            $field->addLabel($label, $this->tableName, false, $language);
        }

        return $this;
    }

    /**
     * Adds the labels for a giving field.
     *
     * @param CrestApps\CodeGenerator\Models\Field $field
     * @param array $names
     * @param string $table
     * @param bool $isPlain
     * @param string $locale
     *
     * @return $this
    */
    protected function addLabels(Field & $field, array $names, $table, $isPlain, $locale)
    {
        foreach ($names as $name) {
            $field->addLabel($name, $table, $isPlain, $locale);
        }
        
        return $this;
    }

    /**
     * Sets the keys for a giving field.
     *
     * @param CrestApps\CodeGenerator\Models\Field $field
     * @param string $key
     * @param string $extra
     *
     * @return $this
    */
    protected function setKey(Field & $field, $key, $extra)
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
     * Gets a labe field's label from a giving name.
     *
     * @return string
    */
    protected function getLabelName($name)
    {
        return trim(ucwords(str_replace(['-','_'], ' ', $name)));
    }

    /**
     * Gets the eloquent's method to html
     *
     * @return array
    */
    protected function getMap()
    {
        return config('codegenerator.eloquent_type_to_html_type');
    }

    /**
     * Gets the eloquent's type to method collection.
     *
     * @return array
    */
    protected function dataTypeMap()
    {
        return config('codegenerator.eloquent_type_to_method');
    }

    /**
     * Sets the comment's property for a giving field.
     *
     * @param CrestApps\CodeGenerator\Models\Field $field
     * @param string $comment
     *
     * @return $this
    */
    protected function setComment(Field & $field, $comment)
    {
        if (!empty($comment)) {
            $field->comment = $comment;
        }

        return $this;
    }

    /**
     * Gets column meta info from the information schema.
     *
     * @return array
    */
    abstract protected function getColumn();

    /**
     * Transfers every column in the giving array to a collection of fields.
     *
     * @return array of CrestApps\CodeGenerator\Models\Field;
    */
    abstract protected function getTransfredFields(array $columns);
}
