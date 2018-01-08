<?php

namespace CrestApps\CodeGenerator\Commands\Bases;

use CrestApps\CodeGenerator\Commands\Bases\ControllerRequestCommandBase;
use CrestApps\CodeGenerator\Models\ForeignRelationship;
use CrestApps\CodeGenerator\Models\Resource;
use CrestApps\CodeGenerator\Support\Config;
use CrestApps\CodeGenerator\Support\Helpers;
use CrestApps\CodeGenerator\Support\ViewLabelsGenerator;

abstract class ControllerCommandBase extends ControllerRequestCommandBase
{
    /**
     * The request object's name to use in the controller.
     *
     * @var string
     */
    protected $requestName = 'Request';

    /**
     * The name of the data variable.
     *
     * @var string
     */
    protected $dataVariable = 'data';

    /**
     * The request object's namespace to use in the controller.
     *
     * @var string
     */
    protected $requestNameSpace = 'Illuminate\Http\Request';

    /**
     * The request variable to use in the controller.
     *
     * @var string
     */
    protected $requestVariable = '$request';

    /**
     * Build the model class with the given name.
     *
     * @param  CrestApps\CodeGenerator\Models\Resource $resource
     * @param string $destenationFile
     *
     * @return bool
     */
    protected function hasErrors(Resource $resource, $destenationFile)
    {
        $hasErrors = false;

        if ($resource->isProtected($this->getControllerType())) {
            $this->warn('The ' . $this->getControllerType() . ' is protected and cannot be regenerated. To regenerate the file, unprotect it from the resource file.');

            $hasErrors = true;
        }

        if ($this->alreadyExists($destenationFile)) {
            $this->error('The ' . $this->getControllerType() . ' already exists!');

            $hasErrors = true;
        }

        return $hasErrors;
    }

    /**
     * Extracts a namespace from a giving string
     *
     * @param string $string
     *
     * @return string
     */
    protected function extractClassFromString($string)
    {
        $string = trim($string);

        if ($this->isQualifiedNamespace($string)) {
            if (($index = strrpos($string, '::')) !== false) {
                $subString = substr($string, 0, $index);

                if (($positionOfSlash = strrpos($subString, '\\')) != false) {
                    return substr($string, $positionOfSlash + 1);
                }
            }

            if (($index = strrpos($string, '\\')) !== false) {
                $string = substr($string, $index + 1);
            }
        }

        return $string;
    }

    /**
     * Gets the signature of the getData method.
     *
     * @param array  $fields
     * @param string $requestFullname
     * @param bool   $withFormRequest
     * @param object $input
     *
     * @return string
     */
    protected function getDataMethod(array $fields, $requestFullname, $input)
    {
        if ($input->withFormRequest) {
            return '';
        }

        $stub = $this->getDataMethodStubContent();

        $this->replaceFileSnippet($stub, $this->getFileSnippet($fields, $this->requestVariable))
            ->replaceValidationRules($stub, $this->getValidationRules($fields))
            ->replaceFillables($stub, $this->getFillables($fields))
            ->replaceFileValidationSnippet($stub, $this->getFileValidationSnippet($fields, $input, $this->requestVariable))
            ->replaceBooleadSnippet($stub, $this->getBooleanSnippet($fields, $this->requestVariable))
            ->replaceStringToNullSnippet($stub, $this->getStringToNullSnippet($fields))
            ->replaceRequestNameComment($stub, $this->getRequestNameComment($requestFullname))
            ->replaceMethodVisibilityLevel($stub, 'protected');

        $stub = str_replace(PHP_EOL . PHP_EOL, PHP_EOL, $stub);

        return $stub;
    }

    /**
     * Extracts a namespace from a giving string
     *
     * @param string $string
     *
     * @return string
     */
    protected function extractNamespace($string)
    {
        $string = trim($string);

        if ($this->isQualifiedNamespace($string) && ($index = strrpos($string, '::')) != false) {
            $namespace = substr($string, 0, $index);

            if (!empty($namespace)) {
                return $this->getUseClassCommand($namespace);
            }
        }

        return null;
    }

