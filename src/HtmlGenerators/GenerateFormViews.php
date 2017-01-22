<?php

namespace CrestApps\CodeGenerator\HtmlGenerators;

use CrestApps\CodeGenerator\Traits\CommonCommand;
use CrestApps\CodeGenerator\Support\Field;
use CrestApps\CodeGenerator\Support\Helpers;
use CrestApps\CodeGenerator\Support\ViewInput;
use CrestApps\CodeGenerator\Support\ValidationParser;

class GenerateFormViews
{
    use CommonCommand;

    /**
     * Array of fields
     *
     * @var array
    */
    protected $fields = [];

    /**
     * model name
     *
     * @var string
    */
    protected $modelName;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(array $fields, $modelName)
    {
        $this->modelName = $modelName;
        $this->fields = $fields;
    }

    /**
     * Gets html field for the current set fields.
     *
     * @return string
    */
    public function getHtmlFields()
    {
        $htmlFields = '';

        foreach($this->fields as $field)
        {
            if(!$field->isOnFormView)
            {
                continue;
            }

            $parser = new ValidationParser($field->validationRules);
            
            if(in_array($field->htmlType, ['select','multipleSelect']) )
            {
                $htmlFields .= $this->getSelectHtmlField($field, $parser);
            } 
            elseif(in_array($field->htmlType, ['radio','checkbox']) )
            {
                $htmlFields .= $this->getPickItemsHtmlField($field, $parser);
            } 
            elseif($field->htmlType == 'textarea')
            {
                $htmlFields .= $this->getTextareaHtmlField($field, $parser);
            } 
            elseif($field->htmlType == 'password')
            {
                $htmlFields .= $this->getPasswordHtmlField($field, $parser);
            } 
            elseif($field->htmlType == 'file')
            {
                $htmlFields .= $this->getFileHtmlField($field);
            }
            elseif($field->htmlType == 'selectRange')
            {
                $htmlFields .= $this->getSelectRangeHtmlField($field);
            }
            elseif($field->htmlType == 'selectMonth')
            {
                $htmlFields .= $this->getSelectMontheHtmlField($field);
            }
            else 
            {
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
        $stub = $this->getStubContent('show.row.blade');
        $fields = $this->getFieldsToDisplay($fields);
        $rows = '';

        foreach($fields as $field)
        {
            if($field->isOnShowView)
            {
                $row = $stub;
                $this->replaceFieldName($row, $field->name)
                     ->replaceModelName($row, $this->modelName)
                     ->replaceRowFieldValue($row, $this->getFieldValue($field))
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
        $stub = $this->getStubContent('index.header.cell.blade');
        $fields = $this->getFieldsToDisplay($fields);
        $rows = '';

        foreach($fields as $field)
        {
            if($field->isOnIndexView)
            {
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
        $stub = $this->getStubContent('index.body.cell.blade');
        $fields = $this->getFieldsToDisplay($fields);
        $rows = '';

        foreach($fields as $field)
        {

            if($field->isOnIndexView)
            {
                $row = $stub;

                $this->replaceFieldName($row, $field->name)
                     ->replaceModelName($row, $this->modelName)
                     ->replaceRowFieldValue($row, $this->getFieldValue($field))
                     ->replaceFieldTitle($row, $this->getTitle($field->getLabel(), true));

                $rows .= $row . PHP_EOL;
            }
        }

        return $rows;
    }

    protected function replaceRowFieldValue(& $stub, $value)
    {
        $stub = str_replace('{{fieldValue}}', $value, $stub);

        return $this;
    }

    /**
     * Gets a value accessor for the field.
     *
     * @param CrestApps\CodeGenerator\Support\Field $field
     *
     * @return string
    */
    protected function getFieldValue(Field $field)
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
        if(!empty($fields))
        {       
            return array_filter($this->fields, function($field) use($fields){
                return in_array($field->name, $fields);
            });
        }

        return $this->fields;
    }

    /**
     * Gets creates an textarea html field for a giving field.
     *
     * @param CrestApps\CodeGeneraotor\Support\Field $field
     *
     * @return string
    */
    protected function getTextareaHtmlField(Field $field, ValidationParser $parser)
    {

        $stub = $this->getStubContent('form-textarea-field.blade');

        $this->replaceFieldName($stub, $field->name)
             ->replaceFieldValue($stub, $field->htmlValue, $field->name)
             ->replaceFieldMinValue($stub, $this->getFieldMinValueWithName($parser->getMinValue()))
             ->replaceFieldMaxValue($stub, $this->getFieldMaxValueWithName($parser->getMaxValue()))
             ->replaceFieldMinLengthName($stub, $parser->getMinLength())
             ->replaceFieldMaxLengthName($stub, $parser->getMaxLength())
             ->replaceFieldRequired($stub, $parser->isRequired())
             ->replaceFieldPlaceHolder($stub, $field->placeHolder)
             ->wrapField($stub, $field);

        return $stub;

    }
    
    /**
     * Gets creates an checkbox/radio button html field for a giving field.
     *
     * @param CrestApps\CodeGeneraotor\Support\Field $field
     *
     * @return string
    */
    protected function getPickItemsHtmlField(Field $field, ValidationParser $parser)
    {
 
        $stub = $this->getStubContent(sprintf('form-pickitems%s-field.blade', $field->isInlineOptions ? '-inline' : ''));
        $fields = '';
        $fieldName = ($field->isMultipleAnswers) ? $this->getFieldNameAsArray($field->name) : $field->name;
        $isCheckbox = ($field->htmlType == 'checkbox');

        foreach ($field->getOptionsByLang() as $option) 
        {
            $fieldStub = $stub;
            $this->replaceFieldType($fieldStub, $field->htmlType)
                 ->replaceFieldName($fieldStub, $fieldName)
                 ->replaceOptionValue($fieldStub, $option->value)
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
     *
     * @return string
    */
    protected function getSelectHtmlField(Field $field, ValidationParser $parser)
    {
        $stub = $this->getStubContent('form-selectmenu-field.blade');

        $fieldName = ($field->isMultipleAnswers) ? $this->getFieldNameAsArray($field->name) : $field->name;
        $this->replaceFieldName($stub, $fieldName)
             ->replaceFieldItems($stub, $field->getOptionsByLang())
             ->replaceFieldMultiple($stub, $field->isMultipleAnswers)
             ->replaceFieldValue($stub, $field->htmlValue, $field->name)
             ->replaceFieldRequired($stub, $parser->isRequired())
             ->replaceFieldPlaceHolder($stub, $field->placeHolder)
             ->wrapField($stub, $field);

        return $stub;
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
        $stub = $this->getStubContent('form-file-field.blade');

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
        $stub = $this->getStubContent('form-selectrange-field.blade');

        $this->replaceFieldName($stub, $field->name)
             ->replaceFieldMinValue($stub, isset($field->range[0]) ? $field->range[0] : 1)
             ->replaceFieldMaxValue($stub, isset($field->range[1]) ? $field->range[1] : 10)
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
        $stub = $this->getStubContent('form-month-field.blade');

        $this->replaceFieldName($stub, $field->name)
             ->wrapField($stub, $field);
        
        return $stub;
    }


    /**
     * Gets creates an password html5 field for a giving field.
     *
     * @param CrestApps\CodeGeneraotor\Support\Field $field
     *
     * @return string
    */
    public function getPasswordHtmlField(Field $field, ValidationParser $parser)
    {
        $stub = $this->getStubContent('form-password-field.blade');

        $this->replaceFieldName($stub, $field->name)
             ->replaceFieldType($stub, $field->htmlType)
             ->replaceFieldValue($stub, $field->htmlValue, $field->name)
             ->replaceFieldMinValue($stub, $this->getFieldMinValueWithName($parser->getMinValue()))
             ->replaceFieldMaxValue($stub, $this->getFieldMaxValueWithName($parser->getMaxValue()))
             ->replaceFieldMinLengthName($stub, $parser->getMinLength())
             ->replaceFieldMaxLengthName($stub, $parser->getMaxLength())
             ->replaceFieldRequired($stub, $parser->isRequired())
             ->replaceFieldPlaceHolder($stub, $field->placeHolder)
             ->wrapField($stub, $field);
        
        return $stub;
    }

    /**
     * Gets creates an standard html5 field for a giving field.
     *
     * @param CrestApps\CodeGeneraotor\Support\Field $field
     *
     * @return string
    */
    public function getStandardHtmlField(Field $field, ValidationParser $parser)
    {
        $stub = $this->getStubContent('form-input-field.blade');

        $this->replaceFieldName($stub, $field->name)
             ->replaceFieldType($stub, $field->htmlType)
             ->replaceFieldValue($stub, $field->htmlValue, $field->name)
             ->replaceFieldMinValue($stub, $this->getFieldMinValueWithName($parser->getMinValue()))
             ->replaceFieldMaxValue($stub, $this->getFieldMaxValueWithName($parser->getMaxValue()))
             ->replaceFieldMinLengthName($stub, $parser->getMinLength())
             ->replaceFieldMaxLengthName($stub, $parser->getMaxLength())
             ->replaceFieldRequired($stub, $parser->isRequired())
             ->replaceFieldPlaceHolder($stub, $field->placeHolder)
             ->wrapField($stub, $field);

        return $stub;
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
        $stub = $this->getStubContent('form-input-wrapper.blade');

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
     * @param CrestApps\CodeGenerator\Support\Field $field
     *
     * @return string
     */
    protected function getLabelFromField(Field $field = null)
    {
        if(empty($field))
        {
            return $this->getStubContent('form-nolabel-field.blade');
        }

        return $this->getLabelElement($field->name, $field->getLabel());
    }

    /**
     * Creates html label.
     *
     * @param string $name
     * @param CrestApps\CodeGenerator\Support\Label $label
     *
     * @return string
     */
    protected function getLabelElement($name, Label $label)
    {
        $labelStub = $this->getStubContent('form-label-field.blade');

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
        $stub = $this->getStubContent('form-helper-field.blade');

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
     * Replace the minValue fo the given stub.
     *
     * @param string $minValue
     *
     * @return string
     */
    protected function getFieldMinValueWithName($minValue)
    {
        return empty($minValue) ? '' : sprintf(" 'min' => '%s',", $minValue);
    }

    /**
     * Replace the maxValue fo the given stub.
     *
     * @param string $maxValue
     *
     * @return string
     */
    protected function getFieldMaxValueWithName($maxValue)
    {
        return empty($maxValue) ? '' : sprintf(" 'max' => '%s',", $maxValue);
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
        $value = empty($minLength) ? '' : sprintf(" 'minlength' => '%s',", $minLength);

        $stub = str_replace('{{minLength}}', $value, $stub);

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

        $value = empty($maxLength) ? '' : sprintf(" 'maxlength' => '%s',", $maxLength);

        $stub = str_replace('{{maxLength}}', $value, $stub);

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
        $value = $required ? sprintf(" 'required' => %s,", ($required ? 'true' : 'false')) : '';
        
        $stub = str_replace('{{requiredField}}', $value, $stub);

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
        $value = empty($placeholder) ? '' : sprintf(" 'placeholder' => '%s',", $placeholder);

        $stub = str_replace('{{placeHolder}}', $value, $stub);

        return $this;
    }

    /**
     * Replace the fieldValue fo the given stub.
     *
     * @param string $stub
     * @param string $fieldValue
     * @param string $name
     *
     * @return $this
     */
    protected function replaceFieldValue(&$stub, $fieldValue, $name)
    {
        $value = 'null';

        if(!is_null($fieldValue) )
        {
            $value = sprintf(" isset(\$post->%s) ? \$post->%s : '%s' ", $name, $name, $fieldValue);
        }

        $stub = str_replace('{{fieldValue}}', $value, $stub);

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
        //{{requiredClass}}
        $value = empty($class) ? '' : sprintf(" 'class' => '%s' ", $class);

        $stub = str_replace('{{requiredClass}}', $value, $stub);

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
     * @param CrestApps\CodeGenerator\Support\Label $label
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
     * Gets title to display from a label
     *
     * @param CrestApps\CodeGenerator\Support\Label $label
     * @param bool $raw
     *
     * @return $this
     */
    protected function getTitle(Label $label, $raw = false)
    {

        if(!$label->isPlain)
        {
            $template = !$raw ? "trans('%s')" : "{{ trans('%s') }}" ;

            return sprintf($template, $label->localeGroup);
        }
            
        return sprintf(!$raw ? "'%s'" : "%s", $label->text);
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
     *
     * @return string
     */
    protected function replaceFieldItems(&$stub, array $items)
    {
        $readyItems = array_map(function($label) {
            return sprintf("'%s' => '%s'", $label->value, $label->text);
        }, $items);

        $replacement = sprintf('[%s]', implode(', ', $readyItems) );
        
        //$replacement = "['STILL NEED MORE WORK!!!!']";
        $stub = str_replace('{{fieldItems}}', $replacement, $stub);

        return $this;
    }

    /**
     * It replaces the fieldMultiple template
     *
     * @param string $stub
     * @param bool $isMulti
     *
     * @return $this
     */
    protected function replaceFieldMultiple(&$stub, $isMulti)
    {
        $replacement = $isMulti ? "'multiple' => 'multiple'," : '';

        $stub = str_replace('{{fieldMultiple}}', $replacement, $stub);

        return $this;
    }

}