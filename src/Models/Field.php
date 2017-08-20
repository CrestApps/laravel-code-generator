<?php

namespace CrestApps\CodeGenerator\Models;

use App;
use CrestApps\CodeGenerator\Models\Label;
use CrestApps\CodeGenerator\Support\Helpers;
use CrestApps\CodeGenerator\Models\ForeignRelationhip;
use CrestApps\CodeGenerator\Support\Config;

class Field
{
    /**
     * Fields that are auto managed by Laravel on update.
     *
     * @var array
     */
    protected $autoManagedFieldsOnUpdate = [
        'created_at',
        'updated_at',
    ];

    /**
     * Fields that are auto managed by Laravel on delete.
     *
     * @var array
     */
    protected $autoManagedFieldsOnDelete = [
        'deleted_at',
    ];

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
     * Makes the field bahaves as a header.
     *
     * @var bool
     */
    public $isHeader = false;

    /**
     * The placeholders for the fields.
     *
     * @var array
     */
    private $placeholders = [];

    /**
     * Field options delimiter.
     *
     * @var string
     */
    public $optionsDelimiter  = '; ';

    /**
     * Additional css cssClass to add to the field's input.
     *
     * @var string
     */
    public $cssClass  = '';

    /**
     * Defines the datetime display format for a date field.
     *
     * @var string
     */
    public $dateFormat  = '';

    /**
     * The range of a selector
     *
     * @var array
     */
    public $range = [];

    /**
     * Checks if the field should be mutated to date
     *
     * @var bool
     */
    public $isDate = false;

    /**
     * The foreign relations
     *
     * @var CrestApps\CodeGenerator\Models\ForeignRelationhip
     */
    private $foreignRelation;

    /**
     * The foreign Constraint.
     *
     * @var CrestApps\CodeGenerator\Models\ForeignConstraint
     */
    private $foreignConstraint;

    /**
     * The app's default language.
     *
     * @var string
     */
    protected $defaultLang;

    /**
     * raw php command to execute when the model is created.
     *
     * @var string
     */
    public $onStore;

    /**
     * raw php command to execute when the model is updated.
     *
     * @var string
     */
    public $onUpdate;

