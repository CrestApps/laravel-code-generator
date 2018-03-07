<?php

namespace CrestApps\CodeGenerator\Models\Bases;

use CrestApps\CodeGenerator\Support\Contracts\JsonWriter;

class MigrationChangeBase
{
    /**
     * Is this a new field
     *
     * @var bool
     */
    public $isAdded = false;

    /**
     * Is this field flagged to be deleted
     *
     * @var bool
     */
    public $isDeleted = false;

    /**
     * Get a raw property using the given property name
     *
     * @return mix (null | array)
     */
    protected function getRawProperty($name)
    {
        if (property_exists($this, 'name')) {
            if (!is_null($this->{$name}) && $this->{$name} instanceof JsonWriter) {
                return $this->{$name}->toArray();
            }
        }

        return null;
    }
}
