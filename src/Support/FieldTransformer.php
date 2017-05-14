<?php

namespace CrestApps\CodeGenerator\Support;

use Exception;
use App;
use CrestApps\CodeGenerator\Support\Helpers;
use CrestApps\CodeGenerator\Support\FieldsOptimizer;
use CrestApps\CodeGenerator\Models\Field;
use CrestApps\CodeGenerator\Models\Label;
use CrestApps\CodeGenerator\Models\FieldMapper;
use CrestApps\CodeGenerator\Models\ForeignRelationship;
use CrestApps\CodeGenerator\Models\ForeignConstraint;
use CrestApps\CodeGenerator\Traits\CommonCommand;
use CrestApps\CodeGenerator\Support\Config;

class FieldTransformer
{
    use CommonCommand;
    
    /**
     * The raw field before transformation
     *
     * @var array
     */
    protected $rawFields = [];

    /**
     * The field after transformation
     *
     * @var array
     */
    protected $fields = [];

    /**
     * The name of the file where labels will reside
     *
     * @var string
     */
    protected $localeGroup;

    /**
     * Mapps the user input to a valid property name in the field object
     *
     * @return array
    */
    protected $predefinedKeyMapping =
    [
        'html-type' => 'htmlType',
        'html-value' => 'htmlValue',
        'value' => ['dataValue','htmlValue'],
        'is-on-views' => ['isOnIndexView','isOnFormView','isOnShowView'],
        'is-on-index' => 'isOnIndexView',
        'is-on-form' => 'isOnFormView',
        'is-on-show' => 'isOnShowView',
        'data-value' => 'dataValue',
        'is-primary' => 'isPrimary',
        'is-index' => 'isIndex',
        'is-unique' => 'isUnique',
        'comment' => 'comment',
        'is-nullable' => 'isNullable',
        'is-auto-increment' => 'isAutoIncrement',
        'is-inline-options' => 'isInlineOptions',
        'placeholder' => 'placeHolder',
        'place-holder' => 'placeHolder',
        'delimiter' => 'optionsDelimiter',
        'is-header' => 'isHeader',
        'class' => 'cssClass',
        'css-class' => 'cssClass',
        'date-format' => 'dateFormat',
    ];

    /**
     * Array of the valid html-types
     *
     * @return array
    */
    protected $validHtmlTypes = [
        'text',
        'password',
        'email',
        'file',
        'checkbox',
        'radio',
        'number',
        'date',
        'select',
        'multipleSelect',
        'textarea',
        'selectMonth',
    ];

    /**
     * List of data types that would make a field unsigned.
     *
     * @return array
    */
    protected $unsignedTypes = [
        'bigIncrements',
        'bigInteger',
        'increments',
        'mediumIncrements',
        'smallIncrements',
        'unsignedBigInteger',
        'unsignedInteger',
        'unsignedMediumInteger',
        'unsignedSmallInteger',
        'unsignedTinyInteger'
    ];

    /**
     * The apps default language
     *
     * @var string
     */
    protected $defaultLang;

    /**
     * Create a new transformer instance.
     *
     * @return void
     */
    protected function __construct($properties, $localeGroup)
    {
        if (empty($localeGroup)) {
            throw new Exception("$localeGroup must have a valid value");
        }

        $this->rawFields = is_array($properties) ? $properties : $this->parseRawString($properties);
        $this->localeGroup = $localeGroup;
        $this->defaultLang = App::getLocale();
    }

    /**
     * It transfred a gining string to a collection of field
     *
     * @param string $fieldsString
     * @param string $localeGroup
     *
     * @return array Support\Field
    */
    public static function text($fieldsString, $localeGroup)
    {
        $transformer = new self($fieldsString, $localeGroup);

        return $transformer->transfer()->getFields();
    }

    /**
     * It transfres a gining string to a collection of field
     *
     * @param string|json $json
     * @param string $localeGroup
     *
     * @return array
    */
    public static function json($json, $localeGroup)
    {
        if (empty($json) || ($fields = json_decode($json, true)) === null) {
            throw new Exception("The provided string is not a valid json.");
        }

        $transformer = new self($fields, $localeGroup);

        return $transformer->transfer()->getFields();
    }

