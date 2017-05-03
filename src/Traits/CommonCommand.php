<?php

namespace CrestApps\CodeGenerator\Traits;

use App;
use File;
use Exception;
use CrestApps\CodeGenerator\Support\Helpers;
use CrestApps\CodeGenerator\Models\Field;
use Illuminate\Container\Container;

trait CommonCommand
{
    /**
     * The default route actions
     *
     * @var array
     */
    protected $actions = ['index','create','show','update','edit','destroy','store'];

    /**
     * The default views actions
     *
     * @var array
     */
    protected $views = ['form','index','create','show','edit'];

    /**
     * Gets the field from the input
     *
     * @return Field array
     */
    protected function getFields($fields, $langFile, $fieldsFile = null)
    {
        if (!empty($fieldsFile)) {
            return Helpers::getFieldsFromFile($fieldsFile, $langFile);
        }

        return Helpers::getFields($fields, $langFile);
    }

    /**
     * Evaluates the current version of the framework to see if it >= a giving version.
     *
     * @param $version
     *
     * @return bool
     */
    protected function isNewerThan($version = '5.3')
    {
        return (App::VERSION() >= $version);
    }

    /**
     * Gets the correct routes fullname based on current framework version.
     *
     * @return string
     */
    protected function getRoutesFileName()
    {
        if ($this->isNewerThan()) {
            return base_path('routes/web.php');
        }

        return app_path('Http/routes.php');
    }

    /**
     * Gets all command's arguments depending on the current framework version.
     *
     * @return string
     */
    public function arguments()
    {
        if ($this->isNewerThan()) {
            return parent::arguments();
        }

        return parent::argument();
    }

    /**
     * Reduceses multiple new line into one.
     *
     * @param string $stub
     *
     * @return $this
     */
    protected function reduceNewLines(&$stub)
    {
        while (strpos($stub, "\r\n\r\n") !== false) {
            $stub = str_replace("\r\n\r\n", "\r\n", $stub);
        }

        return $this;
    }

    /**
     * Gets all command's options depending on the current framework version.
     *
     * @return string
     */
    public function options()
    {
        if ($this->isNewerThan()) {
            return parent::options();
        }

        return parent::option();
    }

    /**
     * It Replaces the view names in a giving stub
     *
     * @param string $stub
     * @param string $viewDirectory
     * @param array $views
     *
     * @return $this
     */
    protected function replaceViewNames(&$stub, $viewDirectory, $routesPrefix, array $views = null)
    {
        $views = empty($views) ? $this->views : $views;

        foreach ($views as $view) {
            $viewName = $this->getDotNotationName($viewDirectory, $routesPrefix, $view);
            $stub = str_replace($this->getViewName($view), $viewName, $stub);
        }
        
        return $this;
    }

    /**
     * Gets a full name with dot notation
     *
     * @param string $viewDirectory
     * @param string $routesPrefix
     * @param string $name
     *
     * @return $this
     */
    protected function getDotNotationName($viewDirectory, $routesPrefix, $name = 'index')
    {
        if (!empty($viewDirectory)) {
            $name = Helpers::getWithDotPostFix(Helpers::convertToDotNotation($viewDirectory)) . $name;
        }

        if (!empty($routesPrefix)) {
            $name = Helpers::getWithDotPostFix(Helpers::convertToDotNotation($routesPrefix)) . $name;
        }

        return strtolower($name);
    }

    /**
     * Gets the stub file.
     *
     *@param string $name
     * @param string $template
     *
     * @return string
     */
    protected function getStubByName($name, $template = null)
    {
        return sprintf('%s%s.stub', $this->getPathToTemplates($template), $name);
    }

