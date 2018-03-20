<?php

namespace CrestApps\CodeGenerator\Models;

use CrestApps\CodeGenerator\Models\Bases\MigrationChangeBase;
use CrestApps\CodeGenerator\Support\Contracts\ChangeDetector;
use CrestApps\CodeGenerator\Support\Contracts\JsonWriter;

class FieldMigrationChange extends MigrationChangeBase implements JsonWriter, ChangeDetector
{
    /**
     * The field to be deleted or added
     *
     * @var CrestApps\CodeGenerator\Models\Field
     */
    public $field;

    /**
     * The field to be changed from
     *
     * @var CrestApps\CodeGenerator\Models\Field
     */
    public $fromField;

    /**
     * The field to be changed to
     *
     * @var CrestApps\CodeGenerator\Models\Field
     */
    public $toField;

    /**
     * Create a new field migration change instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Check whether or not the object has change value
     *
     * @return bool
     */

    public function hasChange()
    {
        foreach ($this as $key => $value) {
            if ($this->isAdded || $this->isDeleted) {
                return true;
            }

            if (!is_null($value) && $value instanceof ChangeDetector && $value->hasChange()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check whether or not the fromField and the toField has values
     *
     * @return bool
     */
    protected function hasFromToFields()
    {
        return !is_null($this->fromField) && !is_null($this->toField);
    }

    /**
     * Check whether or not the field has a name change
     *
     * @return bool
     */
    public function isRenamed()
    {
        if ($this->hasFromToFields() && $this->fromField->name !== $this->toField->name) {
            return true;
        }

        return false;
    }

    /**
     * Get new migration change from the given field
     *
     * @param CrestApps\CodeGenerator\Models\Field $field
     *
     * @return CrestApps\CodeGenerator\Models\FieldMigrationChange
     */
    public static function getAdded(Field $field)
    {
        $change = new FieldMigrationChange();
        $change->field = $field;
        $change->isAdded = true;

        return $change;
    }

    /**
     * Get new migration change from the given field
     *
     * @param CrestApps\CodeGenerator\Models\Field $field
     *
     * @return CrestApps\CodeGenerator\Models\FieldMigrationChange
     */
    public static function getDeleted(Field $field)
    {
        $change = new FieldMigrationChange();

        $change->isDeleted = true;
        $change->field = $field;

        return $change;
    }
    /**
     * Get the migration change after comparing two given fields
     *
     * @param CrestApps\CodeGenerator\Models\Field $fieldA
     * @param CrestApps\CodeGenerator\Models\Field $fieldB
     *
     * @return CrestApps\CodeGenerator\Models\FieldMigrationChange
     */
    public static function compare(Field $fieldA, Field $fieldB)
    {
        $change = new FieldMigrationChange();
        $change->fromField = $fieldA;
        $change->toField = $fieldB;

        return $change;
    }

    /**
     * Return current object as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'field' => $this->getRawProperty('field'),
            'from-field' => $this->getRawProperty('fromField'),
            'to-field' => $this->getRawProperty('toField'),
            'is-deleted' => $this->isDeleted,
            'is-added' => $this->isAdded,
        ];
    }
}
