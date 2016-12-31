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
                     ->replaceFieldTitle($row, $field->getLabel(), true);

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
                $this->replaceFieldTitle($row, $field->getLabel(), true);
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
                     ->replaceFieldTitle($row, $field->getLabel(), true);

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
     * @return string
    */
    protected function getPickItemsHtmlField(Field $field)
    {
        $stub = $this->getStubContent('form-pickitems-field.blade');
        $fields = '';
        foreach ($field->options as $value => $options) 
        {
            $fieldStub = $stub;
            $this->replaceFieldType($fieldStub, $field->htmlType)
                 ->replaceFieldName($fieldStub, $field->name)
                 ->replaceFieldValue($fieldStub, $value, $field->name)
                 ->replaceIsItemSelected($fieldStub, $value, $field->htmlValue);
 
            $fields .= $fieldStub . PHP_EOL;
        }

        $this->wrapField($fields, $field, false);

        return $fields;
    }

    /**
     * Gets creates an select menu html field for a giving field.
     *
     * @return string
    */
    protected function getSelectHtmlField(Field $field)
    {
        $stub = $this->getStubContent('form-selectmenu-field.blade');

        $this->replaceFieldName($stub, $field->name)
             ->replaceFieldItems($stub, $field->options)
             ->replaceFieldMultiple($stub, false)
             ->replaceFieldValue($stub, $field->htmlValue, $field->name)
             ->wrapField($stub, $field);

        return $stub;
    }

    /**
     * Gets creates an standard html5 field for a giving field.
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
     * @param string $stub
     *
     * @return $this
     */
    protected function wrapField(&$fieldStub, Field $field, $standardLabel = true)
    {
        $stub = $this->getStubContent('form-input-wrapper.blade');

        $this->replaceFieldName($stub, $field->name)
             ->replaceFieldLabel($stub, $this->getNewLabel($standardLabel ? $field : null))
             ->replaceFieldValidationHelper($stub, $this->getNewHelper($field))
             ->replaceFieldInput($stub, $fieldStub);

        $fieldStub = $stub;

        return $this;
    }

    /**
     * Creates html label
     *
     * @param string $stub
     *
     * @return string
     */
    protected function getNewLabel(Field $field = null)
    {
        if(empty($field))
        {
            return $this->getStubContent('form-nolabel-field.blade');
        }

        $labelStub = $this->getStubContent('form-label-field.blade');

        $this->replaceFieldName($labelStub, $field->name)
             ->replaceFieldTitle($labelStub, $field->getLabel());

        return $labelStub;
    }

    /**
     * Creates helper block
     *
     * @param string $stub
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
     * Replace the fieldValue fo the given stub.
     *
     * @param string $stub
     * @param string $fieldValue
     *
     * @return $this
     */
    protected function replaceIsItemSelected(&$stub, $name, $value)
    {
        $stub = str_replace('{{isItemSelected}}', ($name == $value) ? 'true' : 'false', $stub);

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
     * @param string $fieldTitle
     *
     * @return $this
     */
    protected function replaceFieldTitle(&$stub, $fieldTitle, $raw = false)
    {
        $title = "''";

        if(!is_null($fieldTitle))
        {
            $title = sprintf(!$raw ? "'%s'" : "%s", $fieldTitle->value);

            if(!$fieldTitle->isPlain)
            {
                $template = !$raw ? "trans('%s')" : "{{ trans('%s') }}" ;

                $title = sprintf($template, $fieldTitle->langKey);
            }
            
        }
        
        $stub = str_replace('{{fieldTitle}}', $title, $stub);

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
     * Replaces the field Tems
     *
     * @param string $stub
     *
     * @return string
     */
    protected function replaceFieldItems(&$stub, array $items)
    {

        $readyItems = array_map(function($item, $key) {
            return sprintf("'%s' => '%s'", $key, $item);
        }, $items, array_keys($items));

        $replacement = sprintf('[%s]', implode(', ', $readyItems) );

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