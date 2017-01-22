<?php
namespace CrestApps\CodeGenerator\DatabaseParser;

use CrestApps\CodeGenerator\DatabaseParser\ParserBase;
use CrestApps\CodeGenerator\Support\Field;
use DB;

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
		                  ,DATA_TYPE
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
     * @return CrestApps\CodeGenerator\Support\Field;
    */
	protected function getTransfredField($column)
	{
		$field = new Field($column->COLUMN_NAME);

		$this->setIsNullable($field, $column->IS_NULLABLE)
			 ->setMaxLength($field, $column->CHARACTER_MAXIMUM_LENGTH)
			 ->setDefault($field, $column->COLUMN_DEFAULT)
			 ->setDataType($field, $column->DATA_TYPE)
			 ->setKey($field,$column->COLUMN_KEY, $column->EXTRA)
			 ->setLabel($field, $column->COLUMN_NAME)
			 ->setComment($field, $column->COLUMN_COMMENT)
			 ->setOptions($field, $column->COLUMN_TYPE)
			 ->setUnsigned($field, $column->COLUMN_TYPE)
			 ->setHtmlType($field, $column->DATA_TYPE);

		return $field;
	}

    /**
     * Set the options for a giving field.
     *
     * @param CrestApps\CodeGenerator\Support\Field $field
     * @param string $type
     *
     * @return $this
    */
	protected function setOptions(Field & $field, $type)
	{
		if( ($options = $this->getOptions($type)) !== null )
		{
			foreach($options as $option)
			{
				$field->addOption($this->getLabelName($option), $this->tableName, true, $this->locale, $option);
			}
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

		return !isset($match[1]) ? null : array_map(function($option){
												return trim($option, "'");
											}, explode(',', $match[1]));
	}

}