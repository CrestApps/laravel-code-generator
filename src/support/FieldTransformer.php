<?php

namespace CrestApps\CodeGenerator\Support;

use Exception;
use App;
use CrestApps\CodeGenerator\Support\Helpers;

class FieldTransformer {

    /**
     * The raw field before transformation
     *
     * @var array
     */
    protected $rawFields = [];

    /**
     * The field after transformation
     *
     * @var array
     */
	protected $fields = [];

    /**
     * The name of the file where labels will reside
     *
     * @var string
     */
	protected $langFileName;

    /**
     * Mapps the user input to a valid property name in the field object
     * 
     * @return array
    */
    protected $predefinedKeyMapping = 
    [
        'html-type' => 'htmlType',
        'html-value' => 'htmlValue',
        'value' => ['dataValue','htmlValue'],
        'is-on-views' => ['isOnIndexView','isOnFormView','isOnShowView'],
        'is-on-index' => 'isOnIndexView',
        'is-on-form' => 'isOnFormView',
        'is-on-show' => 'isOnShowView',
        'data-type' => 'dataType',
        'data-value' => 'dataValue',
        'is-primary' => 'isPrimary',
        'is-index' => 'isIndex',
        'is-unique' => 'isUnique',
        'comment' => 'comment',
        'is-nullable' => 'isNullable',
        'is-unsigned' => 'isUnsigned',
        'is-auto-increment' => 'isAutoIncrement'
    ];

    /**
     * Array of the valid primary key data-types
     * 
     * @return array
    */
    protected $validPrimaryDataTypes = 
    [
        'int',
        'integer',
        'bigint',
        'biginteger',
        'mediumint',
        'mediuminteger',
        'uuid'
    ];

    /**
     * The apps default language
     *
     * @var string
     */
    protected $defaultLang;

    /**
     * Create a new transformer instance.
     *
     * @return void
     */
	protected function __construct($fields, $langFileName)
	{
        if( empty($langFileName))
        {
            throw new Exception("\$langFileName must have a valid value");
        }

        $this->rawFields = is_array($fields) ? $fields : $this->parseRawString($fields);
        $this->langFileName = $langFileName;
        $this->defaultLang = App::getLocale();
	}

    /**
     * It transfred a gining string to a collection of field
     * 
     * @param string $fieldsString
     * @param string $langFileName
     *
     * @return array Support\Field
    */
    public static function Text($fieldsString, $langFileName)
    {
        $transformer = new self($fieldsString, $langFileName);

        return $transformer->transfer()->getFields();
    }

    /**
     * It transfres a gining string to a collection of field
     * 
     * @param string|json $json
     * @param string $langFileName
     *
     * @return array Support\Field
    */
    public static function Json($json, $langFileName)
    {
        if( empty($json) || ($fields = json_decode($json, true)) === null )
        {
            throw new Exception("The provided string is not a valid json.");
        }

        $transformer = new self($fields, $langFileName);

        return $transformer->transfer()->getFields();
    }

    /**
     * It transfres the raw fields into Fields by setting the $this->fields array
     * 
     * @return $this
    */
    protected function transfer()
    {
        $finalFields = [];
        $assignedPrimary = false;
        foreach($this->rawFields as $rawField)
        {
            $finalFields[] = $this->transferField($rawField, $assignedPrimary);
        }

        $this->fields = $finalFields;

        return $this;
    }

    /**
     * It transfres a giving array to a field object by matching predefined keys
     * 
     * @param string|json $json
     * @param string $langFileName
     *
     * @return array Support\Field
    */
    protected function transferField(array $field, & $assignedPrimary)
    {
        
        if(!array_key_exists('name', $field) || empty(Helpers::removeNonEnglishChars($field['name']) ) )
        {
            throw new Exception("The field 'name' was not provided!");
        }

        $newField = new Field();

        $newField->name = Helpers::removeNonEnglishChars($field['name']);

        $this->setPredefindProperties($newField, $field)
             ->setOptionsProperty($newField, $field)
             ->setValidationProperty($newField, $field)
             ->setLabelsProperty($newField, $field)
             ->optimizePrimaryKey($newField);

        return $newField;
    }

    /**
     * If the property name is "id" or if the field is primary or autoincrement.
     * Ensure, the datatype is set to be valid otherwise make it "int"
     * 
     * @param Field $newField
     *
     * @return $this
    */
    protected function optimizePrimaryKey(Field & $newField)
    {
        if( $this->isPrimaryField($newField))
        {
            $newField->dataType = 'int';
        }

        return $this;
    }

    /**
     * It checks if a giving field is a primary or not.
     * 
     * @param Field $newField
     *
     * @return bool
    */
    protected function isPrimaryField(Field & $newField)
    {
        return ($newField->name == 'id' || $newField->isAutoIncrement || $newField->isPrimary) && !in_array($newField->dataType, $this->validPrimaryDataTypes);
    }

    /**
     * It set the labels property for a giving field
     * 
     * @param Field $newField
     * @param array $field
     *
     * @return $this
    */
    protected function setLabelsProperty(Field & $newField, array $field)
    {
        $labels = $this->getLabels($field);

        foreach($labels as $label)
        {
            $newField->addLabel($label->value, $this->langFileName, $label->isPlain, $label->lang);
        }

        return $this;
    }

    /**
     * It set the validationRules property for a giving field
     * 
     * @param Field $newField
     * @param array $field
     *
     * @return $this
    */
    protected function setValidationProperty(Field & $newField, array $field)
    {
        if(array_key_exists('validation', $field))
        {
            $newField->validationRules = is_array($field['validation']) ? $field['validation'] : Helpers::removeEmptyItems(explode('|', $field['validation']));
        }

        return $this;
    }

