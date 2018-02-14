<?php

namespace CrestApps\CodeGenerator\Commands\ApiDocs;

use CrestApps\CodeGenerator\Support\Str;
use CrestApps\CodeGenerator\Traits\ApiDocViewsTrait;
use CrestApps\CodeGenerator\Traits\CommonCommand;
use CrestApps\CodeGenerator\Traits\ScaffoldTrait;
use Illuminate\Console\Command;

class CreateApiDocsScaffoldCommand extends command
{
    use ApiDocViewsTrait, CommonCommand, ScaffoldTrait;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scaffold all necessary resources for the api-documentation.';

    /**
     * check if the base class was created during this request
     *
     * @var bool
     */
    protected $isBaseCreated = false;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'api-docs:scaffold
                            {model-name : The model name that this controller will represent.}
                            {--controller-name= : The name of the controler.}
                            {--controller-directory= : The directory where the controller should be created under.}
                            {--views-directory= : The path where the views should be created under.}
                            {--resource-file= : The name of the resource-file to import from.}
                            {--routes-prefix=default-form : Prefix of the route group.}
                            {--language-filename= : The languages file name to put the labels in.}
                            {--with-auth : Generate the controller with Laravel auth middlewear. }
                            {--template-name= : The template name to use when generating the code.}
                            {--controller-extends=default-controller : The base controller to be extend.}
                            {--api-version= : The api version to prefix your resurces with.}
                            {--force : This option will override the controller if one already exists.}';

    /**
     * Build the model class with the given name.
     *
     * @return string
     */
    public function handle()
    {
        $input = $this->getCommandInput();

        $this->printInfo('Scaffolding api-documentation resources for ' . $this->modelNamePlainEnglish($input->modelName) . '...')
            ->createController($input)
            ->createRoutes($input)
            ->createLanguageFile($input)
            ->createViews($input)
            ->info('Done!');
    }

    /**
     * Executes the command that generates the controller.
     *
     * @param object $input
     *
     * @return $this
     */
    protected function createController($input)
    {
        $this->call('api-docs:create-controller', [
            'model-name' => $input->modelName,
            '--controller-name' => $input->controllerName,
            '--controller-directory' => $input->controllerDirectory,
            '--views-directory' => $input->viewDirectory,
            '--resource-file' => $input->resourceFile,
            '--routes-prefix' => $input->prefix,
            '--language-filename' => $input->languageFilename,
            '--with-auth' => $input->withAuth,
            '--template-name' => $input->template,
            '--controller-extends' => $input->controllerExtends,
            '--api-version' => $input->apiVersion,
            '--force' => $input->force,
        ]);

        return $this;
    }

    /**
     * Executes the command that generates the views.
     *
     * @param object $input
     *
     * @return $this
     */
    protected function createViews($input)
    {

        $this->call('api-docs:create-view', [
            'model-name' => $input->modelName,
            '--controller-name' => $input->controllerName,
            '--controller-directory' => $input->controllerDirectory,
            '--views-directory' => $input->viewDirectory,
            '--resource-file' => $input->resourceFile,
            '--routes-prefix' => $input->prefix,
            '--language-filename' => $input->languageFilename,
            '--with-auth' => $input->withAuth,
            '--template-name' => $input->template,
            '--api-version' => $input->apiVersion,
            '--force' => $input->force,
        ]);

        return $this;
    }

    /**
     * Executes the command that generates the routes.
     *
     * @param object $input
     *
     * @return $this
     */
    protected function createRoutes($input)
    {
        $this->call(
            'create:routes',
            [
                'model-name' => $input->modelName,
                '--controller-name' => $input->controllerName,
                '--routes-prefix' => $input->prefix,
                '--template-name' => $input->template,
                '--controller-directory' => $input->controllerDirectory,
                '--without-route-clause' => false,
                '--routes-type' => 'api-docs',
                '--api-version' => $input->apiVersion,
            ]
        );

        return $this;
    }

    /**
     * Executes the command that generates the lanaguage entries.
     *
     * @param object $input
     *
     * @return $this
     */
    protected function createLanguageFile($input)
    {
        $this->callSilent('create:language', [
            'model-name' => $input->modelName,
            '--language-filename' => $input->languageFilename,
            '--resource-file' => $input->resourceFile,
            '--template-name' => $input->template,
        ]);

        return $this;
    }

    /**
     * Gets a clean command-line arguments and options.
     *
     * @return object
     */
    protected function getCommandInput()
    {
        $modelName = $this->argument('model-name');
        $controllerName = $this->option('controller-name');
        $prefix = $this->option('routes-prefix');
        $resourceFile = $this->option('resource-file');
        $force = $this->option('force');
        $template = $this->getTemplateName();
        $controllerExtends = $this->generatorOption('controller-extends');
        $withAuth = $this->option('with-auth');
        $viewDirectory = $this->option('views-directory');
        $languageFilename = $this->option('language-filename');
        $apiVersion = $this->option('api-version');
        $controllerDirectory = $this->option('controller-directory');
        $withFormRequest = false;

        return (object) compact(
            'modelName',
            'prefix',
            'force',
            'resourceFile',
            'languageFilename',
            'template',
            'controllerName',
            'controllerExtends',
            'withAuth',
            'apiVersion',
            'controllerDirectory',
            'withFormRequest',
            'viewDirectory'
        );
    }
}
