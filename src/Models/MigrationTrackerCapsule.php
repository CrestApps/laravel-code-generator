<?php

namespace CrestApps\CodeGenerator\Models;

use CrestApps\CodeGenerator\Models\FieldMigrationChange;
use CrestApps\CodeGenerator\Models\IndexMigrationChange;
use CrestApps\CodeGenerator\Models\MigrationCapsule;
use CrestApps\CodeGenerator\Models\MigrationChangeCapsule;
use CrestApps\CodeGenerator\Models\Resource;
use CrestApps\CodeGenerator\Support\Contracts\JsonWriter;
use File;

class MigrationTrackerCapsule implements JsonWriter
{
    /**
     * The provided modelName
     *
     * @var string
     */
    public $modelName;

    /**
     * The provided field's file name
     *
     * @var string
     */
    public $resourceFile;

    /**
     * The table name
     *
     * @var string
     */
    public $tableName;

    /**
     * The table name
     *
     * @var array
     */
    private $migrations = [];

    /**
     * Create a new input instance.
     *
     * @return void
     */
    public function __construct(array $properties = [])
    {
        if (!isset($properties['table-name']) || empty($properties['table-name'])) {
            throw new Eexception('The table-name is required to construct a migration capsule!');
        }

        $this->tableName = $properties['table-name'];

        if (isset($properties['model-name'])) {
            $this->modelName = $properties['model-name'];
        }

        if (isset($properties['resource-file'])) {
            $this->resourceFile = $properties['resource-file'];
        }

        if (isset($properties['migrations']) && is_array($properties['migrations'])) {
            $this->addMigrations($properties['migrations']);
        }
    }

    /**
     * Adds array of properties to the migrations collection.
     *
     * @return void
     */
    public function addMigrations(array $properties)
    {
        foreach ($properties as $property) {
            $this->addMigration(new MigrationCapsule($property));
        }
    }

    /**
     * Adds a migration capsule the migrations collection.
     *
     * @return void
     */
    public function addMigration(MigrationCapsule $capsule)
    {
        $this->migrations[] = $capsule;
    }

    /**
     * Get the difference between a givin resource and the
     * resource in the current migration
     *
     * @param CrestApps\CodeGenerator\Models\Resource $resourceA
     * @param CrestApps\CodeGenerator\Models\Resource $resourceB
     *
     * @return CrestApps\CodeGenerator\Models\MigrationChangeCapsule
     */
    public function getDelta(Resource $resourceA, Resource $resourceB)
    {
        $fieldChanges = $this->getFieldsDelta($resourceA->fields, $resourceB->fields);

        $indexChanges = $this->getIndexesDelta($resourceA->indexes, $resourceB->indexes);

        return new MigrationChangeCapsule($fieldChanges, $indexChanges);
    }

    /**
     * Get the field difference between two giving Field arrays
     *
     * @param array $fieldsA
     * @param array $fieldsB
     *
     * @return array
     */
    protected function getFieldsDelta(array $fieldsA, array $fieldsB)
    {
        $currentFields = Collect($fieldsB)->keyBy('name');
        $currentFieldNames = $currentFields->keys()->toArray();

        $requestedFields = Collect($fieldsA)->keyBy('name');
        $requestedFieldNames = $requestedFields->keys()->toArray();

        $updatedFields = $currentFields->whereIn('name', $requestedFieldNames)->all();

        $addedFields = $requestedFields->reject(function ($requestedField) use ($currentFieldNames) {
            return in_array($requestedField->name, $currentFieldNames);
        })->all();

        $deletedFields = $currentFields->reject(function ($currentFields) use ($requestedFieldNames) {
            return in_array($currentFields->name, $requestedFieldNames);
        })->all();

        $fieldChanges = [];

        foreach ($updatedFields as $key => $updatedField) {
            $fieldChanges[] = FieldMigrationChange::compare($updatedField, $requestedFields[$key]);
        }

        foreach ($addedFields as $addedField) {
            $fieldChanges[] = FieldMigrationChange::getAdded($addedField);
        }

        foreach ($deletedFields as $deletedField) {
            $fieldChanges[] = FieldMigrationChange::getDeleted($deletedField);
        }

        return $fieldChanges;
    }