    /**
     * Replaces the route names for all the provided actions
     *
     * @param string $stub
     * @param string $modelName
     * @param string $routesPrefix
     * @param array $actions
     *
     * @return $this
     */
    protected function replaceRouteNames(&$stub, $modelName, $routesPrefix, array $actions = null)
    {
        $actions = empty($actions) ? $this->actions : $actions;

        foreach ($actions as $action) {
            $routeName = $this->getDotNotationName($modelName, $routesPrefix, $action);
            $stub = str_replace($this->getRouteName($action), $routeName, $stub);
        }
        
        return $this;
    }

    /**
     * Gets a route name
     *
     * @param string $action
     *
     * @return string
     */
    protected function getRouteName($action)
    {
        return sprintf('{{%sRouteName}}', $action);
    }

    /**
     * Gets a view name
     *
     * @param string $action
     *
     * @return string
     */
    protected function getViewName($view)
    {
        return sprintf('{{%sViewName}}', $view);
    }

    /**
     * Gets the content of a stub
     *
     * @param string $name
     * @param string $template
     *
     * @return string
     */
    protected function getStubContent($name, $template = null)
    {
        return File::get($this->getStubByName($name, $template));
    }

    /**
     * Replace the modelName fo the given stub.
     *
     * @param string $stub
     * @param string $modelName
     *
     * @return $this
     */
    protected function replaceModelName(&$stub, $modelName)
    {
        $stub = str_replace('{{modelName}}', $this->getModelName($modelName), $stub);
        $stub = str_replace('{{modelNameClass}}', $this->getModelClassName($modelName), $stub);
        $stub = str_replace('{{modelNamePlural}}', $this->getModelPluralName($modelName), $stub);
        $stub = str_replace('{{modelNamePluralCap}}', $this->getModelNamePluralCap($modelName), $stub);

        return $this;
    }

    protected function getModelClassName($name)
    {
        return ucwords($name);
    }

    protected function getModelName($name)
    {
        return strtolower($name);
    }

    protected function getModelPluralName($name)
    {
        return str_plural(strtolower($name));
    }

    protected function getModelNamePluralCap($name)
    {
        return ucwords($this->getModelPluralName($name));
    }

    protected function getAppNamespace()
    {
        return Container::getInstance()->getNamespace();
    }

    /**
     * Gets the common name patterns to use for headers.
     * 
     * @return array
    */
    protected function getCommonHeadersPatterns()
    {
        return config('codegenerator.common_header_patterns') ?: [];
    }

    /**
     * Gets the common datetime patterns.
     * 
     * @return array
    */
    protected function getCommonDateTimePatterns()
    {
        return config('codegenerator.common_datetime_patterns') ?: [];
    }

    /**
     * Gets the common primary ids patterns.
     * 
     * @return array
    */
    protected function getCommonIdPatterns()
    {
        return config('codegenerator.common_id_patterns') ?: [];
    }

    /**
     * Gets the common key patterns.
     * 
     * @return array
    */
    protected function getCommonKeyPatterns()
    {
        return config('codegenerator.common_key_patterns') ?: [];
    }

    /**
     * It checks if a giving field is a primary or not.
     * 
     * @param CrestApps\CodeGenerator\Models\Field $field
     * @return bool
    */
    protected function isPrimaryField(Field $field)
    {
        return (in_array($field->name, $this->getCommonHeadersPatterns()) || $field->isAutoIncrement || $field->isPrimary);
    }

    /**
     * Determine if the class already exists.
     *
     * @param  string  $rawName
     * @return bool
     */
    protected function alreadyExists($rawName)
    {
        if ($this->option('force')) {
            return false;
        }

        return parent::alreadyExists($rawName);
    }

    /**
     * Determine the primary field in a giving array
     *
     * @param array $fields
     *
     * @return CrestApps\CodeGenerator\Models\Field
     */
    protected function getPrimaryField(array $fields)
    {
        foreach ($fields as $field) {
            if ($this->isField($field) && ($field->isPrimary || $field->isAutoIncrement)) {
                return $field;
            }
        }

        return null;
    }

