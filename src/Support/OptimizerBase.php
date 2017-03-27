<?php

namespace CrestApps\CodeGenerator\Support;

use CrestApps\CodeGenerator\Models\Field;

abstract class OptimizerBase
{

    /**
     * It checks if a giving field is a primary or not.
     * 
     * @param CrestApps\CodeGenerator\Models\Field $field
     * @return bool
    */
    protected function isPrimaryField(Field $field)
    {
        return (in_array($field->name, $this->getCommonIdNames()) || $field->isAutoIncrement || $field->isPrimary);
    }

    /**
     * Gets the common name to use for headers
     * 
     * @return array
    */
    protected function getCommonHeadersNames()
    {
        return config('codegenerator.common_header_names') ?: [];
    }

    /**
     * Gets the common name to use for headers
     * 
     * @return array
    */
    protected function getCommonIdNames()
    {
        return config('codegenerator.common_id_names') ?: ['id'];
    }
}