    /**
     * Gets the content of a controller-getdata-method stub.
     *
     * @return string
     */
    protected function getDataMethodStubContent()
    {
        $stubName = 'controller-getdata-method';

        if (Helpers::isNewerThanOrEqualTo('5.5')) {
            $stubName .= '-5.5';
        }

        return $this->getStubContent($stubName);
    }

    /**
     * Get a string to set a giving $variable with a $key and $value pair
     *
     * @param string $key
     * @param string $value
     * @param string $variable
     *
     * @return string
     */
    protected function getArrayReadyString($key, $value, $variable = '$data')
    {
        $value = trim(rtrim($value, ';'));

        return sprintf("%s['%s'] = %s;", $variable, $key, $value);
    }

    /**
     * Gets the method that calls the getData()
     *
     * @param bool $withFormRequest
     *
     * @return string
     */
    protected function getCallDataMethod($withFormRequest)
    {
        if ($withFormRequest) {
            return sprintf('%s->getData()', $this->requestVariable);
        }

        return sprintf('$this->getData(%s)', $this->requestVariable);
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
        $viewDirectory = $this->option('views-directory');
        $prefix = ($this->option('routes-prefix') == 'default-form') ? Helpers::makeRouteGroup($modelName) : $this->option('routes-prefix');
        $perPage = intval($this->option('models-per-page'));
        $resourceFile = trim($this->option('resource-file')) ?: Helpers::makeJsonFileName($modelName);
        $langFile = $this->option('language-filename') ?: Helpers::makeLocaleGroup($modelName);
        $withFormRequest = $this->option('with-form-request');
        $force = $this->option('force');
        $modelDirectory = $this->option('model-directory');
        $controllerDirectory = trim($this->option('controller-directory'));
        $formRequestName = Helpers::makeFormRequestName($modelName);
        $template = $this->getTemplateName();
        $formRequestDirectory = trim($this->option('form-request-directory'));
        $extends = $this->generatorOption('controller-extends');
        $withAuth = $this->option('with-auth');

        return (object) compact(
            'formRequestDirectory',
            'viewDirectory',
            'viewName',
            'modelName',
            'prefix',
            'perPage',
            'fileSnippet',
            'modelDirectory',
            'langFile',
            'fields',
            'withFormRequest',
            'formRequestName',
            'force',
            'resourceFile',
            'template',
            'controllerName',
            'extends',
            'withAuth',
            'controllerDirectory'
        );
    }

    /**
     * Converts giving array of variables to a compact statements.
     *
     * @param array $variables
     *
     * @return string
     */
    protected function getCompactVariables(array $variables)
    {
        if (empty($variables)) {
            return '';
        }

        return sprintf(', compact(%s)', implode(',', Helpers::wrapItems($variables)));
    }

    /**
     * Gets the needed compact variables for the edit/create views.
     *
     * @param array $fields
     *
     * @return string
     */
    protected function getCompactVariablesFor(array $fields, $modelName, $view)
    {
        $variables = [];

        if (!empty($modelName)) {
            $variables[] = $modelName;
        }

        if ($view == 'form') {
            $collections = $this->getRelationCollections($fields, $view);

            foreach ($collections as $collection) {
                $variables[] = $collection->getCollectionName();
            }
        }

        return $this->getCompactVariables($variables);
    }

    /**
     * Gets controller's constructor.
     *
     * @param bool $withAuth
     *
     * @return string
     */
    protected function getConstructor($withAuth)
    {
        $stub = $this->getStubContent('controller-constructor');

        $middleware = $withAuth ? '$this->middleware(\'' . $this->getAuthMiddleware() . '\');' : '';

        $this->replaceAuthMiddlewear($stub, $middleware);

        $starts = strpos($stub, '{');
        $ends = strrpos($stub, '}');

        if ($starts !== false && $ends !== false) {
            $content = trim(substr($stub, $starts + 1, $ends - $starts - 1));
            if (!empty($content)) {
                return $stub;
            }
        }

        return '';
    }

    /**
     * Gets name of the middleware
     *
     * @return string
     */
    protected function getAuthMiddleware()
    {
        return 'auth';
    }

    /**
     * Gets the code to extend the controller.
     *
     * @param string $namespace
     *
     * @return string
     */
    protected function getControllerExtends($namespace)
    {
        $class = $this->extractClassFromString($namespace);
        if (!empty($class)) {
            return sprintf('extends %s', $class);
        }

        return '';
    }

