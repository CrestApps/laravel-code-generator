<?php

namespace CrestApps\CodeGenerator\Support;

use Exception;
use App;
use CrestApps\CodeGenerator\Support\Helpers;
use CrestApps\CodeGenerator\Support\FieldsOptimizer;
use CrestApps\CodeGenerator\Models\Field;
use CrestApps\CodeGenerator\Models\FieldMapper;

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
	protected $localeGroup;

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
        'data-value' => 'dataValue',
        'is-primary' => 'isPrimary',
        'is-index' => 'isIndex',
        'is-unique' => 'isUnique',
        'comment' => 'comment',
        'is-nullable' => 'isNullable',
        'is-unsigned' => 'isUnsigned',
        'is-auto-increment' => 'isAutoIncrement',
        'is-inline-options' => 'isInlineOptions',
        'placeholder' => 'placeHolder',
        'place-holder' => 'placeHolder',
        'delimiter' => 'optionsDelimiter',
        'is-header' => 'isHeader'
    ];



    /**
     * Array of the valid html-types
     * 
     * @return array
    */
    protected $validHtmlTypes = [
        'text',
        'password',
        'email',
        'file',
        'checkbox',
        'radio',
        'number',
        'date',
        'select',
        'multipleSelect',
        'textarea',
        'selectMonth'
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
	protected function __construct($fields, $localeGroup)
	{

        if( empty($localeGroup))
        {
            throw new Exception("$localeGroup must have a valid value");
        }

        $this->rawFields = is_array($fields) ? $fields : $this->parseRawString($fields);
        $this->localeGroup = $localeGroup;
        $this->defaultLang = App::getLocale();
	}

    /**
     * It transfred a gining string to a collection of field
     * 
     * @param string $fieldsString
     * @param string $localeGroup
     *
     * @return array Support\Field
    */
    public static function Text($fieldsString, $localeGroup)
    {
        $transformer = new self($fieldsString, $localeGroup);

        return $transformer->transfer()->getFields();
    }

    /**
     * It transfres a gining string to a collection of field
     * 
     * @param string|json $json
     * @param string $localeGroup
     *
     * @return array
    */
    public static function Json($json, $localeGroup)
    {
        if( empty($json) || ($fields = json_decode($json, true)) === null )
        {
            throw new Exception("The provided string is not a valid json.");
        }

        $transformer = new self($fields, $localeGroup);

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

        foreach($this->rawFields as $rawField)
        {
            $finalFields[] = $this->transferField($rawField);
        }

        $optimizer = new FieldsOptimizer($finalFields);
        $this->fields = $optimizer->optimize()->getFields();

        return $this;
    }

    /**
     * It transfres a giving array to a field object by matching predefined keys
     * 
     * @param array $field
     * @param string $localeGroup
     *
     * @return array
    */
    protected function transferField(array $field)
    {
        if(!$this->isKeyExists($field, 'name') || empty(Helpers::removeNonEnglishChars($field['name']) ) )
        {
            throw new Exception("The field 'name' was not provided!");
        }

        if(!$this->isValidHtmlType($field))
        {
            unset($field['html-type']);
        }

        $newField = new Field(Helpers::removeNonEnglishChars($field['name']));

        $this->setPredefindProperties($newField, $field)
             ->setDataType($newField, $field)
             ->setOptionsProperty($newField, $field)
             ->setValidationProperty($newField, $field)
             ->setLabelsProperty($newField, $field)
             ->setDataTypeParams($newField, $field)
             ->setMultipleAnswers($newField, $field)
             ->setRange($newField, $field);

        if($this->isValidSelectRangeType($field))
        {
            $newField->htmlType = 'selectRange';
        }

        if($newField->dataType == 'enum' && empty($newField->getOptions()) )
        {
            throw new Exception('To construct an enum data-type field, options must be set');
        }

        return new FieldMapper($newField, $field);
    }

   /**
     * Checks if a field contains a valid html-type name
     * 
     * @param array $field
     *
     * @return bool
    */
    protected function isValidHtmlType(array $field)
    {
        return $this->isKeyExists($field, 'html-type') && 
        ( 
             in_array($field['html-type'], $this->validHtmlTypes)
          || $this->isValidSelectRangeType($field)
        );
    }

   /**
     * Checks if a field contains a valid "selectRange" html-type element.
     * 
     * @param array $field
     *
     * @return bool
    */
    protected function isValidSelectRangeType(array $field)
    {
        return $this->isKeyExists($field, 'html-type') && Helpers::startsWith($field['html-type'], 'selectRange|');
    }

   /**
     * Checks if a key exists in a giving array
     * 
     * @param array $field
     * @param string $name
     *
     * @return bool
    */
    protected function isKeyExists(array $field, $name)
    {
        return array_key_exists($name, $field);
    }

    /**
     * Sets the dataType for a giving field
     * 
     * @param CrestApps\CodeGenerator\Models\Field $newField
     * @param array $field
     *
     * @return $this
    */
    protected function setDataType(Field & $newField, array $field)
    {
        $map = $this->dataTypeMap();

        if($this->isKeyExists($field, 'data-type') && $this->isKeyExists($map, 'data-type') )
        {
            $newField->dataType = $map[$field['data-type']];
        }

        return $this;
    }

    /**
     * Sets the range for a giving field
     * 
     * @param CrestApps\CodeGenerator\Models\Field $newField
     * @param array $field
     *
     * @return $this
    */
    protected function setRange(Field & $newField, array $field)
    {
        if($this->isValidSelectRangeType($field))
        {
            $newField->range = explode(':', substr($field['html-type'], 12));
        }

        return $this;
    }

    /**
     * Sets the DataTypeParam for a giving field
     * 
     * @param CrestApps\CodeGenerator\Models\Field $newField
     * @param array $field
     *
     * @return $this
    */
    protected function setDataTypeParams(Field & $newField, array $field)
    {
        if($this->isKeyExists($field, 'data-type-params') && is_array($field['data-type-params']))
        {
            $newField->methodParams = $field['data-type-params'];
        }

        return $this;
    }

    /**
     * Sets the isMultipleAnswers for a giving field
     * 
     * @param CrestApps\CodeGenerator\Models\Field $newField
     *
     * @return $this
    */
    protected function setMultipleAnswers(Field & $newField, array $field)
    {

        if(in_array($newField->htmlType, ['checkbox','multipleSelect']))
        {
            $newField->isMultipleAnswers = true;
        }

        return $this;
    }

    /**
     * It set the labels property for a giving field
     * 
     * @param CrestApps\CodeGenerator\Models\Field $newField
     * @param array $field
     *
     * @return $this
    */
    protected function setLabelsProperty(Field & $newField, array $field)
    {
        $labels = $this->getLabels($field);

        foreach($labels as $label)
        {   
            $newField->addLabel($label->text, $this->localeGroup, $label->isPlain, $label->lang);
        }

        return $this;
    }

    /**
     * It set the validationRules property for a giving field
     * 
     * @param CrestApps\CodeGenerator\Models\Field $newField
     * @param array $field
     *
     * @return $this
    */
    protected function setValidationProperty(Field & $newField, array $field)
    {
        if($this->isKeyExists($field, 'validation'))
        {
            $newField->validationRules = is_array($field['validation']) ? $field['validation'] : Helpers::removeEmptyItems(explode('|', $field['validation']));
        }

        return $this;
    }

    /**
     * It set the options property for a giving field
     * 
     * @param CrestApps\CodeGenerator\Models\Field $newField
     * @param array $field
     *
     * @return $this
    */
    protected function setOptionsProperty(Field & $newField, array $field)
    {
        $options = $this->getOptions($field);

        if( !is_null($options))
        {
            foreach($options as $option)
            {
                $newField->addOption($option->text, $this->localeGroup, $option->isPlain, $option->lang, $option->value);
            }
        }

        return $this;
    }

    /**
     * Gets the options from a giving field
     * 
     * @param array $field
     *
     * @return array|null
    */
    protected function getOptions(array $field)
    {
        if(!$this->isKeyExists($field, 'options'))
        {
            return null;
        }

        return is_array($field['options']) ? $this->transferOptionsToLabels($field['options']) : $this->parseOptions($field['options']);
    }

    /**
     * Transfers options array to array on Labels
     * 
     * @param array $options
     *
     * @return array
    */
    protected function transferOptionsToLabels(array $options)
    {
        $finalOptions = [];

        $associative = Helpers::isAssociative($options);
        
        foreach($options as $value => $option)
        {
            $value = $associative ? $value : $option;

            if(!is_array($option))
            {
                // At this point the options are plain text without locale
                $finalOptions[] = $this->getLabelObject($option, true, $this->defaultLang, $value);
                continue;
            }

            foreach($option as $optionValue => $text)
            {
                // At this point the options are in array which mean they need translation.
                $lang = is_numeric($optionValue) || empty($optionValue) ? $this->defaultLang : $optionValue;
                $finalOptions[] = $this->getLabelObject($text, false, $lang, $value);
            }

        }

        return $finalOptions;
    }

    /**
     * It set the predefined property for a giving field.
     * it uses the predefinedKeyMapping array
     * 
     * @param CrestApps\CodeGenerator\Models\Field $newField
     * @param array $field
     *
     * @return $this
    */
    protected function setPredefindProperties(Field & $newField, array $field)
    {
        foreach($this->predefinedKeyMapping as $key => $property)
        {
            if($this->isKeyExists($field, $key) )
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
    protected function getLabelObject($text, $isPlain, $lang, $value = null)
    {
        return (object) [
                'text' => $text,
                'isPlain' => $isPlain,
                'lang' => $lang,
                'value' => $value
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
                $finalOptions[] = $this->getLabelObject(substr($option, $index + 1), true, $this->defaultLang, substr($option, 0, $index));
            } else 
            {
                $finalOptions[] = $this->getLabelObject($option, true, $this->defaultLang, null);
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
        
    	$fields = explode('#', $fieldsString);
    	$finalFields = [];

    	foreach($fields as $field)
    	{
    		$configs = $this->getPropertyConfig(Helpers::removeEmptyItems(explode(';', $field)));

            if(!empty($configs))
            {
                $finalFields[] = $configs;
            }
    	}

    	return $finalFields;
	}

    /**
     * Parses the properties array
     * 
     * @param string $properties
     *
     * @return array
    */
    protected function getPropertyConfig(array $properties)
    {
        $configs = [];
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

        return $configs;
    }

    /**
     * Checks if a string starts with the word "is"
     * 
     * @param string $str
     *
     * @return bool
    */
    protected function isProperyBool($str)
    {
        return Helpers::startsWith($str, 'is');
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

    /**
     * Gets the eloquent type to methof collection
     *
     * @return array
    */
    public function dataTypeMap()
    {
        return config('codegenerator.eloquent_type_to_method');
    }

}