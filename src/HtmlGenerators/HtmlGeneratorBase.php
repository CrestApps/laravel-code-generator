<?php

namespace CrestApps\CodeGenerator\HtmlGenerators;

use CrestApps\CodeGenerator\Traits\CommonCommand;
use CrestApps\CodeGenerator\Models\Field;
use CrestApps\CodeGenerator\Models\Label;
use CrestApps\CodeGenerator\Support\Helpers;
use CrestApps\CodeGenerator\Models\ViewInput;
use CrestApps\CodeGenerator\Support\ValidationParser;

abstract class HtmlGeneratorBase
{
    use CommonCommand;

    /**
     * Array of fields.
     *
     * @var array
    */
    protected $fields = [];

    /**
     * Model name.
     *
     * @var string
    */
    protected $modelName;

    /**
     * Template name.
     *
     * @var string
    */
    protected $template;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(array $fields, $modelName, $template = null)
    {
        $this->modelName = $modelName;
        $this->fields = $fields;
        $this->template = $template;
    }

    /**
     * Gets html field for the current set fields.
     *
     * @return string
    */
    public function getHtmlFields()
    {
        $htmlFields = '';

        foreach ($this->fields as $field) {
            if (!$field->isOnFormView) {
                continue;
            }

            $parser = new ValidationParser($field->validationRules);
            
            if (in_array($field->htmlType, ['select','multipleSelect'])) {
                $htmlFields .= $this->getSelectHtmlField($field, $parser);
            } elseif (in_array($field->htmlType, ['radio','checkbox'])) {
                $htmlFields .= $this->getPickItemsHtmlField($field, $parser);
            } elseif ($field->htmlType == 'textarea') {
                $htmlFields .= $this->getTextareaHtmlField($field, $parser);
            } elseif ($field->htmlType == 'password') {
                $htmlFields .= $this->getPasswordHtmlField($field, $parser);
            } elseif ($field->htmlType == 'file') {
                $htmlFields .= $this->getFileHtmlField($field);
            } elseif ($field->htmlType == 'selectRange') {
                $htmlFields .= $this->getSelectRangeHtmlField($field);
            } elseif ($field->htmlType == 'selectMonth') {
                $htmlFields .= $this->getSelectMontheHtmlField($field);
            } else {
                $htmlFields .= $this->getStandardHtmlField($field, $parser);
            }
        }

        return $htmlFields;
    }

    /**
     * Gets html code for the show view using the current fields colelction.
     * If an value is passed, it will only generate the raws based on the giving fields
     *
     * @param array $fields
     *
     * @return string
    */
    public function getShowRowsHtmlField(array $fields = null)
    {
        $stub = $this->getStubContent('show.row.blade', $this->template);
        $fields = $this->getFieldsToDisplay($fields);
        $rows = '';

        foreach ($fields as $field) {
            if ($field->isOnShowView) {
                $row = $stub;
                $this->replaceFieldName($row, $field->name)
                     ->replaceModelName($row, $this->modelName)
                     ->replaceRowFieldValue($row, $this->getFieldAccessorValue($field))
                     ->replaceFieldTitle($row, $this->getTitle($field->getLabel(), true));

                $rows .= $row . PHP_EOL;
            }
        }

        return $rows;
    }

    /**
     * Gets header cells' html code for the index view using the current fields colelction.
     * If an value is passed, it will only generate the raws based on the giving fields
     *
     * @param array $fields
     *
     * @return string
    */
    public function getIndexHeaderCells(array $fields = null)
    {
        $stub = $this->getStubContent('index.header.cell.blade', $this->template);
        $fields = $this->getFieldsToDisplay($fields);
        $rows = '';

        foreach ($fields as $field) {
            if ($field->isOnIndexView) {
                $row = $stub;
                $this->replaceFieldTitle($row, $this->getTitle($field->getLabel(), true));
                $rows .= $row . PHP_EOL;
            }
        }

        return $rows;
    }

