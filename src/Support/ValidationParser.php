<?php

namespace CrestApps\CodeGenerator\Support;

class ValidationParser
{

    /**
     * The rules to parse.
     *
     * @var array
     */
    protected $rules = [];

    /**
     * Min value
     *
     * @var Mix
     */
    protected $min;

    /**
     * Max value
     *
     * @var Mix
     */
    protected $max;

    /**
     * Required rule
     *
     * @var mix
     */
    protected $required;

    /**
     * Rule size
     *
     * @var mix
     */
    protected $size;

    /**
     * Digit rule
     *
     * @var mix
     */
    protected $digit;

    /**
     * Integer rule
     *
     * @var mix
     */
    protected $integer;

    /**
     * Nullable rule
     *
     * @var mix
     */
    protected $nullable;

    /**
     * Number rule
     *
     * @var mix
     */
    protected $number;

    /**
     * Numeric rule
     *
     * @var mix
     */
    protected $numeric;

    /**
     * Conditional requied rule
     *
     * @var mix
     */
    protected $conditionalRequired;

    /**
     * String rule
     *
     * @var mix
     */
    protected $string;

    /**
     * Creates a new parser instance.
     *
     * @param array $rules
     *
     * @return void
     */
    public function __construct(array $rules)
    {
        $this->rules = $rules;
    }

    /**
     * Gets the string min length.
     *
     * @return mix (int|string)
     */
    public function getMinLength()
    {
        return $this->isString() ? $this->getValue('min') : '';
    }

    /**
     * Gets the string max length.
     *
     * @return mix (int|string)
     */
    public function getMaxLength()
    {
        return $this->isString() ? $this->getValue('max') : '';
    }

    /**
     * Gets the numeric min value.
     *
     * @return mix (int|string)
     */
    public function getMinValue()
    {
        return $this->isValidNumber() ? $this->getValue('min') : '';
    }

    /**
     * Gets the numeric max value.
     *
     * @return mix (int|string)
     */
    public function getMaxValue()
    {
        return $this->isValidNumber() ? $this->getValue('max') : '';
    }

    /**
     * Gets the numeric min value.
     *
     * @param $range
     *
     * @return mix (int|string)
     */
    public function getValue($range)
    {
        $range = $range == 'min' ? $range : 'max';

        if (is_null($this->{$range})) {
            $this->{$range} = $this->getFirst($range);
        }

        return $this->{$range};
    }

    /**
     * Gets the size value
     *
     * @return mix (int|string)
     */
    public function getSizeValue()
    {
        if (is_null($this->size)) {
            $this->size = $this->getFirst('size');
        }

        return $this->size;
    }

    /**
     * Checks if the rules conatins a required rule
     *
     * @return bool
     */
    public function isRequired()
    {
        return $this->getSetValue('required');
    }

    /**
     * Checks if the rules digit rule
     *
     * @return bool
     */
    public function isDigit()
    {
        return $this->getSetValue('digit');
    }

    /**
     * Checks if the rules number rule
     *
     * @return bool
     */
    public function isNumber()
    {
        return $this->getSetValue('number');
    }

    /**
     * Checks if the rules numeric rule
     *
     * @return bool
     */
    public function isNumeric()
    {
        return $this->getSetValue('numeric');
    }

    /**
     * Sets a protected value and it gets its value
     *
     * @return bool
     */
    protected function getSetValue($name, $callback = null)
    {
        if (property_exists($this, $name)) {
            return $this->isRuleExists($name);
        }

        if (is_null($this->{$name})) {
            $this->{$name} = $this->isRuleExists($name);
        }

        return $this->{$name};
    }

    /**
     * Checks if the rules is string rule
     *
     * @return bool
     */
    public function isString()
    {
        return $this->getSetValue('string') && !$this->isValidNumber();
    }

    /**
     * Checks if the rules is valid numer
     *
     * @return bool
     */
    public function isValidNumber()
    {
        return $this->isDigit() || $this->isInteger() || $this->isNumeric();
    }

    /**
     * Checks if the rules has a conditional required
     *
     * @return bool
     */
    public function isConditionalRequired()
    {
        if (is_null($this->conditionalRequired)) {
            $this->conditionalRequired = $this->startsWith('required_');
        }

        return $this->conditionalRequired;
    }

    /**
     * Checks if the rules is nullable
     *
     * @return bool
     */
    public function isNullable()
    {
        if (is_null($this->nullable)) {
            $this->nullable = $this->isRuleExists('nullable');
        }

        return $this->nullable;
    }

    /**
     * Checks if the rules is interger.
     *
     * @return bool
     */
    public function isInteger()
    {
        if (is_null($this->integer)) {
            $this->integer = $this->isRuleExists('integer');
        }

        return $this->integer;
    }

    /**
     * Checks if a given rule is required
     *
     * @param string $name
     *
     * @return bool
     */
    public function isRuleExists($name)
    {
        return in_array($name, $this->rules);
    }

    /**
     * Gets the first found key in the rules
     *
     * @param string $key
     *
     * @return bool
     */
    protected function getFirst($key)
    {
        foreach ($this->rules as $rule) {
            if (substr($rule, 0, strlen($key)) == $key) {
                $params = explode(':', $rule, 2);
                if (isset($params[1])) {
                    return intval($params[1]);
                }
            }
        }

        return '';
    }

    /**
     * Checks if any rule starts with a given key
     *
     * @param string $key
     *
     * @return bool
     */
    protected function startsWith($key)
    {
        foreach ($this->rules as $rule) {
            if (starts_with($rule, $key)) {
                return true;
            }
        }

        return false;
    }
}
