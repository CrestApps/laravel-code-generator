<?php

namespace CrestApps\CodeGenerator\Support;

use CrestApps\CodeGenerator\Support\Config;

class Str
{
    /**
     * Gets a plural version of a giving word.
     *
     * @param string $word
     *
     * @return string
     */
    public static function plural($word)
    {   
        $definitions = Config::getPluralDefinitions();

        if(array_key_exists($word, $definitions)) {
            return $definitions[$word];
        }

        return str_plural($word);
    }

    /**
     * Gets a singular version of a giving word.
     *
     * @param string $word
     *
     * @return string
     */
    public static function singular($word)
    {
        $definitions = Config::getPluralDefinitions();

        if(($singular = array_search($word, $definitions)) !== false) {
            return $singular;
        }

        return str_singular($word);
    }
}
