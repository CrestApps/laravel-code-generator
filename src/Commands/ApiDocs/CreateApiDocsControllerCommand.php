<?php

namespace CrestApps\CodeGenerator\Commands\ApiDocs;

use CrestApps\CodeGenerator\Commands\Bases\ControllerCommandBase;
use CrestApps\CodeGenerator\Models\Resource;
use CrestApps\CodeGenerator\Support\Config;
use CrestApps\CodeGenerator\Support\Helpers;
use CrestApps\CodeGenerator\Traits\ApiDocViewsTrait;
use CrestApps\CodeGenerator\Traits\LanguageTrait;

class CreateApiDocsControllerCommand extends ControllerCommandBase
{
    use ApiDocViewsTrait, LanguageTrait;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new controller for the api-documentation resource.';

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
    protected $signature = 'api-docs:create-controller
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
        $directoryName = $this->getControllerDirectory($input->controllerDirectory);
        $resource = Resource::fromFile($input->resourceFile, $input->langFile);
        $destenationFile = $this->getDestenationFile($input->controllerName, $directoryName);

        if ($this->hasErrors($resource, $destenationFile)) {
            return false;
        }

        $stub = $this->getControllerStub();

        $apiVersionPlaceHolder = ($input->apiVersion) ? '%s' : '';

        return $this->replaceModelName($stub, $input->modelName)
            ->createControllerBaseClass($input->apiVersion)
            ->replaceNamespace($stub, $this->getControllersNamespace($directoryName))
            ->replaceControllerExtends($stub, $this->getControllerExtends($this->getFullClassToExtend()))
            ->replaceConstructor($stub, $this->getConstructor($input->withAuth))
            ->replaceAppName($stub, Helpers::getAppName())
            ->replaceControllerName($stub, $input->controllerName)
            ->replaceApiVersionNumber($stub, $input->apiVersion)
            ->replaceUseCommandPlaceholder($stub, $this->getRequiredUseClass($directoryName))
            ->replaceViewAccessFullname($stub, $this->getPathToViewHome($input->viewDirectory, $input->prefix, $apiVersionPlaceHolder))
            ->createFile($destenationFile, $stub)
            ->info('A ' . $this->getControllerType() . ' was crafted successfully.');
    }

    /**
     * Created a new controller base class if one does not exists
     *
     * @param string $apiVersion
     *
     * @return $this
     */
    protected function createControllerBaseClass($apiVersion)
    {
        if (!$apiVersion) {
            // At this point we know this isn't a version based.
            // Bail out, No need to create base controller.

            return $this;
        }

        $filename = class_basename($this->getFullClassToExtend());

        $destenationFile = $this->getDestenationFile($filename, null);

        if (!$this->isFileExists($destenationFile)) {
            // At this point the base class does not exists.
            // Create a new one
            $this->isBaseCreated = true;

            $this->createFile($destenationFile, $this->getBaseClassContent($apiVersion))
                ->info('A new api-documentation-controller based class was created!');
        }

        return $this;
    }

    /**
     * Gets the Controller's base class content.
     *
     * @param string $apiVersion
     *
     * @return string
     */
    protected function getBaseClassContent($apiVersion)
    {
        $stub = $this->getStubContent('api-documentation-version-based-base-controller');

        $this->replaceApiVersionNumber($stub, $apiVersion)
            ->replaceNamespace($stub, $this->getControllersNamespace(null));

        return $stub;
    }
    /**
     * Gets the name of the controller stub.
     *
     * @param string $controllerDirectory
     *
     * @return string
     */
    protected function getRequiredUseClass($controllerDirectory)
    {
        $namespace = $this->getControllersNamespace('Controller');

        return $this->getUseClassCommand($namespace);
    }

    /**
     * Gets the name of the controller stub.
     *
     * @return string
     */
    protected function getControllerStubName()
    {
        $version = '';
        if ($this->option('api-version')) {
            $version = '-version-based';
        }

        return parent::getControllerStubName() . $version;
    }

    /**
     * Replaces the create-index variables.
     *
     * @param $stub
     * @param $version
     *
     * @return $this
     */
    protected function replaceApiVersionNumber(&$stub, $version)
    {
        return $this->replaceTemplate('api_version_number', $version, $stub);
    }

    /**
     * Replaces the create-index variables.
     *
     * @param $stub
     * @param $version
     *
     * @return $this
     */
    protected function replaceViewAccessFullname(&$stub, $version)
    {
        return $this->replaceTemplate('view_access_fullname', $version, $stub);
    }

    /**
     * Gets the controller directory
     *
     * @param string $controllerDirectory
     *
     * @return string
     */
    protected function getControllerDirectory($controllerDirectory)
    {
        $base = Helpers::getPathWithSlash($controllerDirectory);

        return Helpers::fixPathSeparator($base);
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

        $fileName = app_path($this->getControllerPath($path . $name . '.php'));

        return Helpers::fixPathSeparator($fileName);
    }

    /**
     * Gets the type of the controller
     *
     * @return string
     */
    protected function getControllerType()
    {
        return 'api-documentation-controller';
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
        return Config::getApiDocsControllersPath($file);
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
     * Gets a clean command-line arguments and options.
     *
     * @return object
     */
    protected function getCommandInput()
    {
        $modelName = trim($this->argument('model-name'));
        $cName = trim($this->option('controller-name'));
        $controllerName = $cName ? str_finish($cName, Config::getControllerNamePostFix()) : Helpers::makeControllerName($modelName);
        $prefix = ($this->option('routes-prefix') == 'default-form') ? Helpers::makeRouteGroup($modelName) : $this->option('routes-prefix');
        $resourceFile = trim($this->option('resource-file')) ?: Helpers::makeJsonFileName($modelName);
        $force = $this->option('force');

        $template = $this->getTemplateName();
        $extends = $this->generatorOption('controller-extends');
        $withAuth = $this->option('with-auth');
        $viewDirectory = $this->option('views-directory');
        $langFile = $this->option('language-filename') ?: self::makeLocaleGroup($modelName);
        $apiVersion = $this->option('api-version');
        $controllerDirectory = trim($this->option('controller-directory'));
        $withFormRequest = false;

        return (object) compact(
            'modelName',
            'prefix',
            'force',
            'resourceFile',
            'langFile',
            'template',
            'controllerName',
            'extends',
            'withAuth',
            'apiVersion',
            'controllerDirectory',
            'withFormRequest',
            'viewDirectory'
        );
    }
}