    /**
     * Gets header body's html code for the index view using the current fields colelction.
     * If an value is passed, it will only generate the raws based on the giving fields
     *
     * @param array $fields
     *
     * @return string
    */
    public function getIndexBodyCells(array $fields = null)
    {
        $stub = $this->getStubContent('index.body.cell.blade', $this->template);
        $fields = $this->getFieldsToDisplay($fields);
        $rows = '';

        foreach ($fields as $field) {
            if ($field->isOnIndexView) {
                $row = $stub;

                $this->replaceFieldName($row, $field->name)
                     ->replaceModelName($row, $this->modelName)
                     ->replaceRowFieldValue($row, $this->getFieldAccessorValue($field))
                     ->replaceFieldTitle($row, $this->getTitle($field->getLabel(), true));

                $rows .= $row . PHP_EOL;
            }
        }

        return $rows;
    }

    /**
     * Replace the selectedValue fo the given stub.
     *
     * @param string $stub
     * @param string $value
     *
     * @return $this
     */
    protected function replaceSelectedValue(& $stub, $value)
    {
        $stub = str_replace('{{selectedValue}}', $value, $stub);

        return $this;
    }

    /**
     * Replace the checkedItem fo the given stub.
     *
     * @param string $stub
     * @param string $value
     *
     * @return $this
     */
    protected function replaceCheckedItem(& $stub, $value)
    {
        $stub = str_replace('{{checkedItem}}', $value, $stub);

        return $this;
    }

    /**
     * Replace the fieldValue fo the given stub.
     *
     * @param string $stub
     * @param string $value
     *
     * @return $this
     */
    protected function replaceRowFieldValue(& $stub, $value)
    {
        $stub = str_replace('{{fieldValue}}', $value, $stub);

        return $this;
    }

    /**
     * Gets a value accessor for the field.
     *
     * @param CrestApps\CodeGenerator\Models\Field $field
     *
     * @return string
    */
    protected function getFieldAccessorValue(Field $field)
    {
        $fieldAccessor = sprintf('$%s->%s', strtolower($this->modelName), $field->name);

        return $field->isMultipleAnswers ? sprintf("implode('%s', %s)", $field->optionsDelimiter, $fieldAccessor) : $fieldAccessor;
    }

    /**
     * It find the union field between a giving collection and the current field collection.
     * If null is provided, the current field collection is returned.
     *
     * @param array $fields
     *
     * @return string
    */
    protected function getFieldsToDisplay(array $fields = null)
    {
        if (!empty($fields)) {
            return array_filter($this->fields, function ($field) use ($fields) {
                return in_array($field->name, $fields);
            });
        }

        return $this->fields;
    }

    /**
     * Gets creates an textarea html field for a giving field.
     *
     * @param CrestApps\CodeGeneraotor\Support\Field $field
     * @param CrestApps\CodeGeneraotor\Support\ValidationParser $parser
     *
     * @return string
    */
    protected function getTextareaHtmlField(Field $field, ValidationParser $parser)
    {
        $stub = $this->getStubContent('form-textarea-field.blade', $this->template);

        $minValue = $this->getMax($parser->getMinValue(), $field->getMinValue());
        $maxValue = $this->getMin($parser->getMaxValue(), $field->getMaxValue());

        $this->replaceFieldName($stub, $field->name)
             ->replaceFieldValue($stub, $this->getFieldValue($field->htmlValue, $field->name))
             ->replaceFieldMinValue($stub, $this->getFieldMinValueWithName($minValue))
             ->replaceFieldMaxValue($stub, $this->getFieldMaxValueWithName($maxValue))
             ->replaceFieldMinLengthName($stub, $parser->getMinLength())
             ->replaceFieldMaxLengthName($stub, $parser->getMaxLength())
             ->replaceFieldRequired($stub, $parser->isRequired())
             ->replaceFieldPlaceHolder($stub, $this->getFieldPlaceHolder($field->placeHolder))
             ->wrapField($stub, $field);

        return $stub;
    }
    
    protected function getHtmlMinValue($validationMinValue, $fieldMinValue)
    {
        if (! is_null($validationMinValue)) {
            return $validationMinValue;
        }

        return $fieldMinValue;
    }

    protected function getHtmlMaxValue($validationMaxValue, $fieldMaxValue)
    {
        if (! is_null($validationMaxValue)) {
            return $validationMaxValue;
        }

        return $fieldMaxValue;
    }

