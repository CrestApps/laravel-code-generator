<?php

namespace CrestApps\CodeGenerator\Support;

use CrestApps\CodeGenerator\Support\Str;
use Illuminate\Support\Arr as LaravelArr;

class Arr extends LaravelArr
{
    /**
     * Checks if a given array is associative
     *
     * @param array $items
     *
     * @return boolean
     */
    public static function isAssociative(array $items)
    {
        return self::isAssoc($items);
    }

    /**
     * Wrapps each item in an array with a given string
     *
     * @param array $items
     * @param string $wrapper
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
     * It splits a given string by a given seperator after trimming each part
     * from whitespaces and single/double quotes. Any empty string is eliminated.
     *
     * @param string $str
     * @param string $seperator
     *
     * @return array
     */
    public static function fromString($str, $seperator = ',', $limit = PHP_INT_MAX)
    {
        return self::removeEmptyItems(explode($seperator, $str, $limit ), function ($param) {
            return Str::trimQuots($param);
        });
    }

    /**
     * Checks if a the given array contains every given parameter.
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
     * Checks an array for the first value that starts with a given pattern
     *
     * @param array $subjects
     * @param string $search
     *
     * @return bool
     */
    public static function isMatch(array $subjects, $search)
    {
        foreach ($subjects as $subject) {
            if (Str::is($search . '*', $subject)) {
                return true;
            }
        }
        return false;
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
}