    /**
     * It transfres a gining array to a collection of field
     *
     * @param array $collection
     * @param string $localeGroup
     *
     * @return array
    */
    public static function array(array $collection, $localeGroup)
    {
        $transformer = new self($collection, $localeGroup);

        return $transformer->transfer()->getFields();
    }

    /**
     * It transfres the raw fields into Fields by setting the $this->fields array
     *
     * @return $this
    */
    protected function transfer()
    {
        $finalFields = [];

        $this->validateFields($this->rawFields);

        foreach ($this->rawFields as $rawField) {
            $finalFields[] = $this->transferField($rawField);
        }

        $optimizer = new FieldsOptimizer($finalFields);
        $this->fields = $optimizer->optimize()->getFields();

        return $this;
    }

    /**
     * It transfres a giving array to a field object by matching predefined keys
     *
     * @param array $field
     * @param string $localeGroup
     *
     * @return array
    */
    protected function transferField(array $properties)
    {
        if (!$this->isKeyExists($properties, 'name') || empty(Helpers::removeNonEnglishChars($properties['name']))) {
            throw new Exception("The field 'name' was not provided!");
        }

        if (!$this->isValidHtmlType($properties)) {
            unset($properties['html-type']);
        }

        $field = new Field(Helpers::removeNonEnglishChars($properties['name']));

        $this->setPredefindProperties($field, $properties)
             ->setDataType($field, $properties)
             ->setOptionsProperty($field, $properties)
             ->setValidationProperty($field, $properties)
             ->setLabelsProperty($field, $properties)
             ->setDataTypeParams($field, $properties)
             ->setMultipleAnswers($field, $properties)
             ->setUnsignedProperty($field, $properties)
             ->setForeignRelation($field, $properties)
             ->setRange($field, $properties)
             ->setForeignConstraint($field, $properties);

        self::setOnStore($field, $properties);
        self::setOnUpdate($field, $properties);

        if ($this->isValidSelectRangeType($properties)) {
            $field->htmlType = 'selectRange';
        }

        if ($field->dataType == 'enum' && empty($field->getOptions())) {
            throw new Exception('To construct an enum data-type field, options must be set');
        }

        return new FieldMapper($field, $properties);
    }

   /**
     * Checks if a field contains a valid html-type name
     *
     * @param array $properties
     *
     * @return bool
    */
    protected function isValidHtmlType(array $properties)
    {
        return $this->isKeyExists($properties, 'html-type') &&
        (
             in_array($properties['html-type'], $this->validHtmlTypes)
          || $this->isValidSelectRangeType($properties)
        );
    }

    /**
     * Validates the giving properties.
     *
     * @param array $properties
     *
     * @return void
     */
    protected function validateFields(array $properties)
    {
        $names = array_column($properties, 'name');
        $un = array_unique($names);

        if (array_unique($names) !== $names) {
            throw new Exception('Each field name must be unique. Please check the profided field names');
        }
    }

    /**
     * Checks if a properties contains a valid "selectRange" html-type element.
     *
     * @param array $properties
     *
     * @return bool
     */
    protected function isValidSelectRangeType(array $properties)
    {
        return $this->isKeyExists($properties, 'html-type') && starts_with('selectRange|', $properties['html-type']);
    }

