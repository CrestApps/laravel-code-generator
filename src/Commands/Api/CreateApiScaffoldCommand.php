<?php

namespace CrestApps\CodeGenerator\Commands\Api;

use CrestApps\CodeGenerator\Commands\Bases\CreateScaffoldCommandBase;
use CrestApps\CodeGenerator\Models\ApiScaffoldInput;
use CrestApps\CodeGenerator\Models\Field;
use CrestApps\CodeGenerator\Models\Resource;
use CrestApps\CodeGenerator\Support\Config;
use CrestApps\CodeGenerator\Support\Helpers;
use CrestApps\CodeGenerator\Support\Str;
use CrestApps\CodeGenerator\Traits\ScaffoldTrait;

class CreateApiScaffoldCommand extends CreateScaffoldCommandBase
{
    use ScaffoldTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:api-scaffold
                            {model-name : The model name that this resource will represent.}
                            {--controller-name= : The name of the controler.}
                            {--controller-directory= : The directory where the controller should be created under. }
                            {--controller-extends=default-controller : The base controller to be extend.}
                            {--model-directory= : The path of the model.}
							{--model-extends=default-model : The base model to be extend.}
                            {--form-request-directory= : The directory of the form-request.}
                            {--resource-file= : The name of the resource-file to import from.}
                            {--fields= : If the resource-file does not exists, passing list of fields here will create it first.}
                            {--routes-prefix=default-form : Prefix of the route group.}
                            {--models-per-page=25 : The amount of models per page for index pages.}
                            {--language-filename= : The languages file name to put the labels in.}
                            {--with-form-request : This will extract the validation into a request form class.}
                            {--with-auth : Generate the controller with Laravel auth middlewear. }
                            {--table-name= : The name of the table.}
                            {--primary-key=id : The name of the primary key.}
                            {--with-soft-delete : Enables softdelete future should be enable in the model.}
                            {--without-languages : Generate the resource without the language files. }
                            {--without-model : Generate the resource without the model file. }
                            {--without-controller : Generate the resource without the controller file. }
                            {--without-form-request : Generate the resource without the form-request file. }
                            {--without-timestamps : Prevent Eloquent from maintaining both created_at and the updated_at properties.}
                            {--with-migration : Prevent creating a migration for this resource.}
                            {--migration-class-name= : The name of the migration class.}
                            {--connection-name= : A specific connection name.}
                            {--engine-name= : A specific engine name.}
                            {--template-name= : The template name to use when generating the code.}
                            {--table-exists : This option will attempt to fetch the field from existing database table.}
                            {--translation-for= : A comma seperated string of languages to create fields for.}
                            {--with-api-resource : Generate the controller with both api-resource and api-resource-collection classes.}
                            {--with-api-docs : Create full api documentation.}
                            {--api-resource-directory= : The directory where the api-resource should be created.}
                            {--api-resource-collection-directory= : The directory where the api-resource-collection should be created.}
                            {--api-resource-name= : The api-resource file name.}
                            {--api-version= : The api version to prefix your resurces with.}
                            {--api-resource-collection-name= : The api-resource-collection file name.}
                            {--force : This option will override the controller if one already exists.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scaffold all necessary resources for an api.';

    /**
     * Executes the console command.
     *
     * @return void
     */
    public function handle()
    {
        $input = $this->getCommandInput();

        $this->beforeScaffold($input);

        $resource = Resource::fromFile($input->resourceFile, $input->languageFileName ?: 'CrestApps');

        $this->validateField($resource->fields)
            ->printInfo('Scaffolding api-based resources for ' . $this->modelNamePlainEnglish($input->modelName) . '...')
            ->createModel($input)
            ->createController($input)
            ->createRoutes($input, $resource->getPrimaryField())
            ->createLanguage($input)
            ->createMigration($input)
            ->info('Done!');

        if ($input->withDocumentations) {
            $this->createDocs($input);
        }
    }

    /**
     * Executes the command that generates the controller.
     *
     * @param CrestApps\CodeGenerator\Models\ApiScaffoldInput $input
     * @return $this
     */
    protected function createController(ApiScaffoldInput $input)
    {
        if (!$this->option('without-controller')) {
            $this->call(
                'create:api-controller',
                [
                    'model-name' => $input->modelName,
                    '--controller-name' => $input->controllerName,
                    '--controller-directory' => $input->controllerDirectory,
                    '--controller-extends' => $input->controllerExtends,
                    '--model-directory' => $input->modelDirectory,
                    '--resource-file' => $input->resourceFile,
                    '--models-per-page' => $input->perPage,
                    '--routes-prefix' => $input->prefix,
                    '--language-filename' => $input->languageFileName,
                    '--with-form-request' => $input->withFormRequest,
                    '--without-form-request' => $this->option('without-form-request'),
                    '--form-request-directory' => $input->formRequestDirectory,
                    '--with-auth' => $input->withAuth,
                    '--template-name' => $input->template,
                    '--with-api-resource' => $input->withApiResource,
                    '--api-resource-directory' => $input->apiResourceDirectory,
                    '--api-resource-collection-directory' => $input->apiResourceCollectionDirectory,
                    '--api-resource-name' => $input->apiResourceName,
                    '--api-resource-collection-name' => $input->apiResourceCollectionName,
                    '--api-version' => $input->apiVersion,
                    '--force' => $input->force,
                ]
            );
        }

        return $this;
    }

    /**
     * Executes the command that generates the documentation
     *
     * @param CrestApps\CodeGenerator\Models\ApiScaffoldInput $input
     * @return $this
     */
    protected function createDocs(ApiScaffoldInput $input)
    {

        $this->call(
            'api-docs:scaffold',
            [
                'model-name' => $input->modelName,
                '--controller-name' => $input->controllerName,
                '--controller-directory' => $input->controllerDirectory,
                '--controller-extends' => $input->controllerExtends,
                '--resource-file' => $input->resourceFile,
                '--routes-prefix' => $input->prefix,
                '--language-filename' => $input->languageFileName,
                '--with-auth' => $input->withAuth,
                '--template-name' => $input->template,
                '--api-version' => $input->apiVersion,
                '--force' => $input->force,
            ]
        );

        return $this;
    }

    /**
     * Executes the command that generates the routes.
     *
     * @param CrestApps\CodeGenerator\Models\ApiScaffoldInput $input
     * @param CrestApps\CodeGenerator\Models\Field $primaryField
     * @param bool $forApi
     *
     * @return $this
     */
    protected function createRoutes(ApiScaffoldInput $input, Field $primaryField = null)
    {
        $withClause = (!is_null($primaryField) && $primaryField->isNumeric());
        $controllerDirectory = $this->getControllerDirectory($input->controllerDirectory);
        $this->call(
            'create:routes',
            [
                'model-name' => $input->modelName,
                '--controller-name' => $input->controllerName,
                '--routes-prefix' => $input->prefix,
                '--template-name' => $input->template,
                '--controller-directory' => $input->controllerDirectory,
                '--without-route-clause' => !$withClause,
                '--routes-type' => 'api',
                '--api-version' => $input->apiVersion,
            ]
        );

        return $this;
    }

    /**
     * Get the Api folder after removing the controllers path.
     *
     * @param string $path
     *
     * @return string
     */
    protected function getControllerDirectory($path)
    {
        $web = Helpers::fixNamespace(Config::getControllersPath());
        $apis = Helpers::fixNamespace(Config::getApiControllersPath());

        $directory = trim(Str::trimStart($apis, $web), '\\/');

        if (!empty($path)) {
            return $directory . '\\' . $path;
        }

        return $directory;

    }
    /**
     * Gets a clean user inputs.
     *
     * @return CrestApps\CodeGenerator\Models\ApiScaffoldInput
     */
    protected function getCommandInput()
    {
        $input = new ApiScaffoldInput(parent::getCommandInput());

        $input->withApiResource = $this->option('with-api-resource');
        $input->apiResourceDirectory = $this->option('api-resource-directory');
        $input->apiResourceCollectionDirectory = $this->option('api-resource-collection-directory');
        $input->apiResourceName = $this->option('api-resource-name');
        $input->apiResourceCollectionName = $this->option('api-resource-collection-name');
        $input->apiVersion = $this->option('api-version');
        $input->withDocumentations = $this->option('with-api-docs');

        return $input;
    }
}
