<?php

namespace CrestApps\CodeGenerator\Models;

use CrestApps\CodeGenerator\Support\Contracts\JsonWriter;

class MigrationChangeCapsule implements JsonWriter
{
    /**
     * Collection of the field changes
     *
     * @var array
     */
    public $fieldChanges = [];

    /**
     * collection of index changes
     *
     * @var string
     */
    public $indexChanges = [];

    /**
     * Create a new migration change capsule instance.
     *
     * @param array $fieldChanges
     * @param array $indexChanges
     *
     * @return void
     */
    public function __construct($fieldChanges = [], $indexChanges = [])
    {
        $this->fieldChanges = $fieldChanges;
        $this->indexChanges = $indexChanges;
    }

    /**
     * Get the fields with update
     *
     * @return array
     */
    public function getFieldsWithUpdate()
    {
        return array_filter($this->fieldChanges, function ($fieldChange) {
            return $fieldChange->hasChange();
        });
    }

    /**
     * Get the indexes with update
     *
     * @return array
     */
    public function getIndexesWithUpdate()
    {
        return array_filter($this->indexChanges, function ($indexChange) {
            return $indexChange->hasChange();
        });
    }

    /**
     * Gets array of the paramets
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'field-changes' => $this->fieldChanges,
            'index-changes' => $this->indexChanges,
        ];
    }
}
