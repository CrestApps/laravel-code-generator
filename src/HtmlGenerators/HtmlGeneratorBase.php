<?php

namespace CrestApps\CodeGenerator\HtmlGenerators;

use CrestApps\CodeGenerator\Traits\CommonCommand;
use CrestApps\CodeGenerator\Traits\GeneratorReplacers;
use CrestApps\CodeGenerator\Support\Config;
use CrestApps\CodeGenerator\Models\Field;
use CrestApps\CodeGenerator\Models\Label;
use CrestApps\CodeGenerator\Support\Helpers;
use CrestApps\CodeGenerator\Models\ViewInput;
use CrestApps\CodeGenerator\Support\ValidationParser;

abstract class HtmlGeneratorBase
{
    use CommonCommand, GeneratorReplacers;

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
                $htmlFields .= $this->getPickItemsHtml($field, $parser);
            } elseif ($field->htmlType == 'textarea') {
                $htmlFields .= $this->getTextareaHtmlField($field, $parser);
            } elseif ($field->htmlType == 'password') {
                $htmlFields .= $this->getPasswordHtmlField($field, $parser);
            } elseif ($field->htmlType == 'file') {
                $htmlFields .= $this->getFileHtmlField($field);
            } elseif ($field->htmlType == 'selectRange') {
                $htmlFields .= $this->getSelectRangeHtmlField($field);
            } elseif ($field->htmlType == 'selectMonth') {
                $htmlFields .= $this->getSelectMonthHtmlField($field);
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
    public function getShowRowsHtml(array $fields = null)
    {
        $rows = '';
        $stub = $this->getStubContent('show.row.blade', $this->template);

        foreach ($this->getFieldsToDisplay($fields) as $field) {
            if ($field->isOnShowView) {
                $rows .= $this->getShowRowHtmlField($stub, $field) . PHP_EOL;
            }
        }

        return $rows;
    }

    /**
     * Gets show row html code for the giving field.
     *
     * @param string $stub
     * @param CreatApps\Models\Field $field
     *
     * @return string
    */
    protected function getShowRowHtmlField($stub, Field $field)
    {
        $this->replaceFieldName($stub, $field->name)
             ->replaceModelName($stub, $this->modelName)
             ->replaceRowFieldValue($stub, $this->getFieldAccessorValue($field, 'show'))
             ->replaceFieldTitle($stub, $this->getTitle($field->getLabel(), true));

        return $stub;
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
                $this->replaceFieldTitle($row, $this->getTitle($field->getLabel(), true))
                     ->replaceCommonTemplates($row, $field);
                $rows .= $row . PHP_EOL;
            }
        }

        return $rows;
    }

    /**
     * Replaces field's common templates
     *
     * @param CrestApps\CodeGenerator\Models\Field $field
     *
     * @return $this
    */
    public function replaceCommonTemplates(& $stub, Field $field)
    {
        return $this->replaceFieldName($stub, $field->name)
                    ->replaceModelName($stub, $this->modelName)
                    ->replaceCssClass($stub, $field->cssClass)
                    ->replaceFieldType($stub, $field->htmlType);
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
        $rows = '';
        $stub = $this->getStubContent('index.body.cell.blade', $this->template);
        foreach ($this->getFieldsToDisplay($fields) as $field) {
            if ($field->isOnIndexView) {
                $rows .= $this->getIndexBodyCell($stub, $field) . PHP_EOL;
            }
        }

        return $rows;
    }

    /**
     * Gets index body cell html code for the giving field.
     *
     * @param string $stub
     * @param CreatApps\Models\Field $field
     *
     * @return string
    */
    protected function getIndexBodyCell($stub, Field $field)
    {
        $this->replaceFieldName($stub, $field->name)
             ->replaceModelName($stub, $this->modelName)
             ->replaceRowFieldValue($stub, $this->getFieldAccessorValue($field, 'index'))
             ->replaceFieldTitle($stub, $this->getTitle($field->getLabel(), true))
             ->replaceCommonTemplates($stub, $field);

        return $stub;
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
        $stub = $this->strReplace('selected_value', $value, $stub);

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
        $stub = $this->strReplace('checked_item', $value, $stub);

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
        $stub = $this->strReplace('field_value', $value, $stub);

        return $this;
    }

    /**
     * Gets a value accessor for the field.
     *
     * @param CrestApps\CodeGenerator\Models\Field $field
     * @param string $view
     *
     * @return string
    */
    protected function getFieldAccessorValue(Field $field, $view)
    {
        $fieldAccessor = sprintf('$%s->%s', $this->getModelName($this->modelName), $field->name);

        if ($field->hasForeignRelation() && $field->isOnView($view)) {
            $relation = $field->getForeignRelation();

            $fieldAccessor = sprintf('$%s->%s->%s', $this->getModelName($this->modelName), $relation->name, $relation->getField());

            $fieldAccessor = sprintf(" isset(%s) ? %s : '' ",$fieldAccessor, $fieldAccessor);
        }

        if ($field->isBoolean()) {
            return sprintf("(%s) ? '%s' : '%s'", $fieldAccessor, $field->getTrueBooleanOption()->text, $field->getFalseBooleanOption()->text);
        }

        if ($field->isMultipleAnswers) {
            return sprintf("implode('%s', %s)", $field->optionsDelimiter, $fieldAccessor);
        }

        return $fieldAccessor;
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
             ->replaceCssClass($stub, $field->cssClass)
             ->wrapField($stub, $field);

        return $stub;
    }
    
    /**
     * Gets the html min value.
     *
     * @param mix (int|float) $validationMinValue
     * @param mix (int|float) $fieldMinValue
     *
     * @return mix (int|float)
    */
    protected function getHtmlMinValue($validationMinValue, $fieldMinValue)
    {
        if (! is_null($validationMinValue)) {
            return $validationMinValue;
        }

        return $fieldMinValue;
    }

    /**
     * Gets the html max value.
     *
     * @param mix (int|float) $validationMaxValue
     * @param mix (int|float) $fieldMaxValue
     *
     * @return mix (int|float)
    */
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
    protected function getPickItemsHtml(Field $field, ValidationParser $parser)
    {
        $fields = '';
                
        if ($field->isBoolean()) {
            // At this point we know this is a boolean field, we only need one option
            $fields .= $this->getPickItemsHtmlField($field, $field->getTrueBooleanOption(), $parser) . PHP_EOL;
        } else {
            foreach ($field->getOptionsByLang() as $index => $option) {
                $fields .= $this->getPickItemsHtmlField($field, $option, $parser) . PHP_EOL;
            }
        }

        $this->wrapField($fields, $field, true, (($field->htmlType == 'checkbox') && !$field->isBoolean()) ? 'required' : '');

        return $fields;
    }

    /**
     * Gets creates an checkbox/radio button html field for a giving field.
     *
     * @param CrestApps\CodeGeneraotor\Support\Field $field
     * @param CrestApps\CodeGeneraotor\Support\Label $option
     * @param CrestApps\CodeGeneraotor\Support\ValidationParser $parser
     *
     * @return string
    */
    protected function getPickItemsHtmlField(Field $field, Label $option, ValidationParser $parser)
    {
        $stub = $this->getStubContent(sprintf('form-pickitems%s-field.blade', $field->isInlineOptions ? '-inline' : ''), $this->template);
        $fieldName = ($field->isMultipleAnswers) ? $this->getFieldNameAsArray($field->name) : $field->name;

        $this->replaceFieldType($stub, $field->htmlType)
             ->replaceFieldName($stub, $fieldName)
             ->replaceOptionValue($stub, $option->value)
             ->replaceCheckedItem($stub, $this->getCheckedItemForPickItem($option->value, $field->name, $field->isMultipleAnswers))
             ->replaceItemId($stub, $option->id)
             ->replaceFieldRequired($stub, ($field->htmlType == 'checkbox') ? false : $parser->isRequired())
             ->replaceCssClass($stub, $field->cssClass)
             ->replaceItemLabel($stub, $this->getTitle($option, true));

        return $stub;
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
             ->replaceFieldItems($stub, $this->getFieldItems($field))
             ->replaceFieldMultiple($stub, $field->isMultipleAnswers)
             ->replaceFieldValue($stub, $optionValue)
             ->replaceSelectedValue($stub, $this->getSelectedValueForMenu($field))
             ->replaceFieldItem($stub, $this->getFieldItem($field))
             ->replaceFieldItemAccessor($stub, $this->getFieldItemAccessor($field))
             ->replaceFieldValueAccessor($stub, $this->getFieldValueAccessor($field))
             ->replaceFieldRequired($stub, $parser->isRequired())
             ->replaceFieldPlaceHolder($stub, $this->getFieldPlaceHolderForMenu($field->placeHolder, $field->name))
             ->replaceCssClass($stub, $field->cssClass)
             ->wrapField($stub, $field);

        return $stub;
    }

    protected function getFieldItem(Field $field)
    {
        if ($field->hasForeignRelation()) {
            $relation = $field->getForeignRelation();

            return sprintf('$%s', $relation->getSingleName());
        }

        return '$text';
    }

    protected function getFieldItemAccessor(Field $field)
    {
        if ($field->hasForeignRelation()) {
            $relation = $field->getForeignRelation();
            return sprintf('$%s->%s', $relation->getSingleName(), $relation->getField());
        }

        return '$text';
    }

    protected function getFieldValueAccessor(Field $field)
    {
        if ($field->hasForeignRelation()) {
            $relation = $field->getForeignRelation();

            return sprintf('$%s->%s', $relation->getSingleName(), $relation->getPrimaryKeyForForeignModel());
        }

        return '$value';
    }

    /**
     * Gets the selected value for the pick items.
     *
     * @param string $value
     * @param string $name
     * @param bool $isMultiple
     *
     * @return string
    */
    protected function getCheckedItemForPickItem($value, $name, $isMultiple)
    {
        return $isMultiple ? $this->getMultipleCheckedItem($value, $name) : $this->getCheckedItem($value, $name);
    }

    /**
     * Gets the selected value for a menu.
     *
     * @param string $value
     * @param string $name
     * @param bool $isMultiple
     *
     * @return string
    */
    protected function getSelectedValueForMenu(Field $field)
    {
        $valueAccessor = $this->getFieldValueAccessor($field);

        if ($field->isMultipleAnswers) {
            return $this->getMultipleSelectedValue($field->name, $valueAccessor);
        }

        return $this->getSelectedValue($field->name, $valueAccessor);
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
             ->replaceCssClass($stub, $field->cssClass)
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
             ->replaceCssClass($stub, $field->cssClass)
             ->replaceFieldMinValue($stub, isset($field->range[0]) ? $field->range[0] : 1)
             ->replaceFieldMaxValue($stub, isset($field->range[1]) ? $field->range[1] : 10)
             ->replaceSelectedValue($stub, $this->getSelectedValueForMenu($field))
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
    public function getSelectMonthHtmlField(Field $field)
    {
        $stub = $this->getStubContent('form-month-field.blade', $this->template);

        $this->replaceFieldName($stub, $field->name)
             ->replaceCssClass($stub, $field->cssClass)
             ->replaceSelectedValue($stub, $this->getSelectedValue($field->name, '$value'))
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
             ->replaceCssClass($stub, $field->cssClass)
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
             ->replaceCssClass($stub, $field->cssClass)
             ->replaceFieldType($stub, $field->htmlType)
             ->replaceFieldValue($stub, $this->getFieldValue($field->htmlValue, $field->name))
             ->replaceFieldMinValue($stub, $this->getFieldMinValueWithName($minValue))
             ->replaceFieldMaxValue($stub, $this->getFieldMaxValueWithName($maxValue))
             ->replaceFieldMinLengthName($stub, $parser->getMinLength())
             ->replaceFieldMaxLengthName($stub, $parser->getMaxLength())
             ->replaceFieldRequired($stub, $parser->isRequired())
             ->replaceFieldPlaceHolder($stub, $this->getFieldPlaceHolder($field->placeHolder))
             ->replaceFieldStep($stub, $this->getStepsValue($field->getDecimalPointLength()))
             ->wrapField($stub, $field);

        return $stub;
    }

    /**
     * Gets the gretest value of the giving parameters. It ignores null or empty string.
     *
     * @return numeric
    */
    protected function getMax()
    {
        $params = array_filter(func_get_args() ?: [], function ($arg) {
            return ! is_null($arg) &&  $arg !== "";
        });

        return count($params) > 0 ? max($params) : null;
    }

    /**
     * Gets the smallest value of the giving parameters. It ignores null or empty string.
     *
     * @return numeric
    */
    protected function getMin()
    {
        $params = array_filter(func_get_args() ?: [], function ($arg) {
            return ! is_null($arg) && $arg !== "";
        });

        return count($params) > 0 ? min($params) : null;
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
             ->replaceCssClass($stub, $field->cssClass)
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
            if(!$label->isPlain) {
                return sprintf("'%s' => %s", $label->value, $this->getTranslatedTitle($label));
            }

            return sprintf("'%s' => '%s'", $label->value, $label->text);
        }, $labels);
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
        $template = $raw === false ? "trans('%s')" : "{{ trans('%s') }}" ;

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
     * Replace the fieldName fo the given stub.
     *
     * @param string $stub
     * @param string $fieldName
     *
     * @return $this
     */
    protected function replaceFieldName(&$stub, $fieldName)
    {
        $stub = $this->strReplace('field_name', $fieldName, $stub);

        return $this;
    }

    /**
     * Replace the fieldItem fo the given stub.
     *
     * @param string $stub
     * @param string $name
     *
     * @return $this
     */
    protected function replaceFieldItem(&$stub, $name)
    {
        $stub = $this->strReplace('field_item', $name, $stub);

        return $this;
    }

    /**
     * Replace the fieldItemAccessor fo the given stub.
     *
     * @param string $stub
     * @param string $name
     *
     * @return $this
     */
    protected function replaceFieldItemAccessor(&$stub, $name)
    {
        $stub = $this->strReplace('field_item_accessor', $name, $stub);

        return $this;
    }

    /**
     * Replace the fieldValueAccessor fo the given stub.
     *
     * @param string $stub
     * @param string $name
     *
     * @return $this
     */
    protected function replaceFieldValueAccessor(&$stub, $name)
    {
        $stub = $this->strReplace('field_value_accessor', $name, $stub);

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
        $stub = $this->strReplace('min_value', $minValue, $stub);

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
        $stub = $this->strReplace('max_value', $maxValue, $stub);

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
        $stub = $this->strReplace('min_length', $this->getFieldMinLengthName($minLength), $stub);

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
        $stub = $this->strReplace('max_length', $this->getFieldMaxLengthName($maxLength), $stub);

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
        $stub = $this->strReplace('required_field', $this->getFieldRequired($required), $stub);

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
        $stub = $this->strReplace('placeholder', $placeholder, $stub);

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
        $stub = $this->strReplace('field_value', $fieldValue, $stub);

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
        $stub = $this->strReplace('option_value', $optionValue, $stub);

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
        $stub = $this->strReplace('required_class', $this->getRequiredClass($class), $stub);

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
        $stub = $this->strReplace('item_id', $itemId, $stub);

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
        $stub = $this->strReplace('item_title', $itemTitle, $stub);

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
        $stub = $this->strReplace('field_type', $fieldType, $stub);

        return $this;
    }

    /**
     * Replace the CssClass fo the given stub.
     *
     * @param string $stub
     * @param CrestApps\CodeGenerator\Models\Label $label
     * @param string $class
     *
     * @return $this
     */
    protected function replaceCssClass(&$stub, $class)
    {
        $stub = $this->strReplace('css_class', (!empty($class) ? ' ' . $class : ''), $stub);

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
        $stub = $this->strReplace('field_title', $title, $stub);

        return $this;
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
        $stub = $this->strReplace('field_validation_helper', $helper, $stub);

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
        $stub = $this->strReplace('field_label', $fieldLabel, $stub);

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
        $stub = $this->strReplace('field_input', $fieldInput, $stub);

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
        $stub = $this->strReplace('field_items', $items, $stub);

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
        $stub = $this->strReplace('field_multiple', $this->getFieldMultiple($isMultiple), $stub);

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
        $stub = $this->strReplace('step', $step, $stub);

        return $this;
    }

    /**
     * It gets converts an array to a stringbase array for the views.
     *
     * @param CrestApps\CodeGenerator\Models\Field $field
     *
     * @return string
     */
    abstract protected function getFieldItems(Field $field);

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
     * @param string $valueAccessor
     *
     * @return string
     */
    abstract protected function getSelectedValue($name, $valueAccessor);

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
     * @param string $valueAccessor
     *
     * @return string
     */
    abstract protected function getMultipleSelectedValue($name, $valueAccessor);

    /**
     * Gets the html steps attribute.
     *
     * @param int value
     *
     * @return string
     */
    abstract protected function getStepsValue($value);
}
