<?php

namespace CrestApps\CodeGenerator\Models;

class Resource
{
    /**
     * The resource fields
     *
     * @var array
     */
    public $fields;

    /**
     * The resource relations
     *
     * @var array
     */
    public $relations;

    /**
     * The resource indexes
     *
     * @var array
     */
    public $indexes;

    /**
     * Create a new input instance.
     *
     * @return void
     */
    public function __construct(array $fields = [], array $relations = [], array $indexes = [])
    {
        $this->fields = $fields;
        $this->relations = $relations;
        $this->indexes = $indexes;
    }
}
