<?php

namespace CrestApps\CodeGenerator\Support;

use CrestApps\CodeGenerator\Models\Field;
use CrestApps\CodeGenerator\Support\ValidationParser;
use CrestApps\CodeGenerator\Traits\CommonCommand;
use CrestApps\CodeGenerator\Support\Config;

class FieldOptimizer
{
    use CommonCommand;
    
    /**
     * The field to optimize
     *
     * @var CrestApps\CodeGenerator\Models\Field
     */
    protected $field;
    
    /**
     * The validation parser instance.
     *
     * @var void
     */
    protected $parser;

    /**
     * Create a new optimizer instance.
     *
     * @var array
     */
    protected $meta;

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


    public function __construct(Field $field, array $meta = null)
    {
        $this->field = $field;
        $this->parser = new ValidationParser($field->validationRules);
        $this->meta = $meta;
    }

    /**
     * Gets the optimized field.
     *
     * @return CrestApps\CodeGenerator\Models\Field
     */
    public function getField()
    {
        return $this->field;
    }


    public function optimize()
    {
        $this->optimizeStringField()
             ->optimizeRequiredField()
             ->optimizeDateFields()
             ->optimizeBoolean()
             ->optimizePrimaryKey()
             ->optimizeValidations()
             ->optimizeHtmlType()
             ->addPlaceHolder();

        return $this;
    }

    /**
     * If the "data-type-params" is not set, and the dataType is string,
     * yet the validation rules has a max value, create data-type-parameter
     *
     *
     * @return $this
    */
    protected function optimizeStringField()
    {
        if (empty($this->field->methodParams) && in_array($this->field->dataType, ['string','char']) && ! empty($this->parser->getMaxLength())) {
            $this->field->methodParams[] = $this->parser->getMaxLength();
        }

        return $this;
    }

    /**
     * If the field is not required, well make it nullable
     *
     * @return $this
    */
    protected function optimizeRequiredField()
    {
        if ($this->parser->isNullable() || !$this->parser->isRequired() || $this->parser->isConditionalRequired()) {
            $this->field->isNullable = true;
        }

        return $this;
    }

    /**
     * If the field is date, datetime or time, set the output format.
     *
     * @return $this
    */
    protected function optimizeDateFields()
    {
        if (empty($this->field->dateFormat) && $this->field->isDateOrTime()) {
            $this->field->dateFormat = 'm/d/Y H:i A';
        }

        return $this;
    }

    /**
     * If the field has a relation and the placeholder is missing, add generic one.
     *
     * @return $this
    */
    protected function addPlaceHolder()
    {
        if(empty($this->field->placeHolder) && $this->field->hasForeignRelation()) {
            $this->field->placeHolder = 'Please select a ' . $this->field->getForeignRelation()->name;
        }

        return $this;
    }

    /**
     * If the field is not visible on the form view, clear the validation.
     *
     * @return $this
    */
    protected function optimizeValidations()
    {
        if ($this->field->isCheckBox() && $this->field->isBoolean()) {
            // At this point we know the field is a check book and is a boolean type
            // remove the required validation rule.
            $this->field->validationRules = array_filter($this->field->validationRules, function ($rule) {
                return $rule != 'required';
            });
        }

        if (! $this->field->isOnFormView) {
            // At this point we know the field is not going to be on any request form
            // remove all validation rules if any exists.
            $this->field->validationRules = [];
        }

        return $this;
    }

    /**
     * If the field is not visible on the form view, clear the validation.
     *
     * @return $this
    */
    protected function optimizeHtmlType()
    {
        if ($this->field->hasForeignRelation()) {
            // At this point we know the field has a foreign relation
            // set the htmlType to select since the user will have to select an item(s)
            // from a colelction
            $this->field->htmlType = 'select';
        }

        return $this;
    }

    /**
     * If the data-type is boolean, make the field boolean as well.
     *
     * @return $this
    */
    protected function optimizeBoolean()
    {
        if ($this->field->dataType == 'boolean') {
            $this->field->isBoolean = true;
            $this->isMultipleAnswers = false;
        }

        return $this;
    }


    /**
     * If the property name is "id" or if the field is primary or autoincrement.
     * Ensure, the datatype is set to be valid otherwise make it "int".
     * It also make sure the primary column does not appears on the views unless it specified
     *
     * @param CrestApps\CodeGenerator\Models\Field $this->field
     *
     * @return $this
    */
    protected function optimizePrimaryKey()
    {
        if ($this->field->isPrimary() || in_array($this->field->name, Config::getIdPatterns())) {
            if (!$this->isNumericField()) {
                $this->field->dataType = 'int';
            }

            if ($this->meta == null) {
                $this->field->isOnFormView = false;
                $this->field->isOnIndexView = false;
                $this->field->isOnShowView = false;

                return $this;
            }

            if (!array_key_exists('is-on-views', $this->meta)) {
                if (!array_key_exists('is-on-form', $this->meta)) {
                    $this->field->isOnFormView = false;
                }

                if (!array_key_exists('is-on-index', $this->meta)) {
                    $this->field->isOnIndexView = false;
                }

                if (!array_key_exists('is-on-show', $this->meta)) {
                    $this->field->isOnShowView = false;
                }
            }
        }

        return $this;
    }

    /**
     * It checks if the field is numeric type
     *
     * @return bool
    */
    protected function isNumericField()
    {
        return in_array($this->field->dataType, $this->validPrimaryDataTypes);
    }
}
