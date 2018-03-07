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
        $name = self::getProperCaseFor($modelName, 'controller-name');
        $case = ucfirst(camel_case($name));

        if (!empty($postFix = Config::getControllerNamePostFix())) {
            return str_finish($case, $postFix);
        }

        return $case;
    }

    /**
     * Eliminate a duplicate given phrase from a given string
     *
     * @param string $subject
     * @param string $eliminate
     *
     * @return string
     */
    public static function eliminateDupilcates($subject, $eliminate = "\\")
    {
        $pattern = $eliminate . $eliminate;

        while (strpos($subject, $pattern) !== false) {
            $subject = str_replace($pattern, $eliminate, $subject);
        }

        return $subject;
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
     * Gets the model full path.
     *
     * @return string
     */
    public static function getModelsPath()
    {
        return self::getAppNamespace() . Config::getModelsPath();
    }

    /**
     * Gets the app namespace.
     *
     * @return string
     */
    public static function getAppNamespace()
    {
        return Container::getInstance()->getNamespace();
    }

    /**
     * Checks an array for the first value that starts with a given pattern
     *
     * @param array $subjects
     * @param string $search
     *
     * @return bool
     */
    public static function inArraySearch(array $subjects, $search)
    {
        foreach ($subjects as $subject) {
            if (str_is($search . '*', $subject)) {
                return true;
            }
        }

        return false;
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

        return self::removePostFixWith($title, ' Id');
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
        $name = self::getProperCaseFor($modelName, 'request-form-name');
        $case = ucfirst(camel_case($name));

        if (!empty($postFix = Config::getFormRequestNamePostFix())) {
            return str_finish($case, $postFix);
        }

        return $case;
    }

    /**
     * Creates a colection of messages out of a given fields collection.
     *
     * @param array $fields
     *
     * @return array
     */
    public static function getLanguageItems(array $fields)
    {
        $items = [];

        foreach ($fields as $field) {
            foreach ($field->getLabels() as $label) {
                if (!$label->isPlain) {
                    $items[$label->lang][] = $label;
                }
            }

            foreach ($field->getPlaceholders() as $label) {
                if (!$label->isPlain) {
                    $items[$label->lang][] = $label;
                }
            }

            foreach ($field->getOptions() as $lang => $labels) {
                foreach ($labels as $label) {
                    if (!$label->isPlain) {
                        $items[$label->lang][] = $label;
                    }
                }
            }
        }

        return $items;
    }

    /**
     * Guesses the model full name using the given field's name
     *
     * @param string $name
     * @param string $modelsPath
     *
     * @return string
     */
    public static function guessModelFullName($name, $modelsPath)
    {
        $model = $modelsPath . ucfirst(self::extractModelName($name));

        return self::convertSlashToBackslash($model);
    }

    /**
     * Extracts the model name from the given field's name.
     *
     * @param string $name
     *
     * @return string
     */
    public static function extractModelName($name)
    {
        $name = self::removePostFixWith($name, '_id');

        return ucfirst(studly_case(Str::singular($name)));
    }

    /**
     * Checks if a key exists in a given array
     *
     * @param array $properties
     * @param string $name
     *
     * @return bool
     */
    public static function isKeyExists(array $properties, ...$name)
    {
        $args = func_get_args();

        for ($i = 1; $i < count($args); $i++) {
            if (!array_key_exists($args[$i], $properties)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Makes the locale groups name
     *
     * @param string $modelName
     *
     * @return string
     */
    public static function makeLocaleGroup($modelName)
    {
        return self::getProperCaseFor($modelName, 'language-filename');
    }

    /**
     * Makes the proper english case given a model name and a file type
     *
     * @param string $modelName
     * @param string $key
     *
     * @return string
     */
    public static function getProperCaseFor($modelName, $key = null)
    {
        $snake = snake_case($modelName);

        if (Config::shouldBePlural($key)) {
            return Str::plural($snake);
        }

        return $snake;
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
        return self::getProperCaseFor($modelName, 'table-name');
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
        return self::getProperCaseFor($modelName, 'route-group');
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
        $snake = self::getProperCaseFor($modelName, 'resource-file-name');

        return str_finish($snake, '.json');
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
     * Replaces found pattern in a subject only one time.
     *
     * @param string $pattern
     * @param string $replacment
     * @param string $subject
     *
     * @return string
     */
    public static function strReplaceOnce($pattern, $replacment, $subject)
    {
        $index = strpos($subject, $pattern);
        if ($index !== false) {
            return substr_replace($subject, $replacment, $index, strlen($pattern));
        }

        return $subject;
    }

    /**
     * It trims each element in a givin array and removes the empty elements.
     * If a callback is passed as a second parameter, the callbacl is applied on each item.
     *
     * @param array $items
     * @param function $callback
     *
     * @return $array
     */
    public static function removeEmptyItems(array $items, $callback = null)
    {
        $final = [];

        foreach ($items as $item) {
            $item = trim($item);

            $item = !is_null($callback) && is_callable($callback) ? call_user_func($callback, $item) : $item;

            if (!empty($item)) {
                $final[] = $item;
            }
        }

        return $final;
    }

    /**
     * Checks if a string matches at least one given pattern
     *
     * @param string|array $patterns
     * @param string $subject
     * @param string $matchedPattern
     * @param bool $caseSensitive
     *
     * @return bool
     */
    public static function strIs($patterns, $subject, &$matchedPattern = '', $caseSensitive = false)
    {
        if (!is_array($patterns)) {
            $patterns = (array) $patterns;
        }

        $lowerSubject = strtolower($subject);

        foreach ($patterns as $pattern) {

            if ($caseSensitive) {
                if (str_is($pattern, $subject)) {
                    $matchedPattern = $pattern;
                    return true;
                }
            } else {
                $lowerPattern = strtolower($pattern);
                if (str_is($lowerPattern, $lowerSubject)) {
                    $matchedPattern = $pattern;
                    return true;
                }
            }

        }

        return false;
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
     * Replaces any non-english letters with an empty string
     *
     * @param string $str
     * @param bool $keep
     *
     * @return string
     */
    public static function removeNonEnglishChars($str, $keep = '')
    {
        $pattern = sprintf('A-Za-z0-9_%s', $keep);

        return preg_replace("/[^" . $pattern . "]/", '', $str);
    }

    /**
     * Checks if a given array is associative
     *
     * @param array $items
     *
     * @return boolean
     */
    public static function isAssociative(array $items)
    {
        return array() === $items ? false : array_keys($items) !== range(0, count($items) - 1);
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
     * Check a string for a positive keyword
     *
     * @param string $str
     *
     * @return array
     */
    public static function stringToBool($str)
    {
        if (is_bool($str)) {
            return $str;
        }

        return in_array(strtolower($str), ['true', 'yes', '1', 'valid', 'correct']);
    }

    /**
     * Removes a string from the end of another given string if it already ends with it.
     *
     * @param  string  $name
     * @param  string  $fix
     *
     * @return string
     */
    public static function removePostFixWith($name, $fix = '/')
    {
        $position = strripos($name, $fix);

        if ($position !== false) {
            return substr($name, 0, $position);
        }

        return $name;
    }

    /**
     * Adds a postFix string at the end of another given string if it does not already ends with it.
     *
     * @param  string  $name
     * @param  string  $fix
     *
     * @return string
     */
    public static function postFixWith($name, $fix = '/')
    {
        if (!ends_with($name, $fix)) {
            return $name . $fix;
        }

        return $name;
    }

    /**
     * Adds a preFix string at the begining of another given string if it does not already ends with it.
     *
     * @param  string  $name
     * @param  string  $fix
     *
     * @return string
     */
    public static function preFixWith($name, $fix = '/')
    {
        if (!starts_with($name, $fix)) {
            return $fix . $name;
        }

        return $name;
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
        return self::postFixWith($path, DIRECTORY_SEPARATOR);
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
        return self::postFixWith($string, '.');
    }

    /**
     * Turns every word in a path to uppercase
     *
     * @return string
     */
    public static function upperCaseEveyWord($sentence, $delimiter = '\\')
    {
        $words = explode($delimiter, $sentence);

        return implode($delimiter, array_map('ucfirst', $words));
    }

    /**
     * Wrapps each item in an array with a given string
     *
     * @return array
     */
    public static function wrapItems(array $items, $wrapper = "'")
    {
        return array_map(function ($item) use ($wrapper) {
            $item = str_replace($wrapper, '\\' . $wrapper, trim($item, $wrapper));

            return sprintf('%s%s%s', $wrapper, $item, $wrapper);
        }, $items);
    }

    /**
     * Trims a given string from whitespaces and single/double quotes and square brake.
     *
     * @return string
     */
    public static function trimQuots($str)
    {
        return trim($str, " \t\n\r\0\x0B \"'[]");
    }

    /**
     * It splits a given string by a given seperator after trimming each part
     * from whitespaces and single/double quotes. Any empty string is eliminated.
     *
     * @param string $str
     * @param string $seperator
     *
     * @return array
     */
    public static function convertStringToArray($str, $seperator = ',')
    {
        return self::removeEmptyItems(explode($seperator, $str), function ($param) {
            return self::trimQuots($param);
        });
    }
}
