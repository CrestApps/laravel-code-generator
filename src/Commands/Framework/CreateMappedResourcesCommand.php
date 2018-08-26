<?php

namespace CrestApps\CodeGenerator\Commands\Framework;

use CrestApps\CodeGenerator\Models\Bases\ScaffoldInputBase;
use CrestApps\CodeGenerator\Models\ScaffoldInput;
use CrestApps\CodeGenerator\Support\Config;
use CrestApps\CodeGenerator\Support\Helpers;
use CrestApps\CodeGenerator\Traits\CommonCommand;
use Exception;
use Illuminate\Console\Command;

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
                            {--model-directory= : The path of the model.}
                            {--views-directory= : The name of the view path.}
                            {--models-per-page=25 : The amount of models per page for index pages.}
                            {--language-filename= : The languages file name to put the labels in.}
                            {--table-name= : The name of the table.}
                            {--controller-name= : The name of the controler.}
                            {--with-form-request : This will extract the validation into a request form class.}
                            {--with-auth : Generate the controller with Laravel auth middlewear. }
                            {--with-migration : Prevent creating a migration for this resource.}
                            {--with-soft-delete : Enables softdelete future should be enable in the model.}
                            {--without-timestamps : Prevent Eloquent from maintaining both created_at and the updated_at properties.}
                            {--without-languages : Generate the resource without the language files. }
                            {--without-model : Generate the resource without the model file. }
                            {--without-controller : Generate the resource without the controller file. }
                            {--without-form-request : Generate the resource without the form-request file. }
                            {--without-views : Generate the resource without the views. }
                            {--connection-name= : A specific connection name.}
                            {--engine-name= : A specific engine name.}
                            {--layout-name= : This will extract the validation into a request form class.}
                            {--template-name= : The template name to use when generating the code.}
                            {--table-exists : This option will attempt to fetch the field from existing database table.}
                            {--routes-prefix=default-form : Prefix of the route group.}
                            {--primary-key=id : The name of the primary key.}
                            {--translation-for= : A comma seperated string of languages to create fields for.}
                            {--form-request-directory= : The directory of the form-request.}
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
        $objects = json_decode($content, true);

        if (!is_array($objects)) {
            throw new Exception('The mapping-file does not contain a valid array.');
        }

        $validInputs = $this->getValidInputs($objects, $this->getCommandInput());

        foreach ($validInputs as $validInput) {
            $this->call(
                'create:scaffold',
                [
                    'model-name' => $validInput->modelName,
                    '--controller-name' => $validInput->controllerName,
                    '--controller-directory' => $validInput->controllerDirectory,
                    '--controller-extends' => $validInput->controllerExtends,
                    '--model-directory' => $validInput->modelDirectory,
                    '--model-extends' => $validInput->modelExtends,
                    '--views-directory' => $validInput->viewsDirectory,
                    '--resource-file' => $validInput->resourceFile,
                    '--routes-prefix' => $validInput->prefix,
                    '--models-per-page' => $validInput->perPage,
                    '--language-filename' => $validInput->languageFileName,
                    '--with-form-request' => $validInput->withFormRequest,
                    '--form-request-directory' => $validInput->formRequestDirectory,
                    '--form-request-directory' => $validInput->formRequestDirectory,
                    '--with-auth' => $validInput->withAuth,
                    '--table-name' => $validInput->table,
                    '--primary-key' => $validInput->primaryKey,
                    '--with-soft-delete' => $validInput->withSoftDelete,
                    '--without-model' => $validInput->withoutModel,
                    '--without-controller' => $validInput->withoutController,
                    '--without-form-request' => $validInput->withoutFormRequest,
                    '--without-views' => $validInput->withoutViews,
                    '--without-timestamps' => $validInput->withoutTimeStamps,
                    '--without-languages' => $validInput->withoutLanguages,
                    '--with-migration' => $validInput->withMigration,
                    '--migration-class-name' => $validInput->migrationClass,
                    '--connection-name' => $validInput->connectionName,
                    '--engine-name' => $validInput->engineName,
                    '--layout-name' => $validInput->layoutName,
                    '--template-name' => $validInput->template,
                    '--table-exists' => $validInput->tableExists,
                    '--translation-for' => $validInput->translationFor,
                    '--force' => $validInput->force,
                ]
            );

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
     * @return array of CrestApps\CodeGenerator\Models\ScaffoldInput
     */
    protected function getValidInputs(array $objects, ScaffoldInput $originalInput)
    {
        $validInputs = [];

        foreach ($objects as $obj) {
            $input = clone $originalInput;
            $object = (object) $obj;

            if (!isset($object->{'model-name'})) {
                throw new Exception('Each entry in the mapping file must a have value for model-name');
            }

            $input->modelName = trim($object->{'model-name'});
            $madeupTableName = Helpers::makeTableName($input->modelName);
            $controllerName = Helpers::makeControllerName($input->modelName);
            $input->resourceFile = $this->getValue($object, 'resource-file', Helpers::makeJsonFileName($input->modelName));
            $input->table = $this->getValue($object, 'table-name', $madeupTableName);
            $input->fields = null;
            $input->prefix = $this->getValue($object, 'routes-prefix', $madeupTableName);
            $input->controllerName = $this->getValue($object, 'controller-name', $controllerName);
            $input->languageFileName = $this->getValue($object, 'language-filename', $madeupTableName);
            $input->table = $this->getValue($object, 'table-name', $madeupTableName);
            $input->viewsDirectory = $this->getValue($object, 'views-directory', $input->viewsDirectory);
            $input->perPage = $this->getValue($object, 'models-per-page', $input->perPage);
            $input->withFormRequest = $this->getValue($object, 'with-form-request', $input->withFormRequest);
            $input->controllerDirectory = $this->getValue($object, 'controller-directory', $input->controllerDirectory);
            $input->controllerExtends = $this->getValue($object, 'controller-extends', 'default-controller');

            $input->modelExtends = $this->getValue($object, 'model-extends', 'default-model');
            $input->withMigration = $this->getValue($object, 'with-migration', $input->withMigration);
            $input->force = $this->getValue($object, 'force', $input->force);
            $input->modelDirectory = $this->getValue($object, 'model-directory', $input->modelDirectory);
            $input->primaryKey = $this->getValue($object, 'primary-key', $input->primaryKey);
            $input->withSoftDelete = $this->getValue($object, 'with-soft-delete', $input->withSoftDelete);
            $input->withoutTimeStamps = $this->getValue($object, 'without-timestamps', $input->withoutTimeStamps);
            $input->withoutLanguages = $this->getValue($object, 'without-languages', $input->withoutLanguages);
            $input->withoutModel = $this->getValue($object, 'without-model', $input->withoutModel);
            $input->withoutController = $this->getValue($object, 'without-controller', $input->withoutController);
            $input->withoutFormRequest = $this->getValue($object, 'without-form-request', $input->withoutFormRequest);
            $input->withoutViews = $this->getValue($object, 'without-views', $input->withoutViews);
            $input->migrationClass = $this->getValue($object, 'migration-class-name', $input->migrationClass);
            $input->connectionName = $this->getValue($object, 'connection-name', $input->connectionName);
            $input->engineName = $this->getValue($object, 'engine-name', $input->engineName);
            $input->template = $this->getValue($object, 'template-name', $input->template);
            $input->layoutName = $this->getValue($object, 'layout-name', $input->layoutName);
            $input->tableExists = $this->getValue($object, 'table-exists', $input->tableExists);
            $input->translationFor = $this->getValue($object, 'translation-for', $input->translationFor);
            $input->withAuth = $this->getValue($object, 'with-auth', $input->withAuth);
            $input->formRequestDirectory = $this->getValue($object, 'form-request-directory', $input->formRequestDirectory);

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

        return base_path(Config::getResourceFilePath($name));
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
        if (property_exists($object, $name)) {
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
     * @return CrestApps\CodeGenerator\Models\ScaffoldInput
     */
    protected function getCommandInput()
    {
        $inputBase = new ScaffoldInputBase('');

        $inputBase->perPage = intval($this->option('models-per-page'));
        $inputBase->withFormRequest = $this->option('with-form-request');
        $inputBase->controllerDirectory = $this->option('controller-directory');
        $inputBase->withMigration = $this->option('with-migration');
        $inputBase->force = $this->option('force');
        $inputBase->modelDirectory = $this->option('model-directory');
        $inputBase->primaryKey = $this->option('primary-key');
        $inputBase->withSoftDelete = $this->option('with-soft-delete');
        $inputBase->connectionName = $this->option('connection-name');
        $inputBase->engineName = $this->option('engine-name');
        $inputBase->template = $this->getTemplateName();
        $inputBase->tableExists = $this->option('table-exists');
        $inputBase->translationFor = $this->option('translation-for');
        $inputBase->withAuth = $this->option('with-auth');
        $inputBase->formRequestDirectory = $this->option('form-request-directory');
        $inputBase->withoutTimeStamps = $this->option('without-timestamps');
        $inputBase->withoutLanguages = $this->option('without-languages');
        $inputBase->withoutModel = $this->option('without-model');
        $inputBase->withoutController = $this->option('without-controller');
        $inputBase->withoutFormRequest = $this->option('without-form-request');
        $inputBase->withoutViews = $this->option('without-views');

        $input = new ScaffoldInput($inputBase);
        $input->viewsDirectory = trim($this->option('views-directory'));
        $input->layoutName = $this->option('layout-name') ?: 'layouts.app';

        return $input;
    }
}
