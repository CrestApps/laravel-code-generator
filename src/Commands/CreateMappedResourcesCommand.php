<?php

namespace CrestApps\CodeGenerator\Commands;

use Exception;
use Illuminate\Console\Command;
use CrestApps\CodeGenerator\Support\Helpers;
use CrestApps\CodeGenerator\Traits\CommonCommand;
use CrestApps\CodeGenerator\Support\Config;
use CrestApps\CodeGenerator\Models\ResourceInput;

class CreateMappedResourcesCommand extends Command
{
    use CommonCommand;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:mapped-resources
                            {--controller-directory= : The directory where the controller should be created under. }
                            {--controller-extends=Http\Controllers\Controller : The base controller to be extend.}
                            {--model-directory= : The path of the model.}
                            {--views-directory= : The name of the view path.}
                            {--models-per-page=25 : The amount of models per page for index pages.}
                            {--with-form-request : This will extract the validation into a request form class.}
                            {--with-auth : Generate the controller with Laravel auth middlewear. }
                            {--with-soft-delete : Enables softdelete future should be enable in the model.}
                            {--without-timestamps : Prevent Eloquent from maintaining both created_at and the updated_at properties.}
                            {--without-migration : Prevent creating a migration for this resource.}
                            {--connection-name= : A specific connection name.}
                            {--engine-name= : A specific engine name.}
                            {--layout-name=layouts.app : This will extract the validation into a request form class.}
                            {--template-name= : The template name to use when generating the code.}
                            {--table-exists : This option will attempt to fetch the field from existing database table.}
                            {--primary-key=id : The name of the primary key.}
                            {--translation-for= : A comma seperated string of languages to create fields for.}
                            {--mapping-filename= : The name of the resource mapping file.}
                            {--force : This option will override the controller if one already exists.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create all resources for every model listed in the resources mapping file.';

    /**
     * Executes the console command.
     *
     * @return void
     */
    public function handle()
    {
        $content = $this->getFileContent($this->getMappingFile());
        $objects = json_decode($content);

        if (!is_array($objects)) {
            throw new Exception('The mapping-file does not contain a valid array.');
        }

        $validInputs = $this->getValidInputs($objects, $this->getCommandInput());

        foreach ($validInputs as $validInput) {
            $this->call('create:resources',
                [
                    'model-name'             => $validInput->modelName,
                    '--controller-name'      => $validInput->controllerName,
                    '--controller-directory' => $validInput->controllerDirectory,
                    '--controller-extends'   => $validInput->controllerExtends,
                    '--model-directory'      => $validInput->modelDirectory,
                    '--views-directory'      => $validInput->viewsDirectory,
                    '--fields-file'          => $validInput->fieldsFile,
                    '--fields'               => $validInput->fields,
                    '--routes-prefix'        => $validInput->prefix,
                    '--models-per-page'      => $validInput->perPage,
                    '--lang-file-name'       => $validInput->languageFileName,
                    '--with-form-request'    => $validInput->formRequest,
                    '--with-auth'            => $validInput->withAuth,
                    '--table-name'           => $validInput->table,
                    '--fillable'             => $validInput->fillable,
                    '--primary-key'          => $validInput->primaryKey,
                    '--with-soft-delete'     => $validInput->withSoftDelete,
                    '--without-timestamps'   => $validInput->withoutTimeStamps,
                    '--relationships'        => $validInput->relationships,
                    '--without-migration'    => $validInput->withoutMigration,
                    '--migration-class-name' => $validInput->migrationClass,
                    '--connection-name'      => $validInput->connectionName,
                    '--indexes'              => $validInput->indexes,
                    '--foreign-keys'         => $validInput->foreignKeys,
                    '--engine-name'          => $validInput->engineName,
                    '--layout-name'          => $validInput->layoutName,
                    '--template-name'        => $validInput->template,
                    '--table-exists'         => $validInput->tableExists,
                    '--translation-for'      => $validInput->translationFor,
                    '--force'                => $validInput->force
                ]);

            $this->info('---------------------------------');
        }

        return $this->printInfo('All Done!');
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
            if (!isset($object->{'model-name'})) {
                throw new Exception('Each entry in the mapping file must a have value for model-name');
            }

            $input->modelName = trim($object->{'model-name'});
            $madeupTableName = $this->makeTableName($input->modelName);
            $controllerName = Helpers::makeControllerName($input->modelName);
            $input->fieldsFile = $this->getValue($object, 'fields-file', Helpers::makeJsonFileName($input->modelName));
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
            $input->tableExists = $this->getValue($object, 'table-exists', $input->tableExists);
            $input->translationFor = $this->getValue($object, 'translation-for', $input->translationFor);
            $input->withAuth = $this->getValue($object, 'with-auth', $input->withAuth);

            $fields = $this->getFields($input->fields, $input->languageFileName, $input->fieldsFile);

            $this->validateField($fields);
            $validInputs[] = $input;
        }

        return $validInputs;
    }

    /**
     * Gets the full name of the mapping file.
     *
     * @return string
     */
    protected function getMappingFile()
    {
        $name = trim($this->option('mapping-filename')) ?: Config::getDefaultMapperFileName();

        return base_path(Config::getFieldsFilePath($name));
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
     * Gets a clean user inputs.
     *
     * @return CrestApps\CodeGenerator\Models\ResourceInput
     */
    protected function getCommandInput()
    {
        $input = new ResourceInput(Config::getDefaultMapperFileName());
        $input->viewsDirectory = trim($this->option('views-directory'));
        $input->perPage = intval($this->option('models-per-page'));
        $input->formRequest = $this->option('with-form-request');
        $input->controllerDirectory = $this->option('controller-directory');
        $input->controllerExtends = $this->option('controller-extends') ?: null;
        $input->withoutMigration = $this->option('without-migration');
        $input->force = $this->option('force');
        $input->modelDirectory = $this->option('model-directory');
        $input->primaryKey = $this->option('primary-key');
        $input->withSoftDelete = $this->option('with-soft-delete');
        $input->withoutTimeStamps = $this->option('without-timestamps');
        $input->connectionName = $this->option('connection-name');
        $input->engineName = $this->option('engine-name');
        $input->template = $this->getTemplateName();
        $input->layoutName = $this->option('layout-name') ?: 'layouts.app';
        $input->tableExists = $this->option('table-exists');
        $input->translationFor = $this->option('translation-for');
        $input->withAuth = $this->option('with-auth');

        return $input;
    }
}
