<?php

namespace CrestApps\CodeGenerator\Commands;

use Exception;
use Illuminate\Console\Command;
use CrestApps\CodeGenerator\Support\Helpers;
use CrestApps\CodeGenerator\Traits\CommonCommand;
use CrestApps\CodeGenerator\Support\Config;
use CrestApps\CodeGenerator\Models\ResourceInput;

class CreateResourcesCommand extends Command
{
    use CommonCommand;
    
    /**
     * The prefix string to identify a mapping file to create multiple resource
     *
     * @var string
     */
    protected $mapperPrefix = 'mapping-file=';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:resources
                            {model-name : The model name that this resource will represent.}
                            {--controller-name= : The name of the controler.}
                            {--controller-directory= : The directory where the controller should be created under. }
                            {--controller-extends=Http\Controllers\Controller : The base controller to be extend.}
                            {--model-directory= : The path of the model.}
                            {--views-directory= : The name of the view path.}
                            {--fields= : Fields to use for creating the validation rules.}
                            {--fields-file= : File name to import fields from.}
                            {--routes-prefix=model-name-as-plural : Prefix of the route group.}
                            {--models-per-page=25 : The amount of models per page for index pages.}
                            {--lang-file-name= : The languages file name to put the labels in.}
                            {--with-form-request : This will extract the validation into a request form class.}
                            {--with-auth : Generate the controller with Laravel auth middlewear. }
                            {--table-name= : The name of the table.}
                            {--fillable= : The exact string to put in the fillable property of the model.}
                            {--primary-key=id : The name of the primary key.}
                            {--with-soft-delete : Enables softdelete future should be enable in the model.}
                            {--without-timestamps : Prevent Eloquent from maintaining both created_at and the updated_at properties.}
                            {--relationships= : The relationships for the model.}
                            {--without-migration : Prevent creating a migration for this resource.}
                            {--migration-class-name= : The name of the migration class.}
                            {--connection-name= : A specific connection name.}
                            {--indexes= : A list of indexes to be add.}
                            {--foreign-keys= : A list of the foreign-keys to be add.}
                            {--engine-name= : A specific engine name.}
                            {--layout-name=layouts.app : This will extract the validation into a request form class.}
                            {--template-name= : The template name to use when generating the code.}
                            {--table-exists : This option will attempt to fetch the field from existing database table.}
                            {--translation-for= : A comma seperated string of languages to create fields for.}
                            {--force : This option will override the controller if one already exists.}';


    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create all resources for a model.';

    /**
     * Executes the console command.
     *
     * @return void
     */
    public function handle()
    {
        $input = $this->getCommandInput();

        if ($input->tableExists) {
            $input->fields = null;
            $input->fieldsFile = $input->table . '.json';
            $input->withoutMigration = true;

            $this->createFieldsFile($input);
        }

        if (starts_with($input->modelName, $this->mapperPrefix)) {
            $filename = str_replace($this->mapperPrefix, '', $input->modelName);

            $objects = json_decode(Helpers::jsonFileContent($filename));

            if (!is_array($objects)) {
                throw new Exception('The mapping-file does not contain a valid array.');
            }

            $validInputs = $this->getValidInputs($objects, $input);

            foreach ($validInputs as $validInput) {
                $this->printInfo('Scaffolding resources for ' . $validInput->modelName . '...')
                     ->createModel($validInput)
                     ->createController($validInput)
                     ->createRoutes($validInput)
                     ->createLanguage($validInput)
                     ->createViews($validInput)
                     ->createMigration($validInput)
                     ->info('---------------------------------');
            }

            return $this->printInfo('All Done!');
        }

        $fields = $this->getFields($input->fields, $input->languageFileName ?: 'generic', $input->fieldsFile);

        $this->validateField($fields)
             ->printInfo('Scaffolding...')
             ->createModel($input)
             ->createController($input)
             ->createRoutes($input)
             ->createLanguage($input)
             ->createViews($input)
             ->createMigration($input)
             ->info('All Done!');
    }

