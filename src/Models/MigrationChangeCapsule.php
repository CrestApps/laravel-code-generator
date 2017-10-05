<?php

namespace CrestApps\CodeGenerator\Models;

use CrestApps\CodeGenerator\Support\Contracts\ChangeDetector;
use CrestApps\CodeGenerator\Support\Contracts\JsonWriter;

class MigrationChangeCapsule implements JsonWriter, ChangeDetector
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
     * Should timestamps be added
     *
     * @var bool
     */
    public $addTimestamps = false;

    /**
     * Should soft-delete fields be added
     *
     * @var bool
     */
    public $addSoftDelete = false;

    /**
     * Should timestamps be dropped
     *
     * @var bool
     */
    public $dropTimestamps = false;

    /**
     * Should soft-delete fields be dropped
     *
     * @var bool
     */
    public $dropSoftDelete = false;

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
            'add-timestamp' => $this->addTimestamps,
            'add-soft-delete' => $this->addSoftDelete,
            'drop-timestamp' => $this->dropTimestamps,
            'drop-soft-delete' => $this->dropSoftDelete,
        ];
    }

    /**
     * Checks if an object has change
     *
     * @return bool
     */
    public function hasChange()
    {
        return (
            count($this->getFieldsWithUpdate()) > 0
            || count($this->getIndexesWithUpdate()) > 0
            || $this->addSoftDelete
            || $this->dropSoftDelete
            || $this->addTimestamps
            || $this->dropTimestamps);
    }
}
