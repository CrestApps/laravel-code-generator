<?php

namespace CrestApps\CodeGenerator\Traits;

use CrestApps\CodeGenerator\Support\Helpers;

trait LabelTransformerTrait
{
    /**
     * Gets labels from a giving title and field name.
     *
     * @param string $title
     * @param string $fieldName
     * @param array $languages
     *
     * @return mix (string | array)
     */
    protected function getFieldLabels($title, $fieldName, array $languages = null)
    {
        if (is_array($title)) {
            $title = $this->getFirstElement($title);
        }

        $fieldName = Helpers::removePostFixWith($fieldName, '_id');

        $this->replaceModelName($title, $fieldName, 'field_');

        if (!empty($this->languages)) {
            return $this->makeLabelsForLanguages($title, $languages);
        }

        return $title;
    }

    /**
     * Gets labels from a giving title and field name.
     *
     * @param string $title
     * @param string $modelName
     * @param array $languages
     *
     * @return mix (string | array)
     */
    protected function getModelLabels($title, $modelName, array $languages = null)
    {
        if (is_array($title)) {
            $title = $this->getFirstElement($title);
        }

        $this->replaceModelName($title, $modelName, 'model_');

        if (!empty($languages)) {
            return $this->makeLabelsForLanguages($title, $languages);
        }

        return $title;
    }

    /**
     * Make labels array for giving languages
     *
     * @param string $title
     * @param array $languages
     *
     * @return array
     */
    protected function makeLabelsForLanguages($title, array $languages)
    {
        $labels = [];

        foreach ($languages as $language) {
            $labels[$language] = $title;
        }

        return $labels;
    }

    /**
     * Gets options from a giving array of options
     *
     * @param string $name
     *
     * @return mix (string|array)
     */
    protected function getFirstElement(array $array)
    {
        $value = reset($array);

        if (is_array($value)) {
            return $this->getFirstElement($value);
        }

        return $value;
    }

}
