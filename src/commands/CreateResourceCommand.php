<?php

namespace CrestApps\CodeGenerator\Commands;

use Illuminate\Console\Command;
use CrestApps\CodeGenerator\Support\Helpers;
use CrestApps\CodeGenerator\Traits\CommonCommand;

class CreateResourceCommand extends Command
{
    use CommonCommand;
    
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:resource
                            {model-name : The model name that this resource will represent.}
                            {--controller-name= : The name of the controler.}
                            {--controller-directory= : The directory where the controller should be created under. }
                            {--model-directory= : The path of the model.}
                            {--views-directory= : The name of the view path.}
                            {--fields= : Fields to use for creating the validation rules.}
                            {--fields-file= : File name to import fields from.}
                            {--routes-prefix=model-name-as-plural : Prefix of the route group.}
                            {--models-per-page=25 : The amount of models per page for index pages.}
                            {--lang-file-name= : The languages file name to put the labels in.}
                            {--with-form-request : This will extract the validation into a request form class.}
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
                            {--force : This option will override the controller if one already exists.}';


    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new resource.';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $input = $this->getCommandInput();

        if($input->tableExists)
        {
            $input->fields = null;
            $input->fieldsFile = $input->table . '.json';
            $input->withoutMigration = true;

            $this->createFieldsFile($input);

        }

        $fields = $this->getFields($input->fields, $input->languageFileName, $input->fieldsFile);

