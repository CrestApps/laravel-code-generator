<?php

namespace CrestApps\CodeGenerator\Support;

use CrestApps\CodeGenerator\Support\Config;
use Illuminate\Support\Str as LaravelStr;

class Str
{
    /**
     * Gets a plural version of a given word.
     *
     * @param string $word
     *
     * @return string
     */
    public static function plural($word)
    {
        $definitions = Config::getPluralDefinitions();

        if (array_key_exists($word, $definitions)) {
            return $definitions[$word];
        }

        return LaravelStr::plural($word);
    }

    /**
     * Gets a singular version of a given word.
     *
     * @param string $word
     *
     * @return string
     */
    public static function singular($word)
    {
        $definitions = Config::getPluralDefinitions();

        if (($singular = array_search($word, $definitions)) !== false) {
            return $singular;
        }

        return LaravelStr:: singular($word);
    }

    /**
     * Converts the give value into a title case.
     *
     * @param string $value
     *
     * @return string
     */
    public static function kebabCase($value)
    {
        return LaravelStr::snake($value, '-');
    }

    /**
     * Converts the give value into a title case.
     *
     * @param string $value
     *
     * @return string
     */
    public static function titleCase($value)
    {
        return LaravelStr::title($value);
    }
}
