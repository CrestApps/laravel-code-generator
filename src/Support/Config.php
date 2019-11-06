<?php

namespace CrestApps\CodeGenerator\Support;

use Config as LaravelConfig;
use CrestApps\CodeGenerator\Support\Helpers;
use Exception;
use Illuminate\Config\Repository;

class Config
{
    /**
     * Gets the default configuration repository
     *
     * @var Illuminate\Config\Repository
     */
    private static $repository;

    /**
     * Gets the default value for whether to use smart migrations or not
     *
     * @return bool
     */
    public static function useSmartMigration()
    {
        return self::getBoolBaseValue('use_smart_migrations');
    }

    /**
     * Gets the default value for whether to organize migrations or not
     * or not.
     *
     * @return bool
     */
    public static function organizeMigrations()
    {
        return self::getBoolBaseValue('organize_migrations');
    }

    /**
     * Gets the postfix value for a controller name
     *
     * @return string
     */
    public static function getControllerNamePostFix()
    {
        return self::getStringBaseValue('controller_name_postfix');
    }

    /**
     * Gets the postfix value for a api-resource name
     *
     * @return string
     */
    public static function getApiResourceNamePostFix()
    {
        return self::getStringBaseValue('api_resource_name_postfix');
    }

    /**
     * Gets the postfix value for a api-resource name
     *
     * @return string
     */
    public static function getApiResourceCollectionNamePostFix()
    {
        return self::getStringBaseValue('api_resource_collection_name_postfix');
    }

    /**
     * Gets the default api-documentation labels
     *
     * @return array
     */
    public static function getApiDocumentationLabels()
    {
        return self::getArrayBaseValue('generic_api_documentation_labels');
    }

    /**
     * Gets the postfix value for a controller name
     *
     * @return string
     */
    public static function getFormRequestNamePostFix()
    {
        return self::getStringBaseValue('form_request_name_postfix');
    }

    /**
     * Gets the non-English singular to plural definitions.
     *
     * @return array
     */
    public static function getPluralDefinitions()
    {
        return self::getArrayBaseValue('irregular_plurals');
    }

    /**
     * Gets the default datetime output format
     *
     * @return array
     */
    public static function getDateTimeFormat()
    {
        return self::getStringBaseValue('datetime_out_format');
    }

    /**
     * Gets the default resources mapper file
     *
     * @return string
     */
    public static function getDefaultMapperFileName()
    {
        return self::getStringBaseValue('default_mapper_file_name');
    }

    /**
     * Check if the resource mapper should be auto managed.
     *
     * @return bool
     */
    public static function autoManageResourceMapper()
    {
        return self::getBoolBaseValue('auto_manage_resource_mapper');
    }

    /**
     * Gets the default placeholders by type
     *
     * @return array
     */
    public static function getPlaceholderByHtmlType()
    {
        return self::getArrayBaseValue('placeholder_by_html_type');
    }

    /**
     * Gets the common name patterns to use for headers.
     *
     * @return array
     */
    public static function getHeadersPatterns()
    {
        return self::getArrayBaseValue('common_header_patterns');
    }

    /**
     * Gets the common key patterns.
     *
     * @return array
     */
    public static function getKeyPatterns()
    {
        return self::getArrayBaseValue('common_key_patterns');
    }

    /**
     * Gets the template names that uses Laravel-Collective
     *
     * @return array
     */
    public static function getCollectiveTemplates()
    {
        return self::getArrayBaseValue('laravel_collective_templates');
    }

    /**
     * Gets the default template name.
     *
     * @return string
     */
    public static function getDefaultTemplateName()
    {
        return self::getStringBaseValue('template');
    }

    /**
     * Gets the eloquent type to method collection.
     *
     * @return array
     */
    public static function dataTypeMap()
    {
        return self::getArrayBaseValue('eloquent_type_to_method');
    }

    /**
     * Get the custom model templates.
     *
     * @return array
     */
    public static function getCustomModelTemplates()
    {
        return self::getArrayBaseValue('generic_view_labels');
    }

    /**
     * Gets the eloquent's method to html
     *
     * @return array
     */
    public static function getEloquentToHtmlMap()
    {
        return self::getArrayBaseValue('eloquent_type_to_html_type');
    }

    /**
     * Gets the default value of the system path
     *
     * @param string $file
     *
     * @return string
     */
    public static function getSystemPath($file = '')
    {
        return self::getPathBaseValue('system_files_path', $file);
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
        return self::getPathBaseValue('form_requests_path', $file);
    }

    /**
     * Gets the path to models
     *
     * @return string
     */
    public static function getModelsPath($file = '')
    {
        return self::getPathBaseValue('models_path', $file);
    }

    /**
     * Gets the path to api-resource
     *
     * @return string
     */
    public static function getApiResourcePath($file = '')
    {
        return self::getPathBaseValue('api_resources_path', $file);
    }

