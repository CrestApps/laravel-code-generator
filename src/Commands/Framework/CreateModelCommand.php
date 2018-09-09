<?php

namespace CrestApps\CodeGenerator\Commands\Framework;

use CrestApps\CodeGenerator\Models\Field;
use CrestApps\CodeGenerator\Models\ForeignRelationship;
use CrestApps\CodeGenerator\Models\Resource;
use CrestApps\CodeGenerator\Support\Arr;
use CrestApps\CodeGenerator\Support\Config;
use CrestApps\CodeGenerator\Support\FieldTransformer;
use CrestApps\CodeGenerator\Support\Helpers;
use CrestApps\CodeGenerator\Support\Str;
use CrestApps\CodeGenerator\Traits\CommonCommand;
use CrestApps\CodeGenerator\Traits\GeneratorReplacers;
use CrestApps\CodeGenerator\Traits\LanguageTrait;
use Illuminate\Console\Command;

class CreateModelCommand extends Command
{
    use CommonCommand, GeneratorReplacers, LanguageTrait;

    /**
     * Total white-spaced to eliminate when creating an array string.
     *
     * @var string
     */
    protected $backspaceCount = 8;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:model
                            {model-name : The name of the model.}
                            {--table-name= : The name of the table.}
                            {--primary-key=id : The name of the primary key.}
                            {--resource-file= : The name of the resource-file to import from.}
                            {--language-filename= : The languages file name to put the labels in.}
                            {--model-directory= : The directory where the model should be created.}
                            {--with-soft-delete : Enables softdelete future should be enable in the model.}
                            {--without-timestamps : Prevent Eloquent from maintaining both created_at and the updated_at properties.}
                            {--template-name= : The template name to use when generating the code.}
                            {--model-extends=default-model : The base model to be extend.}
                            {--force : Override the model if one already exists.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new model.';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Model';

    /**
     * Builds the model class with the given name.
     *
     * @return string
     */
    public function handle()
    {
        $input = $this->getCommandInput();

        $resource = Resource::fromFile($input->resourceFile, $input->languageFileName);

        $destenationFile = $this->getDestenationFile($input->modelName, $input->modelDirectory);

        if ($this->hasErrors($resource, $destenationFile)) {
            return false;
        }

        $fields = $resource->fields;
        if ($input->useSoftDelete) {
            $fields = $this->upsertDeletedAt($fields);
        }

        $stub = $this->getStubContent('model');
        $primaryField = $this->getPrimaryField($fields);
        $relations = $this->getRelationMethods($resource->relations, $fields);
        $namespacesToUse = $this->getRequiredUseClasses($this->getAdditionalNamespaces($input));

        return $this->replaceTable($stub, $resource->getTableName($input->table))
            ->replaceModelName($stub, $input->modelName)
            ->replaceUseCommandPlaceholder($stub, $namespacesToUse)
            ->replaceModelExtends($stub, $this->getModelExtends($this->getFullClassToExtend()))
            ->replaceNamespace($stub, $this->getNamespace($input->modelName, $input->modelDirectory))
            ->replaceSoftDelete($stub, $input->useSoftDelete)
            ->replaceTimestamps($stub, $this->getTimeStampsStub($input->useTimeStamps, $resource->isCreateAndUpdateAtManaged()))
            ->replaceFillable($stub, $this->getFillables($stub, $fields))
            ->replaceDateFields($stub, $this->getDateFields($stub, $fields))
            ->replaceCasts($stub, $this->getCasts($stub, $fields))
            ->replacePrimaryKey($stub, $this->getNewPrimaryKey($primaryField, $input->primaryKey))
            ->replaceRelationshipPlaceholder($stub, $relations)
            ->replaceAccessors($stub, $this->getAccessors($fields))
            ->replaceMutators($stub, $this->getMutators($fields))
            ->createFile($destenationFile, $stub)
            ->info('A model was crafted successfully.');
    }

    /**
     * Checks for basic errors
     *
     * @param  CrestApps\CodeGenerator\Models\Resource $resource
     * @param string $destenationFile
     *
     * @return bool
     */
    protected function hasErrors(Resource $resource, $destenationFile)
    {
        $hasErrors = false;

        if ($resource->isProtected('model')) {
            $this->warn('The model is protected and cannot be regenerated. To regenerate the file, unprotect it from the resource file.');

            $hasErrors = true;
        }

        if ($this->alreadyExists($destenationFile)) {
            $this->error('The model already exists!');

            $hasErrors = true;
        }

        return $hasErrors;
    }

    /**
     * Gets the destenation file to be created.
     *
     * @param string $name
     * @param string $path
     *
     * @return string
     */
    protected function getDestenationFile($name, $path)
    {
        if (!empty($path)) {
            $path = Helpers::getPathWithSlash($path);
        }

        return app_path(Config::getModelsPath($path . $name . '.php'));
    }

    /**
     * Gets the model's namespace.
     *
     * @param string $modelName
     * @param string $modelDirectory
     *
     * @return string
     */
    protected function getNamespace($modelName, $modelDirectory)
    {
        $namespace = Helpers::getAppNamespace() . Config::getModelsPath($modelDirectory);

        return rtrim(Helpers::convertSlashToBackslash($namespace), '\\');
    }

    /**
     * Gets the correct primary key name.
     *
     * @param CreatApps\CodeGenerator\Models\Field $primaryField
     * @param string $primaryKey
     *
     * @return string
     */
    protected function getPrimaryKeyName(Field $primaryField = null, $primaryKey = 'id')
    {
        return !is_null($primaryField) ? $primaryField->name : $primaryKey;
    }

    /**
     * If a given fields collection does not contain a field called "deleted_at", one will be created.
     *
     * @param array $fields
     *
     * @return array
     */
    protected function upsertDeletedAt(array $fields)
    {
        foreach ($fields as $field) {
            if ($field->isAutoManagedOnDelete()) {
                return $fields;
            }
        }

        $fields[] = $this->getNewDeletedAtField();

        return $fields;
    }

    /**
     * Gets a new field called "deleted_at"
     *
     * @return string
     */
    protected function getNewDeletedAtField()
    {
        $field = current(FieldTransformer::fromString('deleted_at'));
        $field->isDate = true;
        $field->dataType = 'datetime';
        $field->dateFormat = Config::getDateTimeFormat();

        return $field;
    }

    /**
     * Gets the formatted fillable line.
     *
     * @param string $stub
     * @param string $fillables
     * @param array $fields
     *
     * @return string
     */
    protected function getFillables($stub, array $fields)
    {
        $fillables = [];
        $indentCount = $this->getIndent($stub, $this->getTemplateVariable('fillable'));
        $indent = $this->Indent($indentCount - $this->backspaceCount);
        foreach ($fields as $field) {
            if ($field->isOnFormView) {
                $fillables[] = sprintf("%s'%s'", $indent, $field->name);
            }
        }

        return $this->makeArrayString($fillables, $indentCount - $this->backspaceCount - 4);
    }

    /**
     * Gets the code to extend the controller.
     *
     * @param string $namespace
     *
     * @return string
     */
    protected function getModelExtends($namespace)
    {
        $class = Str::extractClassFromString($namespace);
        if (!empty($class)) {
            return sprintf('extends %s', $class);
        }

        return '';
    }

    /**
     * Replaces model_extends
     *
     * @param  string  $stub
     * @param  string  $commands
     *
     * @return $this
     */
    protected function replaceModelExtends(&$stub, $commands)
    {
        return $this->replaceTemplate('model_extends', $commands, $stub);
    }

    /**
     * Gets the fillable string from a given raw string.
     *
     * @param string $stub
     * @param string $fillablesString
     *
     * @return string
     */
    protected function getFillablesFromString($stub, $fillablesString)
    {
        $columns = Arr::removeEmptyItems(explode(',', $fillablesString), function ($column) {
            return trim(Str::removeNonEnglishChars($column));
        });

        $fillables = [];
        $indentCount = $this->getIndent($stub, $this->getTemplateVariable('fillable'));
        $indent = $this->Indent($indentCount - $this->backspaceCount);

        foreach ($columns as $column) {
            $fillables[$column] = sprintf("%s'%s'", $index, $column);
        }

        return $this->makeArrayString($fillables, $indentCount - $this->backspaceCount - 4);
    }

    /**
     * Gets the date fields string from a given fields array.
     *
     * @param string $stub
     * @param array $fields
     *
     * @return string
     */
    protected function getDateFields($stub, array $fields)
    {
        $dates = [];
        $indentCount = $this->getIndent($stub, $this->getTemplateVariable('dates'));
        $indent = $this->Indent($indentCount - $this->backspaceCount);
        foreach ($fields as $field) {
            if ($field->isDate) {
                $dates[] = sprintf("%s'%s'", $indent, $field->name);
            }
        }

        return $this->makeArrayString($dates, $indentCount - $this->backspaceCount - 4);
    }

    /**
     * Gets the castable fields in a string from a given fields array.
     *
     * @param string $stub
     * @param array $fields
     *
     * @return string
     */
    protected function getCasts($stub, array $fields)
    {
        $casts = [];
        $indentCount = $this->getIndent($stub, $this->getTemplateVariable('casts'));
        $indent = $this->Indent($indentCount - $this->backspaceCount);
        foreach ($fields as $field) {
            if ($field->isCastable()) {
                $casts[$field->name] = sprintf("%s'%s' => '%s'", $indent, $field->name, $field->castAs);
            }
        }

        return $this->makeArrayString($casts, $indentCount - $this->backspaceCount - 4);
    }

    /**
     * Gets array ready string
     *
     * @param array $name
     * @param int $index
     *
     * @return string
     */
    protected function makeArrayString(array $names, $index = 0)
    {
        $string = implode(',' . PHP_EOL, $names);

        if (!empty($string)) {
            return sprintf('[%s%s%s%s]', PHP_EOL, $string, PHP_EOL, $this->indent($index));
        }

        return '[]';
    }

    /**
     * Gets a clean user inputs.
     *
     * @return object
     */
    protected function getCommandInput()
    {
        $modelName = trim($this->argument('model-name'));
        $table = trim($this->option('table-name')) ?: Helpers::makeTableName($modelName);
        $primaryKey = trim($this->option('primary-key'));
        $useSoftDelete = $this->option('with-soft-delete');
        $useTimeStamps = !$this->option('without-timestamps');

        $modelDirectory = trim($this->option('model-directory'));
        $resourceFile = trim($this->option('resource-file')) ?: Helpers::makeJsonFileName($modelName);
        $languageFileName = $this->option('language-filename') ?: self::makeLocaleGroup($modelName);
        $template = $this->getTemplateName();
        $extends = $this->generatorOption('model-extends');

        return (object) compact(
            'table',
            'primaryKey',
            'useSoftDelete',
            'useTimeStamps',
            'resourceFile',
            'template',
            'modelName',
            'modelDirectory',
            'languageFileName'
        );
    }

    /**
     * Gets the full class name to extend
     *
     * @return string
     */
    protected function getFullClassToExtend()
    {
        $extend = $this->generatorOption('model-extends');

        if (empty($extend)) {
            return '';
        }

        if ($this->isExtendsDefault()) {
            return $this->getDefaultClassToExtend();
        }

        return Helpers::fixNamespace($extend);
    }

    /**
     * Checks if the model extends the default "Model" class
     *
     * @return bool
     */
    protected function isExtendsDefault()
    {
        return $this->option('model-extends') == 'default-model';
    }

    /**
     * Gets the default class to extend
     *
     * @return string
     */
    protected function getDefaultClassToExtend()
    {
        return 'Illuminate\Database\Eloquent\Model';
    }

    /**
     * Gets the full use statement for the classes
     *
     * @param array $additions
     *
     * @return string
     */
    protected function getRequiredUseClasses(array $additions = [])
    {
        $commands = [];

        foreach ($additions as $addition) {
            $commands[] = $this->getUseClassCommand($addition);
        }

        $commands = array_unique($commands);
        sort($commands);

        return implode(PHP_EOL, $commands);
    }

    /**
     * Gets the relations methods.
     *
     * @param  array $relationships
     * @param  array $fields
     *
     * @return array
     */
    protected function getRelationMethods(array $relations, array $fields)
    {
        $methods = [];

        foreach ($fields as $field) {
            if ($field->hasForeignRelation()) {
                $relation = $field->getForeignRelation();
                $methods[$relation->name] = $this->getRelationshipMethod($relation);
            }
        }

        foreach ($relations as $relation) {
            $methods[$relation->name] = $this->getRelationshipMethod($relation);
        }

        return $methods;
    }

    /**
     * Wraps each non-empty item in an array with single quote.
     *
     * @param  array  $arrguments
     *
     * @return string
     */
    protected function joinArguments(array $arrguments)
    {
        return implode(',', Arr::wrapItems(Arr::removeEmptyItems($arrguments)));
    }

    /**
     * Gets accessors for each field that accepts multiple answers.
     *
     * @param  array  $fields
     *
     * @return string
     */
    protected function getAccessors(array $fields = null)
    {
        $accessors = [];

        foreach ($fields as $field) {
            if ($field->isMultipleAnswers()) {
                $content = $this->getStubContent('model-accessor-multiple-answers');

                $accessors[] = $this->getAccessor($field, $content);
            }

            if ($field->isDateOrTime() && !$field->isDate) {
                // We should not create accessor for a datetime field that is casted to Carbon object
                $content = $this->getStubContent('model-accessor-datetime');
                $this->replaceDateFormat($content, $field->dateFormat);

                $accessors[] = $this->getAccessor($field, $content);
            }
        }

        return implode(PHP_EOL, $accessors);
    }

    /**
     * Gets mutators for each field that accepts multiple answers.
     *
     * @param  array  $fields
     *
     * @return string
     */
    protected function getMutators(array $fields = null)
    {
        $mutators = [];

        foreach ($fields as $field) {
            if ($field->isAutoManaged()) {
                continue;
            }

            if ($field->isMultipleAnswers()) {
                $content = $this->getStubContent('model-mutator-multiple-answers');
                $this->replaceFieldName($content, $field->name);

                $mutators[] = $this->getMutator($field, $content);
            }

            if ($field->isDateOrTime()) {
                $content = $this->getStubContent('model-mutator-datetime');
                $this->replaceFieldName($content, $field->name);

                $mutators[] = $this->getMutator($field, $content);
            }
        }

        return implode(PHP_EOL, $mutators);
    }

    /**
     * Gets accessor for a given field.
     *
     * @param  CrestApps\CodeGenerator\Models\Field  $field
     * @param  string $content
     * @return string
     */
    protected function getAccessor(Field $field, $content)
    {
        $stub = $this->getStubContent('model-accessor');

        $this->replaceFieldName($stub, $field->name)
            ->replaceFieldContent($stub, $content);

        return $stub;
    }

    /**
     * Gets mutator for a given field.
     *
     * @param  CrestApps\CodeGenerator\Models\Field  $field
     * @param  string $content
     *
     * @return string
     */
    protected function getMutator(Field $field, $content)
    {
        $stub = $this->getStubContent('model-mutator');

        $this->replaceFieldName($stub, $field->name)
            ->replaceFieldContent($stub, $content);

        return $stub;
    }

    /**
     * Creates the code for a relationship.
     *
     * @param CrestApps\CodeGenerator\Models\ForeignRelation $relation
     *
     * @return string
     */
    protected function getRelationshipMethod(ForeignRelationship $relation)
    {
        $stub = $this->getStubContent('model-relation');

        $this->replaceRelationName($stub, $relation->name)
            ->replaceRelationType($stub, $relation->getType())
            ->replaceRelationParams($stub, $this->joinArguments($relation->parameters))
            ->replaceRelationReturnType($stub, $this->getRelationReturnType($relation));

        return $stub;
    }

    /**
     * Gets the return type for the given relationship
     *
     * @param CrestApps\CodeGenerator\Models\ForeignRelation $relation
     *
     * @return string
     */
    protected function getRelationReturnType(ForeignRelationship $relation)
    {
        if ($relation->isSingleRelation()) {
            return $relation->getFullForeignModel();
        }

        return 'Illuminate\Database\Eloquent\Collection';
    }

    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     * @return string
     */
    protected function getNewPrimaryKey(Field $primaryKey = null, $defaultName = 'id')
    {
        $stub = $this->getStubContent('model-primary-key');

        $this->replacePrimaryKey($stub, $this->getPrimaryKeyName($primaryKey, $defaultName));

        if (!is_null($primaryKey) && !$primaryKey->isNumeric()) {
            $lines = explode("\n", $stub);
            $last = end($lines);
            $spaces = str_repeat(' ', $this->getIndent($last, 'p'));
            $stub .= PHP_EOL . $spaces . 'protected $keyType = \'string\';' . PHP_EOL;
            $stub .= $spaces . 'public $incrementing = false;' . PHP_EOL;
        }

        return $stub;
    }

    /**
     * Gets any additional classes to include in the use statement
     *
     * @param object $input
     *
     * @return array
     */
    protected function getAdditionalNamespaces($input)
    {
        $commands = [
            // Here add the using soft delete
            $this->getFullClassToExtend(),
        ];

        if ($input->useSoftDelete) {
            $commands[] = 'Illuminate\Database\Eloquent\SoftDeletes';
        }

        return $commands;
    }

    /**
     * Gets the timestamp block.
     *
     * @param  bool  $shouldUseTimeStamps
     * @param  bool  $hasUpdatedAt
     *
     * @return string
     */
    protected function getTimeStampsStub($shouldUseTimeStamps, $hasUpdatedAt)
    {
        if (!$shouldUseTimeStamps || !$hasUpdatedAt) {
            return $this->getStubContent('model-timestamps');
        }

        return null;
    }

    /**
     * Replaces date format for the given field.
     *
     * @param  string  $stub
     * @param  string  $format
     *
     * @return $this
     */
    protected function replaceDateFormat(&$stub, $format)
    {
        return $this->replaceTemplate('date_format', $format, $stub);
    }

    /**
     * Replaces date format for the given field.
     *
     * @param  string  $stub
     * @param  string  $format
     *
     * @return $this
     */
    protected function replaceRelationReturnType(&$stub, $format)
    {
        return $this->replaceTemplate('relation_return_type', $format, $stub);
    }

    /**
     * Replaces content of the given stub.
     *
     * @param  string  $stub
     * @param  string  $content
     *
     * @return $this
     */
    protected function replaceFieldContent(&$stub, $content)
    {
        return $this->replaceTemplate('content', $content, $stub);
    }

    /**
     * Replaces useCommandPlaceHolder
     *
     * @param  string  $stub
     * @param  string  $commands
     *
     * @return $this
     */
    protected function replaceUseCommandPlaceholder(&$stub, $commands)
    {
        return $this->replaceTemplate('use_command_placeholder', $commands, $stub);
    }

    /**
     * Replaces the table for the given stub.
     *
     * @param  string  $stub
     * @param  string  $table
     *
     * @return $this
     */
    protected function replaceTable(&$stub, $table)
    {
        return $this->replaceTemplate('table', $table, $stub);
    }

    /**
     * Replaces the accessors for the given stub.
     *
     * @param  string  $stub
     * @param  string  $accessors
     *
     * @return $this
     */
    protected function replaceAccessors(&$stub, $accessors)
    {
        return $this->replaceTemplate('accessors', $accessors, $stub);
    }

    /**
     * Replaces the dates for the given stub.
     *
     * @param  string  $stub
     * @param  string  $dates
     *
     * @return $this
     */
    protected function replaceDateFields(&$stub, $dates)
    {
        return $this->replaceTemplate('dates', $dates, $stub);
    }

    /**
     * Replaces the casts for the given stub.
     *
     * @param  string  $stub
     * @param  string  $casts
     *
     * @return $this
     */
    protected function replaceCasts(&$stub, $casts)
    {
        return $this->replaceTemplate('casts', $casts, $stub);
    }

    /**
     * Replaces the delimiter for the given stub.
     *
     * @param  string  $stub
     * @param  string  $delimiter
     *
     * @return $this
     */
    protected function replaceDelimiter(&$stub, $delimiter)
    {
        return $this->replaceTemplate('delimiter', $delimiter, $stub);
    }

    /**
     * Replaces the mutators for the given stub.
     *
     * @param  string  $stub
     * @param  string  $mutators
     *
     * @return $this
     */
    protected function replaceMutators(&$stub, $mutators)
    {
        return $this->replaceTemplate('mutators', $mutators, $stub);
    }

    /**
     * Replaces the field name for the given stub.
     *
     * @param  string  $stub
     * @param  string  $name
     *
     * @return $this
     */
    protected function replaceFieldName(&$stub, $name)
    {
        $stub = $this->strReplace('field_name', $name, $stub);
        $stub = $this->strReplace('field_name_cap', ucwords(camel_case($name)), $stub);

        return $this;
    }

    /**
     * Replaces useSoftDelete and useSoftDeleteTrait for the given stub.
     *
     * @param  string  $stub
     * @param  bool  $shouldUseSoftDelete
     *
     * @return $this
     */
    protected function replaceSoftDelete(&$stub, $shouldUseSoftDelete)
    {
        if ($shouldUseSoftDelete) {
            $stub = $this->strReplace('use_soft_delete', PHP_EOL . 'use Illuminate\Database\Eloquent\SoftDeletes;' . PHP_EOL, $stub);
            $stub = $this->strReplace('use_soft_delete_trait', PHP_EOL . '    use SoftDeletes;' . PHP_EOL, $stub);

            return $this;
        }

        $stub = $this->strReplace('use_soft_delete', null, $stub);
        $stub = $this->strReplace('use_soft_delete_trait', null, $stub);

        return $this;
    }

    /**
     * Replaces the fillable for the given stub.
     *
     * @param  string  $stub
     * @param  string  $fillable
     *
     * @return $this
     */
    protected function replaceFillable(&$stub, $fillable)
    {
        return $this->replaceTemplate('fillable', $fillable, $stub);
    }

    /**
     * Replaces the primary key for the given stub.
     *
     * @param  string  $stub
     * @param  string  $primaryKey
     *
     * @return $this
     */
    protected function replacePrimaryKey(&$stub, $primaryKey)
    {
        return $this->replaceTemplate('primary_key', $primaryKey, $stub);
    }

    /**
     * Replaces the replationships for the given stub.
     *
     * @param $stub
     * @param array $relationMethods
     *
     * @return $this
     */
    protected function replaceRelationshipPlaceholder(&$stub, array $relationMethods)
    {
        return $this->replaceTemplate('relationships', implode("\r\n", $relationMethods), $stub);
    }

    /**
     * Replaces the replation type for the given stub.
     *
     * @param string $stub
     * @param string $type
     *
     * @return $this
     */
    protected function replaceRelationType(&$stub, $type)
    {
        return $this->replaceTemplate('relation_type', $type, $stub);
    }

    /**
     * Replaces the replation name for the given stub.
     *
     * @param string $stub
     * @param string $name
     *
     * @return $this
     */
    protected function replaceRelationName(&$stub, $name)
    {
        return $this->replaceTemplate('relation_name', $name, $stub);
    }

    /**
     * Replaces the replation params for the given stub.
     *
     * @param string $stub
     * @param string $params
     *
     * @return $this
     */
    protected function replaceRelationParams(&$stub, $params)
    {
        return $this->replaceTemplate('relation_params', $params, $stub);
    }

    /**
     * Replace the table for the given stub.
     *
     * @param  string  $stub
     * @param  string  $timestamp
     *
     * @return $this
     */
    protected function replaceTimestamps(&$stub, $timestamp)
    {
        return $this->replaceTemplate('time_stamps', $timestamp, $stub);
    }
}
