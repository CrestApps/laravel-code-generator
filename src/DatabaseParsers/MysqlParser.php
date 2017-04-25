<?php
namespace CrestApps\CodeGenerator\DatabaseParsers;

use DB;
use CrestApps\CodeGenerator\DatabaseParsers\ParserBase;
use CrestApps\CodeGenerator\Models\Field;

class MysqlParser extends ParserBase
{

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
                 ->setIsOnViews($field);

            $fields[] = $field;
        }

        return $fields;
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
            return $this->addOptions($field, [
                '0' => 'No',
                '1' => 'Yes'
            ]);
        }

        if (($options = $this->getOptions($column->COLUMN_TYPE)) !== null) {
            return $this->addOptions($field, $options);
        }

        return $this;
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
        if (empty($this->languages)) {
            return $this->addOptionsFor($field, $options, true, $this->locale);
        }

        foreach ($this->languages as $language) {
            $this->addOptionsFor($field, $options, false, $language);
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
            if ($field->dataType != 'boolean') {
                $value = $option;
            }

            $field->addOption($this->getLabelName($option), $this->tableName, $isPlain, $locale, $value);
        }

        return $this;
    }

    /**
     * Sets the property visibility status for the giving field.
     *
     * @param CrestApps\CodeGenerator\Models\Field $field
     *
     * @return $this
    */
    protected function setIsOnViews(Field & $field)
    {
        if (in_array($field->name, ['created_at','updated_at','deleted_at','id'])) {
            $field->isOnIndexView = false;
            $field->isOnShowView = false;
            $field->isOnFormView = false;
        }

        if ($field->htmlType == 'textarea') {
            $field->isOnIndexView = false;
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

        return !isset($match[1]) ? null : array_map(function ($option) {
            return trim($option, "'");
        }, explode(',', $match[1]));
    }
}