    /**
     * Gets the path to api-resource-collection
     *
     * @return string
     */
    public static function getApiResourceCollectionPath($file = '')
    {
        return self::getPathBaseValue('api_resources_collection_path', $file);
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
        return self::getPathBaseValue('controllers_path', $file);
    }

    /**
     * Gets the path to API based controllers
     *
     * @param string $file
     *
     * @return string
     */
    public static function getApiControllersPath($file = '')
    {
        return self::getPathBaseValue('api_controllers_path', $file);
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
        return self::getPathBaseValue('resource_file_path', $file);
    }

    /**
     * Gets the path to api-docs-controller
     *
     * @param string $file
     *
     * @return string
     */
    public static function getApiDocsControllersPath($file = '')
    {
        return self::getPathBaseValue('api_docs_controller_path', $file);
    }

    /**
     * Gets the default template name.
     *
     * @return array
     */
    public static function getTemplatesPath()
    {
        return self::getPathBaseValue('templates_path');
    }

    /**
     * Gets the path to languages
     *
     * @return string
     */
    public static function getLanguagesPath()
    {
        return self::getPathBaseValue('languages_path');
    }

    /**
     * Gets the path to api-doc views
     *
     * @return string
     */
    public static function getApiDocsViewsPath()
    {
        return self::getPathBaseValue('api_docs_path');
    }

    /**
     * Gets the migrations path.
     *
     * @return string
     */
    public static function getMigrationsPath()
    {
        return self::getPathBaseValue('migrations_path');
    }

    /**
     * Gets the path to views
     *
     * @return string
     */
    public static function getViewsPath()
    {
        $paths = config('view.paths', [0 => 'resources/views']);

        return Helpers::fixPathSeparator(Helpers::getPathWithSlash($paths[0]));
    }

    /**
     * Checks if a given file type should be a plural or not.
     *
     * @return array
     */
    public static function shouldBePlural($key)
    {
        $config = self::getArrayBaseValue('plural_names_for');

        if (isset($config[$key])) {
            return (bool) $config[$key];
        }

        return ($key != 'model-name');
    }

    /**
     * Get the config value of the given index, using the default configs.
     *
     * @param string $index
     * @param mix $default
     *
     * @return mix
     */
    public static function get($index, $default = null)
    {
        $key = self::getKey($index);

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
     * Checks the key to access the default-config.
     *
     * @param string $index
     *
     * @return string
     */
    public static function getKey($index)
    {
        return sprintf('laravel-code-generator.%s', $index);
    }

    /**
     * Get the proper array-based value from the config
     *-
     * @param string $index
     *
     * @return array
     */
    public static function getArrayBaseValue($index)
    {
        $values = (array) self::get($index, []);
        $default = (array) self::getDefaultConfig($index);

        return array_merge($default, $values);
    }

    /**
     * Get the proper bool-based value from the config
     *
     * @param string $index
     *
     * @return bool
     */
    public static function getBoolBaseValue($index)
    {
        if (self::has($index)) {
            return (bool) self::get($index);
        }

        return (bool) self::getDefaultConfig($index);
    }

    /**
     * Get the proper string-based value from the config
     *
     * @param string $index
     *
     * @return string
     */
    public static function getStringBaseValue($index)
    {
        if (self::has($index)) {
            return (string) self::get($index);
        }

        return (string) self::getDefaultConfig($index);
    }

    /**
     * Gets a path base value
     *
     * @param string $file
     *
     * @return string
     */
    public static function getPathBaseValue($index, $file = '')
    {
        $path = self::getStringBaseValue($index);

        return Helpers::fixPathSeparator(Helpers::getPathWithSlash($path)) . $file;
    }

    /**
     * Gets the common definitions.
     *
     * @return array
     */
    public static function getCommonDefinitions()
    {
        $customValues = (array) self::get('common_definitions', []);
        $defaultValues = (array) self::getDefaultConfig('common_definitions');

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

            if (!self::isValidDefinitionArray($custom)) {
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

    /**
     * Checks if the given variable is a valid definition array
     *
     * @param string $custom
     *
     * @return bool
     */
    protected static function isValidDefinitionArray($custom)
    {
        return is_array($custom)
        && array_key_exists('match', $custom)
        && array_key_exists('set', $custom);
    }

    /**
     * Retrieves the default value from the default repository.
     *
     * @param string $index
     *
     * @return Illuminate\Config\Repository
     */
    protected static function getDefaultConfig($index)
    {
        $repository = self::getDefaultRepository();

        if (!$repository->has($index)) {
            throw new Exception('The default configuration does not have definition for "' . $index . '"');
        }

        return $repository->get($index);
    }

    /**
     * Gets the default configuration repository
     *
     * @return Illuminate\Config\Repository
     */
    protected static function getDefaultRepository()
    {
        if (is_null(self::$repository)) {
            $config = include_once __DIR__ . '/../../config/default.php';

            self::$repository = new Repository($config);
        }

        return self::$repository;
    }
}