        if(empty($fields) || !isset($fields[0]))
        {
            $this->error('You must provide at least one field to generate the views!');
        } else 
        {

            $this->createModel($input)
                 ->createController($input)
                 ->createRoutes($input)
                 ->createViews($input)
                 ->createLanguage($input)
                 ->createMigration($input)
                 ->info('All Done!');
        }

    }

    /**
     * Execute the command that generates a migration
     * 
     * @param object $input
     * @return $this
     */
    protected function createMigration($input)
    {
        if(!$input->withoutMigration)
        {
            $this->call('create:migration', 
                [
                    'table-name' => $input->table,
                    '--migration-class-name' => $input->migrationClass,
                    '--connection-name' => $input->connectionName,
                    '--indexes' => $input->indexes,
                    '--foreign-keys' => $input->foreignKeys,
                    '--engine-name' => $input->engineName,
                    '--fields' => $input->fields,
                    '--fields-file' => $input->fieldsFile,
                    '--force' => $input->force,
                    '--template-name' => $input->template,
                    '--without-timestamps' => $input->withoutTimeStamps,
                ]);
        }

        return $this;
    }

    /**
     * Execute the command that generate fields' file.
     * 
     * @param object $input
     * @return $this
     */
    protected function createFieldsFile($input)
    {
        $this->callSilent('create:fields-file', 
            [
                'table-name' => $input->table,
                '--force' => $input->force
            ]);

        return $this;
    }

    /**
     * Execute the command that generates a language
     * 
     * @param object $input
     * @return $this
     */
    protected function createLanguage($input)
    {
        $this->callSilent('create:language', 
            [
                'language-file-name' => $input->languageFileName,
                '--fields' => $input->fields,
                '--fields-file' => $input->fieldsFile,
                '--template-name' => $input->template
            ]);

        return $this;
    }

    /**
     * Execute the command that generates all default views
     * 
     * @param object $input
     * @return $this
     */
    protected function createViews($input)
    {
        $this->call('create:views', 
            [
                'model-name' => $input->modelName,
                '--fields' => $input->fields,
                '--fields-file' => $input->fieldsFile,
                '--views-directory' => $input->viewsDirectory,
                '--routes-prefix' => $input->prefix,
                '--layout-name' => $input->layoutName,
                '--force' => $input->force,
                '--template-name' => $input->template
            ]);

        return $this;
    }

    /**
     * Execute the command that generates the routes
     * 
     * @param object $input
     * @return $this
     */
    protected function createRoutes($input)
    {
        $this->call('create:routes', 
            [
                'controller-name' => $input->controllerName,
                '--model-name' => $input->modelName,
                '--routes-prefix' => $input->prefix,
                '--template-name' => $input->template
            ]);

        return $this;
    }

    /**
     * Execute the command that generates a controller
     * 
     * @param object $input
     * @return $this
     */
    protected function createController($input)
    {

        $this->call('create:controller', 
            [
                'controller-name' => $input->controllerName,
                '--model-name' => $input->modelName,
                '--controller-directory' => $input->controllerDirectory,
                '--model-directory' => $input->modelDirectory,
                '--views-directory' => $input->viewsDirectory,
                '--fields' => $input->fields,
                '--fields-file' => $input->fieldsFile,
                '--routes-prefix' => $input->prefix,
                '--lang-file-name' => $input->languageFileName,
                '--with-form-request' => $input->formRequest,
                '--force' => $input->force,
                '--template-name' => $input->template
            ]);

        return $this;
    }

    /**
     * Execute the command that generates a model
     * 
     * @param object $input
     * @return $this
     */
    protected function createModel($input)
    {
        $this->call('create:model', 
            [
                'model-name' => $input->modelName,
                '--table-name' => $input->table,
                '--fillable' => $input->fillable,
                '--relationships' => $input->relationships,
                '--primary-key' => $input->primaryKey,
                '--fields' => $input->fields,
                '--fields-file' => $input->fieldsFile,
                '--model-directory' => $input->modelDirectory,
                '--with-soft-delete' => $input->useSoftDelete,
                '--without-timestamps' => $input->withoutTimeStamps,
                '--force' => $input->force,
                '--template-name' => $input->template
            ]);

        return $this;
    }

    /**
     * Gets a clean user inputs.
     *
     * @return object
     */
    protected function getCommandInput()
    {
        $modelName = trim($this->argument('model-name'));
        $modelNamePlural = strtolower(str_plural($modelName));
        $controllerName = trim($this->option('controller-name') ?: ucfirst(Helpers::postFixWith(str_plural($modelName), 'Controller')));
        $viewsDirectory = trim($this->option('views-directory'));
        $prefix = trim($this->option('routes-prefix'));
        $prefix = $prefix == 'model-name-as-plural' ? $modelNamePlural : $prefix;
        $perPage = intval($this->option('models-per-page'));
        $fields = trim($this->option('fields'));
        $fieldsFile = trim($this->option('fields-file'));
        $languageFileName = trim($this->option('lang-file-name')) ?: $modelNamePlural;
        $formRequest = $this->option('with-form-request');
        $controllerDirectory = trim($this->option('controller-directory'));
        $withoutMigration = $this->option('without-migration');
        $force = $this->option('force');
        $modelDirectory = trim($this->option('model-directory'));
        $table = trim($this->option('table-name')) ?: $modelNamePlural;
        $fillable = trim($this->option('fillable'));
        $primaryKey = trim($this->option('primary-key'));
        $relationships = trim($this->option('relationships'));
        $useSoftDelete = $this->option('with-soft-delete');
        $withoutTimeStamps = $this->option('without-timestamps');
        $migrationClass = trim($this->option('migration-class-name'));
        $connectionName = trim($this->option('connection-name'));
        $indexes = trim($this->option('indexes'));
        $foreignKeys = trim($this->option('foreign-keys'));
        $engineName = trim($this->option('engine-name'));
        $template = $this->getTemplateName();
        $layoutName = trim($this->option('layout-name')) ?: 'layouts.app';
        $tableExists = $this->option('table-exists');

        return (object) compact('modelName','controllerName','viewsDirectory','prefix','perPage','fields','force',
                                'languageFileName','fieldsFile','formRequest','modelDirectory','table','fillable','primaryKey',
                                'relationships','useSoftDelete','withoutTimeStamps','controllerDirectory','withoutMigration',
                                'migrationClass','connectionName','indexes','foreignKeys','engineName','layoutName','template','tableExists');
    }

}