    /**
     * Field placeholder
     *
     * @var string
     */
    public $castAs = '';

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
        $this->defaultLang = App::getLocale();
    }

    /**
     * Gets the field labels.
     *
     * @return array
     */
    public function getLabels()
    {
        return $this->labels;
    }

    /**
     * Gets the field placeholders.
     *
     * @return array
     */
    public function getPlaceholders()
    {
        return $this->placeholders;
    }
    
    /**
     * Gets the languages available in the labels.
     *
     * @return array
     */
    public function getAvailableLanguages()
    {
        $langs = [];

        foreach($this->getLabels() as $label){
            if(!$label->isPlain) {
                $langs[] = $label->lang;
            }
        }

        foreach($this->getOptions() as $labels){
            foreach($labels as $label) {
                if(!$label->isPlain) {
                    $langs[] = $label->lang;
                }
            }
        }

        return array_unique($langs);
    }

    /**
     * Gets a label by a giving language
     *
     * @param string $lang
     *
     * @return CrestApps\CodeGenerator\Models\Label
     */
    public function getLabel($lang = null)
    {
        $lang = empty($lang) ? $this->getDefaultLanguage() : $lang;
        
        if (!isset($this->labels[$lang])) {
            return $this->getFirstLabel();
        }

        return $this->labels[$lang];
    }

    /**
     * Gets a label by a giving language
     *
     * @param string $lang
     *
     * @return CrestApps\CodeGenerator\Models\Label
     */
    public function getPlaceholder($lang = null)
    {
        $lang = empty($lang) ? $this->getDefaultLanguage() : $lang;

        if (!isset($this->placeholders[$lang])) {
            return $this->getFirstPlaceholder();
        }

        return $this->placeholders[$lang];
    }

    /**
     * Gets the app's default language.
     *
     * @return string
     */
    public function getDefaultLanguage()
    {
        return $this->defaultLang;
    }

    /**
     * Gets the first available label if any.
     *
     * @return CrestApps\CodeGenerator\Models\Label
     */
    public function getFirstLabel()
    {
        return current($this->labels);
    }

    /**
     * Gets the first available placeholder if any.
     *
     * @return CrestApps\CodeGenerator\Models\Label | null
     */
    public function getFirstPlaceholder()
    {
        $first = current($this->placeholders);

        return ($first !== false) ? $first : null;
    }

    /**
     * Checks if this field is auto managed by eloquent.
     *
     * @return bool
     */
    public function isAutoManaged()
    {
        return $this->isAutoManagedOnUpdate() || $this->isAutoManagedOnDelete();
    }

    /**
     * Checks if this field is auto managed by eloquent on update event.
     *
     * @return bool
     */
    public function isAutoManagedOnUpdate()
    {
        return in_array($this->name, $this->autoManagedFieldsOnUpdate);
    }

    /**
     * Checks if this field is auto managed by eloquent on delete event.
     *
     * @return bool
     */
    public function isAutoManagedOnDelete()
    {
        return in_array($this->name, $this->autoManagedFieldsOnDelete);
    }

    /**
     * Adds a label to the labels collection
     *
     * @param string $text
     * @param string $localeGroup
     * @param bool $isPlain
     * @param string $lang
     *
     * @return void
     */
    public function addLabel($text, $localeGroup, $isPlain = true, $lang = 'en')
    {
        $this->labels[$lang] = new Label($text, $this->getLocaleKey($localeGroup), $isPlain, $lang, $this->name);
    }

    /**
     * Adds a label to the placeholders collection
     *
     * @param string $text
     * @param string $localeGroup
     * @param bool $isPlain
     * @param string $lang
     *
     * @return void
     */
    public function addPlaceholder($text, $localeGroup, $isPlain = true, $lang = 'en')
    {
        $value = '__placeholder';
        $localKey = $this->getLocaleKey($localeGroup, '_placeholder');

        $this->placeholders[$lang] = new Label($text, $localKey, $isPlain, $lang, $this->name . $value);
    }

    /**
     * Sets the foreign relationship of the field.
     *
     * @param CrestApps\CodeGenerator\Models\ForeignRelationship $relation
     *
     * @return void
     */
    public function setForeignRelation(ForeignRelationship $relation = null)
    {
        $this->foreignRelation = $relation;
    }

    /**
     * Sets the foreign key of the field.
     *
     * @param CrestApps\CodeGenerator\Models\ForeignConstraint $foreignConstraint
     *
     * @return void
     */
    public function setForeignConstraint(ForeignConstraint $constraint = null)
    {
        $this->foreignConstraint = $constraint;
    }

    /**
     * Gets the field's foreign relationship.
     *
     * @return CrestApps\CodeGenerator\Models\ForeignRelationhip
     */
    public function getForeignRelation()
    {
        return $this->foreignRelation;
    }

    /**
     * Gets the field's foreign key.
     *
     * @return CrestApps\CodeGenerator\Models\ForeignConstraint
     */
    public function getForeignConstraint()
    {
        return $this->foreignConstraint;
    }

    /**
     * Checks if the field has a foreign relation.
     *
     * @return bool
     */
    public function hasForeignRelation()
    {
        return ! is_null($this->foreignRelation);
    }

    /**
     * Checks if the field shoudl be casted.
     *
     * @return bool
     */
    public function isCastable()
    {
        return ! empty($this->castAs);
    }

    /**
     * Checks if the field has a foreign relation.
     *
     * @return bool
     */
    public function hasForeignConstraint()
    {
        return ! is_null($this->foreignConstraint);
    }
    /**
     * Checks if the field is on a giving view.
     *
     * @return bool
     */
    public function isOnView($view)
    {
        $view = ucfirst(strtolower($view));

        if (in_array($view, ['Form','Index','Show'])) {
            return $this->{'isOn' . $view . 'View'};
        }

        return false;
    }

    /**
     * Adds a label to the options collection
     *
     * @param string $value
     * @param string $localeGroup
     * @param bool $isPlain
     * @param string $lang
     * @param string $value
     *
     * @return object
     */
    public function addOption($text, $localeGroup, $isPlain = true, $lang = 'en', $value = null)
    {
        $localKey = $this->getLocaleKey($localeGroup, $value);
        $id = $this->getFieldId($value);
        
        $this->options[$lang][] = new Label($text, $localKey, $isPlain, $lang, $id, $value);
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

        foreach ($this->getOptions() as $options) {
            foreach ($options as $option) {
                if ($option->lang == $lang || $option->isPlain) {
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
     * Checks if this field is boolean type.
     *
     * @return bool
     */
    public function isBoolean()
    {
        return $this->dataType == 'boolean';
    }

    /**
     * Checks if this field's html is checkbox
     *
     * @return bool
     */
    public function isCheckbox()
    {
        return $this->htmlType == 'checkbox';
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
        if (!is_null($optionValue)) {
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
        $value = strtolower(str_replace(' ', '_', $optionValue));
        
        return Helpers::removeNonEnglishChars($value);
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

    /**
     * Returns current object into an array.
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'name'               => $this->name,
            'labels'             => $this->labelsToRaw($this->getLabels()),
            'html-type'          => $this->htmlType,
            'css-class'          => $this->cssClass,
            'options'            => $this->optionsToRaw($this->getOptions()),
            'html-value'         => $this->htmlValue,
            'validation'         => implode('|', $this->validationRules),
            'is-on-index'        => $this->isOnIndexView,
            'is-on-show'         => $this->isOnShowView,
            'is-on-form'         => $this->isOnFormView,
            'data-type'          => $this->getRawDataType(),
            'data-type-params'   => $this->methodParams,
            'data-value'         => $this->dataValue,
            'is-index'           => $this->isIndex,
            'is-unique'          => $this->isUnique,
            'is-primary'         => $this->isPrimary,
            'comment'            => $this->comment,
            'is-nullable'        => $this->isNullable,
            'is-header'          => $this->isHeader,
            'is-unsigned'        => $this->isUnsigned,
            'is-auto-increment'  => $this->isAutoIncrement,
            'is-inline-options'  => $this->isInlineOptions,
            'is-date'            => $this->isDate,
            'date-format'        => $this->dateFormat,
            'cast-as'            => $this->castAs,
            'placeholder'        => $this->labelsToRaw($this->getPlaceholders()),
            'delimiter'          => $this->optionsDelimiter,
            'range'              => $this->range,
            'foreign-relation'   => $this->getForeignRelationToRaw(),
            'foreign-constraint' => $this->getForeignConstraintToRaw(),
            'on-store'           => $this->onStore,
            'on-update'          => $this->onUpdate,
        ];
    }

    /**
     * Gets the data type in a raw format.
     *
     * @return string
     */
    protected function getRawDataType()
    {
        $type = array_search($this->dataType, Config::dataTypeMap());

        return $type !== false ? $type : $this->dataType;
    }

    /**
     * Gets a relation properties.
     *
     * @return array | null
     */
    public function getForeignRelationToRaw()
    {
        if ($this->hasForeignRelation()) {
            return $this->getForeignRelation()->toArray();
        }

        return null;
    }

    /**
     * Gets a foreign key to a raw format.
     *
     * @return array | null
     */
    public function getForeignConstraintToRaw()
    {
        if ($this->hasForeignConstraint()) {
            return $this->getForeignConstraint()->toArray();
        }

        return null;
    }
    /**
     * Returns current object into proper json format.
     *
     * @return string
     */
    public function toJson()
    {
        return json_encode($this->toArray());
    }

    /**
     * Checks if the field is the primary.
     *
     * @return bool
     */
    public function isPrimary()
    {
        return $this->isAutoIncrement || $this->isPrimary;
    }

    /**
     * Checks if the data type is datetime.
     *
     * @return bool
     */
    public function isDateTime()
    {
        return     in_array($this->dataType, ['dateTime','dateTimeTz'])
                || in_array($this->name, ['created_at','updated_at','deleted_at']);
    }

    /**
     * Checks if the data type is date.
     *
     * @return bool
     */
    public function isDate()
    {
        return $this->dataType == 'date';
    }

    /**
     * Checks if the data type is time.
     *
     * @return bool
     */
    public function isTime()
    {
        return in_array($this->dataType, ['time','timeTz']);
    }

    /**
     * Checks if the data type is numeric.
     *
     * @return bool
     */
    public function isNumeric()
    {
        return $this->isDecimal() || in_array($this->dataType, ['bigIncrements','bigInteger','increments','integer','mediumIncrements','mediumInteger','smallIncrements','smallInteger','tinyInteger','unsignedBigInteger','unsignedInteger','unsignedMediumInteger','unsignedSmallInteger','unsignedTinyInteger']);
    }

    /**
     * Checks if the data type string
     *
     * @return bool
     */
    public function isString()
    {
        return in_array($this->dataType, ['char','string']);
    }

    /**
     * Checks if the data type is time stamp.
     *
     * @return bool
     */
    public function isTimeStamp()
    {
        return in_array($this->dataType, ['timestamp','timestampTz']);
    }

    /**
     * Checks if the field's type is any valid date.
     *
     * @return bool
     */
    public function isDateOrTime()
    {
        return $this->isDate() || $this->isDateTime() || $this->isTime() || $this->isTimeStamp();
    }

    /**
     * Gets date validation rule for a datetime field.
     *
     * @return mix (null | string)
     */
    public function getDateValidationRule()
    {
        if ($this->isDateOrTime()) {
            if (!empty($this->dateFormat)) {
                return sprintf('date_format:%s', $this->dateFormat);
            }

            return 'date';
        }

        return null;
    }
    
    /**
     * Checks if the data type contains decimal.
     *
     * @return bool
     */
    public function isDecimal()
    {
        return in_array($this->dataType, ['float','decimal','double']);
    }

    /**
     * Gets the true label for the boolean field.
     *
     * @return mix(null | restApps\CodeGenerator\Models\Label)
     */
    public function getTrueBooleanOption()
    {
        if (!$this->isBoolean()) {
            return null;
        }

        $options = $this->getOptionsByLang($this->defaultLang);

        if (isset($options[1])) {
            return $options[1];
        }

        if (isset($options[0])) {
            return $options[0];
        }

        $label = new Label('Yes', $this->getLocaleKey(''), true, $this->defaultLang, $this->getFieldId(1), 1);

        return $label;
    }

    /**
     * Gets the false label for the boolean field.
     *
     * @return mix(null | restApps\CodeGenerator\Models\Label)
     */
    public function getFalseBooleanOption()
    {
        if (!$this->isBoolean()) {
            return null;
        }

        $options = $this->getOptionsByLang($this->defaultLang);

        if (isset($options[0])) {
            return $options[0];
        }

        return new Label('No', $this->getLocaleKey(''), true, 'en', $this->getFieldId(0), 0);
    }

    /**
     * Get the total decimal point
     *
     * @return int
     */
    public function getDecimalPointLength()
    {
        if ($this->isDecimal() && ! is_null($value = $this->getMethodParam(1))) {
            return $value / ($value * 100);
        }

        return 0;
    }

    /**
     * Gets the Min length a field.
     *
     * @return int|null
     */
    public function getMinLength()
    {
        if($this->isRequired() || !$this->isNullable) {
            return 1;
        }

        return 0;
    }

    /**
     * Gets the Min length a field.
     *
     * @return int|null
     */
    public function getMaxLength()
    {
        if($this->isString() && isset($this->methodParams[0])) {
            return intval($this->methodParams[0]);
        }

        return null;
    }

    /**
     * Gets the maximum value a field can equal.
     *
     * @return int|float|null
     */
    public function getMaxValue()
    {
        if (! $this->isNumeric()) {
            return null;
        }

        if ($this->isDecimal()) {
            $length = $this->getMethodParam(0) ?: 1;
            $declimal = $this->getMethodParam(1) ?: 0;
            $max = str_repeat('9', $length);

            if ($declimal > 0) {
                $max = substr_replace($max, '.', $declimal * -1, 0);
            }
            $max = floatval($max);
        } elseif ($this->dataType == 'integer') {
            $max = $this->isUnsigned ? 4294967295 : 2147483647;
        } elseif ($this->dataType == 'mediumInteger') {
            $max = $this->isUnsigned ? 16777215 : 8388607;
        } elseif ($this->dataType == 'smallInteger') {
            $max = $this->isUnsigned ? 65535 : 32767;
        } elseif ($this->dataType == 'tinyInteger') {
            $max = $this->isUnsigned ? 255 : 127;
        } elseif ($this->dataType == 'bigInteger') {
            $max = $this->isUnsigned ? 18446744073709551615 : 9223372036854775807;
        }

        return $max;
    }

    /**
     * Gets the minimum value a field can equal.
     *
     * @return int|float|null
     */
    public function getMinValue()
    {
        if ($this->isUnsigned) {
            return 0;
        }

        if (! is_null($value = $this->getMaxValue())) {
            return ($value * -1) - ($this->isDecimal() ? 0: 1);
        }

        return null;
    }

    /**
     * Gets method's parameter for a giving index.
     *
     * @return mix (int|null)
     */
    protected function getMethodParam($index)
    {
        if (isset($this->methodParams[$index]) && ($value = intval($this->methodParams[$index])) > 0) {
            return $value;
        }

        return null;
    }

    /**
     * Gets current labels in a raw format.
     *
     * @return mix (string|object)
     */
    protected function labelsToRaw(array $labels)
    {
        $final = [];

        foreach ($labels as $label) {
            if ($label->isPlain) {
                return $label->text;
            }

            $final[$label->lang] = $label->text;
        }

        return (object) $final;
    }

    /**
     * Gets current Options into a raw format.
     *
     * @return object
     */
    protected function optionsToRaw(array $options)
    {
        $collections = [];

        foreach ($options as $lang => $labels) {
            $finalWithTranslations = [];
            foreach ($labels as $label) {
                if ($label->isPlain) {
                    $collections[$label->value] = $label->text;
                } else {
                    $collections[$label->lang][$label->value] = $label->text;
                }
            }
        }
        
        $finals = [];

        foreach ($collections as $value => $collection) {
            $finals[$value] = is_array($collection) ? (object) $collection : $collection;
        }

        return (object) $finals;
    }
}
