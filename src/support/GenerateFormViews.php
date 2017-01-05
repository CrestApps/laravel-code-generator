<?php

namespace CrestApps\CodeGenerator\Support;

use CrestApps\CodeGenerator\Traits\CommonCommand;
use CrestApps\CodeGenerator\Support\Field;
use CrestApps\CodeGenerator\Support\Helpers;
use CrestApps\CodeGenerator\Support\ViewInput;

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
            
            if(in_array($field->htmlType, ['select']) )
            {
                $htmlFields .= $this->getSelectHtmlField($field);
            } 
            elseif(in_array($field->htmlType, ['radio','checkbox']))
            {
                $htmlFields .= $this->getPickItemsHtmlField($field);

            } 
            elseif(in_array($field->htmlType, ['textarea']))
            {
                $htmlFields .= $this->getTextareaHtmlField($field);
            } 
            elseif(in_array($field->htmlType, ['password']))
            {
                $htmlFields .= $this->getPasswordHtmlField($field);
            } 
            else 
            {
                $htmlFields .= $this->getStandardHtmlField($field);
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
                     ->replaceFieldTitle($row, $this->getTitle($field->getLabel(), true));

                $rows .= $row . PHP_EOL;
            }
        }

        return $rows;
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
    protected function getTextareaHtmlField(Field $field)
    {
        $stub = $this->getStubContent('form-textarea-field.blade');

        $this->replaceFieldName($stub, $field->name)
             ->replaceFieldValue($stub, $field->htmlValue, $field->name)
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
    protected function getPickItemsHtmlField(Field $field)
    {
        if($field->isInlineOptions)
        {
            $stub = $this->getStubContent('form-pickitems-inline-field.blade');
        } else {
            $stub = $this->getStubContent('form-pickitems-field.blade');
        }
        
        $fields = '';
        $fieldName = ($field->isMultipleAnswers) ? $this->getFieldNameAsArray($field->name) : $field->name;

        foreach ($field->getOptionsByLang() as $option) 
        {
            $fieldStub = $stub;
            $this->replaceFieldType($fieldStub, $field->htmlType)
                 ->replaceFieldName($fieldStub, $fieldName)
                 ->replaceOptionValue($fieldStub, $option->value)
                 ->replaceItemId($fieldStub, $option->id)
                 ->replaceItemLabel($fieldStub, $this->getTitle($option, true));
 
            $fields .= $fieldStub . PHP_EOL;
        }

        $this->wrapField($fields, $field, false);

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
    protected function getSelectHtmlField(Field $field)
    {
        $stub = $this->getStubContent('form-selectmenu-field.blade');

        $this->replaceFieldName($stub, $field->name)
             ->replaceFieldItems($stub, $field->getOptionsByLang())
             ->replaceFieldMultiple($stub, false)
             ->replaceFieldValue($stub, $field->htmlValue, $field->name)
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
    public function getPasswordHtmlField(Field $field)
    {
        $stub = $this->getStubContent('form-password-field.blade');

        $this->replaceFieldName($stub, $field->name)
             ->replaceFieldType($stub, $field->htmlType)
             ->replaceFieldValue($stub, $field->htmlValue, $field->name)
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
    public function getStandardHtmlField(Field $field)
    {
        $stub = $this->getStubContent('form-input-field.blade');

        $this->replaceFieldName($stub, $field->name)
             ->replaceFieldType($stub, $field->htmlType)
             ->replaceFieldValue($stub, $field->htmlValue, $field->name)
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
    protected function wrapField(&$fieldStub, Field $field, $standardLabel = true)
    {
        $stub = $this->getStubContent('form-input-wrapper.blade');

        $this->replaceFieldName($stub, $field->name)
             ->replaceFieldLabel($stub, $this->getLabelFromField($standardLabel ? $field : null))
             ->replaceFieldValidationHelper($stub, $this->getNewHelper($field))
             ->replaceFieldInput($stub, $fieldStub);

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
        $replacement = $isMulti ? ", 'multiple'=>'multiple'" : '';

        $stub = str_replace('{{fieldMultiple}}', $replacement, $stub);

        return $this;
    }

}