    /**
     * Gets creates an checkbox/radio button html field for a giving field.
     *
     * @param CrestApps\CodeGeneraotor\Support\Field $field
     * @param CrestApps\CodeGeneraotor\Support\ValidationParser $parser
     *
     * @return string
    */
    protected function getPickItemsHtmlField(Field $field, ValidationParser $parser)
    {
        $stub = $this->getStubContent(sprintf('form-pickitems%s-field.blade', $field->isInlineOptions ? '-inline' : ''), $this->template);
        $fields = '';
        $fieldName = ($field->isMultipleAnswers) ? $this->getFieldNameAsArray($field->name) : $field->name;
        $isCheckbox = ($field->htmlType == 'checkbox');

        foreach ($field->getOptionsByLang() as $option) {
            $fieldStub = $stub;
            $this->replaceFieldType($fieldStub, $field->htmlType)
                 ->replaceFieldName($fieldStub, $fieldName)
                 ->replaceOptionValue($fieldStub, $option->value)
                 ->replaceCheckedItem($fieldStub, $this->getCheckedItemForPickItem($option->value, $field->name, $field->isMultipleAnswers))
                 ->replaceItemId($fieldStub, $option->id)
                 ->replaceFieldRequired($fieldStub, $isCheckbox ? false : $parser->isRequired())
                 ->replaceItemLabel($fieldStub, $this->getTitle($option, true));

            $fields .= $fieldStub . PHP_EOL;
        }

        $this->wrapField($fields, $field, true, $isCheckbox ? 'required' : '');

        return $fields;
    }

    /**
     * Gets field name ending with square brakets
     *
     * @param string $name
     *
     * @return string
    */
    protected function getFieldNameAsArray($name)
    {
        return sprintf('%s[]', $name);
    }

    /**
     * Gets creates an select menu html field for a giving field.
     *
     * @param CrestApps\CodeGeneraotor\Support\Field $field
     * @param CrestApps\CodeGeneraotor\Support\ValidationParser $parser
     *
     * @return string
    */
    protected function getSelectHtmlField(Field $field, ValidationParser $parser)
    {
        $stub = $this->getStubContent('form-selectmenu-field.blade', $this->template);

        $fieldName = ($field->isMultipleAnswers) ? $this->getFieldNameAsArray($field->name) : $field->name;
        $optionValue = $this->getFieldValue($field->htmlValue, $field->name);

        $this->replaceFieldName($stub, $fieldName)
             ->replaceFieldItems($stub, $this->getFieldItems($field->getOptionsByLang()))
             ->replaceFieldMultiple($stub, $field->isMultipleAnswers)
             ->replaceFieldValue($stub, $optionValue)
             ->replaceSelectedValue($stub, $this->getSelectedValueForMenu($optionValue, $field->name, $field->isMultipleAnswers))
             ->replaceFieldRequired($stub, $parser->isRequired())
             ->replaceFieldPlaceHolder($stub, $this->getFieldPlaceHolderForMenu($field->placeHolder, $field->name))
             ->wrapField($stub, $field);

        return $stub;
    }

    protected function getCheckedItemForPickItem($value, $name, $isMultiple)
    {
        return $isMultiple ? $this->getMultipleCheckedItem($value, $name) : $this->getCheckedItem($value, $name);
    }

    protected function getSelectedValueForMenu($value, $name, $isMultiple)
    {
        return $isMultiple ? $this->getMultipleSelectedValue($name) : $this->getSelectedValue($name);
    }

    /**
     * Gets creates an password html5 field for a giving field.
     *
     * @param CrestApps\CodeGeneraotor\Support\Field $field
     *
     * @return string
    */
    public function getFileHtmlField(Field $field)
    {
        $stub = $this->getStubContent('form-file-field.blade', $this->template);

        $this->replaceFieldName($stub, $field->name)
              ->wrapField($stub, $field);
        
        return $stub;
    }

