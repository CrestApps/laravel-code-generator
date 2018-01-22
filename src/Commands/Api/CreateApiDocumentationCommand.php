<?php

namespace CrestApps\CodeGenerator\Commands\Api;

use CrestApps\CodeGenerator\Models\Resource;
use CrestApps\CodeGenerator\Support\Config;
use CrestApps\CodeGenerator\Support\Helpers;
use CrestApps\CodeGenerator\Support\ViewLabelsGenerator;
use CrestApps\CodeGenerator\Traits\ApiResourceTrait;
use CrestApps\CodeGenerator\Traits\CommonCommand;
use CrestApps\CodeGenerator\Traits\GeneratorReplacers;
use Illuminate\Console\Command;

class CreateApiDocumentationCommand extends Command
{
    use CommonCommand, GeneratorReplacers, ApiResourceTrait;

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
    protected $signature = 'api-doc:create-view
                            {model-name : The model name that this controller will represent.}
                            {--controller-name= : The name of the controler.}
                            {--controller-directory= : The directory where the controller should be created under.}
                            {--resource-file= : The name of the resource-file to import from.}
                            {--routes-prefix=default-form : Prefix of the route group.}
                            {--language-filename= : The languages file name to put the labels in.}
                            {--with-auth : Generate the controller with Laravel auth middlewear. }
                            {--views-directory= : The name of the directory to create the views under.}
                            {--layout-name=layouts.api-doc-layout : This will extract the validation into a request form class.}
                            {--template-name= : The template name to use when generating the code.}
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

        $destenationFile = $this->getDestinationViewFullname($input->viewsDirectory, $input->prefix, 'index');

        if ($this->hasErrors($resource, $destenationFile)) {
            return false;
        }

        $stub = $this->getStubContent('api-documentation-index');

        $viewLabels = new ViewLabelsGenerator($input->modelName, $resource->fields, $this->isCollectiveTemplate());