    /**
     * Gets valid input collection
     *
     * @param array $object
     * @param CrestApps\CodeGenerator\Models\ResourceInput $input
     *
     * @return array of CrestApps\CodeGenerator\Models\ResourceInput
     */
    protected function getValidInputs(array $objects, ResourceInput $originalInput)
    {
        $validInputs = [];
        
        foreach ($objects as $object) {
            $input = clone $originalInput;
            if(!isset($object->{'model-name'})) {
                throw new Exception('Each entry in the mapping file must a have value for model-name');
            }

            $madeupTableName = $this->makeTableName($object->{'model-name'});
            $controllerName = ucfirst(Helpers::postFixWith(str_plural($object->{'model-name'}), 'Controller'));
            $input->modelName = $object->{'model-name'};
            $input->fieldsFile = $this->getValue($object, 'fields-file', Helpers::makeJsonFileName($modelName));
            $input->table = $this->getValue($object, 'table-name', $madeupTableName);
            $input->fields = null;
            $input->prefix = $this->getValue($object, 'routes-prefix', $madeupTableName);
            $input->controllerName = $this->getValue($object, 'controller-name', $controllerName);
            $input->languageFileName = $this->getValue($object, 'lang-file-name', $madeupTableName);
            $input->table = $this->getValue($object, 'table-name', $madeupTableName);
            $input->viewsDirectory = $this->getValue($object, 'views-directory', $input->viewsDirectory);
            $input->perPage = $this->getValue($object, 'models-per-page', $input->perPage);
            $input->formRequest = $this->getValue($object, 'with-form-request', $input->formRequest);
            $input->controllerDirectory = $this->getValue($object, 'controller-directory', $input->controllerDirectory);
            $input->controllerExtends = $this->getValue($object, 'controller-extends', $input->controllerExtends);
            $input->withoutMigration = $this->getValue($object, 'without-migration', $input->withoutMigration);
            $input->force = $this->getValue($object, 'force', $input->force);
            $input->modelDirectory = $this->getValue($object, 'model-directory', $input->modelDirectory);
            $input->fillable = $this->getValue($object, 'fillable', $input->fillable);
            $input->primaryKey = $this->getValue($object, 'primary-key', $input->primaryKey);
            $input->relationships = $this->getValue($object, 'relationships', $input->relationships);
            $input->withSoftDelete = $this->getValue($object, 'with-soft-delete', $input->withSoftDelete);
            $input->withoutTimeStamps = $this->getValue($object, 'without-timestamps', $input->withoutTimeStamps);
            $input->migrationClass = $this->getValue($object, 'migration-class-name', $input->migrationClass);
            $input->connectionName = $this->getValue($object, 'connection-name', $input->connectionName);
            $input->indexes = $this->getValue($object, 'indexes', $input->indexes);
            $input->foreignKeys = $this->getValue($object, 'foreign-keys', $input->foreignKeys);
            $input->engineName = $this->getValue($object, 'engine-name', $input->engineName);
            $input->template = $this->getValue($object, 'template-name', $input->template);
            $input->layoutName = $this->getValue($object, 'layout-name', $input->layoutName);
            $input->tableExists = $this->getValue($object, 'table-exists', $input->layoutName);
            $input->translationFor = $this->getValue($object, 'translation-for', $input->translationFor);
            $input->withAuth = $this->getValue($object, 'with-auth', $input->withAuth);

            $fields = $this->getFields($input->fields, $input->languageFileName, $input->fieldsFile);

            $this->validateField($fields);
            $validInputs[] = $input;
        }

        return $validInputs;
    }

    /**
     * Gets the value of a property of a givig object if exists.
     *
     * @param object $object
     * @param string $name
     * @param mix $default
     *
     * @return mix
     */
    protected function getValue($object, $name, $default = null)
    {
        if (isset($object->{$name})) {
            return $object->{$name};
        }

        return $default;
    }

    /**
     * Prints a message
     *
     * @param string $message
     *
     * @return $this
     */
    protected function printInfo($message)
    {
        $this->info($message);

        return $this;
    }

    /**
     * Ensured fields contains at least one field.
     *
     * @param array $fields
     *
     * @return $this
     */
    protected function validateField($fields)
    {
        if (empty($fields) || !isset($fields[0])) {
            throw new Exception('You must provide at least one field to generate the views!');
        }

        return $this;
    }

    /**
     * Executes the command that generates a migration.
     *
     * @param CrestApps\CodeGenerator\Models\ResourceInput $input
     *
     * @return $this
     */
    protected function createMigration($input)
    {
        if (!$input->withoutMigration) {
            $this->call('create:migration',
                [
                    'model-name'             => $input->modelName,
                    '--table-name'           => $input->table,
                    '--migration-class-name' => $input->migrationClass,
                    '--connection-name'      => $input->connectionName,
                    '--indexes'              => $input->indexes,
                    '--foreign-keys'         => $input->foreignKeys,
                    '--engine-name'          => $input->engineName,
                    '--fields'               => $input->fields,
                    '--fields-file'          => $input->fieldsFile,
                    '--force'                => $input->force,
                    '--template-name'        => $input->template,
                    '--without-timestamps'   => $input->withoutTimeStamps,
                    '--with-soft-delete'     => $input->withSoftDelete,
                ]);
        }

        return $this;
    }

    /**
     * Executes the command that generate fields' file.
     *
     * @param CrestApps\CodeGenerator\Models\ResourceInput $input
     *
     * @return $this
     */
    protected function createFieldsFile($input)
    {
        $this->callSilent('create:fields-file',
            [
                'table-name'        => $input->table,
                '--force'           => $input->force,
                '--translation-for' => $input->translationFor,
            ]);

        return $this;
    }

    /**
     * Executes the command that generates a language files.
     *
     * @param CrestApps\CodeGenerator\Models\ResourceInput $input
     *
     * @return $this
     */
    protected function createLanguage($input)
    {
        $this->callSilent('create:language',
            [
                'model-name'           => $input->modelName,
                '--language-file-name' => $input->languageFileName,
                '--fields'             => $input->fields,
                '--fields-file'        => $input->fieldsFile,
                '--template-name'      => $input->template
            ]);

        return $this;
    }

