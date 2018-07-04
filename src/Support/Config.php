<?php

namespace CrestApps\CodeGenerator\Support;

use Config as LaravelConfig;
use CrestApps\CodeGenerator\Support\Helpers;

class Config
{
    /**
     * Gets the default value for whether to generate the moveFile method
     * or not.
     *
     * @return bool
     */
    public static function createMoveFileMethod()
    {
        return self::getBoolBaseValue('create_move_file_method', true);
    }

    /**
     * Gets the default value for whether to use smart migrations or not
     *
     * @return bool
     */
    public static function useSmartMigration()
    {
        return self::getBoolBaseValue('use_smart_migrations', true);
    }

    /**
     * Gets the default value for whether to organize migrations or not
     * or not.
     *
     * @return bool
     */
    public static function organizeMigrations()
    {
        return self::getBoolBaseValue('organize_migrations', false);
    }

    /**
     * Gets the postfix value for a controller name
     *
     * @return string
     */
    public static function getControllerNamePostFix()
    {
        return self::getStringBaseValue('controller_name_postfix', 'Controller');
    }

    /**
     * Gets the default value of the system path
     *
     * @param string $file
     *
     * @return string
     */
    public static function getSystemPath($file = null)
    {
        $path = self::getStringBaseValue('system_files_path', 'resources/laravel-code-generator/system');

        return Helpers::getPathWithSlash($path) . $file;
    }

    /**
     * Gets the postfix value for a controller name
     *
     * @return string
     */
    public static function getFormRequestNamePostFix()
    {
        return self::getStringBaseValue('request_name_postfix', 'FormRequest');
    }

    /**
     * Checks if a given file type should be a plural or not.
     *
     * @return array
     */
    public static function shouldBePlural($key)
    {
        $config = self::getArrayBaseValue('plural_names_for', [
            'controller-name' => true,
            'request-form-name' => true,
            'route-group' => true,
            'language-file-name' => true,
            'resource-file-name' => true,
            'table-name' => true,
        ]);

        if (isset($config[$key])) {
            return (bool) $config[$key];
        }

        return ($key != 'model-name');
    }

    /**
     * Gets the non-English singular to plural definitions.
     *
     * @return array
     */
    public static function getPluralDefinitions()
    {
        return self::getArrayBaseValue('irregular_plurals', ['software' => 'software']);
    }

    /**
     * Gets the default datetime output format
     *
     * @return array
     */
    public static function getDateTimeFormat()
    {
        return self::getStringBaseValue('datetime_out_format', 'n/j/Y H:i A');
    }

    /**
     * Gets the default resources mapper file
     *
     * @return string
     */
    public static function getDefaultMapperFileName()
    {
        return self::getStringBaseValue('default_mapper_file_name', 'resources_map.json');
    }

    /**
     * Check if the resource mapper should be auto managed.
     *
     * @return bool
     */
    public static function autoManageResourceMapper()
    {
        return self::getBoolBaseValue('auto_manage_resource_mapper', true);
    }

    /**
     * Gets the default placeholders by type
     *
     * @return array
     */
    public static function getPlaceholderByHtmlType()
    {
        $default = [
            'text' => 'Enter [% field_name %] here...',
            'number' => 'Enter [% field_name %] here...',
            'password' => 'Enter [% field_name %] here...',
            'email' => 'Enter [% field_name %] here...',
            'select' => 'Select [% field_name %]',
        ];

        return self::getArrayBaseValue('placeholder_by_html_type', $default);
    }

    /**
     * Gets the common name patterns to use for headers.
     *
     * @return array
     */
    public static function getHeadersPatterns()
    {
        $default = [
            'title',
            'name',
            'label',
            'header',
        ];

        return self::getArrayBaseValue('common_header_patterns', $default);
    }

    /**
     * Gets the common key patterns.
     *
     * @return array
     */
    public static function getKeyPatterns()
    {
        $default = [
            '*_id',
            '*_by',
        ];

        return self::getArrayBaseValue('common_key_patterns', $default);
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
        $path = self::getStringBaseValue('form_requests_path', 'Http/Requests');

        return Helpers::getPathWithSlash($path) . $file;
    }

    /**
     * Gets the default template name.
     *
     * @return array
     */
    public static function getTemplatesPath()
    {
        $path = self::getStringBaseValue('templates_path', 'resources/laravel-code-generator/templates');

        return Helpers::getPathWithSlash($path);
    }

