<?php

namespace CrestApps\CodeGenerator\HtmlGenerators;

use CrestApps\CodeGenerator\HtmlGenerators\HtmlGeneratorBase;
use CrestApps\CodeGenerator\Models\Label;
use CrestApps\CodeGenerator\Models\Field;
use CrestApps\CodeGenerator\Support\Helpers;

class StandardHtml extends HtmlGeneratorBase
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
        return is_null($minValue) ? '' : sprintf(' min="%s"', $minValue);
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
        return is_null($maxValue) ? '' : sprintf(' max="%s"', $maxValue);
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
        return empty($minLength) ? '' : sprintf(' minlength="%s"', $minLength);
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
        return empty($maxLength) ? '' : sprintf(' maxlength="%s"', $maxLength);
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
        return $required ? sprintf(' required="%s"', ($required ? 'true' : 'false')) : '';
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
        if(is_null($placeholder)) {
            return '';
        }

        return empty($placeholder) ? '' : sprintf(' placeholder="%s"', $this->getTitle($placeholder, true) );
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
        if(is_null($placeholder)) {
            return '';
        }

        return sprintf('<option value="" style="display: none;" {{ %s == \'\' ? \'selected\' : \'\' }} disabled selected>%s</option>', $this->getRawOptionValue($name, ''), $this->getTitle($placeholder, true));
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
        return $isMulti ? 'multiple="multiple"' : '';
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

        $labels = $field->getOptionsByLang() ?: [];

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
        return $label->text;
    }

    /**
     * Gets the fields value
     *
     * @param string $value
     * @param string $name
     *
     * @return string
     */
    protected function getFieldValue($value, $name)
    {
        return sprintf("{{ %s }}", $this->getRawOptionValue($name, $value));
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
        return sprintf(" {{ %s == '%s' ? 'checked' : '' }}", $this->getRawOptionValue($name, ''), $value);
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
        return sprintf(" {{ %s ? 'checked' : '' }}", $this->getMultipleRawOptionValue($name, $value));
    }

    /**
     * Gets selected value attribute.
     *
     * @param string $name
     * @param string $valueAccessor
     *
     * @return string
     */
    protected function getMultipleSelectedValue($name, $valueAccessor)
    {
        return sprintf(" {{ %s ? 'selected' : '' }}", $name);
    }

    /**
     * Gets selected value attribute.
     *
     * @param string $name
     * @param string $valueAccessor
     *
     * @return string
     */
    protected function getSelectedValue($name, $valueAccessor)
    {
        return sprintf(" {{ %s == %s ? 'selected' : '' }}", $this->getRawOptionValue($name, ''), $valueAccessor);
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
     *
     * @return string
     */
    protected function getMultipleRawOptionValue($name, $value)
    {
        $modelVariable = $this->getSingularVariable($this->modelName);
        $valueString = 'null';

        if (!is_null($value)) {
            $valueString = starts_with('$', $value) ? sprintf("%s", $value) : sprintf("'%s'", $value);
        }

        return sprintf("in_array(%s, old('%s', isset(\$%s->%s) ? \$%s->%s : []))", $valueString, $name, $modelVariable, $name, $modelVariable, $name);
    }

    /**
     * Creates html label.
     *
     * @param string $name
     * @param CrestApps\CodeGenerator\Models\Label $label
     *
     * @return string
     */
    protected function getLabelElement($name, Label $label)
    {
        $labelStub = $this->getStubContent('form-label-field.blade', $this->template);

        $this->replaceFieldName($labelStub, $name)
             ->replaceFieldTitle($labelStub, $this->getTitle($label, true));

        return $labelStub;
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
        return ($value) > 0 ? ' step="any"' : '';
    }
}
