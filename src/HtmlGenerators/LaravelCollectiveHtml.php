<?php

namespace CrestApps\CodeGenerator\HtmlGenerators;

use CrestApps\CodeGenerator\HtmlGenerators\HtmlGeneratorBase;
use CrestApps\CodeGenerator\Models\Label;

class LaravelCollectiveHtml extends HtmlGeneratorBase
{

    /**
     * Gets the min value attribute.
     *
     * @param string $minValue
     *
     * @return string
     */
    protected function getFieldMinValueWithName($minValue)
    {
        return is_null($minValue) ? '' : sprintf(" 'min' => '%s',", $minValue);
    }

    /**
     * Gets the maxValue attribute.
     *
     * @param string $maxValue
     *
     * @return string
     */
    protected function getFieldMaxValueWithName($maxValue)
    {
        return is_null($maxValue) ? '' : sprintf(" 'max' => '%s',", $maxValue);
    }

    /**
     * Get the minLength attribute.
     *
     * @param string $minLength
     *
     * @return string
     */
    protected function getFieldMinLengthName($minLength)
    {
        return empty($minLength) ? '' : sprintf(" 'minlength' => '%s',", $minLength);
    }

    /**
     * Gets the maxLength attribute.
     *
     * @param string $maxLength
     *
     * @return string
     */
    protected function getFieldMaxLengthName($maxLength)
    {
        return empty($maxLength) ? '' : sprintf(" 'maxlength' => '%s',", $maxLength);
    }

    /**
     * Gets the required attribute.
     *
     * @param string $required
     *
     * @return string
     */
    protected function getFieldRequired($required)
    {
        return $required ? sprintf(" 'required' => %s,", ($required ? 'true' : 'false')) : '';
    }

    /**
     * Get the placeholder attribute.
     *
     * @param string $placeholder
     *
     * @return string
     */
    protected function getFieldPlaceHolder($placeholder)
    {
        return empty($placeholder) ? '' : sprintf(" 'placeholder' => '%s',", $placeholder);
    }

    /**
     * Get the placeholder attribute for a menu.
     *
     * @param string $placeholder
     * @param string $name
     *
     * @return string
     */
    protected function getFieldPlaceHolderForMenu($placeholder, $name)
    {
        return $this->getFieldPlaceHolder($placeholder);
    }

    /**
     * Get the multiple attribute.
     *
     * @param bool $isMulti
     *
     * @return string
     */
    protected function getFieldMultiple($isMulti)
    {
        return $isMulti ? "'multiple' => 'multiple'," : '';
    }

    /**
     * It gets converts an array to a stringbase array for the views.
     *
     * @param array $labels
     *
     * @return string
     */
    protected function getFieldItems(array $labels)
    {
        return sprintf('[%s]', implode(', ', $this->getKeyValueStringsFromLabels($labels)));
    }

    /**
     * Gets a plain title from a giving label.
     *
     * @param CrestApps\CodeGenerator\Models\Label $label
     * @param bool $raw
     *
     * @return string
     */
    protected function getPlainTitle(Label $label, $raw = false)
    {
        return sprintf(!$raw ? "'%s'" : "%s", $label->text);
    }

    /**
     * Gets the fields value
     *
     * @param string $stub
     * @param string $fieldValue
     * @param string $name
     *
     * @return $this
     */
    protected function getFieldValue($value, $name)
    {
        if (!is_null($value)) {
            $modelName = strtolower($this->modelName);
            return sprintf(" isset(\$%s) ? \$%s->%s : '%s' ", $modelName, $modelName, $name, $value);
        }

        return 'null';
    }

    /**
     * Gets checked item attribute.
     *
     * @param string $value
     * @param string $name
     *
     * @return string
     */
    protected function getCheckedItem($value, $name)
    {
        return '';
    }

    /**
     * Gets selected value attribute.
     *
     * @param string $name
     *
     * @return string
     */
    protected function getSelectedValue($name)
    {
        return '';
    }

    /**
     * Gets multiple-checked item attribute.
     *
     * @param string $value
     * @param string $name
     *
     * @return string
     */
    protected function getMultipleCheckedItem($value, $name)
    {
        return '';
    }

    /**
     * Gets selected value attribute.
     *
     * @param string $name
     *
     * @return string
     */
    protected function getMultipleSelectedValue($name)
    {
        return '';
    }

    /**
     * Gets the html steps attribute.     
     *
     * @param int value
     *
     * @return string
     */
    protected function getStepsValue($value)
    {
        return $value > 0 ? sprintf(" step => '%s', ", $value) : '';
    }

}
