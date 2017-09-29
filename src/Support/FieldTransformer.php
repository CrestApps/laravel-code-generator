<?php

namespace CrestApps\CodeGenerator\Support;

use App;
use CrestApps\CodeGenerator\Models\Field;
use CrestApps\CodeGenerator\Models\FieldMapper;
use CrestApps\CodeGenerator\Models\ForeignConstraint;
use CrestApps\CodeGenerator\Models\ForeignRelationship;
use CrestApps\CodeGenerator\Models\Label;
use CrestApps\CodeGenerator\Support\Config;
use CrestApps\CodeGenerator\Support\FieldsOptimizer;
use CrestApps\CodeGenerator\Support\Helpers;
use CrestApps\CodeGenerator\Support\Str;
use CrestApps\CodeGenerator\Traits\CommonCommand;
use Exception;

class FieldTransformer
{
    use CommonCommand;

    /**
     * The apps default language
     *
     * @var string
     */
    protected $defaultLang;

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
        'value' => [
            'dataValue',
            'htmlValue',
        ],
        'is-on-views' => [
            'isOnIndexView',
            'isOnFormView',
            'isOnShowView',
        ],
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
        'delimiter' => 'optionsDelimiter',
        'is-header' => 'isHeader',
        'class' => 'cssClass',
        'css-class' => 'cssClass',
        'date-format' => 'dateFormat',
        'cast-as' => 'castAs',
        'cast' => 'castAs',
        'is-date' => 'isDate',
    ];

    /**
     * The raw field before transformation
     *
     * @var array
     */
    protected $rawFields = [];

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
        'unsignedTinyInteger',
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
        'select',
        'multipleSelect',
        'textarea',
        'selectMonth',
        'selectRange',
    ];

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

    /**
     * Extracts the model name from the giving field's name.
     *
     * @param string $name
     *
     * @return string
     */
    public static function extractModelName($name)
    {
        return ucfirst(studly_case(Str::singular(str_replace('_id', '', $name))));
    }

    /**
     * It transfres a gining array to a collection of field
     *
     * @param array $collection
     * @param string $localeGroup
     *
     * @return array
     */
    public static function fromArray(array $collection, $localeGroup)
    {
        $transformer = new self($collection, $localeGroup);

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
    public static function fromJson($json, $localeGroup)
    {
        if (empty($json) || ($fields = json_decode($json, true)) === null) {
            throw new Exception("The provided string is not a valid json.");
        }

        $transformer = new self($fields, $localeGroup);

        return $transformer->transfer()->getFields();
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
     * Gets a foreign relation from a giving properties.
     *
     * @param CrestApps\CodeGenerator\Models\Field $field
     * @param string $modelPath
     *
     * @return CrestApps\CodeGenerator\Model\ForeignRelationship
     */
    public static function makeForeignRelation(Field &$field, array $properties)
    {
        $relation = ForeignRelationship::get($properties);

        if (!is_null($relation)) {
            self::setOnStore($field, $properties);
            self::setOnUpdate($field, $properties);
        }

        return $relation;
    }

    /**
     * Get a predictable foreign relation using the giving field's name
     *
     * @param CrestApps\CodeGenerator\Models\Field $field
     * @param string $modelPath
     *
     * @return null | CrestApps\CodeGenerator\Model\ForeignRelationship
     */
    public static function predictForeignRelation(Field &$field, $modelPath)
    {
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
     * Transfers options array to array on Labels
     *
     * @param CrestApps\CodeGenerator\Models\Field $field
     * @param array $options
     * @param string $lang
     * @param string $localeGroup
     *
     * @return array
     */
    public static function transferOptionsToLabels(Field &$field, array $options, $lang, $localeGroup)
    {
        $finalOptions = [];

        $associative = Helpers::isAssociative($options);

        $index = 0;

        foreach ($options as $value => $option) {
            if ($field->isBoolean()) {
                // Since we know this field is a boolean type,
                // we should allow only two options and it must 0 or 1

                if ($index > 1) {
                    continue;
                }

                $value = $index;
            }

            ++$index;

            if (!is_array($option)) {
                // At this point the options are plain text without locale
                $finalOptions[] = new Label($option, $localeGroup, true, $lang, null, $value);
                continue;
            }

            $optionLang = $value;

            foreach ($option as $optionValue => $text) {
                // At this point the options are in array which mean they need translation.
                $finalOptions[] = new Label($text, $localeGroup, false, $optionLang, null, $optionValue);
            }
        }

        return $finalOptions;
    }

    /**
     * Create a new transformer instance.
     *
     * @param array $properties
     * @param string $localeGroup
     *
     * @return void
     */
    protected function __construct(array $properties, $localeGroup)
    {
        if (empty($localeGroup)) {
            throw new Exception('LocaleGroup must have a valid value.');
        }

        $this->rawFields = $properties;
        $this->localeGroup = $localeGroup;
        $this->defaultLang = App::getLocale();
    }

    /**
     * Gets the data type parameters for the giving type.
     *
     * @param string $type
     * @param array $params
     *
     * @return $this
     */
    protected function getDataTypeParams($type, array $params)
    {
        if (in_array($type, ['char', 'string']) && isset($params[0]) && ($length = intval($params[0])) > 0) {
            return [$length];
        }

        if (in_array($type, ['decimal', 'double', 'float']) && isset($params[0]) && ($length = intval($params[0])) > 0 && isset($params[1]) && ($decimal = intval($params[1])) > 0) {
            return [$length, $decimal];
        }

        if ($type == 'enum') {
            return $params;
        }

        return [];
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
            $model = $this->isKeyExists($constraint, 'references-model') ? $constraint['references-model'] :
            self::guessModelFullName($properties['name'], $modelPath);

            return new ForeignConstraint($constraint['field'], $constraint['references'], $constraint['on'], $onDelete, $onUpdate, $model);
        }

        return null;
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
        if (isset($properties['label'])) {
            if (is_array($properties['label'])) {
                //At this point we know this the label
                return $this->getLabelsFromArray($properties['label']);
            }

            return [
                new Label($properties['label'], $this->localeGroup, true, $this->defaultLang),
            ];
        }

        if (isset($properties['labels'])) {
            if (is_array($properties['labels'])) {
                //At this point we know this the label
                return $this->getLabelsFromArray($properties['labels']);
            }

            return [
                new Label($properties['labels'], $this->localeGroup, true, $this->defaultLang),
            ];
        }

        $label = self::convertNameToLabel($properties['name']);

        return [
            new Label($label, $this->localeGroup, true, $this->defaultLang),
        ];
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
     * Gets the model full path.
     *
     * @return string
     */
    protected function getModelsPath()
    {
        return $this->getAppNamespace() . Config::getModelsPath();
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
     * Gets the options from a giving field
     *
     * @param CrestApps\CodeGenerator\Models\Field $field
     * @param array $properties
     *
     * @return array|null
     */
    protected function getOptions(Field &$field, array $properties)
    {
        if ($this->isKeyExists($properties, 'options') && is_array($properties['options'])) {
            return self::transferOptionsToLabels($field, $properties['options'], $this->defaultLang, $this->localeGroup);
        }

        return null;
    }

    /**
     * It will get the provided labels for the placeholder
     *
     * @param CrestApps\CodeGenerator\Models\Field $field
     * @param array $properties
     *
     * @return array
     */
    protected function getPlaceholder(Field $field, array $properties)
    {
        if (isset($properties['placeholder']) && !empty($properties['placeholder'])) {
            if (is_array($properties['placeholder'])) {
                //At this point we know this the label
                return $this->getLabelsFromArray($properties['placeholder']);
            }

            return [
                new Label($properties['placeholder'], $this->localeGroup, true, $this->defaultLang),
            ];
        }

        $labels = [];

        if (!isset($properties['placeholder'])) {
            $templates = Config::getPlaceholderByHtmlType();

            foreach ($templates as $type => $title) {
                if ($field->htmlType == $type) {
                    $fieldName = $field->hasForeignRelation() ? $field->getForeignRelation()->name : $field->name;
                    $this->replaceFieldNamePatterns($title, $fieldName);
                    $langs = $field->getAvailableLanguages();

                    if (count($langs) == 0) {
                        return [
                            new Label($title, $this->localeGroup, true, $this->defaultLang),
                        ];
                    }

                    foreach ($langs as $lang) {
                        $labels[] = new Label($title, $this->localeGroup, false, $lang);
                    }
                }
            }
        }

        return $labels;
    }

    /**
     * Get the properties after applying the predefined keys.
     *
     * @param array $properties
     *
     * @return array
     */
    protected function getProperties(array $properties)
    {
        if (!$this->isValidHtmlType($properties)) {
            unset($properties['html-type']);
        }

        $definitions = Config::getCommonDefinitions();

        foreach ($definitions as $definition) {
            $patterns = $this->isKeyExists($definition, 'match') ? (array) $definition['match'] : [];
            $configs = $this->isKeyExists($definition, 'set') ? (array) $definition['set'] : [];

            if (Helpers::strIs($patterns, $properties['name'])) {
                //auto add any config from the master config
                foreach ($configs as $key => $config) {
                    if (!$this->isKeyExists($properties, $key)) {
                        $properties[$key] = $config;
                    }
                }
            }

            if (!isset($properties['is-header']) && Helpers::strIs(Config::getHeadersPatterns(), $properties['name'])) {
                $properties['is-header'] = true;
            }
        }

        return $properties;
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
                $configs[$config[0]] = $this->isProperyBool($config[0]) ? Helpers::stringToBool($config[1]) : $config[1];
            } elseif ($totalParts == 1) {
                $configs[$config[0]] = true;
            }
        }

        return $configs;
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
        return $this->isKeyExists($properties, 'foreign-constraint')
        && is_array($properties['foreign-constraint'])
        && $this->isKeyExists($properties['foreign-constraint'], 'field', 'references', 'on');
    }

    /**
     * Checks an array for the first value that starts with a giving pattern
     *
     * @param array $subjects
     * @param string $search
     *
     * @return bool
     */
    protected function inArraySearch(array $subjects, $search)
    {
        foreach ($subjects as $subject) {
            if (str_is($search . '*', $subject)) {
                return true;
            }
        }

        return false;
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
        $args = func_get_args();

        for ($i = 1; $i < count($args); $i++) {
            if (!array_key_exists($args[$i], $properties)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Checks if a field should be unsigned or not.
     *
     * @param CrestApps\CodeGenerator\Models\Field $field
     * @param array $properties
     *
     * @return bool
     */
    protected function isUnsigned(Field &$field, array $properties)
    {
        return ($this->isKeyExists($properties, 'is-unsigned') && $properties['is-unsigned'])
        || in_array($field->dataType, $this->unsignedTypes);
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
     * Checks if a properties contains a valid "selectRange" html-type element.
     *
     * @param array $properties
     *
     * @return bool
     */
    protected function isValidSelectRangeType(array $properties)
    {
        return $this->isKeyExists($properties, 'html-type')
        && starts_with($properties['html-type'], 'selectRange|');
    }

    /**
     * Replaces the field name pattern of the givin stub.
     *
     * @param string $stub
     * @param string $name
     *
     * @return $this
     */
    protected function replaceFieldNamePatterns(&$stub, $name)
    {
        $snake = snake_case($name);
        $englishSingle = str_replace('_', ' ', $snake);
        $plural = Str::plural($englishSingle);

        $stub = $this->strReplace('field_name', $englishSingle, $stub);
        $stub = $this->strReplace('field_name_flat', strtolower($name), $stub);
        $stub = $this->strReplace('field_name_sentence', ucfirst($englishSingle), $stub);
        $stub = $this->strReplace('field_name_plural', $plural, $stub);
        $stub = $this->strReplace('field_name_plural_title', title_case($plural), $stub);
        $stub = $this->strReplace('field_name_snake', $snake, $stub);
        $stub = $this->strReplace('field_name_studly', studly_case($name), $stub);
        $stub = $this->strReplace('field_name_slug', str_slug($englishSingle), $stub);
        $stub = $this->strReplace('field_name_kebab', Str::kebabCase($name), $stub);
        $stub = $this->strReplace('field_name_title', Str::titleCase($englishSingle), $stub);
        $stub = $this->strReplace('field_name_title_lower', strtolower($englishSingle), $stub);
        $stub = $this->strReplace('field_name_title_upper', strtoupper($englishSingle), $stub);
        $stub = $this->strReplace('field_name_class', $name, $stub);
        $stub = $this->strReplace('field_name_plural_variable', $this->getPluralVariable($name), $stub);
        $stub = $this->strReplace('field_name_singular_variable', $this->getSingularVariable($name), $stub);

        return $this;
    }

    /**
     * Sets the dataType for a giving field
     *
     * @param CrestApps\CodeGenerator\Models\Field $field
     * @param array $properties
     *
     * @return $this
     */
    protected function setDataType(Field &$field, array $properties)
    {
        $map = Config::dataTypeMap();

        if ($this->isKeyExists($properties, 'data-type') && $this->isKeyExists($map, $properties['data-type'])) {
            $field->dataType = $map[$properties['data-type']];
        }

        return $this;
    }

    /**
     * Sets the DataTypeParam for a giving field
     *
     * @param CrestApps\CodeGenerator\Models\Field $field
     * @param array $properties
     *
     * @return $this
     */
    protected function setDataTypeParams(Field &$field, array $properties)
    {
        if ($this->isKeyExists($properties, 'data-type-params') && is_array($properties['data-type-params'])) {
            $field->methodParams = $this->getDataTypeParams($field->dataType, (array) $properties['data-type-params']);
        }

        return $this;
    }

    /**
     * Sets the foreign key for a giving field
     *
     * @param CrestApps\CodeGenerator\Models\Field $field
     * @param array $properties
     *
     * @return $this
     */
    protected function setForeignConstraint(Field &$field, array $properties)
    {
        $foreignConstraint = $this->getForeignConstraint($properties);

        $field->setForeignConstraint($foreignConstraint);

        if ($field->hasForeignConstraint() && !$field->hasForeignRelation()) {
            $field->setForeignRelation($foreignConstraint->getForeignRelation());
        }

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
    protected function setForeignRelation(Field &$field, array $properties)
    {
        if ($this->isKeyExists($properties, 'is-foreign-relation') && !$properties['is-foreign-relation']) {
            return $this;
        }

        if ($this->isKeyExists($properties, 'foreign-relation')) {
            $relation = self::makeForeignRelation($field, (array) $properties['foreign-relation']);
        } else {
            $relation = self::predictForeignRelation($field, $this->getModelsPath());
        }

        $field->setForeignRelation($relation);

        return $this;
    }

    /**
     * It set the labels property for a giving field
     *
     * @param CrestApps\CodeGenerator\Models\Field $field
     * @param array $properties
     *
     * @return $this
     */
    protected function setLabelsProperty(Field &$field, array $properties)
    {
        $labels = $this->getLabels($properties);

        foreach ($labels as $label) {
            $field->addLabel($label->text, $this->localeGroup, $label->isPlain, $label->lang);
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
    protected function setMultipleAnswers(Field &$field)
    {
        if (in_array($field->htmlType, ['checkbox', 'multipleSelect']) && !$field->isBoolean()) {
            $field->isMultipleAnswers = true;
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
    protected static function setOnStore(Field &$field, array $properties)
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
    protected static function setOnUpdate(Field &$field, array $properties)
    {
        if (array_key_exists('on-update', $properties)) {
            $field->onUpdate = self::getOnAction($properties['on-update']);
        }
    }

    /**
     * It set the options property for a giving field
     *
     * @param CrestApps\CodeGenerator\Models\Field $field
     * @param array $properties
     *
     * @return $this
     */
    protected function setOptionsProperty(Field &$field, array $properties)
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
     * It set the placeholder property for a giving field
     *
     * @param CrestApps\CodeGenerator\Models\Field $field
     * @param array $properties
     *
     * @return $this
     */
    protected function setPlaceholder(Field &$field, array $properties)
    {
        $labels = $this->getPlaceholder($field, $properties);

        foreach ($labels as $label) {
            $field->addPlaceholder($label->text, $this->localeGroup, $label->isPlain, $label->lang);
        }

        return $this;
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
    protected function setPredefindProperties(Field &$field, array $properties)
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
     * Sets the range for a giving field
     *
     * @param CrestApps\CodeGenerator\Models\Field $field
     * @param array $properties
     *
     * @return $this
     */
    protected function setRange(Field &$field, array $properties)
    {
        if ($this->isValidSelectRangeType($properties)) {
            $field->range = explode(':', substr($properties['html-type'], 12));
        }

        if ($this->isKeyExists($properties, 'range') && is_array($properties['range'])) {
            $field->range = $properties['range'];
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
    protected function setUnsignedProperty(Field &$field, array $properties)
    {
        $field->isUnsigned = $this->isUnsigned($field, $properties);

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
    protected function setValidationProperty(Field &$field, array $properties)
    {
        if ($this->isKeyExists($properties, 'validation')) {
            $field->validationRules = is_array($properties['validation']) ? $properties['validation'] : Helpers::removeEmptyItems(explode('|', $properties['validation']));
        }

        if (Helpers::isNewerThanOrEqualTo('5.2') && $field->isNullable && !in_array('nullable', $field->validationRules)) {
            $field->validationRules[] = 'nullable';
        }

        if ($field->isBoolean() && !in_array('boolean', $field->validationRules)) {
            $field->validationRules[] = 'boolean';
        }

        if ($field->isFile() && !in_array('file', $field->validationRules)) {
            $field->validationRules[] = 'file';
        }
        if ($field->isMultipleAnswers && !in_array('array', $field->validationRules)) {
            $field->validationRules[] = 'array';
        }

        if (in_array($field->dataType, ['char', 'string']) && in_array($field->htmlType, ['text', 'textarea'])) {
            if (!in_array('string', $field->validationRules)) {
                $field->validationRules[] = 'string';
            }

            if (!$this->inArraySearch($field->validationRules, 'min')) {
                $field->validationRules[] = sprintf('min:%s', $field->getMinLength());
            }

            if (!$this->inArraySearch($field->validationRules, 'max') && !is_null($field->getMaxLength())) {
                $field->validationRules[] = sprintf('max:%s', $field->getMaxLength());
            }
        }

        $params = [];

        if ($this->isKeyExists($properties, 'data-type-params')) {
            $params = $this->getDataTypeParams($field->dataType, (array) $properties['data-type-params']);
        }

        if ($field->htmlType == 'number' || (in_array($field->dataType, ['decimal', 'double', 'float'])
            && isset($params[0]) && ($length = intval($params[0])) > 0
            && isset($params[1]) && ($decimal = intval($params[1])) > 0)) {
            if (!in_array('numeric', $field->validationRules)) {
                $field->validationRules[] = 'numeric';
            }

            if (!$this->inArraySearch($field->validationRules, 'min') && !is_null($minValue = $field->getMinValue())) {
                $field->validationRules[] = sprintf('min:%s', $minValue);
            }

            if (!$this->inArraySearch($field->validationRules, 'max') && !is_null($maxValue = $field->getMaxValue())) {
                $field->validationRules[] = sprintf('max:%s', $maxValue);
            }
        }

        return $this;
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

        $field = new Field(Helpers::removeNonEnglishChars($properties['name']));

        $properties = $this->getProperties($properties);

        $this->setPredefindProperties($field, $properties)
            ->setDataType($field, $properties)
            ->setOptionsProperty($field, $properties)
            ->setLabelsProperty($field, $properties)
            ->setDataTypeParams($field, $properties)
            ->setMultipleAnswers($field, $properties)
            ->setUnsignedProperty($field, $properties)
            ->setValidationProperty($field, $properties)
            ->setForeignRelation($field, $properties)
            ->setPlaceholder($field, $properties) // this must come after setForeignRelation
            ->setRange($field, $properties)
            ->setForeignConstraint($field, $properties);

        self::setOnStore($field, $properties);
        self::setOnUpdate($field, $properties);

        if ($this->isValidSelectRangeType($properties)) {
            $field->htmlType = 'selectRange';
        }

        if ($field->dataType == 'enum' && empty($field->getOptions())) {
            throw new Exception('To construct an enum data-type field, options must be set. ' . $field->name);
        }

        return new FieldMapper($field, $properties);
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
}