    /**
     * Gets a rangeselector element for a giving field.
     *
     * @param CrestApps\CodeGeneraotor\Support\Field $field
     *
     * @return string
    */
    public function getSelectRangeHtmlField(Field $field)
    {
        $stub = $this->getStubContent('form-selectrange-field.blade', $this->template);

        $this->replaceFieldName($stub, $field->name)
             ->replaceFieldMinValue($stub, isset($field->range[0]) ? $field->range[0] : 1)
             ->replaceFieldMaxValue($stub, isset($field->range[1]) ? $field->range[1] : 10)
             ->replaceSelectedValue($stub, $this->getSelectedValueForMenu('', $field->name, $field->isMultipleAnswers))
             ->replaceFieldPlaceHolder($stub, $this->getFieldPlaceHolderForMenu($field->placeHolder, $field->name))
             ->wrapField($stub, $field);
        
        return $stub;
    }

    /**
     * Gets a selectmonth element for a giving field.
     *
     * @param CrestApps\CodeGeneraotor\Support\Field $field
     *
     * @return string
    */
    public function getSelectMontheHtmlField(Field $field)
    {
        $stub = $this->getStubContent('form-month-field.blade', $this->template);

        $this->replaceFieldName($stub, $field->name)
             ->replaceSelectedValue($stub, $this->getSelectedValue($field->name))
             ->replaceFieldPlaceHolder($stub, $this->getFieldPlaceHolderForMenu($field->placeHolder, $field->name))
             ->wrapField($stub, $field);
        
        return $stub;
    }

    /**
     * Gets creates an password html5 field for a giving field.
     *
     * @param CrestApps\CodeGeneraotor\Support\Field $field
     * @param CrestApps\CodeGeneraotor\Support\ValidationParser $parser
     *
     * @return string
    */
    public function getPasswordHtmlField(Field $field, ValidationParser $parser)
    {
        $stub = $this->getStubContent('form-password-field.blade', $this->template);
        $minValue = $this->getMax($parser->getMinValue(), $field->getMinValue());
        $maxValue = $this->getMin($parser->getMaxValue(), $field->getMaxValue());

        $this->replaceFieldName($stub, $field->name)
             ->replaceFieldType($stub, $field->htmlType)
             ->replaceFieldValue($stub, $this->getFieldValue($field->htmlValue, $field->name))
             ->replaceFieldMinValue($stub, $this->getFieldMinValueWithName($minValue))
             ->replaceFieldMaxValue($stub, $this->getFieldMaxValueWithName($maxValue))
             ->replaceFieldMinLengthName($stub, $parser->getMinLength())
             ->replaceFieldMaxLengthName($stub, $parser->getMaxLength())
             ->replaceFieldRequired($stub, $parser->isRequired())
             ->replaceFieldPlaceHolder($stub, $this->getFieldPlaceHolder($field->placeHolder))
             ->wrapField($stub, $field);
        
        return $stub;
    }

    /**
     * Gets creates an standard html5 field for a giving field.
     *
     * @param CrestApps\CodeGeneraotor\Support\Field $field
     * @param CrestApps\CodeGeneraotor\Support\ValidationParser $parser
     *
     * @return string
    */
    public function getStandardHtmlField(Field $field, ValidationParser $parser)
    {
        $stub = $this->getStubContent('form-input-field.blade', $this->template);

        $minValue = $this->getMax($parser->getMinValue(), $field->getMinValue());
        $maxValue = $this->getMin($parser->getMaxValue(), $field->getMaxValue());

        $this->replaceFieldName($stub, $field->name)
             ->replaceFieldType($stub, $field->htmlType)
             ->replaceFieldValue($stub, $this->getFieldValue($field->htmlValue, $field->name))
             ->replaceFieldMinValue($stub, $this->getFieldMinValueWithName($minValue))
             ->replaceFieldMaxValue($stub, $this->getFieldMaxValueWithName($maxValue ))
             ->replaceFieldMinLengthName($stub, $parser->getMinLength())
             ->replaceFieldMaxLengthName($stub, $parser->getMaxLength())
             ->replaceFieldRequired($stub, $parser->isRequired())
             ->replaceFieldPlaceHolder($stub, $this->getFieldPlaceHolder($field->placeHolder))
             ->replaceFieldStep($stub, $this->getStepsValue($field->getDecimalPointLength()))
             ->wrapField($stub, $field);

        return $stub;
    }

    protected function getMax()
    {

        $params = array_filter(func_get_args() ?: [], function($arg){
            return ! is_null($arg) &&  $arg !== "";
        });

        if(count($params) > 0)
        {
            return max($params);
        }

        return null;
    }