   /**
     * Checks if a key exists in a giving array
     *
     * @param array $properties
     * @param string $name
     *
     * @return bool
    */
    protected function isKeyExists(array $properties, ...$name)
    {
        $exists = false;
        $args = func_get_args();

        for ($i = 1; $i < count($args); $i++) {
            if (!array_key_exists($args[$i], $properties)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Sets the dataType for a giving field
     *
     * @param CrestApps\CodeGenerator\Models\Field $field
     * @param array $properties
     *
     * @return $this
    */
    protected function setDataType(Field & $field, array $properties)
    {
        $map = Config::dataTypeMap();

        if ($this->isKeyExists($properties, 'data-type') && $this->isKeyExists($map, $properties['data-type'])) {
            $field->dataType = $map[$properties['data-type']];
        }

        if (! $this->isKeyExists($properties, 'data-type')) {
            if (Helpers::strIs(Config::getDateTimePatterns(), $field->name)) {
                $field->dataType = 'datetime';
            } elseif (Helpers::strIs(Config::getBooleanPatterns(), $field->name)) {
                $field->dataType = 'boolean';
            } elseif (Helpers::strIs(Config::getKeyPatterns(), $field->name)) {
                $field->dataType = 'integer';
            }
        }

        return $this;
    }

    /**
     * Sets the range for a giving field
     *
     * @param CrestApps\CodeGenerator\Models\Field $field
     * @param array $properties
     *
     * @return $this
    */
    protected function setRange(Field & $field, array $properties)
    {
        if ($this->isValidSelectRangeType($properties)) {
            $field->range = explode(':', substr($properties['html-type'], 12));
        }

        return $this;
    }

    /**
     * Sets the raw php command to execute on create.
     *
     * @param CrestApps\CodeGenerator\Models\Field $field
     * @param array $properties
     *
     * @return void
    */
    protected static function setOnStore(Field & $field, array $properties)
    {
        if (array_key_exists('on-store', $properties)) {
            $field->onStore = self::getOnAction($properties['on-store']);
        }
    }

    /**
     * Sets the raw php command to execute on update.
     *
     * @param CrestApps\CodeGenerator\Models\Field $field
     * @param array $properties
     *
     * @return void
    */
    protected static function setOnUpdate(Field & $field, array $properties)
    {
        if (array_key_exists('on-update', $properties)) {
            $field->onUpdate = self::getOnAction($properties['on-update']);
        }
    }

    /**
    * Cleans up a giving action
    *
    * @param string $action
    *
    * @return string
    */
    protected static function getOnAction($action)
    {
        $action = trim($action);

        if (empty($action)) {
            return null;
        }

        return Helpers::postFixWith($action, ';');
    }

    /**
     * Sets the DataTypeParam for a giving field
     *
     * @param CrestApps\CodeGenerator\Models\Field $field
     * @param array $properties
     *
     * @return $this
    */
    protected function setDataTypeParams(Field & $field, array $properties)
    {
        if ($this->isKeyExists($properties, 'data-type-params') && is_array($properties['data-type-params'])) {
            $field->methodParams = $properties['data-type-params'];
        }

        return $this;
    }

    /**
     * Sets the isMultipleAnswers for a giving field
     *
     * @param CrestApps\CodeGenerator\Models\Field $field
     *
     * @return $this
    */
    protected function setMultipleAnswers(Field & $field)
    {
        if (in_array($field->htmlType, ['checkbox','multipleSelect']) && !$field->isBoolean()) {
            $field->isMultipleAnswers = true;
        }

        return $this;
    }

    /**
     * Sets the isUnsigned for a giving field
     *
     * @param CrestApps\CodeGenerator\Models\Field $field
     * @param array $properties
     *
     * @return $this
    */
    protected function setUnsignedProperty(Field & $field, array $properties)
    {
        $field->isUnsigned = $this->isUnsigned($field, $properties);

        return $this;
    }

    /**
     * Sets the foreign relations for a giving field
     *
     * @param CrestApps\CodeGenerator\Models\Field $field
     * @param array $properties
     *
     * @return $this
    */
    protected function setForeignRelation(Field & $field, array $properties)
    {
        if ($this->isKeyExists($properties, 'is-foreign-relation') && ! $properties['is-foreign-relation']) {
            return $this;
        }

        if ($this->isKeyExists($properties, 'foreign-relation')) {
            $relation = self::makeForeignRelation($field, (array)$properties['foreign-relation']);
        } else {
            $relation = self::getPredectableForeignRelation($field, $this->getModelsPath());
        }

        $field->setForeignRelation($relation);

        return $this;
    }

    /**
     * Gets the model full path.
     *
     * @return string
    */
    protected function getModelsPath()
    {
        return $this->getAppNamespace() . Config::getModelsPath();
    }

    /**
     * Sets the foreign key for a giving field
     *
     * @param CrestApps\CodeGenerator\Models\Field $field
     * @param array $properties
     *
     * @return $this
    */
    protected function setForeignConstraint(Field & $field, array $properties)
    {
        $foreignConstraint = $this->getForeignConstraint($properties);

        $field->setForeignConstraint($foreignConstraint);

        if ($field->hasForeignConstraint() && ! $field->hasForeignRelation()) {
            $field->setForeignRelation($foreignConstraint->getForeignRelation());
        }

        return $this;
    }

    /**
     * Get the foreign constraints
     *
     * @param array $properties
     *
     * @return null || CrestApps\CodeGenerator\Models\ForeignConstraint
    */
    protected function getForeignConstraint(array $properties)
    {
        if ($this->hasForeignConstraint($properties)) {
            $constraint = $properties['foreign-constraint'];
            $onUpdate = $this->isKeyExists($constraint, 'on-update') ? $constraint['on-update'] : null;
            $onDelete = $this->isKeyExists($constraint, 'on-delete') ? $constraint['on-delete'] : null;
            $modelPath = $this->getModelsPath();
            $model = $this->isKeyExists($constraint, 'references-model') ? $constraint['references-model'] : self::guessModelFullName($name, $modelPath);

            return new ForeignConstraint($constraint['field'], $constraint['references'], $constraint['on'], $onDelete, $onUpdate, $model);
        }

        return null;
    }

    /**
     * Check if giving properties contains a valid foreign key object
     *
     * @param array $properties
     *
     * @return bool
     */
    protected function hasForeignConstraint(array $properties)
    {
        return  $this->isKeyExists($properties, 'foreign-constraint')
                && is_array($properties['foreign-constraint'])
                && $this->isKeyExists($properties['foreign-constraint'], 'field', 'references', 'on');
    }

    /**
     * Get a foreign relationship from giving array
     *
     * @param array $options
     *
     * @return null | CrestApps\CodeGenerator\Model\ForeignRelationship
     */
    protected static function getForeignRelation(array $options)
    {
        if (!array_key_exists('type', $options) || !array_key_exists('params', $options)|| !array_key_exists('name', $options)) {
            return null;
        }
        
        $field = array_key_exists('field', $options) ? $options['field'] : null;

        return new ForeignRelationship(
                                $options['type'],
                                $options['params'],
                                $options['name'],
                                $field
                            );
    }

    /**
     * Get a predictable foreign relation using the giving field's name
     *
     * @param CrestApps\CodeGenerator\Models\Field $field
     * @param string $modelPath
     *
     * @return null | CrestApps\CodeGenerator\Model\ForeignRelationship
     */
    public static function getPredectableForeignRelation(Field & $field, $modelPath)
    {
        $commonRelations = Config::getForeignKeys();

        if (array_key_exists($field->name, $commonRelations)) {
            return self::makeForeignRelation($field, $commonRelations[$field->name]);
        }

        $patterns = Config::getKeyPatterns();

        if (Helpers::strIs($patterns, $field->name)) {
            $relationName = camel_case(self::extractModelName($field->name));
            $model = self::guessModelFullName($field->name, $modelPath);
            $parameters = [$model, $field->name];

            return new ForeignRelationship('belongsTo', $parameters, $relationName);
        }

        return null;
    }

    /**
     * Gets a foreign relation from a giving properties.
     *
     * @param CrestApps\CodeGenerator\Models\Field $field
     * @param string $modelPath
     *
     * @return CrestApps\CodeGenerator\Model\ForeignRelationship
     */
    public static function makeForeignRelation(Field & $field, array $properties)
    {
        $relation = self::getForeignRelation($properties);

        if (!is_null($relation)) {
            self::setOnStore($field, $properties);
            self::setOnUpdate($field, $properties);
        }

        return $relation;
    }

    /**
     * Guesses the model full name using the giving field's name
     *
     * @param string $name
     * @param string $modelsPath
     *
     * @return string
     */
    public static function guessModelFullName($name, $modelsPath)
    {
        $model = $modelsPath . ucfirst(self::extractModelName($name));

        return Helpers::convertSlashToBackslash($model);
    }

    /**
     * Extracts the model name from the giving field's name.
     *
     * @param string $name
     *
     * @return string
     */
    public static function extractModelName($name)
    {
        return ucfirst(studly_case(str_replace('_id', '', $name)));
    }

    /**
     * Checks if a field should be unsigned or not.
     *
     * @param CrestApps\CodeGenerator\Models\Field $field
     * @param array $properties
     *
     * @return bool
    */
    protected function isUnsigned(Field & $field, array $properties)
    {
        return ($this->isKeyExists($properties, 'is-unsigned') && $properties['is-unsigned'])
              || in_array($field->dataType, $this->unsignedTypes);
    }

    /**
     * It set the labels property for a giving field
     *
     * @param CrestApps\CodeGenerator\Models\Field $field
     * @param array $properties
     *
     * @return $this
    */
    protected function setLabelsProperty(Field & $field, array $properties)
    {
        $labels = $this->getLabels($properties);

        foreach ($labels as $label) {
            $field->addLabel($label->text, $this->localeGroup, $label->isPlain, $label->lang);
        }

        return $this;
    }

    /**
     * It set the validationRules property for a giving field
     *
     * @param CrestApps\CodeGenerator\Models\Field $field
     * @param array $properties
     *
     * @return $this
    */
    protected function setValidationProperty(Field & $field, array $properties)
    {
        if ($this->isKeyExists($properties, 'validation')) {
            $field->validationRules = is_array($properties['validation']) ? $properties['validation'] : Helpers::removeEmptyItems(explode('|', $properties['validation']));
        }

        return $this;
    }

    /**
     * It set the options property for a giving field
     *
     * @param CrestApps\CodeGenerator\Models\Field $field
     * @param array $properties
     *
     * @return $this
    */
    protected function setOptionsProperty(Field & $field, array $properties)
    {
        $labels = $this->getOptions($field, $properties);

        if (!is_null($labels)) {
            foreach ($labels as $label) {
                $field->addOption($label->text, $label->localeGroup, $label->isPlain, $label->lang, $label->value);
            }
        }

        return $this;
    }

    /**
     * Gets the options from a giving field
     *
     * @param CrestApps\CodeGenerator\Models\Field $field
     * @param array $properties
     *
     * @return array|null
    */
    protected function getOptions(Field & $field, array $properties)
    {
        if (!$this->isKeyExists($properties, 'options')) {
            return null;
        }

        if (is_array($properties['options'])) {
            return self::transferOptionsToLabels($field, $properties['options'], $this->defaultLang, $this->localeGroup);
        }

        return $this->parseOptions($properties['options']);
    }

    /**
     * Transfers options array to array on Labels
     *
     * @param CrestApps\CodeGenerator\Models\Field $field
     * @param array $options
     * @param string $lang
     * @param string $localeGroup
     *
     * @return array
    */
    public static function transferOptionsToLabels(Field & $field, array $options, $lang, $localeGroup)
    {
        $finalOptions = [];

        $associative = Helpers::isAssociative($options);
        
        $index = 0;

        foreach ($options as $value => $option) {
            if ($field->isBoolean()) {
                // Since we know this field is a boolean type,
                // we should allow only two options and it must 0 or 1
                $value = $index;

                if ($index > 1) {
                    continue;
                }
            } elseif (!$associative) {
                $value = $option;
            }
            ++$index;
            
            if (!is_array($option)) {
                // At this point the options are plain text without locale
                $finalOptions[] = new Label($option, $localeGroup, true, $lang);
                continue;
            }

            foreach ($option as $optionValue => $text) {
                // At this point the options are in array which mean they need translation.
                $lang = is_numeric($optionValue) || empty($optionValue) ? $lang : $optionValue;
                $finalOptions[] = new Label($text, $localeGroup, false, $lang, null, $value);
            }
        }

        return $finalOptions;
    }

    /**
     * It set the predefined property for a giving field.
     * it uses the predefinedKeyMapping array
     *
     * @param CrestApps\CodeGenerator\Models\Field $field
     * @param array $properties
     *
     * @return $this
    */
    protected function setPredefindProperties(Field & $field, array $properties)
    {
        foreach ($this->predefinedKeyMapping as $key => $property) {
            if ($this->isKeyExists($properties, $key)) {
                if (is_array($property)) {
                    foreach ($property as $name) {
                        $field->{$name} = $properties[$key];
                    }
                } else {
                    $field->{$property} = $properties[$key];
                }
            }
        }

        return $this;
    }

    /**
     * It get the fields collection
     *
     * @return array
    */
    protected function getFields()
    {
        return $this->fields;
    }

    /**
     * It get the labels from a giving array
     *
     * @param array $items
     *
     * @return $this
    */
    protected function getLabelsFromArray(array $items)
    {
        $labels = [];

        foreach ($items as $key => $label) {
            $lang = empty($key) || is_numeric($key) ? $this->defaultLang : $key;
            $labels[] = new Label($label, $this->localeGroup, false, $lang);
        }

        return $labels;
    }

    /**
     * It will get the provided labels from with the $properties's 'label' or 'labels' property
     *
     * @param array $properties
     *
     * @return array
    */
    protected function getLabels(array $properties)
    {
        if (isset($properties['labels']) && is_array($properties['labels'])) {
            //At this point we know the is array of labels
            return $this->getLabelsFromArray($properties['labels']);
        }

        if (isset($properties['label'])) {
            if (is_array($properties['label'])) {
                //At this point we know this the label
                return $this->getLabelsFromArray($properties['label']);
            }

            return [
                new Label($properties['label'], $this->localeGroup, true, $this->defaultLang)
            ];
        }

        $labels = $this->getLabelsFromRawProperties($properties);

        if (!isset($labels[0]) && isset($properties['name'])) {
            //At this point we know there are no labels found, generate one use the name
            $label = self::convertNameToLabel($properties['name']);

            return [
                new Label($label, $this->localeGroup, true, $this->defaultLang)
            ];
        }

        return $labels;
    }

    /**
     * It will get the provided labels from with the $properties's label property
     * it will convert the following format "en|ar:label=Some Label" or "label=Some Label" to an array
     *
     * @param array $properties
     *
     * @return array
    */
    protected function getLabelsFromRawProperties(array $properties)
    {
        $labels = [];

        foreach ($properties as $key => $label) {
            if (!in_array($key, ['labels','label'])) {
                continue;
            }

            $messages = Helpers::removeEmptyItems(explode('|', $label));

            foreach ($messages as $message) {
                $index = strpos($message, ':');

                if ($index !== false) {
                    $labelText = substr($message, $index + 1);
                    $labelValue = substr($message, 0, $index);

                    $labels[] = new Label($labelText, $this->localeGroup, false, $labelValue);
                } else {
                    $labels[] = new Label($message, $this->localeGroup, true, $this->defaultLang);
                }
            }
        }

        return $labels;
    }

    /**
     * Parses a giving string and turns it into a valid array
     *
     * @param string $optionsString
     *
     * @return array
    */
    protected function parseOptions($optionsString)
    {
        $options = Helpers::removeEmptyItems(explode('|', $optionsString));
        $finalOptions = [];

        foreach ($options as $option) {
            $index = strpos(':', $option);

            if ($index !== false) {
                $labelText = substr($option, $index + 1);
                $labelValue = substr($option, 0, $index);
                $finalOptions[] = new Label($labelText, $this->localeGroup, true, $this->defaultLang);
            } else {
                $finalOptions[] = new Label($option, $this->localeGroup, true, $this->defaultLang);
            }
        }

        return $finalOptions;
    }

    /**
     * Parses giving string and turns it into a valid array
     *
     * @param string $fieldsString
     *
     * @return array
    */
    protected function parseRawString($fieldsString)
    {
        if (empty($fieldsString)) {
            return [];
        }
        
        $fields = explode('#', $fieldsString);
        $finalFields = [];

        foreach ($fields as $field) {
            $configs = $this->getPropertyConfig(Helpers::removeEmptyItems(explode(';', $field)));

            if (!empty($configs)) {
                $finalFields[] = $configs;
            }
        }

        return $finalFields;
    }

    /**
     * Parses the properties array
     *
     * @param string $properties
     *
     * @return array
    */
    protected function getPropertyConfig(array $properties)
    {
        $configs = [];
        foreach ($properties as $property) {
            $config = Helpers::removeEmptyItems(explode('=', $property));
            $totalParts = count($config);
        
            if ($totalParts == 2) {
                $configs[$config[0]] = $this->isProperyBool($config[0]) ? Helpers::stringToBool($config[1]): $config[1];
            } elseif ($totalParts == 1 && $this->isProperyBool($config[0])) {
                $configs[$config[0]] = true;
            }
        }

        return $configs;
    }

    /**
     * Checks if a string starts with the word "is"
     *
     * @param string $str
     *
     * @return bool
    */
    protected function isProperyBool($str)
    {
        $patterns = Config::getBooleanPatterns();
        
        return Helpers::strIs($patterns, $str);
    }

    /**
     * Gets a label from a giving name
     *
     * @param string $name
     *
     * @return string
    */
    public static function convertNameToLabel($name)
    {
        return ucwords(str_replace('_', ' ', $name));
    }
}