    /**
     * Executes the command that generates all the views.
     *
     * @param CrestApps\CodeGenerator\Models\ResourceInput $input
     *
     * @return $this
     */
    protected function createViews($input)
    {
        $this->call('create:views',
            [
                'model-name'        => $input->modelName,
                '--fields'          => $input->fields,
                '--fields-file'     => $input->fieldsFile,
                '--views-directory' => $input->viewsDirectory,
                '--routes-prefix'   => $input->prefix,
                '--layout-name'     => $input->layoutName,
                '--force'           => $input->force,
                '--template-name'   => $input->template
            ]);

        return $this;
    }

    /**
     * Executes the command that generates the routes.
     *
     * @param CrestApps\CodeGenerator\Models\ResourceInput $input
     *
     * @return $this
     */
    protected function createRoutes($input)
    {
        $this->call('create:routes',
            [
                'model-name'               => $input->modelName,
                '--controller-name'        => $input->controllerName,
                '--routes-prefix'          => $input->prefix,
                '--template-name'          => $input->template,
                '--controller-directory'   => $input->controllerDirectory
            ]);

        return $this;
    }

    /**
     * Executes the command that generates the controller.
     *
     * @param CrestApps\CodeGenerator\Models\ResourceInput $input
     * @return $this
     */
    protected function createController($input)
    {
        $this->call('create:controller',
            [
                'model-name'               => $input->modelName,
                '--controller-name'        => $input->controllerName,
                '--controller-directory'   => $input->controllerDirectory,
                '--controller-extends'     => $input->controllerExtends,
                '--model-directory'        => $input->modelDirectory,
                '--views-directory'        => $input->viewsDirectory,
                '--fields'                 => $input->fields,
                '--fields-file'            => $input->fieldsFile,
                '--routes-prefix'          => $input->prefix,
                '--lang-file-name'         => $input->languageFileName,
                '--with-form-request'      => $input->formRequest,
                '--force'                  => $input->force,
                '--with-auth'              => $input->withAuth,
                '--template-name'          => $input->template
            ]);

        return $this;
    }

    /**
     * Executes the command that generates a model.
     *
     * @param CrestApps\CodeGenerator\Models\ResourceInput $input
     *
     * @return $this
     */
    protected function createModel($input)
    {
        $this->call('create:model',
            [
                'model-name'           => $input->modelName,
                '--table-name'         => $input->table,
                '--fillable'           => $input->fillable,
                '--relationships'      => $input->relationships,
                '--primary-key'        => $input->primaryKey,
                '--fields'             => $input->fields,
                '--fields-file'        => $input->fieldsFile,
                '--model-directory'    => $input->modelDirectory,
                '--with-soft-delete'   => $input->withSoftDelete,
                '--without-timestamps' => $input->withoutTimeStamps,
                '--force'              => $input->force,
                '--template-name'      => $input->template
            ]);

        return $this;
    }

    /**
     * Gets a clean user inputs.
     *
     * @return CrestApps\CodeGenerator\Models\ResourceInput
     */
    protected function getCommandInput()
    {
        $input = new ResourceInput(trim($this->argument('model-name')));
        $prefix = $this->option('routes-prefix');
        $input->prefix = ($prefix == 'model-name-as-plural') ? $this->makeTableName($input->modelName) : $prefix;
        $input->languageFileName = trim($this->option('lang-file-name'));
        $input->table = trim($this->option('table-name'));
        $input->viewsDirectory = trim($this->option('views-directory'));
        $input->controllerName = trim($this->option('controller-name')) ?: Helpers::makeControllerName($input->modelName);
        $input->perPage = intval($this->option('models-per-page'));
        $input->fields = $this->option('fields');
        $input->fieldsFile = trim($this->option('fields-file')) ?: Helpers::makeJsonFileName($input->modelName);
        $input->formRequest = $this->option('with-form-request');
        $input->controllerDirectory = $this->option('controller-directory');
        $input->controllerExtends = $this->option('controller-extends') ?: null;
        $input->withoutMigration = $this->option('without-migration');
        $input->force = $this->option('force');
        $input->modelDirectory = $this->option('model-directory');
        $input->fillable = $this->option('fillable');
        $input->primaryKey = $this->option('primary-key');
        $input->relationships = $this->option('relationships');
        $input->withSoftDelete = $this->option('with-soft-delete');
        $input->withoutTimeStamps = $this->option('without-timestamps');
        $input->migrationClass = $this->option('migration-class-name');
        $input->connectionName = $this->option('connection-name');
        $input->indexes = $this->option('indexes');
        $input->foreignKeys = $this->option('foreign-keys');
        $input->engineName = $this->option('engine-name');
        $input->template = $this->getTemplateName();
        $input->layoutName = $this->option('layout-name') ?: 'layouts.app';
        $input->tableExists = $this->option('table-exists');
        $input->translationFor = $this->option('translation-for');
        $input->withAuth = $this->option('with-auth');

        return $input;
    }
}