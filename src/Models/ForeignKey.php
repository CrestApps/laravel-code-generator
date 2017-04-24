<?php

namespace CrestApps\CodeGenerator\Models;

class ForeignKey
{
    /**
     * The name of the foreign column.
     *
     * @var string
     */
    public $column;

    /**
     * The name of the column being referenced on the foreign model.
     *
     * @var string
     */
    public $references;
    
    /**
     * The name of the foreign model being referenced.
     *
     * @var string
     */
    public $on;
    
    /**
     * The action to take when the model is updated.
     *
     * @var string
     */
    public $onUpdate;

    /**
     * The action to take when the model is deleted.
     *
     * @var string
     */
    public $onDelete;

    /**
     * Creates a new field instance.
     *
     * @param string $name
     *
     * @return void
     */
    public function __construct($column, $references, $on, $onUpdate = null, $onDelete = null)
    {
        $this->column = $column;
        $this->references = $references;
        $this->on = $on;
        $this->onUpdate = $onUpdate;
        $this->onDelete = $onDelete;
    }
}
