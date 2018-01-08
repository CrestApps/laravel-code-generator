<?php

namespace CrestApps\CodeGenerator\Traits;

use CrestApps\CodeGenerator\Support\Config;
use CrestApps\CodeGenerator\Support\Helpers;

trait ApiResourceTrait
{
    /**
     * Gets a resource-file name
     *
     * @param string $modelName
     *
     * @return string
     */
    protected function getApiResourceClassName($modelName)
    {
        $cName = trim($this->option('api-resource-name'));

        return $cName ? str_finish($cName, Config::getApiResourceNamePostFix()) : Helpers::makeApiResourceName($modelName);
    }

    /**
     * Gets the title of the generated file
     *
     * @param string $modelName
     * @param bool $isCollection
     *
     * @return string
     */
    protected function getFileTitle($isCollection)
    {
        if ($isCollection) {
            return 'api-resource-collection';
        }

        return 'api-resource';
    }

    /**
     * Gets a resource-file name
     *
     * @param string $modelName
     *
     * @return string
     */
    protected function getApiResourceCollectionClassName($modelName)
    {
        $cName = trim($this->option('api-resource-collection-name'));

        return $cName ? str_finish($cName, Config::getApiResourceCollectionNamePostFix()) : Helpers::makeApiResourceCollectionName($modelName);
    }

    /**
     * Gets the field in array ready format.
     *
     * @param array $fields
     * @param bool $forApiCollectionClass
     * @param string $prefix
     *
     * @return string
     */
    protected function getModelApiArray(array $fields, $modelName, $forApiCollectionClass = false, $prefix = '            ')
    {
        $properties = [];
        foreach ($fields as $field) {
            if (!$field->isApiVisible) {
                continue;
            }

            $accessor = $field->getAccessorValue('this->resource');

            $properties[] = sprintf("%s'%s' => %s,", $prefix, $field->getApiKey(), $accessor);
        }

        return implode(PHP_EOL, $properties);
    }

    /**
     * Check if the current laravel version has api-support
     *
     * @return bool
     */
    protected function isApiResourceSupported()
    {
        return Helpers::isNewerThanOrEqualTo('5.5');
    }

    /**
     * Gets the api-resource's namespace.
     *
     * @param string $modelName
     *
     * @return string
     */
    protected function getApiResourceNamespace($className)
    {
        $cName = trim($this->option('api-resource-name'));

        $path = Helpers::convertSlashToBackslash($this->getApiResourcePath());

        return Helpers::getPathWithSlash($path) . $className;
    }

    /**
     * Gets the api-resource's namespace.
     *
     * @param string $modelName
     *
     * @return string
     */
    protected function getApiResourceCollectionNamespace($className)
    {
        $cName = trim($this->option('api-resource-collection-name'));

        $path = Helpers::convertSlashToBackslash($this->getApiResourceCollectionPath());

        return Helpers::getPathWithSlash($path) . $className;
    }

    /**
     * Gets the api-resource's destenation path
     *
     * @return string
     */
    protected function getApiResourcePath()
    {
        $path = trim($this->option('api-resource-directory'));

        if (!empty($path)) {
            $path = Helpers::getPathWithSlash(ucfirst($path));
        }

        $path = Helpers::getAppNamespace() . Config::getApiResourcePath($path);

        return Helpers::getPathWithSlash($path);
    }

    /**
     * Gets the api-resource-collection's destenation path
     *
     * @return string
     */
    protected function getApiResourceCollectionPath()
    {
        $path = trim($this->option('api-resource-collection-directory'));

        if (!empty($path)) {
            $path = Helpers::getPathWithSlash(ucfirst($path));
        }

        return Helpers::getAppNamespace() . Config::getApiResourceCollectionPath($path);
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
        $isCollection = property_exists($input, 'isCollection') ? $input->isCollection : false;

        $this->replaceModelApiArray($stub, $this->getModelApiArray($fields, $input->modelName, $isCollection))
            ->replaceModelName($stub, $input->modelName)
            ->replaceModelFullname($stub, Helpers::getModelNamespace($input->modelName, $input->modelDirectory));

        return $stub;
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
     * Replaces the api_resource_class for the given stub.
     *
     * @param  string  $stub
     * @param  string  $name
     *
     * @return $this
     */
    protected function replaceApiResourceClass(&$stub, $name)
    {
        return $this->replaceTemplate('api_resource_class', $name, $stub);
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

    /**
     * Replaces the api_resource_class for the given stub.
     *
     * @param  string  $stub
     * @param  string  $name
     *
     * @return $this
     */
    protected function replaceApiResourceCollectionClass(&$stub, $name)
    {
        return $this->replaceTemplate('api_resource_collection_class', $name, $stub);
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
}
