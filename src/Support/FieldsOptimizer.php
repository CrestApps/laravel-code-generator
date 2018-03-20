<?php

namespace CrestApps\CodeGenerator\Support;

use CrestApps\CodeGenerator\Models\Field;
use CrestApps\CodeGenerator\Support\Config;
use CrestApps\CodeGenerator\Support\Str;

class FieldsOptimizer
{
    /**
     * The field mappers.
     *
     * @var array
     */
    protected $mappers;

    /**
     * The optimized fields.
     *
     * @var array
     */
    protected $fields = [];

    /**
     * Create a new optemizer instance.
     *
     * @param array $mappers
     *
     * @return void
     */
    public function __construct(array $mappers)
    {
        $this->mappers = $mappers;
    }

    /**
     * Gets the optimized fields.
     *
     * @return CrestApps\CodeGenerator\Models\Field
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * Optemizes the fields.
     *
     * @return void
     */
    public function optimize()
    {
        $mappers = $this->mappers;

        $this->assignPrimaryKey($mappers)
            ->assignPrimaryTitle($mappers);

        foreach ($mappers as $mapper) {
            $optimizer = new FieldOptimizer($mapper->field, $mapper->meta);

            $this->addField($optimizer->optimize()->getField());
        }

        return $this;
    }

    /**
     * Adds field to the fields collection.
     *
     * @param CrestApps\CodeGenerator\Model\Field $field
     *
     * @return void
     */
    protected function addField(Field $field)
    {
        $this->fields[] = $field;
    }

    /**
     * Assigned and fix the primary key field of the given collection.
     *
     * @param array $mappers
     *
     * @return $this
     */
    protected function assignPrimaryKey(array &$mappers)
    {
        $foundPrimary = false;

        foreach ($mappers as $mapper) {
            if ($foundPrimary) {
                $mapper->field->isPrimary = false;
                $mapper->field->isAutoIncrement = false;
                continue;
            }

            if ($mapper->field->isPrimary()) {
                $mapper->field->isPrimary = true;
                $foundPrimary = true;
            }
        }

        return $this;
    }

    /**
     * given a mappers collection, it'll update the the field by making one as primary title
     *
     * @param CrestApps\CodeGenerator\Models\Field $field
     * @return bool
     */
    protected function assignPrimaryTitle(array &$mappers)
    {
        $fieldsWithHeader = array_filter($mappers, function ($mapper) {
            return $mapper->field->isHeader;
        });

        if (count($fieldsWithHeader) == 0) {
            $found = false;

            foreach ($mappers as $mapper) {
                if ($found) {
                    $mapper->field->isHeader = false;
                    continue;
                }

                if ($this->isPrimaryHeader($mapper->field) && (!array_key_exists('is-header', $mapper->meta) || $mapper->meta['is-header'])) {
                    $found = true;
                    $mapper->field->isHeader = true;
                }
            }
        }

        return $this;
    }

    /**
     * It checks if a given field is a primary or not.
     *
     * @param CrestApps\CodeGenerator\Models\Field $field
     * @return bool
     */
    protected function isPrimaryHeader(Field $field)
    {
        return $field->isHeader || Str::match(Config::getHeadersPatterns(), $field->name);
    }
}