    /**
     * Get the field difference between two giving Index arrays
     *
     * @param array $indexesA
     * @param array $indexesB
     *
     * @return array
     */
    protected function getIndexesDelta(array $indexesA, array $indexesB)
    {
        $currentIndexes = Collect($indexesA);
        $currentIndexNames = $currentIndexes->pluck('name');

        $requestedIndexes = Collect($indexesB);
        $requestedIndexNames = $requestedIndexes->pluck('name');

        $updatedIndexes = $currentIndexes->whereIn('name', $requestedIndexNames)->all();

        $addedIndexes = $requestedIndexes->reject(function ($requestedIndex) use ($currentIndexNames) {
            return !is_null($requestedIndex->name) && in_array($requestedIndex->name, $currentIndexNames);
        })->all();

        $deletedIndexes = $currentIndexes->reject(function ($currentIndex) use ($requestedIndexNames) {
            return in_array($currentIndex->name, $requestedIndexNames);
        })->all();

        $indexChanges = [];

        foreach ($updatedIndexes as $key => $updatedIndex) {
            $indexChanges[] = IndexMigrationChange::compare($updatedIndex, $requestedIndexes[$key]);
        }

        foreach ($addedIndexes as $addedIndex) {
            $indexChanges[] = IndexMigrationChange::getAdded($addedIndex);
        }

        foreach ($deletedIndexes as $deletedIndex) {
            $indexChanges[] = IndexMigrationChange::getDeleted($deletedIndex);
        }

        return $indexChanges;
    }

    /**
     * Adds a migration capsule the migrations collection.
     *
     * @return void
     */
    public function getMigrations()
    {
        return $this->migrations;
    }

    /**
     * Update the last migration
     *
     * @param CrestApps\CodeGenerator\Models\MigrationCapsule $migration
     *
     * @return void
     */
    public function updateMigration(MigrationCapsule $migration)
    {
        $key = $this->getMigrationIndex($migration->name);

        if ($key > -1) {
            $current = $this->migrations[$key];
            $this->migrations[$key] = $migration;
            if ($current->path != $migration->path) {
                $this->deleteFile($current->path);
            }
        }
    }

    /**
     * Get the index of a migration by name. It return -1 if not found.
     *
     * @param string $name
     *
     * @return int
     */
    protected function getMigrationIndex($name)
    {
        foreach ($this->getMigrations() as $key => $migration) {
            if ($migration->name == $name) {
                return $key;
            }
        }

        return -1;
    }

    /**
     * Deletes all migrations
     *
     * @return void
     */
    public function forgetAllMigrations()
    {
        foreach ($this->getMigrations() as $key => $migration) {
            $this->forgetMigration($key);
        }
    }

    /**
     * Deletes a migration by index
     *
     * @param string $name
     *
     * @return void
     */
    public function forgetMigration($index)
    {
        if (($current = $this->migrations[$index]) !== null) {
            $this->deleteFile($current->path);
            unset($this->migrations[$index]);
        }
    }

    /**
     * Delete a file
     *
     * @return bool
     */
    protected function deleteFile($file)
    {
        if (File::exists($file)) {
            return File::delete($file);
        }

        return false;
    }

    /**
     * Get the total migratins
     *
     * @return int
     */
    public function totalMigrations()
    {
        return count($this->getMigrations());
    }

    /**
     * Gets the last migration in the collection which is the one we are using
     *
     * @return mix (null || CrestApps\CodeGenerator\Models\MigrationCapsule)
     */
    public function getCurrentMigration()
    {
        return end($this->migrations);
    }

    /**
     * Gets the last before current migration in the
     * collection which is the one we are using
     *
     * @return mix (null || CrestApps\CodeGenerator\Models\MigrationCapsule)
     */
    public function getMigrationBeforeCurrent()
    {
        if (($count = $this->totalMigrations()) > 1) {
            return $this->migrations[$count - 2];
        }

        return null;
    }

    /**
     * Checks if at least one migration exists
     *
     * @return bool
     */
    public function hasMigration()
    {
        return isset($this->migrations[0]);
    }

    /**
     * Gets array of the paramets
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'model-name' => $this->modelName,
            'resource-file' => $this->resourceFile,
            'table-name' => $this->tableName,
            'migrations' => $this->getRawMigrations(),
        ];
    }

    /**
     * Update the last migration
     *
     * @param string $tableName
     * @param string $modelName
     * @param string $resourceFile
     * @param array $migrationProperties
     *
     * @return CrestApps\CodeGenerator\Models\MigrationTrackerCapsule
     */
    public static function get($tableName, $modelName, $resourceFile, array $migrationProperties = [])
    {
        $properties['table-name'] = $tableName;
        $properties['model-name'] = $modelName;
        $properties['resource-file'] = $resourceFile;
        $properties['migrations'] = $migrationProperties;

        return new MigrationTrackerCapsule($properties);
    }

    /**
     * Gets array of raw migrations
     *
     * @return array
     */
    protected function getRawMigrations()
    {
        return array_map(function ($migration) {
            return $migration->toArray();
        }, $this->migrations);
    }
}
