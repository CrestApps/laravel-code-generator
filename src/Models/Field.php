<?php

namespace CrestApps\CodeGenerator\Models;

use CrestApps\CodeGenerator\Models\Label;
use CrestApps\CodeGenerator\Support\Helpers;
use CrestApps\CodeGenerator\Models\ForeignRelationship;

class Field
{
    
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
     * @var CrestApps\CodeGenerator\Models\ForeignRelationship
     */
    private $foreignRelations;

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
        if (!isset($this->labels[$lang])) {
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
     * Sets the foreign relationship of the field.
     *
     * @param CrestApps\CodeGenerator\Models\ForeignRelationship $relation
     *
     * @return void
     */
    public function setForeignRelation(ForeignRelationship $relation = null)
    {
        $this->foreignRelations = $relation;
    }

    /**
     * Gets the field's foreign relationship.
     *
     * @return CrestApps\CodeGenerator\Models\ForeignRelationship
     */
    public function getForeignRelation()
    {
        return $this->foreignRelations;
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

    /**
     * Returns current object into an array.
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'name' => $this->name,
            'labels' => $this->labelsToRaw($this->getLabels()),
            'html-type' => $this->htmlType,
            'options' => $this->optionsToRaw($this->getOptions()),
            'html-value' => $this->htmlValue,
            'validation' => implode('|', $this->validationRules),
            'is-on-index' => $this->isOnIndexView,
            'is-on-show' => $this->isOnShowView,
            'is-on-form' => $this->isOnFormView,
            'data-type' => $this->dataType,
            'data-type-params' => $this->methodParams,
            'data-value' => $this->dataValue,
            'is-index' => $this->isIndex,
            'is-unique' => $this->isUnique,
            'is-primary' => $this->isPrimary,
            'comment' => $this->comment,
            'is-nullable' => $this->isNullable,
            'is-unsigned' => $this->isUnsigned,
            'is-auto-increment' => $this->isAutoIncrement,
            'is-inline-options' => $this->isInlineOptions,
            'is-multiple-answers' => $this->isMultipleAnswers,
            'placeholder' => $this->placeHolder,
            'delimiter' => $this->optionsDelimiter,
            'range' => $this->range,
            'foreign-relations' => $this->getForeignRelationToRaw()
        ];
    }

    public function getForeignRelationToRaw()
    {
        $relation = $this->getForeignRelation();
        if (!is_null($relation)) {
            return [
                        'relation-name' => $relation->name,
                        'type' => $relation->type,
                        'params' => $relation->parameters,
                        'foreign-name' => $relation->foreignColumns
                   ];
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
     * Checks if the data type is datetime.
     *
     * @return bool
     */
    public function isDateTime()
    {
        return in_array($this->dataType, ['dateTime','dateTimeTz']) || in_array($this->name, ['created_at','updated_at','deleted_at']);
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
     * Checks if the field's type is any valid date.
     *
     * @return bool
     */
    public function isDateOrTime()
    {
        return $this->isDate() || $this->isDateTime() || $this->isTime();
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

        $options = $this->getOptionsByLang();

        if (isset($options[1])) {
            return $options[1];
        }

        if (isset($options[0])) {
            return $options[0];
        }

        return new Label('Yes', $this->getLocaleKey(''), true, 'en', $this->name, $this->getFieldId(1), 1);
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

        $options = $this->getOptionsByLang();

        if (isset($options[0])) {
            return $options[0];
        }

        return new Label('No', $this->getLocaleKey(''), true, 'en', $this->name, $this->getFieldId(0), 0);
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
        $final = [];

        foreach ($options as $lang => $labels) {
            $finalWithTranslations = [];
            foreach ($labels as $label) {
                if ($label->isPlain) {
                    $final[$label->value] = $label->text;
                } else {
                    $finalWithTranslations[$label->value][$label->lang] = $label->text;
                }
            }

            foreach ($finalWithTranslations as $value => $finalWithTranslation) {
                $final[$value] = (object) $finalWithTranslation;
            }
        }

        return (object) $final;
    }
}
