<?php

namespace CrestApps\CodeGenerator\Support;

use App;
use File;
use Exception;
use CrestApps\CodeGenerator\Support\FieldTransformer;

class Helpers
{

    /**
     * Evaluates the current version of the framework to see if it >= a giving version.
     *
     * @param $version
     *
     * @return bool
     */
    public static function isNewerThan($version = '5.3')
    {
        return version_compare(App::VERSION(), $version) > 0;
    }


    public static function strReplaceOnce($pattern, $replacment, $subject)
    {
        if (strpos($subject, $pattern) !== false) {
            $occurrence = strpos($subject, $pattern);
            return substr_replace($subject, $replacment, strpos($subject, $pattern), strlen($pattern));
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
     * Checks if a string matches at least one giving pattern
     *
     * @param string|array $patterns
     * @param string $subject
     *
     * @return bool
     */
    public static function strIs($patterns, $subject, &$matchedPattern = '')
    {
        if (!is_array($patterns)) {
            $patterns = (array) $patterns;
        }

        foreach ($patterns as $pattern) {
            if (str_is($pattern, $subject)) {
                $matchedPattern = $pattern;
                return true;
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
        return str_replace(['/','\\'], '.', $string);
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
     * Checks if a giving array is associative
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
     * Converts slash to back slash of a giving string
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
        
        return in_array(strtolower($str), ['true','yes','1','valid','correct']);
    }

    /**
     * Converts a string of field to an array
     *
     * @param $fieldsLine
     *
     * @return array
     */
    public static function getFields($fieldsLine, $langFile = 'generic')
    {
        return FieldTransformer::fromText($fieldsLine, $langFile);
    }

    /**
     * Converts a string of field to an array
     *
     * @param $fieldsLine
     *
     * @return array
     */
    public static function getFieldsFromFile($fileName, $langFile = 'generic')
    {
        $fileFullname = self::getPathWithSlash(config('codegenerator.fields_file_path')) . $fileName;

        if (!File::exists($fileFullname)) {
            throw new Exception('the file ' . $fileFullname . ' was not found!');
        }

        $file = File::get($fileFullname);
        
        return FieldTransformer::fromJson($file, $langFile);
    }

    /**
     * Removes a string from the end of another giving string if it already ends with it.
     *
     * @param  string  $name
     * @param  string  $postFix
     *
     * @return string
     */
    public static function removePostFixWith($name, $postFix = '/')
    {
        if (ends_with($name, $postFix)) {
            return strstr($name, $postFix, true);
        }

        return $name;
    }

    /**
     * Adds a postFix string at the end of another giving string if it does not already ends with it.
     *
     * @param  string  $name
     * @param  string  $postFix
     *
     * @return string
     */
    public static function postFixWith($name, $postFix = '/')
    {
        if (!ends_with($name, $postFix)) {
            return $name . $postFix;
        }

        return $name;
    }

    /**
     * Adds a preFix string at the begining of another giving string if it does not already ends with it.
     *
     * @param  string  $name
     * @param  string  $preFix
     *
     * @return string
     */
    public static function preFixWith($name, $preFix = '/')
    {
        if (!starts_with($name, $preFix)) {
            return $preFix . $name;
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
        return self::postFixWith($path, '/');
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
     * Wrapps each item in an array with a giving string
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
     * Trims a giving string from whitespaces and single/double quotes and square brake.
     *
     * @return string
     */
    public static function trimQuots($str)
    {
        return trim($str, " \t\n\r\0\x0B \"'[]");
    }

    /**
     * It splits a giving string by a giving seperator after trimming each part
     * from whitespaces and single/double quotes. Any empty string is eliminated.
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