    protected function getMin()
    {
        $params = array_filter(func_get_args() ?: [], function($arg){
            return ! is_null($arg) && $arg !== "";
        });

        if(count($params) > 0)
        {
            return min($params);
        }

        return null;
    }

    /**
     * Wraps a field with a wrapper template
     *
     * @param string $fieldStub
     * @param CrestApps\CodeGeneraotor\Support\Field $field
     * @param bool $standardLabel
     *
     * @return $this
     */
    protected function wrapField(&$fieldStub, Field $field, $standardLabel = true, $required = '')
    {
        $stub = $this->getStubContent('form-input-wrapper.blade', $this->template);

        $this->replaceFieldName($stub, $field->name)
             ->replaceFieldLabel($stub, $this->getLabelFromField($standardLabel ? $field : null))
             ->replaceFieldValidationHelper($stub, $this->getNewHelper($field))
             ->replaceFieldInput($stub, $fieldStub)
             ->replaceRequiredClass($stub, $required);

        $fieldStub = $stub;

        return $this;
    }

    /**
     * Creates html label from a giving field
     *
     * @param CrestApps\CodeGenerator\Models\Field $field
     *
     * @return string
     */
    protected function getLabelFromField(Field $field = null)
    {
        if (empty($field)) {
            return $this->getStubContent('form-nolabel-field.blade', $this->template);
        }

        return $this->getLabelElement($field->name, $field->getLabel());
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
             ->replaceFieldTitle($labelStub, $this->getTitle($label));

        return $labelStub;
    }

    /**
     * Creates helper block
     *
     * @param CrestApps\CodeGeneraotor\Support\Field $field
     *
     * @return string
     */
    protected function getNewHelper(Field $field)
    {
        $stub = $this->getStubContent('form-helper-field.blade', $this->template);

        $this->replaceFieldName($stub, $field->name);

        return $stub;
    }

    /**
     * Replace the fieldName fo the given stub.
     *
     * @param string $stub
     * @param string $fieldName
     *
     * @return $this
     */
    protected function replaceFieldName(&$stub, $fieldName)
    {
        $stub = str_replace('{{fieldName}}', $fieldName, $stub);

        return $this;
    }

    /**
     * Replace the minValue fo the given stub.
     *
     * @param string $stub
     * @param string $minValue
     *
     * @return $this
     */
    protected function replaceFieldMinValue(&$stub, $minValue)
    {
        $stub = str_replace('{{minValue}}', $minValue, $stub);

        return $this;
    }

    /**
     * Replace the maxValue fo the given stub.
     *
     * @param string $stub
     * @param string $maxValue
     *
     * @return $this
     */
    protected function replaceFieldMaxValue(&$stub, $maxValue)
    {
        $stub = str_replace('{{maxValue}}', $maxValue, $stub);

        return $this;
    }

    /**
     * Replace the minLength fo the given stub.
     *
     * @param string $stub
     * @param string $minLength
     *
     * @return $this
     */
    protected function replaceFieldMinLengthName(&$stub, $minLength)
    {
        $stub = str_replace('{{minLength}}', $this->getFieldMinLengthName($minLength), $stub);

        return $this;
    }

    /**
     * Replace the maxLength fo the given stub.
     *
     * @param string $stub
     * @param string $maxLength
     *
     * @return $this
     */
    protected function replaceFieldMaxLengthName(&$stub, $maxLength)
    {
        $stub = str_replace('{{maxLength}}', $this->getFieldMaxLengthName($maxLength), $stub);

        return $this;
    }

    /**
     * Replace the requiredField fo the given stub.
     *
     * @param string $stub
     * @param string $required
     *
     * @return $this
     */
    protected function replaceFieldRequired(&$stub, $required)
    {
        $stub = str_replace('{{requiredField}}', $this->getFieldRequired($required), $stub);

        return $this;
    }

    /**
     * Replace the placeholder fo the given stub.
     *
     * @param string $stub
     * @param string $placeholder
     *
     * @return $this
     */
    protected function replaceFieldPlaceHolder(&$stub, $placeholder)
    {
        $stub = str_replace('{{placeHolder}}', $placeholder, $stub);

        return $this;
    }