    /**
     * It set the options property for a giving field
     * 
     * @param Field $newField
     * @param array $field
     *
     * @return $this
    */
    protected function setOptionsProperty(Field & $newField, array $field)
    {
        if(array_key_exists('options', $field))
        {
            $newField->options = is_array($field['options']) ? $field['options'] : $this->parseOptions($field['options']);
        }

        return $this;
    }

    /**
     * It set the predefined property for a giving field.
     * it uses the predefinedKeyMapping array
     * 
     * @param Field $newField
     * @param array $field
     *
     * @return $this
    */
    protected function setPredefindProperties(Field & $newField, array $field)
    {
        foreach($this->predefinedKeyMapping as $key => $property)
        {

            if(array_key_exists($key, $field) )
            {
                if(is_array($property))
                {
                    foreach($property as $name)
                    {
                        $newField->{$name} = $field[$key];
                    }
                } else 
                {
                    $newField->{$property} = $field[$key];
                }
            }
        }

        return $this;
    }

    /**
     * It get the fields collection
     *
     * @return array
    */
	protected function getFields()
	{
		return $this->fields;
	}

    /**
     * It get the labels from a giving array
     * 
     * @param array $items
     *
     * @return $this
    */
    protected function getLabelsFromArray(array $items)
    {
        $labels = [];

        foreach($items as $key => $label)
        {
            $lang = empty($key) || is_numeric($key) ? $this->defaultLang : $key;

            $labels[] = $this->getLabelObject($label, false, $lang);
        }

        return $labels;
    }

    /**
     * It returns a label object from the giving properties
     * 
     * @param string $value
     * @param bool $isPlain
     * @param string $lang
     *
     * @return object
    */
    protected function getLabelObject($value, $isPlain, $lang)
    {
        return (object) [ 
                            'value' => $value,
                            'isPlain' => $isPlain,
                            'lang' => $lang
                        ];
    }

    /**
     * It will get the provided labels from with the $field's 'label' or 'labels' property
     * 
     * @param array $field
     *
     * @return array
    */
    protected function getLabels(array $field)
    {

        if( isset($field['labels']) && is_array($field['labels']))
        {  
            //At this point we know the is array of labels
            return $this->getLabelsFromArray($field['labels']);
        }

        if( isset($field['label']))
        {
            if(is_array($field['label']))
            {  
                //At this point we know this the label
                return $this->getLabelsFromArray($field['label']);
            }

            return [ $this->getLabelObject($field['label'], true, $this->defaultLang) ];
        }

        $labels = $this->getLabelsFromRawProperties($field);

        if(!isset($labels[0]) && isset($field['name']))
        {
            //At this point we know there are no labels found, generate one use the name
            return [$this->getLabelObject($this->convertNameToLabel($field['name']), true, $this->defaultLang)];
        }

        return $labels;
    }

    /**
     * It will get the provided labels from with the $field's label property
     * it will convert the following format "en|ar:label=Some Label" or "label=Some Label" to an array
     * 
     * @param array $field
     *
     * @return array
    */
    protected function getLabelsFromRawProperties(array $field)
    {
        $labels = [];

        foreach($field as $key => $label)
        {
            
            if(!in_array($key, ['labels','label']))
            {
                continue;
            }

            $messages = Helpers::removeEmptyItems(explode('|', $label));

            foreach($messages as $message)
            {
                $index = strpos($message, ':');

                if($index !== false)
                {
                    $labels[] = $this->getLabelObject(substr($message, $index + 1), false, substr($message, 0, $index));
                } else 
                {
                    $labels[] = $this->getLabelObject($message, true, $this->defaultLang);
                }
            }
        }

        return $labels;
    }

    /**
     * Parses a giving string and turns it into a valid array
     * 
     * @param string $optionsString
     *
     * @return array
    */
	protected function parseOptions($optionsString)
	{
		$options = Helpers::removeEmptyItems(explode('|', $optionsString));

		$finalOptions = [];

		foreach($options as $option)
		{
            $index = strpos(':', $option);

            if($index !== false)
            {
                $finalOptions[substr($option, 0, $index)] = substr($option, $index + 1);
            } else 
            {
                $finalOptions[$option] = $option;
            }
		}

		return $finalOptions;
	}

    /**
     * Parses giving string and turns it into a valid array
     * 
     * @param string $fieldsString
     *
     * @return array
    */
	protected function parseRawString($fieldsString)
	{
        if(empty($fieldsString))
        {
            return [];
        }
        
        //name=some_name;is-on-show;is-on-form;is-on-index=false;label=This is a crapy title#name=title;
    	$fields = explode('#', $fieldsString);
    	$finalFields = [];

    	foreach($fields as $field)
    	{
    		$configs = [];
    		$properties = Helpers::removeEmptyItems(explode(';', $field));

    		foreach($properties as $property)
    		{
    			$config = Helpers::removeEmptyItems(explode('=', $property));
                $totalParts = count($config);
            
                if($totalParts == 2)
                {
        			$configs[$config[0]] = $this->isProperyBool($config[0]) ? Helpers::stringToBool($config[1]): $config[1];
                } 
                elseif($totalParts == 1 && $this->isProperyBool($config[0]))
                {
                    $configs[$config[0]] = true;
                }
    		}

    		$finalFields[] = $configs;
    	}

    	return $finalFields;
	}

    protected function isProperyBool($str, $startsWith = 'is')
    {
        return Helpers::startsWith($str, $startsWith);
    }


    /**
     * Gets a label from a giving name
     * 
     * @param string $name
     *
     * @return string
    */
    public function convertNameToLabel($name)
    {
        return ucwords(str_replace('_', ' ', $name));
    }

}