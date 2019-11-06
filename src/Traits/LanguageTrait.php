<?php

namespace CrestApps\CodeGenerator\Traits;

use CrestApps\CodeGenerator\Support\Str;

trait LanguageTrait
{
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
     * Makes the locale groups name
     *
     * @param string $modelName
     *
     * @return string
     */
    public static function makeLocaleGroup($modelName)
    {
        return Str::properSnake($modelName, 'language-filename');
    }
}
