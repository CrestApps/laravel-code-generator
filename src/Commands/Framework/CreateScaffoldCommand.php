<?php

namespace CrestApps\CodeGenerator\Commands\Framework;

use CrestApps\CodeGenerator\Commands\Bases\CreateScaffoldCommandBase;
use CrestApps\CodeGenerator\Models\Field;
use CrestApps\CodeGenerator\Models\Resource;
use CrestApps\CodeGenerator\Models\ScaffoldInput;

class CreateScaffoldCommand extends CreateScaffoldCommandBase
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:scaffold
                            {model-name : The model name that this resource will represent.}
                            {--controller-name= : The name of the controler.}
                            {--controller-directory= : The directory where the controller should be created under. }
                            {--controller-extends=default-controller : The base controller to be extend.}
                            {--model-directory= : The path of the model.}
                            {--model-extends=default-model : The base model to be extend.}
                            {--views-directory= : The name of the view path.}
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
                            {--without-views : Generate the resource without the views. }
                            {--without-timestamps : Prevent Eloquent from maintaining both created_at and the updated_at properties.}
                            {--with-migration : Prevent creating a migration for this resource.}
                            {--migration-class-name= : The name of the migration class.}
                            {--connection-name= : A specific connection name.}
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
    protected $description = 'Create all web-based resources for a given model.';

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
            ->printInfo('Scaffolding web-based resources for ' . $this->modelNamePlainEnglish($input->modelName) . '...')
            ->createModel($input)
            ->createController($input)
            ->createRoutes($input, $resource->getPrimaryField())
            ->createLanguage($input)
            ->createViews($input)
            ->createMigration($input)
            ->info('Done!');
    }

    /**
     * Executes the command that generates all the views.
     *
     * @param CrestApps\CodeGenerator\Models\ScaffoldInput $input
     *
     * @return $this
     */
    protected function createViews(ScaffoldInput $input)
    {
        if (!$this->option('without-views')) {
            $this->call(
                'create:views',
                [
                    'model-name' => $input->modelName,
                    '--resource-file' => $input->resourceFile,
                    '--views-directory' => $input->viewsDirectory,
                    '--routes-prefix' => $input->prefix,
                    '--layout-name' => $input->layoutName,
                    '--template-name' => $input->template,
                    '--force' => $input->force,
                ]
            );
        }

        return $this;
    }

    /**
     * Executes the command that generates the controller.
     *
     * @param CrestApps\CodeGenerator\Models\ScaffoldInput $input
     * @return $this
     */
    protected function createController(ScaffoldInput $input)
    {
        if (!$this->option('without-controller')) {
            $this->call(
                'create:controller',
                [
                    'model-name' => $input->modelName,
                    '--controller-name' => $input->controllerName,
                    '--controller-directory' => $input->controllerDirectory,
                    '--controller-extends' => $input->controllerExtends,
                    '--model-directory' => $input->modelDirectory,
                    '--views-directory' => $input->viewsDirectory,
                    '--resource-file' => $input->resourceFile,
                    '--models-per-page' => $input->perPage,
                    '--routes-prefix' => $input->prefix,
                    '--language-filename' => $input->languageFileName,
                    '--with-form-request' => $input->withFormRequest,
                    '--without-form-request' => $this->option('without-form-request'),
                    '--form-request-directory' => $input->formRequestDirectory,
                    '--with-auth' => $input->withAuth,
                    '--template-name' => $input->template,
                    '--force' => $input->force,
                ]
            );
        }

        return $this;
    }

    /**
     * Executes the command that generates the routes.
     *
     * @param CrestApps\CodeGenerator\Models\ScaffoldInput $input
     * @param CrestApps\CodeGenerator\Models\Field $primaryField
     *
     * @return $this
     */
    protected function createRoutes(ScaffoldInput $input, Field $primaryField = null)
    {
        $withClause = (!is_null($primaryField) && $primaryField->isNumeric());

        $this->call(
            'create:routes',
            [
                'model-name' => $input->modelName,
                '--controller-name' => $input->controllerName,
                '--routes-prefix' => $input->prefix,
                '--template-name' => $input->template,
                '--controller-directory' => $input->controllerDirectory,
                '--without-route-clause' => !$withClause,
            ]
        );

        return $this;
    }

    /**
     * Gets a clean user inputs.
     *
     * @return CrestApps\CodeGenerator\Models\ScaffoldInput
     */
    protected function getCommandInput()
    {
        $input = new ScaffoldInput(parent::getCommandInput());

        $input->viewsDirectory = trim($this->option('views-directory'));
		$input->withoutViews = $this->option('without-views');
		
        $input->layoutName = $this->option('layout-name') ?: 'layouts.app';

        return $input;
    }
}
