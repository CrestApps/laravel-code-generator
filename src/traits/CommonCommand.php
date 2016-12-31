<?php

namespace CrestApps\CodeGenerator\Traits;
use CrestApps\CodeGenerator\Support\Helpers;
use CrestApps\CodeGenerator\Support\Field;

use App;
use File;

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
    protected $views = ['index','create','show','edit','form'];

    /**
     * Gets the field from the input
     *
     * @return Field array 
     */
    protected function getFields($fields, $langFile, $fieldsFile = null)
    {
        if( !empty($fieldsFile))
        {
            return Helpers::getFieldsFromFile($fieldsFile, $langFile);
        }

        return Helpers::getFields($fields, $langFile);
    }

    /**
     * Gets the correct routes fullname based on current framework version.
     *
     * @return string
     */
    protected function getRoutesFileName()
    {

        if (App::VERSION() >= '5.3')
        {
            return base_path('routes/web.php');
        }

        return app_path('Http/routes.php');
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

        foreach ($views as $view)
        {
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

        if(!empty($viewDirectory))
        {
            $name = Helpers::getWithDotPostFix(Helpers::convertToDotNotation($viewDirectory)) . $name;
        }

        if(!empty($routesPrefix))
        {
            $name = Helpers::getWithDotPostFix(Helpers::convertToDotNotation($routesPrefix)) . $name;
        }

        return strtolower($name);
    }

    /**
     * Gets the stub file.
     *
     * @return string
     */
    protected function getStubByName($stubName)
    {
        return sprintf('%s%s.stub', $this->getPathToTemplates(), $stubName);
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

        foreach ($actions as $action)
        {
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
     *
     * @return string
     */
    protected function getStubContent($name)
    {
        return File::get($this->getStubByName($name));
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
        $stub = str_replace('{{modelName}}', strtolower($modelName), $stub);   

        $stub = str_replace('{{modelNameClass}}', ucwords($modelName), $stub);  

        $stub = str_replace('{{modelNamePlural}}', str_plural(strtolower($modelName)), $stub); 

        $stub = str_replace('{{modelNamePluralCap}}', ucwords(str_plural(strtolower($modelName))), $stub);        

        return $this;
    }

    /**
     * Determine if the class already exists.
     *
     * @param  string  $rawName
     * @return bool
     */
    protected function alreadyExists($rawName)
    {
        if($this->option('force'))
        {
            return false;
        }

        return parent::alreadyExists($rawName);
    }

    /**
     * Determine the primary field in a giving array
     *
     * @param array $fields
     * @param string $defaultFieldName
     * @return CrestApps\CodeGenerator\Support\Field 
     */
    protected function getPrimaryField(array $fields, $defaultFieldName = 'id')
    {
        $primaryField = null;

        foreach($fields as $field)
        {
            if($field instanceof Field)
            {
                //The first found field that has the primary flag set, is the primary key
                if($field->isPrimary || $field->isAutoIncrement)
                {
                    return $field;
                }

                //If the user did not specifiy a primary key, but we found a field called "id"
                //, we assume that the field "id" is the primary key
                if(strtolower($field->name) == $defaultFieldName)
                {
                    $primaryField = $field;
                }
            }
        }

        return $primaryField;
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
     * Gets the path to templates
     *
     * @return string
     */
    protected function getPathToTemplates()
    {

        if(!File::isDirectory(config('codegenerator.template')))
        {
            throw new Excption('Invalid templates path was found.');
        }

        return Helpers::getPathWithSlash(config('codegenerator.template'));
    }
}