    /**
     * Gets the eloquent's method to html
     *
     * @return array
     */
    public static function getEloquentToHtmlMap()
    {
        $default = [
            'char' => 'text',
            'date' => 'text',
            'dateTime' => 'text',
            'dateTimeTz' => 'text',
            'bigIncrements' => 'number',
            'bigIncrements' => 'number',
            'binary' => 'textarea',
            'boolean' => 'checkbox',
            'decimal' => 'number',
            'double' => 'number',
            'enum' => 'select',
            'float' => 'number',
            'integer' => 'number',
            'integer' => 'number',
            'ipAddress' => 'text',
            'json' => 'checkbox',
            'jsonb' => 'checkbox',
            'longText' => 'textarea',
            'macAddress' => 'text',
            'mediumInteger' => 'number',
            'mediumText' => 'textarea',
            'string' => 'text',
            'text' => 'textarea',
            'time' => 'text',
            'timeTz' => 'text',
            'tinyInteger' => 'number',
            'tinyInteger' => 'number',
            'timestamp' => 'text',
            'timestampTz' => 'text',
            'unsignedBigInteger' => 'number',
            'unsignedBigInteger' => 'number',
            'unsignedInteger' => 'number',
            'unsignedInteger' => 'number',
            'unsignedMediumInteger' => 'number',
            'unsignedMediumInteger' => 'number',
            'unsignedSmallInteger' => 'number',
            'unsignedSmallInteger' => 'number',
            'unsignedTinyInteger' => 'number',
            'uuid' => 'text',
        ];

        return self::getArrayBaseValue('eloquent_type_to_html_type', $default);
    }

    /**
     * Gets the path to models
     *
     * @return string
     */
    public static function getModelsPath($file = '')
    {
        $path = self::getStringBaseValue('models_path', 'Models');

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
        $path = self::getStringBaseValue('controllers_path', 'Http/Controllers');

        return Helpers::getPathWithSlash($path) . $file;
    }

    /**
     * Gets the path to languages
     *
     * @return string
     */
    public static function getLanguagesPath()
    {
        $path = self::getStringBaseValue('languages_path', 'resources/lang');

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
        $path = self::getStringBaseValue('migrations_path', 'resources/lang');

        return Helpers::getPathWithSlash($path);
    }

    /**
     * Gets the resource file path.
     *
     * @param string $file
     *
     * @return string
     */
    public static function getResourceFilePath($file = '')
    {
        $path = self::getStringBaseValue('resource_file_path', 'resources/laravel-code-generator/sources');

        return Helpers::getPathWithSlash($path) . $file;
    }

    /**
     * Gets the template names that uses Laravel-Collective
     *
     * @return array
     */
    public static function getCollectiveTemplates()
    {
        return self::getArrayBaseValue('laravel_collective_templates', ['default-collective']);
    }

    /**
     * Gets the default template name.
     *
     * @return string
     */
    public static function getDefaultTemplateName()
    {
        return self::getStringBaseValue('template', 'default');
    }

    /**
     * Gets the eloquent type to method collection.
     *
     * @return array
     */
    public static function dataTypeMap()
    {
        $default = [
            'char' => 'char',
            'date' => 'date',
            'datetime' => 'dateTime',
            'datetimetz' => 'dateTimeTz',
            'biginteger' => 'bigIncrements',
            'bigint' => 'bigIncrements',
            'tinyblob' => 'binary',
            'mediumblob' => 'binary',
            'blob' => 'binary',
            'longblob' => 'binary',
            'binary' => 'binary',
            'bool' => 'boolean',
            'bit' => 'boolean',
            'boolean' => 'boolean',
            'decimal' => 'decimal',
            'double' => 'double',
            'enum' => 'enum',
            'list' => 'enum',
            'float' => 'float',
            'int' => 'integer',
            'integer' => 'integer',
            'ipaddress' => 'ipAddress',
            'json' => 'json',
            'jsonb' => 'jsonb',
            'longtext' => 'longText',
            'macaddress' => 'macAddress',
            'mediuminteger' => 'mediumInteger',
            'mediumint' => 'mediumInteger',
            'mediumtext' => 'mediumText',
            'smallInteger' => 'smallInteger',
            'smallint' => 'smallInteger',
            'morphs' => 'morphs',
            'string' => 'string',
            'varchar' => 'string',
            'nvarchar' => 'string',
            'text' => 'text',
            'time' => 'time',
            'timetz' => 'timeTz',
            'tinyinteger' => 'tinyInteger',
            'tinyint' => 'tinyInteger',
            'timestamp' => 'timestamp',
            'timestamptz' => 'timestampTz',
            'unsignedbiginteger' => 'unsignedBigInteger',
            'unsignedbigint' => 'unsignedBigInteger',
            'unsignedInteger' => 'unsignedInteger',
            'unsignedint' => 'unsignedInteger',
            'unsignedmediuminteger' => 'unsignedMediumInteger',
            'unsignedmediumint' => 'unsignedMediumInteger',
            'unsignedsmallinteger' => 'unsignedSmallInteger',
            'unsignedsmallint' => 'unsignedSmallInteger',
            'unsignedtinyinteger' => 'unsignedTinyInteger',
            'uuid' => 'uuid',
        ];

        return self::getArrayBaseValue('eloquent_type_to_method', $default);
    }

