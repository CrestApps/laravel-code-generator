<?php

namespace CrestApps\CodeGenerator\Support;

use App;
use CrestApps\CodeGenerator\Support\Config;
use CrestApps\CodeGenerator\Support\Str;
use File;
use Illuminate\Container\Container;

class Helpers
{
    /**
     * Makes a controller name from a given model name
     *
     * @param string $modelName
     *
     * @return string
     */
    public static function makeControllerName($modelName)
    {
        $name = Str::properSnake($modelName, 'controller-name');
        $case = ucfirst(camel_case($name));

        if (!empty($postfix = Config::getControllerNamePostFix())) {
            return Str::postfix($case, $postfix);
        }

        return $case;
    }

    /**
     * Makes an api-resource name from a given model name
     *
     * @param string $modelName
     *
     * @return string
     */
    public static function makeApiResourceName($modelName)
    {
        $name = Str::properSnake($modelName, 'api-resource-name');
        $case = ucfirst(camel_case($name));

        if (!empty($postfix = Config::getApiResourceNamePostFix())) {
            return Str::postfix($case, $postfix);
        }

        return $case;
    }

    /**
     * Makes an api-resource-collection name from a given model name
     *
     * @param string $modelName
     *
     * @return string
     */
    public static function makeApiResourceCollectionName($modelName)
    {
        $name = Str::properSnake($modelName, 'api-resource-collection-name');
        $case = ucfirst(camel_case($name));

        if (!empty($postfix = Config::getApiResourceCollectionNamePostFix())) {
            return Str::postfix($case, $postfix);
        }

        return $case;
    }

    /**
     * Convert the slash and backslashes to the current system directory seperator.
     *
     * @param string $path
     *
     * @return string
     */
    public static function fixPathSeparator($path)
    {
        return str_replace(["\\", "/"], DIRECTORY_SEPARATOR, $path);
    }

    /**
     * Gets the app folder name,
     *
     * @return string
     */
    public static function getAppName()
    {
        return rtrim(self::getAppNamespace(), '\\');
    }

    /**
     * Fixes a path to a namespace
     *
     * @return string
     */
    public static function fixNamespace($path)
    {
        return rtrim(self::convertSlashToBackslash($path), '\\');
    }

    /**
     * Gets the app namespace afer concatenating any given paths to it
     *
     * @param mix $paths
     *
     * @return string
     */
    public static function getAppNamespace(...$paths)
    {
        $base = Container::getInstance()->getNamespace();
        foreach ($paths as $path) {
            if (!empty($path)) {
                $base .= Str::postfix($path, '\\');
            }
        }

        return Helpers::convertSlashToBackslash($base);
    }

    /**
     * Converts array to a pretty JSON string.
     *
     * @param array $object
     *
     * @return string
     */
    public static function prettifyJson(array $object)
    {
        return json_encode($object, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Gets a label from a given name
     *
     * @param string $name
     *
     * @return string
     */
    public static function convertNameToLabel($name)
    {
        $title = ucwords(str_replace('_', ' ', $name));

        return Str::trimEnd($title, ' Id');
    }

    /**
     * Makes a form request class name of the givin model's name
     *
     * @param string $modelName
     *
     * @return string
     */
    public static function makeFormRequestName($modelName)
    {
        $name = Str::properSnake($modelName, 'request-form-name');
        $case = ucfirst(camel_case($name));

        if (!empty($postFix = Config::getFormRequestNamePostFix())) {
            return str_finish($case, $postFix);
        }

        return $case;
    }

    /**
     * Makes the table name from the given model name.
     *
     * @param  string  $modelName
     *
     * @return string
     */
    public static function makeTableName($modelName)
    {
        return Str::properSnake($modelName, 'table-name');
    }

    /**
     * Makes the route group from the given model name.
     *
     * @param  string  $modelName
     *
     * @return string
     */
    public static function makeRouteGroup($modelName)
    {
        return Str::properSnake($modelName, 'route-group');
    }

    /**
     * Makes the json file name
     *
     * @param string $modelName
     *
     * @return string
     */
    public static function makeJsonFileName($modelName)
    {
        $snake = Str::properSnake($modelName, 'resource-file-name');

        return str_finish($snake, '.json');
    }

    /**
     * Check if the current laravel version has api-support
     *
     * @return bool
     */
    public static function isApiResourceSupported()
    {
        return Helpers::isNewerThanOrEqualTo('5.5');
    }

    /**
     * Evaluates the current version of the framework to see if it >= a given version.
     *
     * @param $version
     *
     * @return bool
     */
    public static function isNewerThanOrEqualTo($version = '5.3')
    {
        return version_compare(App::VERSION(), $version) >= 0;
    }

    /**
     * Evaluates the current version of the framework to see if it < a given version.
     *
     * @param $version
     *
     * @return bool
     */
    public static function isOlderThan($version)
    {
        return version_compare(App::VERSION(), $version) < 0;
    }

    /**
     * Converts a string to a dot notation format
     *
     * @param string $string
     * @return string
     */
    public static function convertToDotNotation($string)
    {
        return str_replace(['/', '\\'], '.', $string);
    }

    /**
     * Converts slash to back slash of a given string
     *
     * @return string
     */
    public static function convertSlashToBackslash($path)
    {
        return str_replace('/', '\\', $path);
    }

    /**
     * It adds a slash to the end of a string if it does not exists
     *
     * @param string $path
     *
     * @return string
     */
    public static function getPathWithSlash($path)
    {
        if (empty($path)) {
            return '';
        }

        return Str::postfix($path, DIRECTORY_SEPARATOR);
    }

    /**
     * It adds a dot to the end of a string if it does not exists
     *
     * @param string $string
     *
     * @return string
     */
    public static function getWithDotPostFix($string)
    {
        return Str::postfix($string, '.');
    }
}
