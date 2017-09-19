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
     * Gets the default value for whether to generate the moveFile method
     * or not.
     *
     * @return bool
     */
    public static function createMoveFileMethod()
    {
        return (bool) config('codegenerator.create_move_file_method', true);
    }

    /**
     * Gets the postfix value for a controller name
     *
     * @return string
     */
    public static function getControllerNamePostFix()
    {
        return config('codegenerator.controller_name_postfix', 'Controller');
    }

    /**
     * Gets the postfix value for a controller name
     *
     * @return string
     */
    public static function getFormRequestNamePostFix()
    {
        return config('codegenerator.form-request_name_postfix', 'FormRequest');
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
     * Gets the common datetime patterns to use for headers.
     *
     * @return array
     */
    public static function getDateTimePatterns()
    {
        return config('codegenerator.common_datetime_patterns', []);
    }

    /**
     * Gets the common id patterns to use for headers.
     *
     * @return array
     */
    public static function getCommonIdPatterns()
    {
        return config('codegenerator.common_id_patterns', []);
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

    /**
     * Get the custom field templates.
     *
     * @return array
     */
    public static function getCustomFieldTemplates()
    {
        return config('codegenerator.generic_field_labels', [
            'current_uploaded_file' => [
                'text' => 'Current [% field_name_title %]:',
                'template' => 'current_uploaded_file',
            ],
        ]);
    }

    /**
     * Get the custom model templates.
     *
     * @return array
     */
    public static function getCustomModelTemplates()
    {
        return config('codegenerator.generic_view_labels', [
            'create' => [
                'text' => 'Create New [% model_name_title %]',
                'template' => 'create_model',
            ],
            'delete' => [
                'text' => 'Delete [% model_name_title %]',
                'template' => 'delete_model',
                'in-function-with-collective' => true,
            ],
            'edit' => [
                'text' => 'Edit [% model_name_title %]',
                'template' => 'edit_model',
            ],
            'show' => [
                'text' => 'Show [% model_name_title %]',
                'template' => 'show_model',
            ],
            'show_all' => [
                'text' => 'Show All [% model_name_title %]',
                'template' => 'show_all_models',
            ],
            'add' => [
                'text' => 'Add',
                'template' => 'add',
                'in-function-with-collective' => true,
            ],
            'update' => [
                'text' => 'Update',
                'template' => 'update',
                'in-function-with-collective' => true,
            ],
            'confirm_delete' => [
                'text' => 'Delete [% model_name_title %]?',
                'template' => 'confirm_delete',
                'in-function-with-collective' => true,
            ],
            'none_available' => [
                'text' => 'No [% model_name_plural_title %] Available!',
                'template' => 'no_models_available',
            ],
            'model_plural' => [
                'text' => '[% model_name_plural_title %]',
                'template' => 'model_plural',
            ],
            'model_was_added' => [
                'text' => '[% model_name_title %] was successfully added!',
                'template' => 'model_was_added',
            ],
            'model_was_updated' => [
                'text' => '[% model_name_title %] was successfully updated!',
                'template' => 'model_was_updated',
            ],
            'model_was_deleted' => [
                'text' => '[% model_name_title %] was successfully deleted!',
                'template' => 'model_was_deleted',
            ],
            'unexpected_error' => [
                'text' => 'Unexpected error occurred while trying to process your request!',
                'template' => 'unexpected_error',
            ],
            'current_uploaded_file' => [
                'text' => 'Current [% model_name_title %]:',
                'template' => 'current_uploaded_file',
            ],
        ]);
    }
}
