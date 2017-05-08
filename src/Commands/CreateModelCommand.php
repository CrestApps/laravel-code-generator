<?php

namespace CrestApps\CodeGenerator\Commands;

use Illuminate\Console\Command;
use CrestApps\CodeGenerator\Traits\CommonCommand;
use CrestApps\CodeGenerator\Support\Config;
use CrestApps\CodeGenerator\Traits\GeneratorReplacers;
use CrestApps\CodeGenerator\Support\Helpers;
use CrestApps\CodeGenerator\Models\Field;
use CrestApps\CodeGenerator\Models\ForeignRelationship;

class CreateModelCommand extends Command
{
    use CommonCommand, GeneratorReplacers;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:model
                            {model-name : The name of the model.}
                            {--table-name= : The name of the table.}
                            {--fillable= : The exact string to put in the fillable property of the model.}
                            {--relationships= : The relationships for the model.}
                            {--primary-key=id : The name of the primary key.}
                            {--fields= : Fields to use for creating the validation rules.}
                            {--fields-file= : File name to import fields from.}
                            {--model-directory= : The directory where the model should be created.}
                            {--with-soft-delete : Enables softdelete future should be enable in the model.}
                            {--without-timestamps : Prevent Eloquent from maintaining both created_at and the updated_at properties.}
                            {--template-name= : The template name to use when generating the code.}
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
        $stub = $this->getStubContent('model');
        $input = $this->getCommandInput();

        $fields = $this->getFields($input->fields, 'model', $input->fieldsFile);
        
        if ($input->useSoftDelete) {
            $fields = $this->upsertDeletedAt($fields);
        }

        $primaryKey = $this->getNewPrimaryKey($this->getPrimaryKeyName($fields, $input->primaryKey));
        $destenationFile = $this->getDestenationFile($input->modelName);
        
        if ($this->alreadyExists($destenationFile)) {
            $this->error('The model already exists! To override the existing file, use --force option.');

            return false;
        }

        $relations = $this->getRelationMethods($input->relationships, $fields);