    /**
     * Determine the field to be used for header from the givin fields.
     *
     * @param array $fields
     * @return CrestApps\CodeGenerator\Models\Field || null
     */
    protected function getHeaderField(array $fields)
    {
        foreach ($fields as $field) {
            if ($this->isField($field) && $field->isHeader) {
                return $field;
            }
        }

        return null;
    }

     /**
     * Build the directory for the class if necessary.
     *
     * @param  string  $path
     * @return $this
     */
    protected function createDirectory($path)
    {
        if (!File::isDirectory($path)) {
            File::makeDirectory($path, 0777, true, true);
        }

        return $this;
    }

    /**
     * Gets laravel ready field validation format from a giving string
     *
     * @param string $validations
     *
     * @return string
     */
    protected function getValidationRules(array $fields)
    {
        $validations = '';

        foreach ($fields as $field) {
            if (!empty($field->validationRules)) {
                $validations .= sprintf("        '%s' => '%s',\n    ", $field->name, implode('|', $field->validationRules));
            }
        }

        return $validations;
    }

    /**
     * Gets the path to requests
     *
     * @return string
     */
    protected function getRequestsPath()
    {
        return Helpers::getPathWithSlash(config('codegenerator.form_requests_path'));
    }

    /**
     * Gets the path to models
     *
     * @return string
     */
    protected function getModelsPath()
    {
        return Helpers::getPathWithSlash(config('codegenerator.models_path'));
    }

    /**
     * Gets the path to controllers
     *
     * @return string
     */
    protected function getControllersPath()
    {
        return Helpers::getPathWithSlash(config('codegenerator.controllers_path'));
    }

    /**
     * Gets the path to languages
     *
     * @return string
     */
    protected function getLanguagesPath()
    {
        return Helpers::getPathWithSlash(config('codegenerator.languages_path'));
    }

    /**
     * Checks if the givin field is an instance of a field or not.
     *
     * @return string
     */
    protected function isField($field)
    {
        return $field instanceof Field;
    }

    /**
     * Gets the path to views
     *
     * @return string
     */
    protected function getViewsPath()
    {
        return Helpers::getPathWithSlash(config('view.paths')[0]);
    }

    /**
     * Gets the migrations path.
     *
     * @return string
     */
    protected function getMigrationsPath()
    {
        return Helpers::getPathWithSlash(config('codegenerator.migrations_path'));
    }

    /**
     * Gets the field's file path.
     *
     * @return string
     */
    protected function getFieldsFilePath()
    {
        return Helpers::getPathWithSlash(config('codegenerator.fields_file_path'));
    }

    /**
     * Gets the template names that uses Laravel-Collective
     *
     * @return array
     */
    protected function getCollectiveTemplates()
    {
        return config('codegenerator.laravel_collective_templates', ['default-collective']);
    }

    /**
     * Gets the path to templates
     *
     * @param string $template
     *
     * @return string
     */
    protected function getPathToTemplates($template = null)
    {
        $template = Helpers::getPathWithSlash($template ?: config('codegenerator.template'));
        $path = Helpers::getPathWithSlash(config('codegenerator.templates_path')) . $template;

        if (!File::exists($path)) {
            throw new Exception('Invalid template name or the templates is invalid. Make sure the following path exists: "' . $path . '"');
        }

        return $path;
    }

    /**
     * Gets the template name from the options line.
     *
     * @return string
     */
    protected function getTemplateName()
    {
        return trim($this->option('template-name')) ?: $this->getDefaultTemplateName();
    }

    /**
     * Gets the default template name.
     *
     * @return string
     */
    protected function getDefaultTemplateName()
    {
        return config('codegenerator.template', 'default');
    }

    /**
     * Checks if a giving fields array conatins at least one file field
     *
     * @param array
     *
     * @return bool
     */
    protected function isContainfile(array $fields)
    {
        $filtered = array_filter($fields, function ($field) {
            return $field->isFile();
        });
        
        return (count($filtered) > 0);
    }
}