    /**
     * Replace the fieldValue fo the given stub.
     *
     * @param string $stub
     * @param string $fieldValue
     *
     * @return $this
     */
    protected function replaceFieldValue(&$stub, $fieldValue)
    {
        $stub = str_replace('{{fieldValue}}', $fieldValue, $stub);

        return $this;
    }

    /**
     * Replace the optionValue fo the given stub.
     *
     * @param string $stub
     * @param string $optionValue
     *
     * @return $this
     */
    protected function replaceOptionValue(&$stub, $optionValue)
    {
        $stub = str_replace('{{optionValue}}', $optionValue, $stub);

        return $this;
    }

    /**
     * Replace the requiredClass fo the given stub.
     *
     * @param string $stub
     * @param string $class
     *
     * @return $this
     */
    protected function replaceRequiredClass(&$stub, $class)
    {
        $stub = str_replace('{{requiredClass}}', $this->getRequiredClass($class), $stub);

        return $this;
    }

    /**
     * Replace the itemId fo the given stub.
     *
     * @param string $stub
     * @param string $fieldValue
     *
     * @return $this
     */
    protected function replaceItemId(&$stub, $itemId)
    {
        $stub = str_replace('{{itemId}}', $itemId, $stub);

        return $this;
    }

    /**
     * Replace the itemTitle fo the given stub.
     *
     * @param string $stub
     * @param string $itemTitle
     *
     * @return $this
     */
    protected function replaceItemLabel(&$stub, $itemTitle)
    {
        $stub = str_replace('{{itemTitle}}', $itemTitle, $stub);

        return $this;
    }

    /**
     * Replace the fieldType fo the given stub.
     *
     * @param string $stub
     * @param string $fieldType
     *
     * @return $this
     */
    protected function replaceFieldType(&$stub, $fieldType)
    {
        $stub = str_replace('{{fieldType}}', $fieldType, $stub);

        return $this;
    }

    /**
     * Replace the fieldTitle fo the given stub.
     *
     * @param string $stub
     * @param CrestApps\CodeGenerator\Models\Label $label
     * @param string $fieldTitle
     *
     * @return $this
     */
    protected function replaceFieldTitle(&$stub, $title)
    {
        $stub = str_replace('{{fieldTitle}}', $title, $stub);

        return $this;
    }

    /**
     * Gets title in trans() method.
     *
     * @param CrestApps\CodeGenerator\Models\Label $label
     * @param bool $raw
     *
     * @return string
     */
    protected function getTranslatedTitle(Label $label, $raw = false)
    {
        $template = !$raw ? "trans('%s')" : "{{ trans('%s') }}" ;

        return sprintf($template, $label->localeGroup);
    }

    /**
     * Gets title to display from a giving label.
     *
     * @param CrestApps\CodeGenerator\Models\Label $label
     * @param bool $raw
     *
     * @return $this
     */
    protected function getTitle(Label $label, $raw = false)
    {
        if (!$label->isPlain) {
            return $this->getTranslatedTitle($label, $raw);
        }
            
        return $this->getPlainTitle($label, $raw);
    }

    /**
     * Replace the fieldValidationHelper fo the given stub.
     *
     * @param string $stub
     * @param string $helper
     *
     * @return $this
     */
    protected function replaceFieldValidationHelper(&$stub, $helper)
    {
        $stub = str_replace('{{fieldValidationHelper}}', $helper, $stub);

        return $this;
    }

    /**
     * Replace the fieldLabel fo the given stub.
     *
     * @param string $stub
     * @param string $fieldLabel
     *
     * @return $this
     */
    protected function replaceFieldLabel(&$stub, $fieldLabel)
    {
        $stub = str_replace('{{fieldLabel}}', $fieldLabel, $stub);

        return $this;
    }

    /**
     * Replace the fieldInput fo the given stub.
     *
     * @param string $stub
     * @param string $fieldInput
     *
     * @return $this
     */
    protected function replaceFieldInput(&$stub, $fieldInput)
    {
        $stub = str_replace('{{fieldInput}}', $fieldInput, $stub);

        return $this;
    }

