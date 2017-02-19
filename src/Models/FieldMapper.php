<?php

namespace CrestApps\CodeGenerator\Models;
use CrestApps\CodeGenerator\Models\Field;

class FieldMapper 
{
    /**
     * The field to optimize
     *
     * @var array of CrestApps\CodeGenerator\Models\Field
     */
	public $field;

    /**
     * Create a new optimizer instance.
     *
     * @var array
     */
    public $meta;

    public function __construct(Field $field, array $meta = null)
    {
    	$this->field = $field;
    	$this->meta = $meta;
    }
}