    /**
     * Gets the controllers namespace
     *
     * @param string $path
     *
     * @return string
     */
    protected function getControllersNamespace($path)
    {
        $path = Helpers::getAppNamespace() . $this->getControllerPath($path);

        return rtrim(Helpers::convertSlashToBackslash($path), '\\');
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
            $path = Helpers::getPathWithSlash(ucfirst($path));
        }

        $fileName = app_path($this->getControllerPath($path . $name . '.php'));

        return Helpers::fixPathSeparator($fileName);
    }

    /**
     * Gets the full class name to extend
     *
     * @return string
     */
    protected function getFullClassToExtend()
    {
        $extend = $this->generatorOption('controller-extends');

        if (empty($extend)) {
            return '';
        }

        $appNamespace = Helpers::getAppNamespace();

        if ($this->isExtendsDefault()) {
            $extend = $this->getDefaultClassToExtend();
        }

        if (starts_with($extend, $appNamespace)) {
            $extend = str_replace($appNamespace, '', $extend);
        }

        return $appNamespace . trim($extend, '\\');
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
        $appNamespace = Helpers::getAppNamespace();

        return Helpers::convertSlashToBackslash($appNamespace . $this->getControllerPath('Controller'));
    }

    /**
     * Checks if the controller extends the default "Controller" class
     *
     * @return bool
     */
    protected function isExtendsDefault()
    {
        return $this->option('controller-extends') == 'default-controller';
    }

    /**
     * Gets the setter action for the giving field on-store.
     *
     * @param array $fields
     * @param string $view
     *
     * @return string
     */
    protected function getOnStoreAction(array $fields)
    {
        $final = [];

        foreach ($fields as $field) {
            $action = $this->extractClassFromString($field->onStore);
            if (!empty($action)) {
                $final[] = $this->getArrayReadyString($field->name, $action);
            }
        }

        return implode(PHP_EOL, $final);
    }

    /**
     * Gets the setter action for the giving field on-update.
     *
     * @param array $fields
     * @param string $view
     *
     * @return string
     */
    protected function getOnUpdateAction(array $fields)
    {
        $final = [];

        foreach ($fields as $field) {
            $action = $this->extractClassFromString($field->onUpdate);
            if (!empty($action)) {
                $final[] = $this->getArrayReadyString($field->name, $action);
            }
        }

        return implode(PHP_EOL, $final);
    }

    /**
     * Gets the relation accessor for the giving foreign renationship.
     *
     * @param CrestApps\CodeGenerator\Models\ForeignRelationship $collection
     *
     * @return string
     */
    protected function getRelationAccessor(ForeignRelationship $collection)
    {
        return sprintf(
            '$%s = %s::pluck(\'%s\',\'%s\')->all();',
            $collection->getCollectionName(),
            $collection->getForeignModel(),
            $collection->getField(),
            $collection->getPrimaryKeyForForeignModel()
        );
    }

    /**
     * Gets the needed compact variables for the edit/create views.
     *
     * @param array $fields
     *
     * @return array
     */
    protected function getRelationCollections(array $fields, $view)
    {
        $variables = [];

        foreach ($fields as $field) {
            if ($field->hasForeignRelation() && $field->isOnView($view)) {
                $variables[] = $field->getForeignRelation();
            }
        }

        return $variables;
    }

    /**
     * Gets the Requests namespace
     *
     * @param string $name
     * @param string $path
     *
     * @return string
     */
    protected function getRequestsNamespace($name, $path)
    {
        if (!empty($path)) {
            $path = str_finish($path, '\\');
        }

        $path = Helpers::getAppNamespace() . Config::getRequestsPath($path);

        return Helpers::convertSlashToBackslash($path) . $name;
    }

    /**
     * Gets the needed compact variables for the edit/create views.
     *
     * @param array $fields
     *
     * @return string
     */
    protected function getRequiredRelationCollections(array $fields)
    {
        $variables = [];

        $collections = $this->getRelationCollections($fields, 'form');

        foreach ($collections as $collection) {
            $accesor = $this->getRelationAccessor($collection);

            if (!in_array($accesor, $variables)) {
                $variables[] = $accesor;
            }
        }

        return implode(PHP_EOL, $variables);
    }

    /**
     * Gets the needed models to use for in the controller.
     *
     * @param array $fields
     * @param array $additions
     *
     * @return string
     */
    protected function getRequiredUseClasses(array $fields, array $additions = [])
    {
        $commands = [];

        foreach ($additions as $addition) {
            $commands[] = $this->getUseClassCommand($addition);
        }

        array_merge($commands, $this->getNamespacesForUsedRelations($fields));

        // Attempt to include classes that don't start with \\ with using command.
        foreach ($fields as $field) {
            if (!empty($field->onStore) && !in_array($field->onStore, $commands)) {
                $commands[] = $this->extractNamespace($field->onStore);
            }

            if (!empty($field->onUpdate) && !in_array($field->onUpdate, $commands)) {
                $commands[] = $this->extractNamespace($field->onUpdate);
            }

            // Extract the name spaces from he custom rules
            $customRules = $this->extractCustomValidationRules($field->getValidationRule());
            $namespaces = $this->extractCustomValidationNamespaces($customRules);
            foreach ($namespaces as $namespace) {
                $commands[] = $this->getUseClassCommand($namespace);
            }
        }
        $commands = array_unique($commands);
        sort($commands);

        return implode(PHP_EOL, $commands);
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
        $commands = [];
        $collections = $this->getRelationCollections($fields, 'form');

        foreach ($collections as $collection) {
            $command = $this->getUseClassCommand($collection->getFullForeignModel());

            if (!in_array($command, $commands)) {
                $commands[] = $this->getUseClassCommand($collection->getFullForeignModel());
            }
        }

        return $commands;
    }

    /**
     * Gets the type hinted request name
     *
     * @param string $name
     *
     * @return string
     */
    protected function getTypeHintedRequestName($name)
    {
        return sprintf('%s %s', $name, $this->requestVariable);
    }

    /**
     * Gets the needed compact variables for the edit/create views.
     *
     * @param array $fields
     * @param string $view
     *
     * @return string
     */
    protected function getWithRelationFor(array $fields, $view)
    {
        $variables = [];
        $collections = $this->getRelationCollections($fields, $view);

        foreach ($collections as $collection) {
            if (!in_array($collection->name, $variables)) {
                $variables[] = strtolower($collection->name);
            }
        }

        return $this->getWithRelationsStatement($variables);
    }

    /**
     * Converts giving array of relation name to a with() statements.
     *
     * @param array $variables
     *
     * @return string
     */
    protected function getWithRelationsStatement(array $relations)
    {
        if (empty($relations)) {
            return '';
        }

        return sprintf('with(%s)->', implode(',', Helpers::wrapItems($relations)));
    }

    /**
     * Checks if a giving fields array conatins at least one multiple answers' field.
     *
     * @param array
     *
     * @return bool
     */
    protected function isContainMultipleAnswers(array $fields)
    {
        $filtered = array_filter($fields, function ($field) {
            return $field->isMultipleAnswers();
        });

        return count($filtered) > 0;
    }

    /**
     * Checks if a string is a qualified namespace.
     *
     * @param string $name
     *
     * @return bool
     */
    protected function isQualifiedNamespace($name)
    {
        return !empty($name) && !starts_with($name, '\\');
    }

    /**
     * Calls the create:form-request command
     *
     * @param  CrestApps\CodeGenerator\Models\ViewInput $input
     *
     * @return $this
     */
    protected function makeFormRequest($input)
    {
        $this->callSilent(
            'create:form-request',
            [
                'model-name' => $input->modelName,
                '--class-name' => $input->formRequestName,
                '--with-auth' => $input->withAuth,
                '--resource-file' => $input->resourceFile,
                '--template-name' => $input->template,
                '--routes-prefix' => $this->option('routes-prefix'),
                '--form-request-directory' => $input->formRequestDirectory,
                '--force' => $input->force,
            ]
        );

        return $this;
    }

    /**
     * Replaces the auth middleware
     *
     * @param  string  $stub
     * @param  string  $middleware
     *
     * @return $this
     */
    protected function replaceAuthMiddlewear(&$stub, $middleware)
    {
        return $this->replaceTemplate('auth_middleware', $middleware, $stub);
    }

    /**
     * Replaces call_get_data
     *
     * @param  string  $stub
     * @param  string  $code
     *
     * @return $this
     */
    protected function replaceCallDataMethod(&$stub, $code)
    {
        return $this->replaceTemplate('call_get_data', $code, $stub);
    }

    /**
     * Replaces the auth contructor
     *
     * @param  string  $stub
     * @param  string  $contructor
     *
     * @return $this
     */
    protected function replaceConstructor(&$stub, $contructor)
    {
        return $this->replaceTemplate('constructor', $contructor, $stub);
    }

    /**
     * Replaces controller_extends
     *
     * @param  string  $stub
     * @param  string  $commands
     *
     * @return $this
     */
    protected function replaceControllerExtends(&$stub, $commands)
    {
        return $this->replaceTemplate('controller_extends', $commands, $stub);
    }

    /**
     * Replaces the data_variable.
     *
     * @param  string  $stub
     * @param  string  $commands
     *
     * @return $this
     */
    protected function replaceDataVariable(&$stub, $commands)
    {
        return $this->replaceTemplate('data_variable', $commands, $stub);
    }

    /**
     * Replaces the upload-method's code for the given stub.
     *
     * @param $stub
     * @param $method
     *
     * @return $this
     */
    protected function replaceFileMethod(&$stub, $method)
    {
        return $this->replaceTemplate('upload_method', $method, $stub);
    }

    /**
     * Replaces on_store_setter
     *
     * @param  string  $stub
     * @param  string  $commands
     *
     * @return $this
     */
    protected function replaceOnStoreAction(&$stub, $commands)
    {
        return $this->replaceTemplate('on_store_setter', $commands, $stub);
    }

    /**
     * Replaces on_update_setter
     *
     * @param  string  $stub
     * @param  string  $commands
     *
     * @return $this
     */
    protected function replaceOnUpdateAction(&$stub, $commands)
    {
        return $this->replaceTemplate('on_update_setter', $commands, $stub);
    }

    /**
     * Replaces the models per page total for the given stub.
     *
     * @param $stub
     * @param $total
     *
     * @return $this
     */
    protected function replacePaginationNumber(&$stub, $total)
    {
        return $this->replaceTemplate('models_per_page', $total, $stub);
    }

    /**
     * Replaces relationCollections for the giving stub.
     *
     * @param $stub
     * @param $collections
     *
     * @return $this
     */
    protected function replaceRelationCollections(&$stub, $collections)
    {
        return $this->replaceTemplate('relation_collections', $collections, $stub);
    }

    /**
     * Replace sthe form-request's fullname for the given stub.
     *
     * @param  string  $stub
     * @param  string  $name
     *
     * @return $this
     */
    protected function replaceRequestFullName(&$stub, $name)
    {
        return $this->replaceTemplate('request_fullname', $name, $stub);
    }

    /**
     * Replaces the form-request's name for the given stub.
     *
     * @param  string  $stub
     * @param  string  $name
     *
     * @return $this
     */
    protected function replaceRequestName(&$stub, $name)
    {
        return $this->replaceTemplate('request_name', $name, $stub);
    }

    /**
     * Replaces request variable.
     *
     * @param  string  $stub
     * @param  string  $variable
     *
     * @return $this
     */
    protected function replaceRequestVariable(&$stub, $variable)
    {
        return $this->replaceTemplate('request_variable', $variable, $stub);
    }

    /**
     * It Replaces the templates of the givin $labels
     *
     * @param string $stub
     * @param array $items
     *
     * @return $this
     */
    protected function replaceStandardLabels(&$stub, array $items)
    {
        foreach ($items as $labels) {
            foreach ($labels as $label) {
                $text = $label->isPlain ? sprintf("'%s'", $label->text) : sprintf("trans('%s')", $label->localeGroup);
                $stub = $this->strReplace($label->template, $text, $stub);
            }
        }

        return $this;
    }

    /**
     * Replaces the type_hinted_request_name for the given stub.
     *
     * @param $stub
     * @param $code
     *
     * @return $this
     */
    protected function replaceTypeHintedRequestName(&$stub, $code)
    {
        return $this->replaceTemplate('type_hinted_request_name', $code, $stub);
    }

    /**
     * Replaces withRelationsForIndex for the giving stub.
     *
     * @param $stub
     * @param $relations
     *
     * @return $this
     */
    protected function replaceWithRelationsForIndex(&$stub, $relations)
    {
        return $this->replaceTemplate('with_relations_for_index', $relations, $stub);
    }

    /**
     * Replaces withRelationsForShow for the giving stub.
     *
     * @param $stub
     * @param $relations
     *
     * @return $this
     */
    protected function replaceWithRelationsForShow(&$stub, $relations)
    {
        return $this->replaceTemplate('with_relations_for_show', $relations, $stub);
    }

    /**
     * Processes common tasks
     *
     * @param object $input
     * @param CrestApps\CodeGenerator\Models\Resource $resource
     * @param string $stub
     *
     * @return $this
     */
    protected function processCommonTasks($input, $resource, &$stub)
    {
        $this->ChangeRequestType($input);

        $dataMethod = $this->getDataMethod($resource->fields, $this->requestNameSpace . '\\' . $this->requestName, $input);
        $languages = array_keys(Helpers::getLanguageItems($resource->fields));
        $viewLabels = new ViewLabelsGenerator($input->modelName, $resource->fields, $this->isCollectiveTemplate());
        $namespacesToUse = $this->getRequiredUseClasses($resource->fields, $this->getAdditionalNamespaces($input));

        return $this->replaceViewNames($stub, $input->viewDirectory, $input->prefix)
            ->replaceGetDataMethod($stub, $dataMethod)
            ->replaceCallDataMethod($stub, $this->getCallDataMethod($input->withFormRequest))
            ->replaceUseCommandPlaceholder($stub, $namespacesToUse)
            ->replaceModelName($stub, $input->modelName)
            ->replaceNamespace($stub, $this->getControllersNamespace($input->controllerDirectory))
            ->replaceControllerExtends($stub, $this->getControllerExtends($this->getFullClassToExtend()))
            ->replaceConstructor($stub, $this->getConstructor($input->withAuth))
            ->replacePaginationNumber($stub, $input->perPage)
            ->replaceFileMethod($stub, $this->getUploadFileMethod($resource->fields, $this->getFullClassToExtend(), $input->withFormRequest))
            ->replaceWithRelationsForIndex($stub, $this->getWithRelationFor($resource->fields, 'index'))
            ->replaceWithRelationsForShow($stub, $this->getWithRelationFor($resource->fields, 'show'))
            ->replaceRelationCollections($stub, $this->getRequiredRelationCollections($resource->fields))
            ->replaceOnStoreAction($stub, $this->getOnStoreAction($resource->fields))
            ->replaceOnUpdateAction($stub, $this->getOnUpdateAction($resource->fields))
            ->replaceAppName($stub, Helpers::getAppName())
            ->replaceControllerName($stub, $input->controllerName)
            ->replaceDataVariable($stub, $this->dataVariable)
            ->replaceRequestName($stub, $this->requestName)
            ->replaceRequestFullName($stub, $this->requestNameSpace)
            ->replaceRequestVariable($stub, $this->requestVariable)
            ->replaceTypeHintedRequestName($stub, $this->getTypeHintedRequestName($this->requestName))
            ->replaceStandardLabels($stub, $viewLabels->getLabels());
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
        return [
            Helpers::getModelNamespace($input->modelName, $input->modelDirectory),
            $this->requestNameSpace,
            $this->getFullClassToExtend(),
        ];
    }

    /**
     * Changes the request to a form-request when required.
     *
     * @param object $input
     *
     * @return void
     */
    protected function ChangeRequestType($input)
    {
        if (!$input->withFormRequest) {
            return;
        }

        $this->requestName = $input->formRequestName;
        $this->requestNameSpace = $this->getRequestsNamespace($this->requestName, $input->formRequestDirectory);

        if (!$this->option('without-form-request')) {
            $this->makeFormRequest($input);
        }
    }

    /**
     * Gets the controller stub.
     *
     * @return string
     */
    protected function getControllerStub()
    {
        return $this->getStubContent($this->getControllerType());
    }

    /**
     * Gets the path to controllers
     *
     * @param string $file
     *
     * @return string
     */
    abstract protected function getControllerPath($file = '');

    /**
     * Gets the type of the controller
     *
     * @return string
     */
    abstract protected function getControllerType();
}