    /**
     * Replaces the field phrases
     *
     * @param string $stub
     * @param string $items
     *
     * @return string
     */
    protected function replaceFieldItems(&$stub, $items)
    {
        $stub = str_replace('{{fieldItems}}', $items, $stub);

        return $this;
    }

    /**
     * It replaces the fieldMultiple template
     *
     * @param string $stub
     * @param bool $isMultiple
     *
     * @return $this
     */
    protected function replaceFieldMultiple(&$stub, $isMultiple)
    {
        $stub = str_replace('{{fieldMultiple}}', $this->getFieldMultiple($isMultiple), $stub);

        return $this;
    }

    /**
     * It replaces the step template
     *
     * @param string $stub
     * @param bool $step
     *
     * @return $this
     */
    protected function replaceFieldStep(&$stub, $step)
    {
        $stub = str_replace('{{step}}', $step, $stub);

        return $this;
    }

    /**
     * Gets the required class arrribute
     *
     * @param string $class
     *
     * @return string
     */
    protected function getRequiredClass($class)
    {
        return !empty($class) ? $class : '';
    }

    /**
     * Gets an array of key/value string from a giving labels collection.
     *
     * @param array $labels
     *
     * @return array
     */
    protected function getKeyValueStringsFromLabels(array $labels)
    {
        return array_map(function ($label) {
            return sprintf("'%s' => '%s'", $label->value, $label->text);
        }, $labels);
    }

     /**
     * It gets converts an array to a stringbase array for the views.
     *
     * @param array $labels
     *
     * @return string
     */
    abstract protected function getFieldItems(array $labels);

    /**
     * Gets the min value attribute.
     *
     * @param string $minValue
     *
     * @return string
     */
    abstract protected function getFieldMinValueWithName($minValue);

    /**
     * Gets the maxValue attribute.
     *
     * @param string $maxValue
     *
     * @return string
     */
    abstract protected function getFieldMaxValueWithName($maxValue);

    /**
     * Get the minLength attribute.
     *
     * @param string $minLength
     *
     * @return string
     */
    abstract protected function getFieldMinLengthName($minLength);

    /**
     * Gets the maxLength attribute.
     *
     * @param string $maxLength
     *
     * @return string
     */
    abstract protected function getFieldMaxLengthName($maxLength);

    /**
     * Gets the required attribute.
     *
     * @param string $required
     *
     * @return string
     */
    abstract protected function getFieldRequired($required);

    /**
     * Get the placeholder attribute.
     *
     * @param string $placeholder
     *
     * @return string
     */
    abstract protected function getFieldPlaceHolder($placeholder);

    /**
     * Get the placeholder attribute for a menu.
     *
     * @param string $placeholder
     * @param string $name
     *
     * @return string
     */
    abstract protected function getFieldPlaceHolderForMenu($placeholder, $name);

    /**
     * Get the multiple attribute.
     *
     * @param bool $isMulti
     *
     * @return string
     */
    abstract protected function getFieldMultiple($isMulti);

       /**
     * Gets a plain title from a giving label.
     *
     * @param CrestApps\CodeGenerator\Models\Label $label
     * @param bool $raw
     *
     * @return string
     */
    abstract protected function getPlainTitle(Label $label, $raw = false);


    /**
     * Gets the fields value.
     *
     * @param string $value
     * @param string $name
     *
     * @return $this
     */
    abstract protected function getFieldValue($value, $name);

    /**
     * Gets checked item attribute.
     *
     * @param string $value
     * @param string $name
     *
     * @return string
     */
    abstract protected function getCheckedItem($value, $name);

    /**
     * Gets selected value attribute.
     *
     * @param string $name
     *
     * @return string
     */
    abstract protected function getSelectedValue($name);

        /**
     * Gets checked item attribute.
     *
     * @param string $value
     * @param string $name
     *
     * @return string
     */
    abstract protected function getMultipleCheckedItem($value, $name);

    /**
     * Gets selected value attribute.
     *
     * @param string $name
     *
     * @return string
     */
    abstract protected function getMultipleSelectedValue($name);

    /**
     * Gets the html steps attribute.
     *
     * @param int value
     *
     * @return string
     */
    abstract protected function getStepsValue($value);
}
