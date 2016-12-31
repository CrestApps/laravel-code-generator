<?php

namespace CrestApps\CodeGenerator\Support;

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
	public $options = [];

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
    public $dataType = 'varchar';

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
     * The data-type-params
     *
     * @var array
     */
    public $methodParams = [];

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
     * Add a label to the labels collection
     *
     * @param string $value
     * @param string $langFile
     * @param bool $isPlain
     * @param string $lang
     *
     * @return object
     */
    public function addLabel($value, $langFile, $isPlain = true, $lang = 'en')
    {
        $this->labels[$lang] = (object) [
                                            'value' => $value,
                                            'isPlain' => $isPlain,
                                            'langKey' => $this->getLocaleKey($langFile),
                                            'lang' => $lang
                                        ];
    }

    /**
     * Creates locale key for a giving languagefile
     *
     * @param string $stub
     *
     * @return string
     */
    protected function getLocaleKey($langFile)
    {
        return sprintf('%s.%s', $langFile, $this->name);
    }

}