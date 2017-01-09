<?php

namespace CrestApps\CodeGenerator\Support;

use CrestApps\CodeGenerator\Support\Label;

class Field {
	
    /**
     * The name of the field
     *
     * @var string
     */
	public $name;

    /**
     * The labels of the field
     *
     * @var array
     */
	private $labels = [];
	
    /**
     * The html-type of the field
     *
     * @var string
     */
    public $htmlType = 'text';
    
    /**
     * The field options of the field
     *
     * @var array
     */
	protected $options = [];

    /**
     * The html-value of the field
     *
     * @var string
     */
    public $htmlValue = null;

    /**
     * The field's validation rules
     *
     * @var string
     */
    public $validationRules = [];

    /**
     * The is-on-index-view flag
     *
     * @var bool
     */
    public $isOnIndexView = true;

    /**
     * The is-on-show-view flag
     *
     * @var bool
     */
    public $isOnShowView = true;

    /**
     * The is-on-form-view flag
     *
     * @var bool
     */
    public $isOnFormView = true;

    /**
     * The field's data type
     *
     * @var bool
     */
    public $dataType = 'string';

    /**
     * The data-type-params
     *
     * @var array
     */
    public $methodParams = [];

    /**
     * The field's data vaue
     *
     * @var bool
     */
    public $dataValue = null;

    /**
     * Indexs this field
     *
     * @var bool
     */
    public $isIndex = false;

    /**
     * Unique indexs this field
     *
     * @var bool
     */
    public $isUnique = false;

    /**
     * Make this a primary field
     *
     * @var bool
     */
    public $isPrimary = false;

    /**
     * Added meta description to this field
     *
     * @var string
     */
    public $comment = null;

    /**
     * Make this field nullable
     *
     * @var bool
     */
    public $isNullable = false;

    /**
     * Make this field unsigned
     *
     * @var bool
     */
    public $isUnsigned = false;

    /**
     * Make this field auto-increment
     *
     * @var bool
     */
    public $isAutoIncrement = false;

    /**
     * Make this field auto-increment
     *
     * @var bool
     */
    public $isInlineOptions = false;

    /**
     * Checks if the field will result in array when a request is made
     *
     * @var bool
     */
    public $isMultipleAnswers = false;

    /**
     * Field placeholder
     *
     * @var string
     */
    public $placeHolder = '';

    /**
     * Field placeholder
     *
     * @var string
     */
    public $optionsDelimiter  = '; ';

    /**
     * The range of a selector
     *
     * @var array
     */
    public $range = [];

    /**
     * Creates a new field instance.
     *
     * @param string $name
     *
     * @return void
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * Gets the field labels
     *
     * @return array
     */
    public function getLabels()
    {
        return $this->labels;
    }
    
    /**
     * Gets a label by a giving language
     *
     * @param string $lang
     *
     * @return object
     */
    public function getLabel($lang = 'en')
    {
        if(!isset($this->labels[$lang]))
        {
            return null;
        }

        return $this->labels[$lang];
    }

    /**
     * Adds a label to the labels collection
     *
     * @param string $value
     * @param string $localeGroup
     * @param bool $isPlain
     * @param string $lang
     *
     * @return object
     */
    public function addLabel($text, $localeGroup, $isPlain = true, $lang = 'en')
    {
        $this->labels[$lang] = new Label($text, $this->getLocaleKey($localeGroup), $isPlain, $lang, $this->name);
    }

    /**
     * Adds a label to the options collection
     *
     * @param string $value
     * @param string $localeGroup
     * @param bool $isPlain
     * @param string $lang
     *
     * @return object
     */
    public function addOption($text, $localeGroup, $isPlain = true, $lang = 'en', $value)
    {
        $this->options[$lang][] = new Label($text, $this->getLocaleKey($localeGroup, $value), $isPlain, $lang, $this->getFieldId($value), $value);
    }

    /**
     * Gets a options by a giving language
     *
     * @param string $lang
     *
     * @return object
     */
    public function getOptionsByLang($lang = 'en')
    {
        $finalOptions = [];

        foreach($this->getOptions() as $options)
        {
            foreach($options as $option)
            {
                if($option->lang == $lang || $option->isPlain)
                {
                    $finalOptions[] = $option;
                }
            }
        }

        return empty($finalOptions) ? null : $finalOptions;
    }

    /**
     * Gets the options for this field
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Checks if this field is required or not.
     *
     * @return bool
     */
    public function isRequired()
    {
        return in_array('required', $this->validationRules);
    }

    /**
     * Creates locale key for a giving languagefile
     *
     * @param string $stub
     * @param string $postFix
     *
     * @return string
     */
    protected function getLocaleKey($localeGroup, $postFix = null)
    {
        return sprintf('%s.%s', $localeGroup, $this->getFieldId($postFix));
    }

    /**
     * Gets the field Id.
     *
     * @param string $optionValue
     *
     * @return string
     */
    protected function getFieldId($optionValue = null)
    {
        if(!is_null($optionValue))
        {
            return sprintf('%s_%s', $this->name, $this->cleanValue($optionValue));
        }

        return $this->name;
    }

    /**
     * It makes a string "id-ready" string
     *
     * @param string $optionValue
     *
     * @return string
     */
    protected function cleanValue($optionValue)
    {
        return Helpers::removeNonEnglishChars(strtolower(str_replace(' ', '_', $optionValue)));
    }

    /**
     * It checks whether the field is a file or not.
     *
     * @return boolean
     */
    public function isFile()
    {
        return ($this->htmlType == 'file');
    }

}