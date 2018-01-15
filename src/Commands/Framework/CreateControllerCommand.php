<?php

namespace CrestApps\CodeGenerator\Commands\Framework;

use CrestApps\CodeGenerator\Commands\Bases\ControllerCommandBase;
use CrestApps\CodeGenerator\Models\Resource;
use CrestApps\CodeGenerator\Support\Config;
use CrestApps\CodeGenerator\Support\Helpers;

class CreateControllerCommand extends ControllerCommandBase
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new controller.';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:controller
                            {model-name : The model name that this controller will represent.}
                            {--controller-name= : The name of the controler.}
                            {--controller-directory= : The directory where the controller should be created under.}
                            {--model-directory= : The path where the model should be created under.}
                            {--views-directory= : The path where the views should be created under.}
                            {--resource-file= : The name of the resource-file to import from.}
                            {--routes-prefix=default-form : Prefix of the route group.}
                            {--models-per-page=25 : The amount of models per page for index pages.}
                            {--language-filename= : The languages file name to put the labels in.}
                            {--with-form-request : This will extract the validation into a request form class.}
                            {--without-form-request : Generate the controller without the form-request file. }
                            {--with-auth : Generate the controller with Laravel auth middlewear. }
                            {--template-name= : The template name to use when generating the code.}
                            {--form-request-directory= : The directory of the form-request.}
                            {--controller-extends=default-controller : The base controller to be extend.}
                            {--force : This option will override the controller if one already exists.}';

    /**
     * Build the model class with the given name.
     *
     * @return string
     */
    public function handle()
    {
        $input = $this->getCommandInput();
        $resource = Resource::fromFile($input->resourceFile, $input->langFile);
        $destenationFile = $this->getDestenationFile($input->controllerName, $input->controllerDirectory);

        if ($this->hasErrors($resource, $destenationFile)) {
            return false;
        }

        $viewVariablesForIndex = $this->getCompactVariablesFor($resource->fields, $this->getPluralVariable($input->modelName), 'index');
        $viewVariablesForShow = $this->getCompactVariablesFor($resource->fields, $this->getSingularVariable($input->modelName), 'show');
        $viewVariablesForEdit = $this->getCompactVariablesFor($resource->fields, $this->getSingularVariable($input->modelName), 'form');
        $affirmMethod = $this->getAffirmMethod($input, $resource->fields);
        $stub = $this->getControllerStub();

        return $this->processCommonTasks($input, $resource, $stub)
            ->replaceViewNames($stub, $input->viewDirectory, $input->prefix)
            ->replaceRouteNames($stub, $this->getModelName($input->modelName), $input->prefix)
            ->replaceCallAffirm($stub, $this->getCallAffirm($input->withFormRequest))
            ->replaceAffirmMethod($stub, $affirmMethod)
            ->replaceViewVariablesForIndex($stub, $viewVariablesForIndex)
            ->replaceViewVariablesForShow($stub, $viewVariablesForShow)
            ->replaceViewVariablesForEdit($stub, $viewVariablesForEdit)
            ->replaceViewVariablesForCreate($stub, $this->getCompactVariablesFor($resource->fields, null, 'form'))
            ->createFile($destenationFile, $stub)
            ->info('A ' . $this->getControllerType() . ' was crafted successfully.');
    }

    /**
     * Gets a clean command-line arguments and options.
     *
     * @return object
     */
    protected function getCommandInput()
    {
        $input = parent::getCommandInput();
        $input->viewDirectory = $this->option('views-directory');

        return $input;
    }

    /**
     * Gets the type of the controller
     *
     * @return string
     */
    protected function getControllerType()
    {
        return 'controller';
    }

    /**
     * Gets the path to controllers
     *
     * @param string $file
     *
     * @return string
     */
    protected function getControllerPath($file = '')
    {
        return Config::getControllersPath($file);
    }

    /**
     * Gets the default class name to extend
     *
     * @param string $extend
     *
     * @return string
     */
    protected function getDefaultClassToExtend()
    {
        $base = $this->getControllerPath();

        return Helpers::fixNamespace(Helpers::getAppNamespace($base, 'Controller'));
    }

    /**
     * Gets the affirm method.
     *
     * @param (object) $input
     * @param array $fields
     *
     * @return string
     */
    protected function getAffirmMethod($input, array $fields)
    {
        if ($input->withFormRequest || Helpers::isNewerThanOrEqualTo('5.5')) {
            return '';
        }

        $stub = $this->getStubContent('controller-affirm-method');

        $this->replaceValidationRules($stub, $this->getValidationRules($fields))
            ->replaceFileValidationSnippet($stub, $this->getFileValidationSnippet($fields, $input, $this->requestVariable))
            ->replaceRequestFullName($stub, $this->requestNameSpace);

        return $stub;
    }

    /**
     * Gets the affirm method call.
     *
     * @param bool $withFormRequest
     *
     * @return string
     */
    protected function getCallAffirm($withFormRequest)
    {
        if ($withFormRequest || Helpers::isNewerThanOrEqualTo('5.5')) {
            return '';
        }

        return '$this->affirm($request);';
    }

    /**
     * Replaces affirm method for the given stub.
     *
     * @param  string  $stub
     * @param  string  $name
     *
     * @return $this
     */
    protected function replaceAffirmMethod(&$stub, $name)
    {
        return $this->replaceTemplate('affirm_method', $name, $stub);
    }

    /**
     * Replaces call affirm for the given stub.
     *
     * @param  string  $stub
     * @param  string  $name
     *
     * @return $this
     */
    protected function replaceCallAffirm(&$stub, $name)
    {
        return $this->replaceTemplate('call_affirm', $name, $stub);
    }

    /**
     * Replaces the create-view variables.
     *
     * @param $stub
     * @param $variables
     *
     * @return $this
     */
    protected function replaceViewVariablesForCreate(&$stub, $variables)
    {
        return $this->replaceTemplate('view_variables_for_create', $variables, $stub);
    }

    /**
     * Replaces the create-edit variables.
     *
     * @param $stub
     * @param $variables
     *
     * @return $this
     */
    protected function replaceViewVariablesForEdit(&$stub, $variables)
    {
        return $this->replaceTemplate('view_variables_for_edit', $variables, $stub);
    }

    /**
     * Replaces the create-index variables.
     *
     * @param $stub
     * @param $variables
     *
     * @return $this
     */
    protected function replaceViewVariablesForIndex(&$stub, $variables)
    {
        return $this->replaceTemplate('view_variables_for_index', $variables, $stub);
    }

    /**
     * Replaces the create-show variables.
     *
     * @param $stub
     * @param $variables
     *
     * @return $this
     */
    protected function replaceViewVariablesForShow(&$stub, $variables)
    {
        return $this->replaceTemplate('view_variables_for_show', $variables, $stub);
    }
}
