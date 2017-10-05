<?php

namespace CrestApps\CodeGenerator\HtmlGenerators;

use CrestApps\CodeGenerator\HtmlGenerators\HtmlGeneratorBase;
use CrestApps\CodeGenerator\Models\Field;
use CrestApps\CodeGenerator\Models\Label;
use CrestApps\CodeGenerator\Support\Helpers;
use CrestApps\CodeGenerator\Support\ViewLabelsGenerator;

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
     * @param CrestApps\CodeGenerator\Models\Label $placeholder
     *
     * @return string
     */
    protected function getFieldPlaceHolder(Label $placeholder = null)
    {
        return is_null($placeholder) ? '' : sprintf(" 'placeholder' => %s,", $this->getTitle($placeholder));
    }

    /**
     * Get the placeholder attribute for a menu.
     *
     * @param CrestApps\CodeGenerator\Models\Label $placeholder
     * @param string $name
     *
     * @return string
     */
    protected function getFieldPlaceHolderForMenu(Label $placeholder = null, $name = '')
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
     * @param CrestApps\CodeGenerator\Models\Field $field
     *
     * @return string
     */
    protected function getFieldItems(Field $field)
    {
        if ($field->hasForeignRelation() && $field->isOnFormView) {
            return sprintf('$%s', $field->getForeignRelation()->getCollectionName());
        }

        $labels = $field->getOptionsByLang();

        return sprintf('[%s]', implode(',' . PHP_EOL, $this->getKeyValueStringsFromLabels($labels)));
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
            $modelVariable = $this->getSingularVariable($this->modelName);

            return sprintf("(!isset(\$%s->%s) ? '%s' : null)", $modelVariable, $name, $value);
        }

        return 'null';
    }

    /**
     * Gets checked item attribute.
     *
     * @param string $value
     * @param string $name
     * @param string $defaultValue
     *
     * @return string
     */
    protected function getCheckedItem($value, $name, $defaultValue)
    {
        return sprintf(
            " (%s == '%s' ? true : null) ",
            $this->getRawOptionValue($name, $defaultValue),
            $value
        );
    }

    /**
     * Gets selected value attribute.
     *
     * @param string $name
     * @param string $valueAccessor
     * @param string $defaultValue
     *
     * @return string
     */
    protected function getSelectedValue($name, $valueAccessor, $defaultValue)
    {
        return sprintf(" (%s == %s ? true : null) ", $this->getRawOptionValue($name, $defaultValue), $valueAccessor);
    }

    /**
     * Gets multiple-checked item attribute.
     *
     * @param string $value
     * @param string $name
     * @param string $defaultValue
     *
     * @return string
     */
    protected function getMultipleCheckedItem($value, $name, $defaultValue)
    {
        return sprintf(" (%s ? true : null) ", $this->getMultipleRawOptionValue($name, $value, $defaultValue));
    }

    /**
     * Gets a raw value for a giving field's name.
     *
     * @param string $name
     * @param string $value
     *
     * @return string
     */
    protected function getRawOptionValue($name, $value)
    {
        $modelVariable = $this->getSingularVariable($this->modelName);

        $valueString = is_null($value) ? 'null' : sprintf("'%s'", $value);

        return sprintf("old('%s', isset(\$%s->%s) ? \$%s->%s : %s)", $name, $modelVariable, $name, $modelVariable, $name, $valueString);
    }

    /**
     * Gets a raw value for a giving field's name.
     *
     * @param string $name
     * @param string $value
     * @param string $defaultValue
     *
     * @return string
     */
    protected function getMultipleRawOptionValue($name, $value, $defaultValue)
    {
        $modelVariable = $this->getSingularVariable($this->modelName);
        $valueString = 'null';

        if (!is_null($value)) {
            $valueString = starts_with('$', $value) ? sprintf("%s", $value) : sprintf("'%s'", $value);
        }

        $defaultValueString = '[]';

        if (!empty($defaultValue)) {
            $joinedValues = implode(',', Helpers::wrapItems((array) $defaultValue));
            $defaultValueString = sprintf('[%s]', $joinedValues);
        }

        return sprintf("in_array(%s, old('%s', isset(\$%s->%s) ? \$%s->%s : %s))", $valueString, $name, $modelVariable, $name, $modelVariable, $name, $defaultValueString);
    }

    /**
     * Gets selected value attribute.
     *
     * @param string $name
     * @param string $valueAccessor
     * @param string $defaultValue
     *
     * @return string
     */
    protected function getMultipleSelectedValue($name, $valueAccessor, $defaultValue)
    {
        return sprintf(" (%s ? true : null) ", $name);
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
        return ($value) > 0 ? "'step' => \"any\"," : '';
    }

    /**
     * Gets an instance of ViewLabelsGenerator
     *
     * @return CrestApps\CodeGenerator\Support\ViewLabelsGenerator
     */
    protected function getViewLabelsGenerator()
    {
        return new ViewLabelsGenerator($this->modelName, $this->fields, false);
    }
}