    /**
     * Get the custom model templates.
     *
     * @return array
     */
    public static function getCustomModelTemplates()
    {
        $default = [
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
        ];

        return self::getArrayBaseValue('generic_view_labels', $default);
    }

    /**
     * Get the config value of the given index, using the default configs.
     *
     * @param string $index
     * @param string $default = null
     *
     * @return mix
     */
    public static function get($index, $default = null)
    {
        $key = self::getKey($index);

        return LaravelConfig::get($key, $default);
    }

    /**
     * Get the config value of the given index, using the custom configs.
     *
     * @param string $index
     * @param string $default = null
     *
     * @return mix
     */
    public static function getCustom($index, $default = null)
    {
        $key = self::getCustomKey($index);

        return LaravelConfig::get($key, $default);
    }

    /**
     * Checks of the default-configs has a given key
     *
     * @param string $index
     *
     * @return bool
     */
    public static function has($index)
    {
        $key = self::getKey($index);

        return LaravelConfig::has($key);
    }

    /**
     * Checks of the custom-configs has a given key
     *
     * @param string $index
     *
     * @return bool
     */
    public static function hasCustom($index)
    {
        $key = self::getCustomKey($index);

        return LaravelConfig::has($key);
    }

    /**
     * Checks the key to access the default-config.
     *
     * @param string $index
     *
     * @return string
     */
    public static function getKey($index)
    {
        return sprintf('codegenerator.%s', $index);
    }

    /**
     * Checks the key to access the custom-config.
     *
     * @param string $index
     *
     * @return string
     */
    public static function getCustomKey($index)
    {
        return sprintf('codegenerator_custom.%s', $index);
    }

    /**
     * Get the proper array-based value from the config
     *
     * @param string $index
     * @param array $default
     *
     * @return array
     */
    public static function getArrayBaseValue($index, $default = [])
    {
        $values = (array) self::getCustom($index, []);

        return array_merge((array) self::get($index, $default), $values);
    }

    /**
     * Get the proper bool-based value from the config
     *
     * @param string $index
     * @param string $default
     *
     * @return bool
     */
    public static function getBoolBaseValue($index, $default)
    {
        if (self::hasCustom($index)) {
            return (bool) self::getCustom($index);
        }

        return (bool) self::get($index, true);
    }

    /**
     * Get the proper string-based value from the config
     *
     * @param string $index
     * @param string $default
     *
     * @return string
     */
    public static function getStringBaseValue($index, $default)
    {
        if (self::hasCustom($index)) {
            return (string) self::getCustom($index);
        }

        return (string) self::get($index, $default);
    }

    /**
     * Gets the common definitions.
     *
     * @return array
     */
    public static function getCommonDefinitions()
    {
        $customValues = (array) self::getCustom('common_definitions', []);

        $defaultValues = self::get('common_definitions', []);
        $final = [];
        $finalMatchingKeys = [];

        // Merge properties with existing patterns
        foreach ($defaultValues as $key => $defaultValue) {
            if (is_array($defaultValue)
                && array_key_exists('match', $defaultValue)
                && array_key_exists('set', $defaultValue)
            ) {
                $matches = (array) $defaultValue['match'];

                $finalMatchingKeys = array_merge($finalMatchingKeys, $matches);

                $final[] = [
                    'match' => $matches,
                    'set' => self::mergeDefinitions($matches, $customValues, (array) $defaultValue['set']),
                ];
            }
        }

        // Add new patterns
        foreach ($customValues as $key => $customValue) {
            if (is_array($customValue)
                && array_key_exists('match', $customValue)
                && array_key_exists('set', $customValue)
            ) {
                $newPatterns = array_diff((array) $customValue['match'], $finalMatchingKeys);

                if (empty($newPatterns)) {
                    // At this point there are no patters that we don't already have
                    continue;
                }

                $finalMatchingKeys = array_merge($finalMatchingKeys, $newPatterns);

                $final[] = [
                    'match' => $newPatterns,
                    'set' => $customValue['set'],
                ];
            }
        }

        return $final;
    }

    /**
     * Merges the field definition
     *
     * @param array $keys
     * @param array $customs
     * @param array $defaultValues
     *
     * @return array
     */
    protected static function mergeDefinitions(array $keys, array $customs, array $defaultValues)
    {
        $final = $defaultValues;

        foreach ($customs as $key => $custom) {

            if (!is_array($custom) || !array_key_exists('match', $custom) || !array_key_exists('set', $custom)) {
                continue;
            }
            $matches = (array) $custom['match'];
            $combined = array_intersect($keys, $matches);

            if (!empty($combined)) {
                $final = array_merge($final, (array) $custom['set']);
            }
        }

        return $final;
    }

}
