<?php

namespace CrestApps\CodeGenerator\Commands;

use CrestApps\CodeGenerator\Commands\Bases\MigrationCommandBase;
use CrestApps\CodeGenerator\Models\Field;
use CrestApps\CodeGenerator\Models\ForeignConstraint;
use CrestApps\CodeGenerator\Models\Label;
use CrestApps\CodeGenerator\Models\MigrationCapsule;
use CrestApps\CodeGenerator\Models\MigrationChangeCapsule;
use CrestApps\CodeGenerator\Models\MigrationTrackerCapsule;
use CrestApps\CodeGenerator\Models\Resource;
use CrestApps\CodeGenerator\Support\Config;
use CrestApps\CodeGenerator\Support\Helpers;
use CrestApps\CodeGenerator\Support\MigrationHistoryTracker;
use CrestApps\CodeGenerator\Traits\CommonCommand;
use Exception;
use File;

class CreateMigrationCommand extends MigrationCommandBase
{
    use CommonCommand;

    protected $notChnagableTypes = ['char', 'double', 'enum', 'mediumInteger', 'timestamp', 'tinyInteger', 'ipAddress', 'json',
        'jsonb', 'macAddress', 'mediumIncrements', 'morphs', 'nullableMorphs', 'nullableTimestamps', 'softDeletes', 'timeTz', 'timestampTz', 'timestamps', 'timestampsTz', 'unsignedMediumInteger', 'unsignedTinyInteger', 'uuid'];
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:migration
                            {model-name : The name of the model.}
                            {--table-name= : The name of the table that the migration will create.}
                            {--migration-class-name= : The name of the migration class.}
                            {--connection-name= : A specific connection name.}
                            {--engine-name= : A specific engine name.}
                            {--resource-file= : The name of the resource-file to import from.}
                            {--template-name= : The template name to use when generating the code.}
                            {--without-timestamps : Prevent Eloquent from maintaining both created_at and the updated_at properties.}
                            {--with-soft-delete : Enables softdelete future should be enable in the model.}
                            {--force : This option will override the migration if one already exists.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a migration file for the model.';

    /**
     * Executes the console command.
     *
     * @return void
     */
    public function handle()
    {
        $input = $this->getCommandInput();
        $tracker = new MigrationHistoryTracker();
        $capsule = $tracker->get($input->tableName);
        $resource = $this->getCurrentResource($input->resourceFile);

        if (is_null($capsule)) {
            // At this point there are no capsule or migration associated with this table.
            $capsule = $this->getMigrationTrackerCapsule($input);
            $migration = $this->getMigrationCapsule($input, $resource, $this->getCreateMigrationName($input->tableName));
            $tracker->add($capsule, $migration);
            $this->makeCreateMigration($input, $migration);

            return false;
        }

        $migration = $capsule->getCurrentMigration();

        if (is_null($migration)) {
            // At this point, there are no migration with this current capsule
            // add it, then create create migration
            $migration = $this->getMigrationCapsule($input, $resource, $this->getCreateMigrationName($input->tableName));
            $tracker->addMigration($input->tableName, $migration);
            $this->makeCreateMigration($input, $migration);

        } elseif (!Config::useSmartMigration() || (!$this->isMigrated($migration->name) && $migration->isCreate && !$migration->isVirtual)) {
            //At this point the current migration is the first one and is not migrated
            //Create update the migration using the current resource then recreate the migration file
            $migration->setResource($resource);
            $migration->path = $this->getMigrationFullName($migration->name, $input->tableName); // make sure we use the same path
            $tracker->updateMigration($capsule->tableName, $migration);
            $this->makeCreateMigration($input, $migration);

        } else if ($this->isMigrated($migration->name) || $migration->isVirtual) {
            // Make new alter migration
            $migrationName = $this->getAlterMigrationName($input->tableName, $capsule->totalMigrations());
            $newMigration = $this->getMigrationCapsule($input, $resource, $migrationName, false);
            $newMigration->className = $this->makeAlterTableClassName($input->tableName, $capsule->totalMigrations());
            $this->makeAlterMigration($input, $newMigration, $capsule->getDelta($resource, $migration->resource));
            $tracker->addMigration($input->tableName, $newMigration);

        } else {
            // update existing alter migration
            $beforeLastMigration = $capsule->getMigrationBeforeCurrent();
            if (is_null($beforeLastMigration)) {
                throw new Exception('The migration before current was not found!');
            }
            $migration->setResource($resource);
            $tracker->updateMigration($capsule->tableName, $migration);

            $this->makeAlterMigration($input, $migration, $capsule->getDelta($resource, $beforeLastMigration->resource));
        }
    }

    /**
     * Gets resource using current fields file
     *
     * @param string $resourceFile
     *
     * @throws Exception
     *
     * @return CrestApps\CodeGenerator\Models\Resource
     */
    protected function getCurrentResource($resourceFile)
    {
        $resource = Resource::fromFile($resourceFile, 'migration');

        if (count($resource->fields) == 0 && count($resource->indexes)) {
            throw new Exception('You must provide at least one field or index to generate the migration');
        }

        return $resource;
    }

    /**
     * Gets a new migration capsule
     *
     * @param object $input
     *
     * @return CrestApps\CodeGenerator\Models\MigrationTrackerCapsule
     */
    protected function getMigrationTrackerCapsule($input)
    {
        return MigrationTrackerCapsule::get($input->tableName, $input->modelName, $input->resourceFile);
    }

    /**
     * Make a migration capsule
     *
     * @param object $input
     * @param CrestApps\CodeGenerator\Models\Resource $resource
     * @param string $name
     * @param bool $isCreate
     *
     * @return CrestApps\CodeGenerator\Models\MigrationCapsule
     */
    protected function getMigrationCapsule($input, $resource, $name, $isCreate = true)
    {
        $migration = MigrationCapsule::get($name);
        $migration->path = $this->getMigrationFullName($name, $input->tableName);
        $migration->resource = $resource;
        $migration->className = $this->makeCreateTableClassName(Helpers::makeTableName($input->tableName));
        $migration->isCreate = $isCreate;

        return $migration;
    }

    /**
     * Gets migration fullname
     *
     * @param string $name
     * @param string $tableName
     *
     * @return string
     */
    protected function getMigrationFullName($name, $tableName)
    {
        $folder = '';

        if (Config::organizeMigrations()) {
            $folder = $tableName;
        }

        return str_finish($this->getMigrationPath($folder) . DIRECTORY_SEPARATOR . $name, '.php');
    }

    /**
     * Make a create migration
     *
     * @param object $input
     * @param CrestApps\CodeGenerator\Models\MigrationCapsule $migration
     *
     * @return void
     */
    protected function makeCreateMigration($input, MigrationCapsule $migration)
    {
        $stub = $this->getStubContent('migration', $input->template);

        $blueprint = $this->getCreateTableBlueprint($migration->resource, $input);

        $this->replaceSchemaUp($stub, $this->getSchemaUpCreateCommand($input, $blueprint, 'create'))
            ->replaceSchemaDown($stub, $this->getSchemaDownCreateBlueprint($input))
            ->replaceMigationClassName($stub, $migration->className)
            ->createFile($migration->path, $stub)
            ->info('A migration was crafted successfully.');
    }

    /**
     * Make an alter migration
     *
     * @param object $input
     * @param CrestApps\CodeGenerator\Models\MigrationCapsule $migration
     * @param CrestApps\CodeGenerator\Models\MigrationChangeCapsule $changeCapsule
     *
     * @return void
     */
    protected function makeAlterMigration($input, MigrationCapsule $migration, MigrationChangeCapsule $changeCapsule)
    {
        $stub = $this->getStubContent('migration', $input->template);

        $blueprint = $this->getAlterTableBlueprint($input, $changeCapsule);
        $downBlueprint = $this->getSchemaDownAlterBlueprint($input, $changeCapsule);
        $this->replaceSchemaUp($stub, $this->getSchemaUpCreateCommand($input, $blueprint, 'table'))
            ->replaceSchemaDown($stub, $this->getSchemaUpAlterCommand($input, $downBlueprint))
            ->replaceMigationClassName($stub, $migration->className)
            ->createFile($migration->path, $stub)
            ->info('A migration was crafted successfully.');
    }

    /**
     * Gets name of the folder to put the migration in
     *
     * @param string  $tableName
     *
     * @return string
     */
    protected function getFolderName($tableName)
    {
        if (Config::organizeMigrations()) {
            return $tableName;
        }

        return null;
    }

    /**
     * Gets the destenation file to be created.
     *
     * @param string  $tableName
     *
     * @return string
     */
    protected function getDestenationFile($tableName)
    {
        $file = $this->getCreateMigrationName($tableName);

        return base_path(Config::getMigrationsPath()) . $file;
    }

    /**
     * Creates the table properties.
     *
     * @param array $fields
     * @param (object) $input
     * @return string
     */
    protected function getCreateTableBlueprint(Resource $resource, $input)
    {
        $properties = '';

        $constraints = $this->getConstraintsFromfields($resource->fields);

        $this->addEngineName($properties, $input->engine)
            ->addPrimaryField($properties, $this->getPrimaryField($resource->fields))
            ->addTimestamps($properties, $input->withoutTimestamps)
            ->addSoftDelete($properties, $input->withSoftDelete)
            ->addFieldProperties($properties, $resource->fields, $input->withoutTimestamps, $input->withSoftDelete)
            ->addIndexes($properties, $resource->indexes)
            ->addForeignConstraints($properties, $constraints);

        return $properties;
    }

    /**
     * Creates the table properties.
     *
     * @param array $fields
     * @param (object) $input
     * @return string
     */
    protected function getAlterTableBlueprint($input, MigrationChangeCapsule $changeCapsule)
    {
        $properties = '';

        $this->addEngineName($properties, $input->engine);

        $fieldsWithChanges = $changeCapsule->getFieldsWithUpdate();

        foreach ($fieldsWithChanges as $fieldsWithChange) {
            if ($fieldsWithChange->isDeleted) {

                $this->addDropColumn($properties, $fieldsWithChange->field->name)
                    ->addFieldPropertyClousure($properties);

            } elseif ($fieldsWithChange->isAdded) {

                $this->addFieldType($properties, $fieldsWithChange->field)
                    ->addFieldComment($properties, $fieldsWithChange->field)
                    ->addFieldUnsigned($properties, $fieldsWithChange->field)
                    ->addFieldNullable($properties, $fieldsWithChange->field)
                    ->addFieldIndex($properties, $fieldsWithChange->field)
                    ->addFieldUnique($properties, $fieldsWithChange->field)
                    ->addFieldPropertyClousure($properties);

            } elseif ($fieldsWithChange->isRenamed()) {
                $this->addRenameColumn($properties, $fieldsWithChange->fromField->name, $fieldsWithChange->toField->name)
                    ->addFieldPropertyClousure($properties);
            } else {

                if (in_array($field->getEloquentDataMethod(), $this->notChnagableTypes)) {
                    throw new Exception('The type' . $field->getEloquentDataMethod() . ' cannot be changed using the change() method!');
                }

                $this->addFieldType($properties, $fieldsWithChange->fromField)
                    ->addFieldComment($properties, $fieldsWithChange->fromField)
                    ->addFieldUnsigned($properties, $fieldsWithChange->fromField)
                    ->addFieldNullable($properties, $fieldsWithChange->fromField)
                    ->addFieldIndex($properties, $fieldsWithChange->fromField)
                    ->addFieldUnique($properties, $fieldsWithChange->fromField)
                    ->addFieldChangeMethod($properties)
                    ->addFieldPropertyClousure($properties);
            }
        }

        $indexesWithChanges = $changeCapsule->getIndexesWithUpdate();

        foreach ($indexesWithChanges as $indexesWithChange) {
            if ($indexesWithChange->isDeleted) {
                $this->addIndex($properties, $indexesWithChange->index);

            } elseif ($indexesWithChange->isAdded) {
                $this->dropIndex($properties, $indexesWithChange->index);
            }
        }

        return $properties;
    }

    /**
     * Adds foreign key constraint to a giving properties.
     *
     * @param string $properties
     * @param array $constraints
     *
     * @return $this
     */
    protected function addForeignConstraints(&$properties, array $constraints)
    {
        foreach ($constraints as $constraint) {
            $this->addForeignConstraint($properties, $constraint)
                ->addReferencesConstraint($properties, $constraint)
                ->addOnConstraint($properties, $constraint)
                ->addOnDeleteConstraint($properties, $constraint)
                ->addOnUpdateConstraint($properties, $constraint)
                ->addFieldPropertyClousure($properties);
        }

        return $this;
    }

    /**
     * Gets Foreign contstrains from giving fields.
     *
     * @param array of CrestApps\CodeGenerator\Models\Field $field
     *
     * @return array of CrestApps\CodeGenerator\Models\ForeignConstraint $field
     */
    protected function getConstraintsFromfields(array $fields)
    {
        $constraints = [];

        foreach ($fields as $field) {
            if ($field->hasForeignConstraint()) {
                $constraints[] = $field->getForeignConstraint();
            }
        }

        return $constraints;
    }

    /**
     * Adds 'foreign' eloquent method to a giving properties.
     *
     * @param string $properties
     * @param CrestApps\CodeGenerator\Models\ForeignConstraint $constraint
     *
     * @return $this
     */
    protected function addForeignConstraint(&$properties, ForeignConstraint $constraint)
    {
        $properties .= PHP_EOL . sprintf("%s('%s')", $this->getPropertyBase('foreign'), $constraint->column);

        return $this;
    }

    /**
     * Adds 'references' eloquent method to a giving properties.
     *
     * @param string $properties
     * @param CrestApps\CodeGenerator\Models\ForeignConstraint $constraint
     *
     * @return $this
     */
    protected function addReferencesConstraint(&$properties, ForeignConstraint $constraint)
    {
        $properties .= $this->getPropertyBaseSpace(18, true) . sprintf("->references('%s')", $constraint->references);

        return $this;
    }

    /**
     * Adds 'on' eloquent method to a giving properties.
     *
     * @param string $properties
     * @param CrestApps\CodeGenerator\Models\ForeignConstraint $constraint
     *
     * @return $this
     */
    protected function addOnConstraint(&$properties, ForeignConstraint $constraint)
    {
        $properties .= $this->getPropertyBaseSpace(18, true) . sprintf("->on('%s')", $constraint->on);

        return $this;
    }

    /**
     * Adds 'onDelete' eloquent method to a giving properties.
     *
     * @param string $properties
     * @param CrestApps\CodeGenerator\Models\ForeignConstraint $constraint
     *
     * @return $this
     */
    protected function addOnDeleteConstraint(&$properties, ForeignConstraint $constraint)
    {
        if ($constraint->hasDeleteAction()) {
            $properties .= $this->getPropertyBaseSpace(18, true) . sprintf("->onDelete('%s')", $constraint->onDelete);
        }

        return $this;
    }

    /**
     * Adds 'onUpdate' eloquent method to a giving properties.
     *
     * @param string $properties
     * @param CrestApps\CodeGenerator\Models\ForeignConstraint $constraint
     *
     * @return $this
     */
    protected function addOnUpdateConstraint(&$properties, ForeignConstraint $constraint)
    {
        if ($constraint->hasUpdateAction()) {
            $properties .= $this->getPropertyBaseSpace(18, true) . sprintf("->onUpdate('%s')", $constraint->onUpdate);
        }

        return $this;
    }

    /**
     * Parses a giving key string.
     *
     * @param string $keysString
     *
     * @return array
     */
    protected function getForeignConstraints($keysString)
    {
        $constraints = [];

        $constraints = Helpers::removeEmptyItems(explode('#', $keysString));

        foreach ($constraints as $constraint) {
            $keyParts = Helpers::removeEmptyItems(explode('|', $constraint));

            if (isset($keyParts[4])) {
                //At this point we know there are foreign, references, on, onDelete, onUpdate
                $constraints[] = new ForeignConstraint($keyParts[0], $keyParts[1], $keyParts[2], $keyParts[3], $keyParts[4]);
            } elseif (isset($keyParts[3])) {
                //At this point we know there are foreign, references, onDelete
                $constraints[] = new ForeignConstraint($keyParts[0], $keyParts[1], $keyParts[2], $keyParts[3]);
            } elseif (isset($keyParts[2])) {
                //At this point we know there are foreign, references
                $constraints[] = new ForeignConstraint($keyParts[0], $keyParts[1], $keyParts[2]);
            } else {
                throw new Exception('The foreign key relation is not configured correctly.');
            }
        }

        return $constraints;
    }

    /**
     * Adds index method to a giving $properties
     *
     * @param string $properties
     * @param array $indexes
     * @return $this
     */
    protected function addIndexes(&$properties, array $indexes)
    {
        foreach ($indexes as $index) {
            if (!$index->hasColumns()) {
                continue;
            }

            $indexName = '';
            if ($index->hasName()) {
                $indexName = sprintf(", '%s'", $index->getName());
            }

            if ($index->hasMultipleColumns()) {
                $indexColumn = sprintf('[%s]', implode(',', Helpers::wrapItems($index->getColumns())));
            } else {
                $indexColumn = sprintf("'%s'", $index->getFirstColumn());
            }

            $properties .= sprintf('%s(%s%s)', $this->getPropertyBase($index->getType()), $indexColumn, $indexName);

            $this->addFieldPropertyClousure($properties);
        }

        return $this;
    }

    /**
     * Cleans the columns by removing any non-english chares and wrapps each column with a single quote
     *
     * @param string $properties
     * @param array $indexes
     *
     * @return $this
     */
    protected function getCleanColumns($columnsString)
    {
        $columns = Helpers::removeEmptyItems(explode(',', $columnsString), function ($column) {
            return trim(Helpers::removeNonEnglishChars($column));
        });

        return Helpers::wrapItems($columns);
    }

    /**
     * Adds the standard field method.
     *
     * @param string $properties
     * @param array $fields
     * @param bool $withoutTimestamps
     * @param bool $withSoftDelete
     *
     * @return $this
     */
    protected function addFieldProperties(&$properties, array $fields, $withoutTimestamps, $withSoftDelete)
    {
        $primaryField = $this->getPrimaryField($fields);

        foreach ($fields as $field) {
            if ($field instanceof Field && $field != $primaryField && !is_null($primaryField)) {
                if (!$withoutTimestamps && $field->isAutoManagedOnUpdate()) {
                    continue;
                }
                if ($withSoftDelete && $field->isAutoManagedOnDelete()) {
                    continue;
                }

                $this->addFieldType($properties, $field)
                    ->addFieldComment($properties, $field)
                    ->addFieldUnsigned($properties, $field)
                    ->addFieldNullable($properties, $field)
                    ->addFieldIndex($properties, $field)
                    ->addFieldUnique($properties, $field)
                    ->addFieldPropertyClousure($properties);
            }
        }

        return $this;
    }

    /**
     * Adds a line closure to a property
     *
     * @param string $properties
     *
     * @return $this
     */
    protected function addFieldPropertyClousure(&$property)
    {
        $property .= ';' . PHP_EOL;

        return $this;
    }

    /**
     * Adds the change method
     *
     * @param string $property
     *
     * @return $this
     */
    protected function addFieldChangeMethod(&$property)
    {
        $property .= '->change()';

        return $this;
    }

    /**
     * Adds a 'field type' to the property
     *
     * @param string $property
     * @param CrestApps\CodeGenerator\Models\Field $field
     *
     * @return $this
     */
    protected function addFieldType(&$property, Field $field)
    {
        $params = $this->getMethodParamerters($field);
        $property .= sprintf("%s('%s'%s)", $this->getPropertyBase($field->getEloquentDataMethod()), $field->name, $params);

        return $this;
    }

    /**
     * Constructs the second parameter to the type method
     *
     * @param CrestApps\CodeGenerator\Models\Field $field
     *
     * @return string
     */
    protected function getMethodParamerters(Field $field)
    {
        $params = count($field->methodParams) == 0 ? '' : ', ' . implode(',', $field->methodParams);

        if ($field->getEloquentDataMethod() == 'enum') {
            $params = ', ' . $this->getEnumParams($field);
        }

        return $params;
    }

    /**
     * Constructs the second parameter to the enum type method
     *
     * @param CrestApps\CodeGenerator\Models\Field $field
     *
     * @return string
     */
    protected function getEnumParams(Field $field)
    {
        if ($field->getEloquentDataMethod() != 'enum') {
            throw new Exception('The field type is not enum! Cannot create an enum column with no options.');
        }

        $labels = array_filter($field->getOptionsByLang(), function ($option) use ($field) {
            return !($field->isRequired() && $option->value == '');
        });

        $values = $this->getLabelValues($labels);

        if (count($values) == 0) {
            throw new Exception('Could not find any option values to construct the enum values from. ' .
                'It is possible that this field is required but only have option available has an empty string.');
        }

        return sprintf('[%s]', implode(',', Helpers::wrapItems($values)));
    }

    /**
     * It gets label's value for each label in the giving labels
     *
     * @param array $labels
     *
     * @return string
     */
    protected function getLabelValues(array $labels)
    {
        $values = [];

        foreach ($labels as $label) {
            if ($label instanceof Label) {
                $values[] = $label->value;
            }
        }

        return $values;
    }

    /**
     * Creates the base property from a giving method.
     *
     * @param string $method
     *
     * @return string
     */
    protected function getPropertyBase($method)
    {
        return sprintf('%s$table->%s', $this->getPropertyBaseSpace(), $method);
    }

    /**
     * Creates a leading space to keep the lines alligned the same in the output file.
     *
     * @param int $multiplier
     * @param bool $prependNewline
     *
     * @return string
     */
    protected function getPropertyBaseSpace($multiplier = 12, $prependNewline = false)
    {
        return ($prependNewline ? PHP_EOL . '' : '') . str_repeat(' ', $multiplier);
    }

    /**
     * Constructs the schema down command.
     *
     * @param (object) $input
     *
     * @return string
     */
    protected function getSchemaDownCreateBlueprint($input)
    {
        $stub = $this->getStubContent('migration-schema-down', $input->template);

        $this->replaceConnectionName($stub, $input->connection)
            ->replaceTableName($stub, $input->tableName);

        return $stub;
    }

    /**
     * Get the schema down blueprint for alter command.
     *
     * @param object $input
     * @param CrestApps\CodeGenerator\Models\MigrationChangeCapsule $changeCapsule
     *
     * @return string
     */
    protected function getSchemaDownAlterBlueprint($input, MigrationChangeCapsule $changeCapsule)
    {
        $properties = '';

        $this->addEngineName($properties, $input->engine);

        $fieldsWithChanges = $changeCapsule->getFieldsWithUpdate();

        foreach ($fieldsWithChanges as $fieldsWithChange) {
            if ($fieldsWithChange->isDeleted) {
                // Add it back
                $this->addFieldType($properties, $fieldsWithChange->field)
                    ->addFieldComment($properties, $fieldsWithChange->field)
                    ->addFieldUnsigned($properties, $fieldsWithChange->field)
                    ->addFieldNullable($properties, $fieldsWithChange->field)
                    ->addFieldIndex($properties, $fieldsWithChange->field)
                    ->addFieldUnique($properties, $fieldsWithChange->field)
                    ->addFieldPropertyClousure($properties);

            } elseif ($fieldsWithChange->isAdded) {

                $this->addDropColumn($properties, $fieldsWithChange->field->name)
                    ->addFieldPropertyClousure($properties);

            } elseif ($fieldsWithChange->isRenamed()) {
                $this->addRenameColumn($properties, $fieldsWithChange->fromField->name, $fieldsWithChange->toField->name)
                    ->addFieldPropertyClousure($properties);
            } else {
                if (in_array($field->getEloquentDataMethod(), $this->notChnagableTypes)) {
                    throw new Exception('The type' . $field->getEloquentDataMethod() . ' cannot be changed using the change() method!');
                }

                $this->addFieldType($properties, $fieldsWithChange->toField)
                    ->addFieldComment($properties, $fieldsWithChange->toField)
                    ->addFieldUnsigned($properties, $fieldsWithChange->toField)
                    ->addFieldNullable($properties, $fieldsWithChange->toField)
                    ->addFieldIndex($properties, $fieldsWithChange->toField)
                    ->addFieldUnique($properties, $fieldsWithChange->toField)
                    ->addFieldChangeMethod($properties)
                    ->addFieldPropertyClousure($properties);
            }
        }

        $indexesWithChanges = $changeCapsule->getIndexesWithUpdate();

        foreach ($indexesWithChanges as $indexesWithChange) {
            if ($indexesWithChange->isDeleted) {
                // Add it back
                $this->dropIndex($properties, $indexesWithChange->index);

            } elseif ($indexesWithChange->isAdded) {
                $this->addIndex($properties, $indexesWithChange->index);
            }
        }

        return $properties;
    }

    /**
     * Constructs the schema down command.
     *
     * @param (object) $input
     *
     * @return string
     */
    protected function getSchemaUpAlterCommand($input, $blueprintBody)
    {
        $stub = $this->getStubContent('migration-schema-up', $input->template);

        $this->replaceConnectionName($stub, $input->connection)
            ->replaceTableName($stub, $input->tableName)
            ->replaceOperationName($stub, 'table')
            ->replaceBlueprintBodyName($stub, $blueprintBody);

        return $stub;
    }

    /**
     * Constructs the schema up command.
     *
     * @param (object) $input
     *
     * @return string
     */
    protected function getSchemaUpCreateCommand($input, $blueprintBody, $operationName)
    {
        $stub = $this->getStubContent('migration-schema-up', $input->template);

        $this->replaceConnectionName($stub, $input->connection)
            ->replaceTableName($stub, $input->tableName)
            ->replaceOperationName($stub, $operationName)
            ->replaceBlueprintBodyName($stub, $blueprintBody);

        return $stub;
    }

    /**
     * Replace the className of the given stub.
     *
     * @param  string  $stub
     * @param  string  $className
     *
     * @return $this
     */
    protected function replaceMigationClassName(&$stub, $className)
    {
        $stub = $this->strReplace('migration_name', $className, $stub);

        return $this;
    }

    /**
     * Replace the operation name of the given stub.
     *
     * @param  string  $stub
     * @param  string  $name
     *
     * @return $this
     */
    protected function replaceOperationName(&$stub, $name)
    {
        $stub = $this->strReplace('operation_name', $name, $stub);

        return $this;
    }

    /**
     * Replace the blueprintBody for the given stub.
     *
     * @param  string  $stub
     * @param  string  $tableName
     *
     * @return $this
     */
    protected function replaceBlueprintBodyName(&$stub, $blueprintBody)
    {
        $stub = $this->strReplace('blue_print_body', $blueprintBody, $stub);

        return $this;
    }

    /**
     * Replace the tableName for the given stub.
     *
     * @param  string  $stub
     * @param  string  $name
     *
     * @return $this
     */
    protected function replaceTableName(&$stub, $name)
    {
        $stub = $this->strReplace('table_name', $name, $stub);

        return $this;
    }

    /**
     * Replaces the connection's name for the given stub.
     *
     * @param  string  $stub
     * @param  string  $name
     *
     * @return $this
     */
    protected function replaceConnectionName(&$stub, $name)
    {
        $connectionLine = !empty($name) ? sprintf("connection('%s')->", $name) : '';

        $stub = $this->strReplace('connection_name', $connectionLine, $stub);

        return $this;
    }

    /**
     * Adds the field's "default" value to the giving property.
     *
     * @param string $property
     * @param CrestApps\CodeGenerator\Models\Field $field
     *
     * @return $this
     */
    protected function addFieldDefaultValue(&$property, Field $field)
    {
        if (!is_null($field->dataValue) && !$field->nullable) {
            $property .= sprintf("->default('%s')", $field->dataValue);
        }

        return $this;
    }

    /**
     * Adds the field's "unsigned" value to the giving property.
     *
     * @param string $property
     * @param CrestApps\CodeGenerator\Models\Field $field
     *
     * @return $this
     */
    protected function addFieldUnsigned(&$property, Field $field)
    {
        if ($field->isUnsigned) {
            $property .= '->unsigned()';
        }

        return $this;
    }

    /**
     * Adds the field's "dropColumn" method call to the giving property.
     *
     * @param string $property
     * @param string $name
     *
     * @return $this
     */
    protected function addDropColumn(&$property, $name)
    {
        if (is_array($name)) {
            $property .= sprintf('%s([%s])', $this->getPropertyBase('dropColumn'), implode(',', Helpers::wrapItems($name)));
        } else {
            $property .= sprintf("%s('%s')", $this->getPropertyBase('dropColumn'), $name);
        }

        return $this;
    }

    /**
     * Adds the field's "renameColumn" method call to the giving property.
     *
     * @param string $property
     * @param string $from
     * @param string $to
     *
     * @return $this
     */
    protected function addRenameColumn(&$property, $from, $to)
    {
        $property .= sprintf("%s('%s', '%s')", $this->getPropertyBase('renameColumn'), $from, $to);

        return $this;
    }

    /**
     * Adds the field's "unique" value to the giving property.
     *
     * @param string $property
     * @param CrestApps\CodeGenerator\Models\Field $field
     *
     * @return $this
     */
    protected function addFieldUnique(&$property, Field $field)
    {
        if ($field->isUnique) {
            $property .= '->unique()';
        }

        return $this;
    }

    /**
     * Adds the field's "index" value to the giving property.
     *
     * @param string $property
     * @param CrestApps\CodeGenerator\Models\Field $field
     *
     * @return $this
     */
    protected function addFieldIndex(&$property, Field $field)
    {
        if ($field->isIndex && !$field->isUnique) {
            $property .= '->index()';
        }

        return $this;
    }

    /**
     * Adds an index
     *
     * @param string $property
     * @param CrestApps\CodeGenerator\Models\Index $index
     *
     * @return $this
     */
    protected function addIndex(&$property, Index $index)
    {
        $property .= sprintf("('%s')", $this->getPropertyBase($index->getType), $index->name);

        return $this->addFieldPropertyClousure($property);
    }

    /**
     * drop an index
     *
     * @param string $property
     * @param CrestApps\CodeGenerator\Models\Index $index
     *
     * @return $this
     */
    protected function dropIndex(&$property, Index $index)
    {
        $property .= sprintf("('%s')", $this->getPropertyBase('drop' . ucfirst($index->getType)), $index->name);

        return $this->addFieldPropertyClousure($property);
    }

    /**
     * Adds the field's "nullable" value to the giving property.
     *
     * @param string $property
     * @param CrestApps\CodeGenerator\Models\Field $field
     *
     * @return $this
     */
    protected function addFieldNullable(&$property, Field $field)
    {
        if ($field->isNullable) {
            $property .= '->nullable()';
        }

        return $this;
    }

    /**
     * Adds the field's "comment" value to the giving property.
     *
     * @param string $property
     * @param CrestApps\CodeGenerator\Models\Field $field
     *
     * @return $this
     */
    protected function addFieldComment(&$property, Field $field)
    {
        if (!empty($field->comment)) {
            $property .= sprintf("->comment('%s')", $field->comment);
        }

        return $this;
    }

    /**
     * Adds the table's "engine" type to the giving property.
     *
     * @param string $property
     * @param string $name
     *
     * @return $this
     */
    protected function addEngineName(&$property, $name)
    {
        if (!empty($name)) {
            $property .= sprintf("%s = '%s'", $this->getPropertyBase('engine'), $name);
            $this->addFieldPropertyClousure($property);
        }

        return $this;
    }

    /**
     * Adds the table's "primary column" to the giving property.
     *
     * @param string $property
     * @param CrestApps\CodeGenerator\Models\Field $field
     *
     * @return $this
     */
    protected function addPrimaryField(&$property, Field $field = null)
    {
        if (!is_null($field)) {
            $eloquentMethodName = $this->getPrimaryMethodName($field->getEloquentDataMethod());
            $property .= sprintf("%s('%s')", $this->getPropertyBase($eloquentMethodName), $field->name);
            $this->addFieldPropertyClousure($property);
        }

        return $this;
    }

    /**
     * Adds 'updated_at' and 'created_at' columns to a giving propery.
     *
     * @param string $property
     * @param bool $without
     *
     * @return $this
     */
    protected function addTimestamps(&$property, $without)
    {
        if (!$without) {
            $property .= sprintf("%s()", $this->getPropertyBase('timestamps'));
            $this->addFieldPropertyClousure($property);
        }

        return $this;
    }

    /**
     * Adds 'delete_at' columns to a giving propery.
     *
     * @param string $property
     * @param bool $withSoftDelete
     *
     * @return $this
     */
    protected function addSoftDelete(&$property, $withSoftDelete)
    {
        if ($withSoftDelete) {
            $property .= sprintf("%s()", $this->getPropertyBase('softDeletes'));
            $this->addFieldPropertyClousure($property);
        }

        return $this;
    }

    /**
     * Gets the correct eloquent increment method from the giving type.
     *
     * @param string $type
     *
     * @return string
     */
    protected function getPrimaryMethodName($type)
    {
        $type = strtolower($type);
        $methodName = 'primary';

        if (in_array($type, ['int', 'integer'])) {
            $methodName = 'increments';
        } elseif (in_array($type, ['bigint', 'biginteger'])) {
            $methodName = 'bigIncrements';
        } elseif (in_array($type, ['mediuminteger', 'mediumincrements'])) {
            $methodName = 'mediumIncrements';
        }

        return $methodName;
    }

    /**
     * Gets a clean user inputs.
     *
     * @return object
     */
    protected function getCommandInput()
    {
        $modelName = trim($this->argument('model-name'));
        $madeUpTableName = Helpers::makeTableName($modelName);
        $tableName = trim($this->option('table-name')) ?: $madeUpTableName;
        $className = trim($this->option('migration-class-name')) ?: $this->makeCreateTableClassName($madeUpTableName);
        $connection = trim($this->option('connection-name'));
        $engine = trim($this->option('engine-name'));
        $resourceFile = trim($this->option('resource-file')) ?: Helpers::makeJsonFileName($modelName);
        $force = $this->option('force');
        $template = $this->getTemplateName();
        $withoutTimestamps = $this->option('without-timestamps');
        $withSoftDelete = $this->option('with-soft-delete');

        return (object) compact(
            'modelName',
            'tableName',
            'className',
            'connection',
            'engine',
            'resourceFile',
            'force',
            'template',
            'withoutTimestamps',
            'withSoftDelete'
        );
    }

    /**
     * Replaces the schema_up for the given stub.
     *
     * @param  string  $stub
     * @param  string  $schemaUp
     *
     * @return $this
     */
    protected function replaceSchemaUp(&$stub, $schemaUp)
    {
        $stub = $this->strReplace('schema_up', $schemaUp, $stub);

        return $this;
    }

    /**
     * Replaces the schema_down for the given stub.
     *
     * @param  string  $stub
     * @param  string  $schemaDown
     *
     * @return $this
     */
    protected function replaceSchemaDown(&$stub, $schemaDown)
    {
        $stub = $this->strReplace('schema_down', $schemaDown, $stub);

        return $this;
    }
}
