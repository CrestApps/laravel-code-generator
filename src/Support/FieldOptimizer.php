<?php

namespace CrestApps\CodeGenerator\Support;
use CrestApps\CodeGenerator\Models\Field;
use CrestApps\CodeGenerator\Support\ValidationParser;

class FieldOptimizer {
	
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
             ->optimizePrimaryKey();

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

        if( empty($this->field->methodParams) && in_array($this->field->dataType, ['string','char']) )
        {
            if( !empty($this->parser->getMaxLength()) )
            {
                $this->field->methodParams[] = $this->parser->getMaxLength();
            }
            
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

        if( !$this->parser->isRequired() || $this->parser->isNullable() || $this->parser->isConditionalRequired() )
        {
            $this->field->isNullable = true;
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
        if( $this->isPrimaryField())
        {
            if(!$this->isNumericField())
            {
                $this->field->dataType = 'int';
            }

            if($this->meta == null)
            {
                $this->field->isOnFormView = false;
                $this->field->isOnIndexView = false;
                $this->field->isOnShowView = false;

                return $this;
            }

            if(!array_key_exists('is-on-views', $this->meta))
            {
                if(!array_key_exists('is-on-form', $this->meta))
                {
                    $this->field->isOnFormView = false;
                }

                if(!array_key_exists('is-on-index', $this->meta))
                {
                    $this->field->isOnIndexView = false;
                }

                if(!array_key_exists('is-on-show', $this->meta))
                {
                    $this->field->isOnShowView = false;
                }
            }
            
        }

        return $this;
    }

    /**
     * It checks if a giving field is a primary or not.
     * 
     * @return bool
    */
    protected function isPrimaryField()
    {
        return ($this->field->name == 'id' || $this->field->isAutoIncrement || $this->field->isPrimary);
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