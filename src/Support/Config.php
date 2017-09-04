<?php

namespace CrestApps\CodeGenerator\Support;

use CrestApps\CodeGenerator\Support\Helpers;

class Config
{
    /**
     * Gets the default datetime output format
     *
     * @return array
     */
    public static function getCommonDefinitions()
    {
        return config('codegenerator.common_definitions', []);
    }

    /**
     * Gets the postfix value for a controller name
     *
     * @return array
     */
    public static function getControllerNamePostFix()
    {
        return config('codegenerator.controller-name-postfix', 'Controller');
    }

    /**
     * Gets the postfix value for a controller name
     *
     * @return array
     */
    public static function getFormRequestNamePostFix()
    {
        return config('codegenerator.form-request-name-postfix', 'FormRequest');
    }

    /**
     * Checks if a giving file type should be a plural or not.
     *
     * @return array
     */
    public static function shouldBePlural($key)
    {
        $config = config('codegenerator.plural_names_for', []);

        if (isset($config[$key])) {
            return (bool) $config[$key];
        }

        return ($key != 'model-name');
    }

    /**
     * Gets the non-english singular to plural definitions.
     *
     * @return array
     */
    public static function getPluralDefinitions()
    {
        return config('codegenerator.plural_definitions', []);
    }

    /**
     * Gets the default datetime output format
     *
     * @return array
     */
    public static function getDateTimeFormat()
    {
        return config('codegenerator.datetime_out_format', 'm/d/Y H:i A');
    }

    /**
     * Gets the default resources mapper file
     *
     * @return string
     */
    public static function getDefaultMapperFileName()
    {
        return config('codegenerator.default_mapper_file_name', 'resources_map.json');
    }

    /**
     * Check if the resource mapper should be auto managed.
     *
     * @return bool
     */
    public static function autoManageResourceMapper()
    {
        return config('codegenerator.auto_manage_resource_mapper', true);
    }

    /**
     * Gets the default placeholders by type
     *
     * @return array
     */
    public static function getPlaceholderByHtmlType()
    {
        return config('codegenerator.placeholder_by_html_type', []);
    }

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
     * Gets the path to the field files.
     *
     * @param string $file = '';
     *
     * @return string
     */
    public static function pathToFieldFiles($file = '')
    {
        $path = config('codegenerator.fields_file_path', '');

        return Helpers::getPathWithSlash($path) . $file;
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
     * @param string $file
     *
     * @return string
     */
    public static function getRequestsPath($file = '')
    {
        $path = config('codegenerator.form_requests_path');

        return Helpers::getPathWithSlash($path) . $file;
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
     * Gets the eloquent's method to html
     *
     * @return array
     */
    public static function getEloquentToHtmlMap()
    {
        return config('codegenerator.eloquent_type_to_html_type', []);
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
     * @param string $file
     *
     * @return string
     */
    public static function getControllersPath($file = '')
    {
        $path = config('codegenerator.controllers_path');

        return Helpers::getPathWithSlash($path) . $file;
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
     * @param string $filename
     *
     * @return string
     */
    public static function getFieldsFilePath($filename = '')
    {
        $path = config('codegenerator.fields_file_path', 'resources/codegenerator-files');

        return Helpers::getPathWithSlash($path) . $filename;
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
