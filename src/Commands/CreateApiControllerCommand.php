<?php

namespace CrestApps\CodeGenerator\Commands;

use CrestApps\CodeGenerator\Commands\Bases\ControllerCommandBase;
use CrestApps\CodeGenerator\Models\Resource;
use CrestApps\CodeGenerator\Support\Config;
use CrestApps\CodeGenerator\Support\Helpers;

class CreateApiControllerCommand extends ControllerCommandBase
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new API based controller.';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:api-controller
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
                            {--with-response-methods : Generate the controller both successResponse and errorResponse methods.}
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

        $getValidatorMethod = $this->getValidatorMethod($input, $resource->fields);

        $stub = $this->getControllerStub();

        return $this->processCommonTasks($input, $resource, $stub)
            ->replaceCallGetValidator($stub, $this->getCallGetValidator($input->withFormRequest))
            ->replaceGetValidatorMethod($stub, $getValidatorMethod)
            ->replaceResponseMethods($stub, $this->getResponseMethods())
            ->replaceTransformMethod($stub, $this->getTransformMethod($input, $resource->fields))
            ->createControllerBaseClass($input->controllerDirectory)
            ->createFile($destenationFile, $stub)
            ->info('A ' . $this->getControllerType() . ' was crafted successfully.');
    }

    /**
     * check if the base class was created during this request
     *
     * @var bool
     */
    protected $isBaseCreated = false;

    /**
     * Gets the type of the controller
     *
     * @return string
     */
    protected function getControllerType()
    {
        return 'api-controller';
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
        return Config::getApiControllersPath($file);
    }

    /**
     * Gets the affirm method.
     *
     * @param (object) $input
     * @param array $fields
     *
     * @return string
     */
    protected function getValidatorMethod($input, array $fields)
    {
        if ($input->withFormRequest || Helpers::isNewerThanOrEqualTo('5.5')) {
            return '';
        }

        $stub = $this->getStubContent('api-controller-get-validator');

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
    protected function getCallGetValidator($withFormRequest)
    {
        if ($withFormRequest || Helpers::isNewerThanOrEqualTo('5.5')) {
            return $this->requestVariable . '->getValidator()';
        }

        return '$this->getValidator($request)';
    }

    /**
     * Gets the response methods.
     *
     * @return string
     */
    protected function getResponseMethods()
    {
        $code = '';

        if (!$this->isBaseCreated && $this->mustHaveMethod('successResponse')) {
            $code .= $this->getStubContent('api-controller-success-response-method');
        }

        if (!$this->isBaseCreated && $this->mustHaveMethod('errorResponse')) {
            $code .= $this->getStubContent('api-controller-error-response-method');
        }

        return $code;
    }

    /**
     * Gets the transform method.
     *
     * @param object $input
     * @param array $fields
     *
     * @return string
     */
    protected function getTransformMethod($input, array $fields)
    {
        $stub = $this->getStubContent('api-controller-transform-method');

        $modelNamespace = $this->getModelNamespace($input->modelName, $input->modelDirectory);

        $this->replaceModelApiArray($stub, $this->getModelApiArray($fields))
            ->replaceModelName($stub, $input->modelName)
            ->replaceModelFullname($stub, $modelNamespace);

        return $stub;
    }

    /**
     * Created a new controller base class if one does not exists
     *
     * @param string $controllerDirectory
     *
     * @return $this
     */
    protected function createControllerBaseClass($controllerDirectory)
    {
        $filename = class_basename($this->getFullClassToExtend());

        $destenationFile = $this->getDestenationFile($filename, $controllerDirectory);

        if (!$this->isFileExists($destenationFile)) {
            // At this point the base class does not exists.
            // Create a new one
            $this->isBaseCreated = true;

            $stub = $this->getStubContent('api-controller-base-class');

            $this->createFile($destenationFile, $stub)
                ->info('A new api-controller based class was created!');
        }

        return $this;
    }

    /**
     * Gets the field in array ready format.
     *
     * @param array $fields
     *
     * @return string
     */
    protected function getModelApiArray(array $fields)
    {
        $properties = '';

        foreach ($fields as $field) {
            if (!$field->isApiVisible) {
                continue;
            }

            $properties .= sprintf("        '%s' => '%s',\n    ", $field->getApiKey(), $field->name);
        }

        return $properties;
    }

    /**
     * Checks if the controller must have a giving method name
     *
     * @param string $name
     *
     * @return bool
     */
    protected function mustHaveMethod($name)
    {
        $baseClass = $this->getFullClassToExtend();

        return !method_exists($baseClass, $name);
    }

    /**
     * Replaces get validator method for the given stub.
     *
     * @param  string  $stub
     * @param  string  $name
     *
     * @return $this
     */
    protected function replaceGetValidatorMethod(&$stub, $name)
    {
        return $this->replaceTemplate('get_validator_method', $name, $stub);
    }

    /**
     * Replaces the response methods for the given stub.
     *
     * @param  string  $stub
     * @param  string  $name
     *
     * @return $this
     */
    protected function replaceResponseMethods(&$stub, $name)
    {
        return $this->replaceTemplate('response_methods', $name, $stub);
    }

    /**
     * Replaces call get validator for the given stub.
     *
     * @param  string  $stub
     * @param  string  $name
     *
     * @return $this
     */
    protected function replaceCallGetValidator(&$stub, $name)
    {
        return $this->replaceTemplate('call_get_validator', $name, $stub);
    }

    /**
     * Replaces the transform method for the giving stub,
     *
     * @param  string  $stub
     * @param  string  $name
     *
     * @return $this
     */
    protected function replaceTransformMethod(&$stub, $name)
    {
        return $this->replaceTemplate('transform_method', $name, $stub);
    }

    /**
     * Replaces the model fullname for the giving stub,
     *
     * @param  string  $stub
     * @param  string  $name
     *
     * @return $this
     */
    protected function replaceModelFullname(&$stub, $name)
    {
        return $this->replaceTemplate('use_full_model_name', $name, $stub);
    }

    /**
     * Replaces the model_api_array for the giving stub,
     *
     * @param  string  $stub
     * @param  string  $name
     *
     * @return $this
     */
    protected function replaceModelApiArray(&$stub, $name)
    {
        return $this->replaceTemplate('model_api_array', $name, $stub);
    }

}
