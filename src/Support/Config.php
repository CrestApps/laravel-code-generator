<?php

namespace CrestApps\CodeGenerator\Support;

use CrestApps\CodeGenerator\Support\Helpers;

class Config
{
    /**
     * Gets the common name patterns to use for headers.
     * 
     * @return array
    */
    public static function getHeadersPatterns()
    {
        return config('codegenerator.common_header_patterns', []);
    }

    /**
     * Gets the common boolean patterns.
     * 
     * @return array
    */
    public static function getBooleanPatterns()
    {
        return config('codegenerator.common_boolean_patterns', []);
    }

    /**
     * Gets the common datetime patterns.
     * 
     * @return array
    */
    public static function getDateTimePatterns()
    {
        return config('codegenerator.common_datetime_patterns', []);
    }

    /**
     * Gets the common primary ids patterns.
     * 
     * @return array
    */
    public static function getIdPatterns()
    {
        return config('codegenerator.common_id_patterns', []);
    }

    /**
     * Gets the common primary ids patterns.
     * 
     * @return array
    */
    public static function getForeignKeys()
    {
        return config('codegenerator.common_foreign_keys', []);
    }

    /**
     * Gets the common key patterns.
     * 
     * @return array
    */
    public static function getKeyPatterns()
    {
        return config('codegenerator.common_key_patterns', []);
    }

    /**
     * Gets the path to requests
     *
     * @return string
     */
    public static function getRequestsPath()
    {
        $path = config('codegenerator.form_requests_path');

        return Helpers::getPathWithSlash($path);
    }

    /**
     * Gets the default template name.
     * 
     * @return array
    */
    public static function getTemplatesPath()
    {
        $path = config('codegenerator.templates_path', 'resources/codegenerator-templates');
        
        return Helpers::getPathWithSlash($path);
    }

    /**
     * Gets the path to models
     *
     * @return string
     */
    public static function getModelsPath($file = '')
    {
        $path = config('codegenerator.models_path');

        return Helpers::getPathWithSlash($path) . $file;
    }

    /**
     * Gets the path to controllers
     *
     * @return string
     */
    public static function getControllersPath()
    {
        $path = config('codegenerator.controllers_path');

        return Helpers::getPathWithSlash($path);
    }

    /**
     * Gets the path to languages
     *
     * @return string
     */
    public static function getLanguagesPath()
    {
        $path = config('codegenerator.languages_path', 'resources/lang');

        return Helpers::getPathWithSlash($path);
    }

    /**
     * Gets the path to views
     *
     * @return string
     */
    public static function getViewsPath()
    {
        $paths = config('view.paths', [0 => 'resources/views']);

        return Helpers::getPathWithSlash($paths[0]);
    }

    /**
     * Gets the migrations path.
     *
     * @return string
     */
    public static function getMigrationsPath()
    {
        $path = config('codegenerator.migrations_path', 'database/migrations');

        return Helpers::getPathWithSlash($path);
    }

    /**
     * Gets the field's file path.
     *
     * @return string
     */
    public static function getFieldsFilePath()
    {
        $path = config('codegenerator.fields_file_path', 'resources/codegenerator-files');

        return Helpers::getPathWithSlash($path);
    }

    /**
     * Gets the template names that uses Laravel-Collective
     *
     * @return array
     */
    public static function getCollectiveTemplates()
    {
        return config('codegenerator.laravel_collective_templates', ['default-collective']);
    }

    /**
     * Gets the default template name.
     *
     * @return string
     */
    public static function getDefaultTemplateName()
    {
        return config('codegenerator.template', 'default');
    }

    /**
     * Gets the eloquent type to method collection.
     *
     * @return array
    */
    public static function dataTypeMap()
    {
        return config('codegenerator.eloquent_type_to_method', []);
    }
}