        return $this->replaceTable($stub, $input->table)
                    ->replaceModelName($stub, $input->modelName)
                    ->replaceNamespace($stub, $this->getNamespace($input->modelName))
                    ->replaceSoftDelete($stub, $input->useSoftDelete)
                    ->replaceTimestamps($stub, $this->getTimeStampsStub($input->useTimeStamps))
                    ->replaceFillable($stub, $this->getFillables($input->fillable, $fields))
                    ->replaceDateFields($stub, $this->getDateFields($fields))
                    ->replacePrimaryKey($stub, $primaryKey)
                    ->replaceRelationshipPlaceholder($stub, $relations)
                    ->replaceAccessors($stub, $this->getAccessors($fields))
                    ->replaceMutators($stub, $this->getMutators($fields))
                    ->createFile($destenationFile, $stub)
                    ->info('A model was crafted successfully.');
    }

    /**
     * Gets the destenation file to be created.
     *
     * @param array $name
     *
     * @return string
     */
    protected function getDestenationFile($name)
    {
        return app_path() . '/' . Config::getModelsPath($name) . '.php';
    }

    /**
     * Gets the model's namespace.
     *
     * @return string
     */
    protected function getNamespace()
    {
        $namespace = $this->getAppNamespace() . Config::getModelsPath();

        return rtrim(Helpers::convertSlashToBackslash($namespace), '\\');
    }

    /**
     * Gets the correct primary key name.
     *
     * @param array $fields
     * @param string $primaryKey
     *
     * @return string
     */
    protected function getPrimaryKeyName(array $fields, $primaryKey)
    {
        $primaryField = $this->getPrimaryField($fields);

        return !is_null($primaryField) ? $primaryField->name : $primaryKey;
    }

    /**
     * If a giving fields collection does not contain a field called "deleted_at", one will be created.
     *
     * @param array $fields
     *
     * @return array
     */
    protected function upsertDeletedAt(array $fields)
    {
        foreach ($fields as $field) {
            if ($field->name == 'deleted_at') {
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
        $field = new Field('deleted_at');
        $field->isDate = true;

        return $field;
    }
    /**
     * Gets the stub file.
     *
     * @return string
     */
    protected function getStub()
    {
        return $this->getStubByName('model', $this->getTemplateName());
    }

    /**
     * Gets the formatted fillable line.
     *
     * @param $fillables
     * @param array $fields
     *
     * @return string
     */
    protected function getFillables($fillables, array $fields)
    {
        if (!empty($fillables)) {
            return $this->getFillablesFromString($fillables);
        }

        return $this->getFillablefields($fields);
    }

    /**
     * Gets the fillable string from a giving raw string.
     *
     * @return string
     */
    protected function getFillablesFromString($fillablesString)
    {
        $columns = Helpers::removeEmptyItems(explode(',', $fillablesString), function ($column) {
            return trim(Helpers::removeNonEnglishChars($column));
        });

        return sprintf('[%s]', implode(',', Helpers::wrapItems($columns)));
    }

    /**
     * Gets the fillable string from a giving fields array.
     *
     * @return string
     */
    protected function getFillablefields(array $fields)
    {
        $fillables = [];

        foreach ($fields as $field) {
            if ($field->isOnFormView) {
                $fillables[] = sprintf("'%s'", Helpers::removeNonEnglishChars($field->name));
            }
        }

        return sprintf('[%s]', implode(',', $fillables));
    }

    /**
     * Gets the date fields string from a giving fields array.
     *
     * @return string
     */
    protected function getDateFields(array $fields)
    {
        $dates = [];

        foreach ($fields as $field) {
            if ($field->isDate) {
                $dates[] = sprintf("'%s'", Helpers::removeNonEnglishChars($field->name));
            }
        }

        return sprintf('[%s]', implode(',', $dates));
    }

    /**
     * Gets a clean user inputs.
     *
     * @return object
     */
    protected function getCommandInput()
    {
        $table = trim($this->option('table-name')) ?: strtolower(str_plural(trim($this->argument('model-name'))));
        $fillable = trim($this->option('fillable'));
        $primaryKey = trim($this->option('primary-key'));
        $relationships = !empty(trim($this->option('relationships'))) ? explode(',', trim($this->option('relationships'))) : [];
        $useSoftDelete = $this->option('with-soft-delete');
        $useTimeStamps = !$this->option('without-timestamps');
        $fields = trim($this->option('fields'));
        $fieldsFile = trim($this->option('fields-file'));
        $template = $this->getTemplateName();
        $modelName = ucfirst(str_singular($table));

        return (object) compact('table', 'fillable', 'primaryKey', 'relationships', 'useSoftDelete', 'useTimeStamps', 'fields', 'fieldsFile', 'template', 'modelName');
    }

    /**
     * Gets the desired class name from the input.
     *
     * @return string
     */
    protected function getNameInput()
    {
        $path = trim($this->option('model-directory'));

        if (!empty($path)) {
            $path = Helpers::getPathWithSlash(ucfirst($path));
        }

        return Config::getModelsPath() . $path . Helpers::upperCaseEveyWord(trim($this->argument('model-name')));
    }

    /**
     * Gets the desired class name from a path.
     *
     * @return string
     */
    protected function getClassNameFromPath($path)
    {
        return strrpos($path, '\\') === false ?: substr($path, $nameStartIndex + 1);
    }

    /**
     * Gets the relations methods.
     *
     * @param  array $relationships
     * @param  array $fields
     *
     * @return array
     */
    protected function getRelationMethods(array $relationships, array $fields)
    {
        $methods = [];

        foreach ($relationships as $relationship) {
            $relationshipParts = explode('#', $relationship);

            if (count($relationshipParts) != 3) {
                throw new Exception("One or more of the provided relations are not formatted correctly. Make sure your input adheres to the following pattern 'posts#hasMany#App\Post|id|post_id'");
            }

            $methodArguments = explode('|', trim($relationshipParts[2]));
            $relation = new ForeignRelationship(trim($relationshipParts[1]), $methodArguments, trim($relationshipParts[0]));
            $methods[$name] = $this->getRelationshipMethod($relation);
        }

        foreach ($fields as $field) {
            
            if ( $field->hasForeignRelation() ) {
                $relation = $field->getForeignRelation();
                $methods[$relation->name] = $this->getRelationshipMethod($relation);
            }
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
    protected function turnRelationArgumentToString(array $arrguments)
    {
        return implode(',', Helpers::wrapItems(Helpers::removeEmptyItems($arrguments)));
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
            if ($field->isMultipleAnswers) {
                $content = $this->getStubContent('model-accessor-multiple-answers');

                $accessors[] = $this->getAccessor($field, $content);
            }

            if ($field->isDateOrTime()) {
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
            if ($field->isMultipleAnswers) {
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
     * Gets accessor for a giving field.
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
     * Gets mutator for a giving field.
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
             ->replaceRelationParams($stub, $this->turnRelationArgumentToString($relation->parameters));

        return $stub;
    }

    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     * @return string
     */
    protected function getNewPrimaryKey($primaryKey)
    {
        $stub = $this->getStubContent('model-primary-key');

        $this->replacePrimaryKey($stub, $primaryKey);

        return $stub;
    }

    /**
     * Gets the timestamp block.
     *
     * @param  bool  $shouldUseTimeStamps
     *
     * @return string
     */
    protected function getTimeStampsStub($shouldUseTimeStamps)
    {
        if($shouldUseTimeStamps)
        {
            return null;
        }
        return $this->getStubContent('model-timestamps');
    }

    /**
     * Replaces date format for the giving field.
     *
     * @param  string  $stub
     * @param  string  $format
     *
     * @return $this
     */
    protected function replaceDateFormat(&$stub, $format)
    {
        $stub = str_replace('{{dateFormat}}', $format, $stub);

        return $this;
    }

    /**
     * Replaces content of the giving stub.
     *
     * @param  string  $stub
     * @param  string  $content
     *
     * @return $this
     */
    protected function replaceFieldContent(&$stub, $content)
    {
        $stub = str_replace('{{content}}', $content, $stub);

        return $this;
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
        $stub = str_replace('{{table}}', $table, $stub);

        return $this;
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
        $stub = str_replace('{{accessors}}', $accessors, $stub);

        return $this;
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
        $stub = str_replace('{{dates}}', $dates, $stub);

        return $this;
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
        $stub = str_replace('{{delimiter}}', $delimiter, $stub);

        return $this;
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
        $stub = str_replace('{{mutators}}', $mutators, $stub);

        return $this;
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
        $stub = str_replace('{{fieldName}}', $name, $stub);
        $stub = str_replace('{{fieldNameCap}}', ucwords(camel_case($name)), $stub);

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
            $stub = str_replace('{{useSoftDelete}}', PHP_EOL . 'use Illuminate\Database\Eloquent\SoftDeletes;' . PHP_EOL, $stub);
            $stub = str_replace('{{useSoftDeleteTrait}}', PHP_EOL . '    use SoftDeletes;' . PHP_EOL, $stub);

            return $this;
        }

        $stub = str_replace('{{useSoftDelete}}', null, $stub);
        $stub = str_replace('{{useSoftDeleteTrait}}', null, $stub);

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
        $stub = str_replace('{{fillable}}', !empty($fillable) ? $fillable : '[]', $stub);

        return $this;
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
        $stub = str_replace('{{primaryKey}}', $primaryKey, $stub);

        return $this;
    }

    /**
     * Replaces the replationships for the giving stub.
     *
     * @param $stub
     * @param array $relationMethods
     *
     * @return $this
     */
    protected function replaceRelationshipPlaceholder(&$stub, array $relationMethods)
    {
        $stub = str_replace('{{relationships}}', implode("\r\n", $relationMethods), $stub);

        return $this;
    }
    /**
     * Replaces the replation type for the giving stub.
     *
     * @param string $stub
     * @param string $type
     *
     * @return $this
     */
    protected function replaceRelationType(&$stub, $type)
    {
        $stub = str_replace('{{relationType}}', $type, $stub);

        return $this;
    }

    /**
     * Replaces the replation name for the giving stub.
     *
     * @param string $stub
     * @param string $name
     *
     * @return $this
     */
    protected function replaceRelationName(&$stub, $name)
    {
        $stub = str_replace('{{relationName}}', $name, $stub);

        return $this;
    }

    /**
     * Replaces the replation params for the giving stub.
     *
     * @param string $stub
     * @param string $params
     *
     * @return $this
     */
    protected function replaceRelationParams(&$stub, $params)
    {
        $stub = str_replace('{{relationParams}}', $params, $stub);

        return $this;
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
        $stub = str_replace('{{timeStamps}}', $timestamp, $stub);
        
        return $this;
    }
}