        return $this->replaceStandardLabels($stub, $viewLabels->getLabels())
            ->replaceApiLabels($stub, $resource->getApiDocLabels())
            ->replaceModelName($stub, $input->modelName)
            ->replaceRouteNames($stub, $input->modelName, $input->prefix)
            ->repaceLayoutName($stub, $input->layoutName)
            ->createFile($destenationFile, $stub)
            ->info('A api-documentation was successfully crafted.');
    }

    /**
     * It gets the views destenation path
     *
     * @param $viewsDirectory
     *
     * @return string
     */
    protected function getDestinationPath($viewsDirectory)
    {
        $path = base_path(Config::getApiDocsViewsPath());

        if (!empty($viewsDirectory)) {
            $path .= Helpers::getPathWithSlash($viewsDirectory);
        }

        return $path;
    }

    /**
     * Gets the destenation file to be created.
     *
     * @param string $name
     * @param string $path
     *
     * @return string
     */
    protected function getDestenationFile($name, $path)
    {
        if (!empty($path)) {
            $path = Helpers::getPathWithSlash($path);
        }

        return app_path(Config::getModelsPath($path . $name . '.php'));
    }

    /**
     * It generate the view including the full path
     *
     * @param string $viewsDirectory
     * @param string $routesPrefix
     * @param string $viewName
     *
     * @return string
     */
    protected function getDestinationViewFullname($viewsDirectory, $routesPrefix, $viewName = 'index')
    {
        $viewsPath = $this->getFullViewsPath($viewsDirectory, $routesPrefix);

        $filename = $this->getDestinationViewName($viewName);

        return $this->getDestinationPath($viewsPath) . $filename;
    }

    /**
     * Gets destenation view path
     *
     * @param string $viewsDirectory
     * @param string $routesPrefix
     *
     * @return $this
     */
    protected function getFullViewsPath($viewsDirectory, $routesPrefix)
    {
        $path = '';

        if (!empty($viewsDirectory)) {
            $path .= Helpers::getPathWithSlash($viewsDirectory);
        }

        $path .= !empty($routesPrefix) ? Helpers::getPathWithSlash(str_replace('.', '-', $routesPrefix)) : '';

        return $path;
    }
    /**
     * It generate the destenation view name
     *
     * @param $action
     *
     * @return string
     */
    protected function getDestinationViewName($action)
    {
        return sprintf('%s.blade.php', $action);
    }
    /**
     * Build the model class with the given name.
     *
     * @param CrestApps\CodeGenerator\Models\Resource $resource
     * @param string $destenationFile
     *
     * @return bool
     */
    protected function hasErrors(Resource $resource, $destenationFile)
    {
        $hasErrors = false;

        if ($resource->isProtected('api-documentation')) {
            $this->warn('The api-documentation is protected and cannot be regenerated. To regenerate the file, unprotect it from the resource file.');

            $hasErrors = true;
        }

        if ($this->alreadyExists($destenationFile)) {
            $this->error('The api-documentation already exists!');

            $hasErrors = true;
        }

        return $hasErrors;
    }
    /**
     * Gets the transform method.
     *
     * @param object $input
     * @param array $fields
     *
     * @return string
     */
    protected function getTransformMethodForApiController($input, array $fields)
    {
        if ($input->withApiResource && $this->isApiResourceSupported()) {
            // Do not generate the transform method in the controller when the
            // controller is generated with the api-resource

            return '';
        }

        return $this->getTransformMethod($input, $fields, true, false);
    }

    /**
     * Gets any additional classes to include in the use statement
     *
     * @param object $input
     *
     * @return array
     */
    protected function getAdditionalNamespaces($input)
    {
        $additionalNamespaces = parent::getAdditionalNamespaces($input);

        if (!$input->withFormRequest) {
            $additionalNamespaces[] = 'Illuminate\Support\Facades\Validator';
        }

        if ($input->withApiResource && $this->isApiResourceSupported()) {

            $additionalNamespaces[] = $this->getApiResourceNamespace(
                $this->getApiResourceClassName($input->modelName)
            );

            $additionalNamespaces[] = $this->getApiResourceCollectionNamespace(
                $this->getApiResourceCollectionClassName($input->modelName)
            );
        }

        return $additionalNamespaces;
    }

    /**
     * Get an array of all relations that are used for relations.
     *
     * @param array $fields
     *
     * @return array
     */
    protected function getNamespacesForUsedRelations(array $fields)
    {
        // Since there is no create/edit forms in the API controller,
        // No need for any relation's namespances.

        return [];
    }

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
        if ($input->withFormRequest && $this->isApiResourceSupported()) {
            return '';
        }

        $stub = $this->getStubContent('api-controller-get-validator');

        $this->replaceValidationRules($stub, $this->getValidationRules($fields))
            ->replaceFileValidationSnippet($stub, $this->getFileValidationSnippet($fields, $input, $this->requestVariable))
            ->replaceRequestFullName($stub, $this->requestNameSpace);

        return $stub;
    }

    /**
     * Gets the return code for a giving method.
     *
     * @param object $input
     * @param array $fields
     * @param string $method
     *
     * @return string
     */
    protected function getReturnSuccess($input, array $fields, $method)
    {
        if ($input->withApiResource && $this->isApiResourceSupported()) {
            return $this->getApiResourceCall($input->modelName, $fields, $method);
        }

        return $this->getSuccessCall($input->modelName, $fields, $method);
    }

    /**
     * Gets the plain success return code for a giving method.
     *
     * @param object $input
     * @param array $fields
     * @param string $method
     *
     * @return string
     */
    protected function getSuccessCall($modelName, array $fields, $method)
    {
        $stub = $this->getStubContent('api-controller-call-' . $method . '-success-method');

        $viewLabels = new ViewLabelsGenerator($modelName, $fields, $this->isCollectiveTemplate());

        $this->replaceModelName($stub, $modelName)
            ->replaceStandardLabels($stub, $viewLabels->getLabels())
            ->replaceDataVariable($stub, $this->dataVariable);

        return $stub;
    }

    /**
     * Gets the plain success return code for a giving method.
     *
     * @param object $input
     * @param array $fields
     * @param string $method
     *
     * @return string
     */
    protected function getApiResourceCall($modelName, $fields, $method)
    {
        $stub = $this->getStubContent('api-controller-call-' . $method . '-api-resource');

        $viewLabels = new ViewLabelsGenerator($modelName, $fields, $this->isCollectiveTemplate());

        $this->replaceModelName($stub, $modelName)
            ->replaceStandardLabels($stub, $viewLabels->getLabels())
            ->replaceDataVariable($stub, $this->dataVariable)
            ->replaceApiResourceClass($stub, $this->getApiResourceClassName($modelName))
            ->replaceApiResourceCollectionClass($stub, $this->getApiResourceCollectionClassName($modelName));

        return $stub;
    }

    /**
     * Gets the response methods.
     *
     * @return string
     */
    protected function getResponseMethods()
    {
        if ($this->isBaseCreated) {
            return '';
        }

        $code = '';

        if ($this->mustHaveMethod('successResponse')) {
            $code .= $this->getStubContent('api-controller-success-response-method');
        }

        if ($this->mustHaveMethod('errorResponse')) {
            $code .= $this->getStubContent('api-controller-error-response-method');
        }

        return $code;
    }

    protected function getValidateRequest($withFormRequest)
    {
        if (!$withFormRequest) {
            return $this->getStubContent('api-controller-validate');
        }

        return '';
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

            $this->createFile($destenationFile, $this->getBaseClassContent($controllerDirectory))
                ->info('A new api-controller based class was created!');
        }

        return $this;
    }

    /**
     * Gets the Controller's base class content.
     *
     * @return string
     */
    protected function getBaseClassContent($controllerDirectory)
    {
        $stub = $this->getStubContent('api-controller-base-class');

        $methods = $this->getStubContent('api-controller-success-response-method') . PHP_EOL . PHP_EOL;
        $methods .= $this->getStubContent('api-controller-error-response-method');

        $this->replaceResponseMethods($stub, $methods)
            ->replaceNamespace($stub, $this->getControllersNamespace($controllerDirectory));

        return $stub;
    }

    /**
     * Gets name of the middleware
     *
     * @return string
     */
    protected function getAuthMiddleware()
    {
        return parent::getAuthMiddleware() . ':api';
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
        return !method_exists($this->getFullClassToExtend(), $name);
    }

    /**
     * Executes the command that generates a migration.
     *
     * @param CrestApps\CodeGenerator\Models\ScaffoldInput $input
     *
     * @return $this
     */
    protected function makeApiResource($input, $isCollection = false)
    {
        $this->call(
            'create:api-resource',
            [
                'model-name' => $input->modelName,
                '--api-resource-directory' => $input->apiResourceDirectory,
                '--api-resource-collection-directory' => $input->apiResourceCollectionDirectory,
                '--api-resource-name' => $input->apiResourceName,
                '--api-resource-collection-name' => $input->apiResourceCollectionName,
                '--resource-file' => $input->resourceFile,
                '--template-name' => $input->template,
                '--collection' => $isCollection,
                '--force' => $input->force,
            ]
        );

        return $this;
    }

    /**
     * Replaces get validator method for the given stub.
     *
     * @param  string  $stub
     * @param  array  $labels
     *
     * @return $this
     */
    protected function replaceApiLabels(&$stub, array $labels)
    {

        foreach ($labels as $lang => $labelsCollection) {

            foreach ($labelsCollection as $label) {
                $text = $label->text;
                if (!$label->isPlain) {
                    $text = sprintf("{{ trans('%s') }}", $label->getAccessor());
                }

                $this->replaceTemplate($label->id, $text, $stub);
            }
        }

        return $this;
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
     * Replaces return_success for the given stub.
     *
     * @param  string  $stub
     * @param  string  $name
     * @param  string  $method
     *
     * @return $this
     */
    protected function replaceReturnSuccess(&$stub, $name, $method)
    {
        return $this->replaceTemplate($method . '_return_success', $name, $stub);
    }

    /**
     * Replaces the layout_name for the giving stub,
     *
     * @param  string  $stub
     * @param  string  $name
     *
     * @return $this
     */
    protected function repaceLayoutName(&$stub, $name)
    {
        return $this->replaceTemplate('layout_name', $name, $stub);
    }

    /**
     * Gets a clean command-line arguments and options.
     *
     * @return object
     */
    protected function getCommandInput()
    {
        $modelName = trim($this->argument('model-name'));
        $cName = trim($this->option('controller-name'));
        $controllerName = $cName ? str_finish($cName, Config::getControllerNamePostFix()) : Helpers::makeControllerName($modelName);
        $controllerDirectory = trim($this->option('controller-directory'));
        $viewsDirectory = trim($this->option('views-directory'));
        $layoutName = trim($this->option('layout-name'));
        $resourceFile = trim($this->option('resource-file')) ?: Helpers::makeJsonFileName($modelName);
        $prefix = ($this->option('routes-prefix') == 'default-form') ? Helpers::makeRouteGroup($modelName) : $this->option('routes-prefix');
        $langFile = $this->option('language-filename') ?: Helpers::makeLocaleGroup($modelName);
        $withAuth = $this->option('with-auth');
        $force = $this->option('force');

        return (object) compact(
            'modelName',
            'controllerName',
            'controllerDirectory',
            'resourceFile',
            'prefix',
            'langFile',
            'withAuth',
            'viewsDirectory',
            'layoutName',
            'force'
        );
    }
}
