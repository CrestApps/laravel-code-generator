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

    /**
     * Checks if the resource is empty.
     *
     * @return bool
     */
    public function isEmpty()
    {
        return empty($this->fields) && empty($this->relations) && empty($this->indexes);
    }

    /**
     * Converts the object into a json-ready array
     *
     * @return array
     */
    public function toArray()
    {
        return
        [
            'fields'    => $this->getFieldsToArray(),
            'relations' => $this->getRelationsToArray(),
            'indexes'   => $this->getIndexesToArray()
        ];
    }

    /**
     * Converts the fields into a json-ready array
     *
     * @return array
     */
    public function getFieldsToArray()
    {
        return array_map(function ($field) {
            return $field->toArray();
        }, $this->fields);
    }

    /**
     * Converts the relations into a json-ready array
     *
     * @return array
     */
    public function getRelationsToArray()
    {
        return array_map(function ($relation) {
            return $relation->toArray();
        }, $this->relations);
    }

    /**
     * Converts the indexes into a json-ready array
     *
     * @return array
     */
    public function getIndexesToArray()
    {
        return array_map(function ($index) {
            return $index->toArray();
        }, $this->indexes);
    }
}
