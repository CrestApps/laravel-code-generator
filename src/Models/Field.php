<?php

namespace CrestApps\CodeGenerator\Models;

use App;
use CrestApps\CodeGenerator\Models\ForeignRelationhip;
use CrestApps\CodeGenerator\Models\Label;
use CrestApps\CodeGenerator\Support\Arr;
use CrestApps\CodeGenerator\Support\Config;
use CrestApps\CodeGenerator\Support\Contracts\JsonWriter;
use CrestApps\CodeGenerator\Support\Helpers;
use CrestApps\CodeGenerator\Support\Str;
use CrestApps\CodeGenerator\Traits\CommonCommand;
use CrestApps\CodeGenerator\Traits\GeneratorReplacers;
use CrestApps\CodeGenerator\Traits\ModelTrait;
use Exception;

class Field implements JsonWriter
{
    use CommonCommand, GeneratorReplacers, ModelTrait;

    /**
     * The apps default language
     *
     * @var string
     */
    protected $defaultLang;

    /**
     * The name of the file where labels will reside
     *
     * @var string
     */
    protected $localeGroup;

    /**
     * Fields that are auto managed by Laravel on update.
     *
     * @var array
     */
    protected $autoManagedFieldsOnUpdate = [
        'created_at',
        'updated_at',
    ];

    /**
     * Fields that are auto managed by Laravel on delete.
     *
     * @var array
     */
    protected $autoManagedFieldsOnDelete = [
        'deleted_at',
    ];

    /**
     * List of the data types that are not changable via migrations
     *
     * @var array
     */
    protected $notChangableTypes = [
        'char',
        'double',
        'enum',
        'mediumInteger',
        'timestamp',
        'tinyInteger',
        'ipAddress',
        'json',
        'jsonb',
        'macAddress',
        'mediumIncrements',
        'morphs',
        'nullableMorphs',
        'nullableTimestamps',
        'softDeletes',
        'timeTz',
        'timestampTz',
        'timestamps',
        'timestampsTz',
        'unsignedMediumInteger',
        'unsignedTinyInteger',
        'uuid',
    ];

    /**
     * List of the html types that collect multiple answers
     *
     * @var array
     */
    protected $multipleAnswerTypes = [
        'checkbox',
        'multipleSelect',
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
        'unsignedTinyInteger',
    ];

    /**
     * Array of the valid html-types
     *
     * @return array
     */
    protected static $validHtmlTypes = [
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
     * The name of the field
     *
     * @var string
     */
    public $name;

    /**
     * The labels of the field
     *
     * @var array
     */
    private $labels = [];

    /**
     * The html-type of the field
     *
     * @var string
     */
    public $htmlType = 'text';

    /**
     * The field options of the field
     *
     * @var array
     */
    protected $options = [];

    /**
     * The html-value of the field
     *
     * @var string
     */
    public $htmlValue = null;

    /**
     * The field's validation rules
     *
     * @var string
     */
    public $validationRules = [];

    /**
     * The is-on-index-view flag
     *
     * @var bool
     */
    public $isOnIndexView = true;

    /**
     * The is-on-show-view flag
     *
     * @var bool
     */
    public $isOnShowView = true;

    /**
     * The is-on-form-view flag
     *
     * @var bool
     */
    public $isOnFormView = true;

    /**
     * The field's data type
     *
     * @var bool
     */
    public $dataType = 'string';

    /**
     * The data-type-params
     *
     * @var array
     */
    public $methodParams = [];

    /**
     * The field's data vaue
     *
     * @var bool
     */
    public $dataValue = null;

    /**
     * Indexs this field
     *
     * @var bool
     */
    public $isIndex = false;
    /**
     * Unique indexs this field
     *
     * @var bool
     */
    public $isUnique = false;

    /**
     * Make this a primary field
     *
     * @var bool
     */
    public $isPrimary = false;

    /**
     * Added meta description to this field
     *
     * @var string
     */
    public $comment = null;

    /**
     * Make this field nullable
     *
     * @var bool
     */
    public $isNullable = false;

    /**
     * Make this field unsigned
     *
     * @var bool
     */
    public $isUnsigned = false;

    /**
     * Make this field auto-increment
     *
     * @var bool
     */
    public $isAutoIncrement = false;

    /**
     * Make this field auto-increment
     *
     * @var bool
     */
    public $isInlineOptions = false;

    /**
     * Makes the field bahaves as a header.
     *
     * @var bool
     */
    public $isHeader = false;

    /**
     * The placeholders for the fields.
     *
     * @var array
     */
    private $placeholders = [];

    /**
     * Field options delimiter.
     *
     * @var string
     */
    public $optionsDelimiter = '; ';

    /**
     * Additional css cssClass to add to the field's input.
     *
     * @var string
     */
    public $cssClass = '';

    /**
     * Defines the datetime display format for a date field.
     *
     * @var string
     */
    public $dateFormat = '';

    /**
     * The range of a selector
     *
     * @var array
     */
    public $range = [];

    /**
     * Checks if the field should be mutated to date
     *
     * @var bool
     */
    public $isDate = false;

    /**
     * The foreign relations
     *
     * @var CrestApps\CodeGenerator\Models\ForeignRelationhip
     */
    private $foreignRelation;

    /**
     * The foreign Constraint.
     *
     * @var CrestApps\CodeGenerator\Models\ForeignConstraint
     */
    private $foreignConstraint;

    /**
     * raw php command to execute when the model is created.
     *
     * @var string
     */
    public $onStore;

    /**
     * raw php command to execute when the model is updated.
     *
     * @var string
     */
    public $onUpdate;

    /**
     * Field placeholder
     *
     * @var string
     */
    public $castAs = '';

    /**
     * If the field is flagged to be deleted during migration changes.
     *
     * @var bool
     */
    public $flaggedForDelete = false;

    /**
     * The name to be used by the public when accessing the API.
     *
     * @var string
     */
    public $apiKey;

    /**
     * Should this field be visible to API
     *
     * @var string
     */
    public $isApiVisible = true;

    /**
     * The labels of the api-description
     *
     * @var array
     */
    private $apiDescription = [];

    /**
     * Creates a new field instance.
     *
     * @param string $name
     * @param string $localeGroup
     *
     * @return void
     */
    public function __construct($name, $localeGroup)
    {
        $this->name = $name;
        $this->localeGroup = $localeGroup;
        $this->defaultLang = App::getLocale();
    }

    /**
     * Gets the field labels.
     *
     * @return array
     */
    public function getLabels()
    {
        return $this->labels;
    }

    /**
     * Checks if the field is a header or not
     *
     * @return bool
     */
    public function isHeader()
    {
        return $this->isHeader;
    }

    /**
     * Checks if the field is nullable or not.
     *
     * @return bool
     */
    public function isNullable()
    {
        return $this->isNullable;
    }

    /**
     * Gets the field placeholders.
     *
     * @return array
     */
    public function getPlaceholders()
    {
        return $this->placeholders;
    }

    /**
     * Gets a label by a given language
     *
     * @param string $lang
     *
     * @return CrestApps\CodeGenerator\Models\Label
     */
    public function getLabel($lang = null)
    {
        $lang = empty($lang) ? $this->getDefaultLanguage() : $lang;

        if (!isset($this->labels[$lang])) {
            return $this->getFirstLabel();
        }

        return $this->labels[$lang];
    }

    /**
     * Gets a label by a given language
     *
     * @param string $lang
     *
     * @return CrestApps\CodeGenerator\Models\Label
     */
    public function getPlaceholder($lang = null)
    {
        $lang = empty($lang) ? $this->getDefaultLanguage() : $lang;

        if (!isset($this->placeholders[$lang])) {
            return $this->getFirstPlaceholder();
        }

        return $this->placeholders[$lang];
    }

    /**
     * Gets the app's default language.
     *
     * @return string
     */
    public function getDefaultLanguage()
    {
        return $this->defaultLang;
    }

    /**
     * Gets the first available label if any.
     *
     * @return CrestApps\CodeGenerator\Models\Label
     */
    public function getFirstLabel()
    {
        return current($this->labels);
    }

    /**
     * Gets the first available placeholder if any.
     *
     * @return CrestApps\CodeGenerator\Models\Label | null
     */
    public function getFirstPlaceholder()
    {
        $first = current($this->placeholders);

        return ($first !== false) ? $first : null;
    }

    /**
     * Checks if this field is auto managed by eloquent.
     *
     * @return bool
     */
    public function isAutoManaged()
    {
        return $this->isAutoManagedOnUpdate() || $this->isAutoManagedOnDelete();
    }

    /**
     * Checks if this field is auto managed by eloquent on update event.
     *
     * @return bool
     */
    public function isAutoManagedOnUpdate()
    {
        return in_array(strtolower($this->name), $this->autoManagedFieldsOnUpdate);
    }

    /**
     * Checks if this field is auto managed by eloquent on delete event.
     *
     * @return bool
     */
    public function isAutoManagedOnDelete()
    {
        return in_array(strtolower($this->name), $this->autoManagedFieldsOnDelete);
    }

    /**
     * Adds a label to the labels collection
     *
     * @param string $text
     * @param bool $isPlain
     * @param string $lang
     *
     * @return void
     */
    public function addLabel($text, $isPlain = true, $lang = 'en')
    {
        $this->labels[$lang] = new Label($text, $this->localeGroup, $isPlain, $lang, $this->name);
    }

    /**
     * Adds a label to the placeholders collection
     *
     * @param string $text
     * @param bool $isPlain
     * @param string $lang
     *
     * @return void
     */
    public function addPlaceholder($text, $isPlain = true, $lang = 'en')
    {
        $id = $this->getFieldId('_placeholder');

        $this->placeholders[$lang] = new Label($text, $this->localeGroup, $isPlain, $lang, $id);
    }

    /**
     * Adds a label to the descriptions collection
     *
     * @param string $text
     * @param bool $isPlain
     * @param string $lang
     *
     * @return void
     */
    public function addApiDescription($text, $isPlain = true, $lang = 'en')
    {
        $id = $this->getFieldId('_api_description');

        $this->apiDescription[$lang] = new Label($text, $this->localeGroup, $isPlain, $lang, $id);
    }

    /**
     * Sets the foreign relationship of the field.
     *
     * @param CrestApps\CodeGenerator\Models\ForeignRelationship $relation
     *
     * @return void
     */
    public function setForeignRelation(ForeignRelationship $relation = null)
    {
        $this->foreignRelation = $relation;
    }

    /**
     * Gets a value accessor for the field.
     *
     * @param string $variable
     * @param string $view
     *
     * @return string
     */
    public function getAccessorValue($variable, $view = null)
    {
        $fieldAccessor = sprintf('$%s->%s', $variable, $this->name);

        if ($this->hasForeignRelation() && (empty($view) || $this->isOnView($view))) {
            $relation = $this->getForeignRelation();
            if (Helpers::isNewerThanOrEqualTo('5.5')) {
                $fieldAccessor = sprintf('optional($%s->%s)->%s', $variable, $relation->name, $relation->getField());
            } else {
                $fieldAccessor = sprintf('$%s->%s->%s', $variable, $relation->name, $relation->getField());
                $fieldAccessor = sprintf("isset(%s) ? %s : ''", $fieldAccessor, $fieldAccessor);
            }
        }

        if ($this->isBoolean()) {
            return sprintf("(%s) ? '%s' : '%s'", $fieldAccessor, $this->getTrueBooleanOption()->text, $this->getFalseBooleanOption()->text);
        }

        if ($this->isMultipleAnswers()) {
            return sprintf("implode('%s', %s)", $this->optionsDelimiter, $fieldAccessor);
        }

        if ($this->isFile()) {
            return $fieldAccessor;
        }

        return $fieldAccessor;
    }

    /**
     * Sets the foreign key of the field.
     *
     * @param CrestApps\CodeGenerator\Models\ForeignConstraint $foreignConstraint
     *
     * @return void
     */
    public function setForeignConstraint(ForeignConstraint $constraint = null)
    {
        $this->foreignConstraint = $constraint;
    }

    /**
     * It set the placeholder property for a given field
     *
     * @param CrestApps\CodeGenerator\Models\Field $field
     * @param array $properties
     *
     * @return $this
     */
    protected function setPlaceholder(array $properties)
    {
        $labels = $this->getPlaceholderFromArray($properties);

        foreach ($labels as $label) {
            $this->addPlaceholder($label->text, $label->isPlain, $label->lang);
        }

        return $this;
    }

    /**
     * Get the api-key value
     *
     * @return $this
     */
    public function getApiKey()
    {
        return $this->apiKey ?: $this->name;
    }

    /**
     * It get the labels from a given array
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
     * It will get the provided labels for the placeholder
     *
     * @param array $properties
     *
     * @return array
     */
    protected function getPlaceholderFromArray(array $properties)
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

        return [];
    }

    /**
     * Gets the field's foreign relationship.
     *
     * @return CrestApps\CodeGenerator\Models\ForeignRelationhip
     */
    public function getForeignRelation()
    {
        return $this->foreignRelation;
    }

    /**
     * Gets the field's foreign key.
     *
     * @return CrestApps\CodeGenerator\Models\ForeignConstraint
     */
    public function getForeignConstraint()
    {
        return $this->foreignConstraint;
    }

    /**
     * Checks if the field has a foreign relation.
     *
     * @return bool
     */
    public function hasForeignRelation()
    {
        return !is_null($this->foreignRelation);
    }

    /**
     * Checks if the field shoudl be casted.
     *
     * @return bool
     */
    public function isCastable()
    {
        return !empty($this->castAs);
    }

    /**
     * Checks if the field has a foreign relation.
     *
     * @return bool
     */
    public function hasForeignConstraint()
    {
        return !is_null($this->foreignConstraint);
    }
    /**
     * Checks if the field is on a given view.
     *
     * @return bool
     */
    public function isOnView($view)
    {
        $view = ucfirst(strtolower($view));

        if (in_array($view, ['Form', 'Index', 'Show'])) {
            return $this->{'isOn' . $view . 'View'};
        }

        return false;
    }

    /**
     * Adds a label to the options collection
     *
     * @param string $value
     * @param bool $isPlain
     * @param string $lang
     * @param string $value
     *
     * @return object
     */
    public function addOption($text, $isPlain = true, $lang = 'en', $value = null)
    {
        $id = $this->getFieldId($value);

        $this->options[$lang][] = new Label($text, $this->localeGroup, $isPlain, $lang, $id, $value);
    }

    /**
     * Gets a options by a given language
     *
     * @param string $lang
     *
     * @return object
     */
    public function getOptionsByLang($lang = 'en')
    {
        $finalOptions = [];

        foreach ($this->getOptions() as $options) {
            foreach ($options as $option) {
                if ($option->lang == $lang || $option->isPlain) {
                    $finalOptions[] = $option;
                }
            }
        }

        return empty($finalOptions) ? null : $finalOptions;
    }

    /**
     * Gets the options for this field
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Checks if this field is required or not.
     *
     * @return bool
     */
    public function isRequired()
    {
        return in_array('required', $this->validationRules);
    }

    /**
     * Get current validation rules.
     *
     * @return array
     */
    public function getValidationRule()
    {
        return $this->validationRules ?: [];
    }

    /**
     * Get current validation rules.
     *
     * @return array
     */
    public function getValidationRules()
    {
        return $this->validationRules ?: [];
    }

    /**
     * Checks if this field is boolean type.
     *
     * @return bool
     */
    public function isBoolean()
    {
        return $this->getEloquentDataMethod() == 'boolean';
    }

    /**
     * Gets Eloquent's method name
     *
     * @return string
     */
    public function getEloquentDataMethod()
    {
        $map = Config::dataTypeMap();

        if (isset($map[$this->dataType])) {
            return $map[$this->dataType];
        }

        return 'string';
    }
    /**
     * Checks if this field's html is checkbox
     *
     * @return bool
     */
    public function isCheckbox()
    {
        return $this->htmlType == 'checkbox';
    }

    /**
     * Check if the Eloquent data method is changable to the given type
     *
     * @var bool
     */
    public function isDataChangeAllowed($type)
    {
        return in_array($type, $this->notChangableTypes);
    }

    /**
     * Gets the field Id.
     *
     * @param string $optionValue
     *
     * @return string
     */
    protected function getFieldId($optionValue = null)
    {
        if (!is_null($optionValue)) {
            return sprintf('%s_%s', $this->name, $this->cleanValue($optionValue));
        }

        return $this->name;
    }

    /**
     * It makes a string "id-ready" string
     *
     * @param string $optionValue
     *
     * @return string
     */
    protected function cleanValue($optionValue)
    {
        $value = strtolower(str_replace(' ', '_', $optionValue));

        return Str::removeNonEnglishChars($value);
    }

    /**
     * It checks whether the field is a file or not.
     *
     * @return boolean
     */
    public function isFile()
    {
        return ($this->htmlType == 'file');
    }

    /**
     * Returns current object into an array.
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'name' => $this->name,
            'labels' => $this->labelsToRaw($this->getLabels()),
            'html-type' => $this->htmlType,
            'css-class' => $this->cssClass,
            'options' => $this->optionsToRaw($this->getOptions()),
            'html-value' => $this->htmlValue,
            'validation' => implode('|', $this->validationRules),
            'is-on-index' => $this->isOnIndexView,
            'is-on-show' => $this->isOnShowView,
            'is-on-form' => $this->isOnFormView,
            'data-type' => $this->getRawDataType(),
            'data-type-params' => $this->methodParams,
            'data-value' => $this->dataValue,
            'is-index' => $this->isIndex,
            'is-unique' => $this->isUnique,
            'is-primary' => $this->isPrimary,
            'comment' => $this->comment,
            'is-nullable' => $this->isNullable,
            'is-header' => $this->isHeader,
            'is-unsigned' => $this->isUnsigned,
            'is-auto-increment' => $this->isAutoIncrement,
            'is-inline-options' => $this->isInlineOptions,
            'is-date' => $this->isDate,
            'date-format' => $this->dateFormat,
            'cast-as' => $this->castAs,
            'placeholder' => $this->labelsToRaw($this->getPlaceholders()),
            'delimiter' => $this->optionsDelimiter,
            'range' => $this->range,
            'foreign-relation' => $this->getForeignRelationToRaw(),
            'foreign-constraint' => $this->getForeignConstraintToRaw(),
            'on-store' => $this->onStore,
            'on-update' => $this->onUpdate,
            'api-key' => $this->getApiKey(),
            'is-api-visible' => $this->isApiVisible,
            'api-description' => $this->labelsToRaw($this->getApiDescription()),
        ];
    }

    /**
     * Gets the labels for the description
     *
     * @return array
     */
    public function getApiDescription()
    {
        return $this->apiDescription ?: [];
    }

    /**
     * Gets the data type in a raw format.
     *
     * @return string
     */
    protected function getRawDataType()
    {
        $type = array_search($this->getEloquentDataMethod(), Config::dataTypeMap());

        return $type !== false ? $type : $this->dataType;
    }

    /**
     * Sets the raw php command to execute on create.
     *
     * @param array $properties
     *
     * @return $this
     */
    public function setOnStore(array $properties)
    {
        if (array_key_exists('on-store', $properties)) {
            $this->onStore = $this->getOnAction($properties['on-store']);
        }

        return $this;
    }

    /**
     * Sets the range for a given field
     *
     * @param array $properties
     *
     * @return $this
     */
    public function setRange(array $properties)
    {
        if (self::isValidSelectRangeType($properties)) {
            $this->range = explode(':', substr($properties['html-type'], 12));
        }

        if (Arr::isKeyExists($properties, 'range') && is_array($properties['range'])) {
            $this->range = $properties['range'];
        }

        return $this;
    }

    /**
     * It set the options property for a given field
     *
     * @param array $properties

     *
     * @return $this
     */
    public function setOptionsProperty(array $properties)
    {
        if (Arr::isKeyExists($properties, 'options') && is_array($properties['options'])) {
            $labels = $this->transferOptionsToLabels($properties['options']);

            foreach ($labels as $label) {
                $this->addOption($label->text, $label->isPlain, $label->lang, $label->value);
            }

        }

        return $this;
    }

    /**
     * Transfers options array to array on Labels
     *
     * @param array $options
     *
     * @return array
     */
    protected function transferOptionsToLabels(array $options)
    {
        $finalOptions = [];
        $index = 0;

        foreach ($options as $value => $option) {

            if (!is_array($option)) {
                // At this point the options are plain text without locale
                $finalOptions[] = new Label($option, $this->localeGroup, true, $this->defaultLang, null, $value);
                continue;
            }

            $optionLang = $value;
            foreach ($option as $optionValue => $text) {
                // At this point the options are in array which mean they need translation.
                $finalOptions[] = new Label($text, $this->localeGroup, false, $optionLang, null, $optionValue);
            }
        }

        return $finalOptions;
    }

    /**
     * Sets the raw php command to execute on update.
     *
     * @param array $properties
     *
     * @return $this
     */
    public function setOnUpdate(array $properties)
    {
        if (array_key_exists('on-update', $properties)) {
            $this->onUpdate = $this->getOnAction($properties['on-update']);
        }

        return $this;
    }

    /**
     * Cleans up a given action
     *
     * @param string $action
     *
     * @return string
     */
    public function getOnAction($action)
    {
        $action = trim($action);

        if (empty($action)) {
            return null;
        }

        return Str::postfix($action, ';');
    }

    /**
     * Sets the DataTypeParam for a given field
     *
     * @param array $properties
     *
     * @return $this
     */
    public function setDataTypeParams(array $properties)
    {
        if (Arr::isKeyExists($properties, 'data-type-params')) {
            $this->methodParams = $this->getDataTypeParams((array) $properties['data-type-params']);
        }

        return $this;
    }

    /**
     * Gets the data type parameters for the given type.
     *
     * @param array $params
     *
     * @return array
     */
    public function getDataTypeParams(array $params)
    {
        $type = $this->getEloquentDataMethod();

        if (in_array($type, ['char', 'string']) && isset($params[0]) && ($length = intval($params[0])) > 0) {
            return [$length];
        }

        if (in_array($type, ['decimal', 'double', 'float'])
            && isset($params[0]) && ($length = intval($params[0])) > 0 && isset($params[1]) && ($decimal = intval($params[1])) > 0) {
            return [$length, $decimal];
        }

        if ($type == 'enum') {
            return $params;
        }

        return [];
    }

    /**
     * It set the labels property for a given field
     *
     * @param array $properties
     *
     * @return $this
     */
    protected function setLabelsProperty(array $properties)
    {
        $labels = $this->getLabelsFromProperties($properties);

        foreach ($labels as $label) {
            $this->addLabel($label->text, $label->isPlain, $label->lang);
        }

        return $this;
    }

    /**
     * It set the labels property for a given field
     *
     * @param array $properties
     *
     * @return $this
     */
    protected function setApiDescriptionProperty(array $properties)
    {
        $labels = $this->getApiDescriptionsFromProperties($properties);

        foreach ($labels as $label) {
            $this->addApiDescription($label->text, $label->isPlain, $label->lang);
        }

        return $this;
    }

    /**
     * Check is the field has multiple answers
     *
     * @return bool
     */
    public function isMultipleAnswers()
    {
        return in_array($this->htmlType, $this->multipleAnswerTypes) && !$this->isBoolean();
    }

    /**
     * It will get the provided labels from with the $properties's 'label' or 'labels' property
     *
     * @param array $properties
     *
     * @return array
     */
    protected function getLabelsFromProperties(array $properties)
    {
        if (!Arr::isKeyExists($properties, 'labels')) {
            throw new Exception('The resource-file is missing the labels entry for the ' . $this->name . ' field.');
        }

        if (is_array($properties['labels'])) {
            return $this->getLabelsFromArray($properties['labels']);
        }

        return [
            new Label($properties['labels'], $this->localeGroup, true, $this->defaultLang),
        ];
    }

    /**
     * It will get the provided labels from with the $properties's 'label' or 'labels' property
     *
     * @param array $properties
     *
     * @return array
     */
    protected function getApiDescriptionsFromProperties(array $properties)
    {
        if (!Arr::isKeyExists($properties, 'api-description')) {
            return [];
        }

        if (is_array($properties['api-description'])) {
            return $this->getLabelsFromArray($properties['api-description']);
        }

        return [
            new Label($properties['api-description'], $this->localeGroup, true, $this->defaultLang),
        ];
    }

    /**
     * It set the validationRules property for a given field
     *
     * @param array $properties
     *
     * @return $this
     */
    public function setValidationProperty(array $properties)
    {
        if (Arr::isKeyExists($properties, 'validation')) {
            $this->validationRules = is_array($properties['validation']) ? $properties['validation'] : Arr::removeEmptyItems(explode('|', $properties['validation']));
        }

        if (Helpers::isNewerThanOrEqualTo('5.2') && $this->isNullable && !in_array('nullable', $this->validationRules)) {
            $this->validationRules[] = 'nullable';
        }

        if ($this->isBoolean() && !in_array('boolean', $this->validationRules)) {
            $this->validationRules[] = 'boolean';
        }

        if ($this->isFile() && !in_array('file', $this->validationRules)) {
            $this->validationRules[] = 'file';
        }
        if ($this->isMultipleAnswers() && !in_array('array', $this->validationRules)) {
            $this->validationRules[] = 'array';
        }

        if (in_array($this->getEloquentDataMethod(), ['char', 'string']) && in_array($this->htmlType, ['text', 'textarea'])) {
            if (!in_array('string', $this->validationRules)) {
                $this->validationRules[] = 'string';
            }

            if (!Arr::isMatch($this->validationRules, 'min')) {
                $this->validationRules[] = sprintf('min:%s', $this->getMinLength());
            }

            if (!Arr::isMatch($this->validationRules, 'max') && !is_null($this->getMaxLength())) {
                $this->validationRules[] = sprintf('max:%s', $this->getMaxLength());
            }
        }

        $params = [];

        if (Arr::isKeyExists($properties, 'data-type-params')) {
            $params = $this->getDataTypeParams((array) $properties['data-type-params']);
        }

        if ($this->htmlType == 'number' || (in_array($this->getEloquentDataMethod(), ['decimal', 'double', 'float'])
            && isset($params[0]) && ($length = intval($params[0])) > 0
            && isset($params[1]) && ($decimal = intval($params[1])) > 0)) {
            if (!in_array('numeric', $this->validationRules)) {
                $this->validationRules[] = 'numeric';
            }

            if (!Arr::isMatch($this->validationRules, 'min') && !is_null($minValue = $this->getMinValue())) {
                $this->validationRules[] = sprintf('min:%s', $minValue);
            }

            if (!Arr::isMatch($this->validationRules, 'max') && !is_null($maxValue = $this->getMaxValue())) {
                $this->validationRules[] = sprintf('max:%s', $maxValue);
            }
        }

        return $this;
    }

    /**
     * Sets the isUnsigned for a given field
     *
     * @param array $properties
     *
     * @return $this
     */
    public function setUnsigned(array $properties)
    {
        $this->isUnsigned = (Arr::isKeyExists($properties, 'is-unsigned') && $properties['is-unsigned'])
        || in_array($this->getEloquentDataMethod(), $this->unsignedTypes);

        return $this;
    }

    /**
     * Sets the foreign relations for a given field
     *
     * @param array $properties
     *
     * @return $this
     */
    protected function setForeignRelationFromArray(array $properties)
    {
        if (Arr::isKeyExists($properties, 'is-foreign-relation') && !$properties['is-foreign-relation']) {
            return $this;
        }

        $relation = ForeignRelationship::predict($this->name, self::getModelsPath());

        if (Arr::isKeyExists($properties, 'foreign-relation')) {
            $relation = ForeignRelationship::get((array) $properties['foreign-relation']);

            if (!is_null($relation)) {
                $this->setOnStore($properties);
                $this->setOnUpdate($properties);
            }

        } else if (Arr::isKeyExists($properties, 'foreign-constraint')) {
            $constraint = $this->getForeignConstraintFromArray((array) $properties);

            if (!is_null($constraint)) {
                $relation = $constraint->getForeignRelation();
            }
        }

        $this->setForeignRelation($relation);

        return $this;
    }

    /**
     * Sets the foreign key for a given field
     *
     * @param array $properties
     *
     * @return $this
     */
    protected function setForeignConstraintFromArray(array $properties)
    {
        $foreignConstraint = $this->getForeignConstraintFromArray($properties);

        $this->setForeignConstraint($foreignConstraint);

        if ($this->hasForeignConstraint() && !$this->hasForeignRelation()) {
            $this->setForeignRelation($foreignConstraint->getForeignRelation());
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
    protected function getForeignConstraintFromArray(array $properties)
    {
        if ($this->containsForeignConstraint($properties)) {
            return ForeignConstraint::fromArray($properties['foreign-constraint'], $properties['name']);
        }

        return null;
    }

    /**
     * Check if given properties contains a valid foreign key object
     *
     * @param array $properties
     *
     * @return bool
     */
    protected function containsForeignConstraint(array $properties)
    {
        return Arr::isKeyExists($properties, 'foreign-constraint')
        && is_array($properties['foreign-constraint'])
        && Arr::isKeyExists($properties['foreign-constraint'], 'field', 'references', 'on');
    }

    /**
     * Gets a relation properties.
     *
     * @return array | null
     */
    public function getForeignRelationToRaw()
    {
        if ($this->hasForeignRelation()) {
            return $this->getForeignRelation()->toArray();
        }

        return null;
    }

    /**
     * Gets a foreign key to a raw format.
     *
     * @return array | null
     */
    public function getForeignConstraintToRaw()
    {
        if ($this->hasForeignConstraint()) {
            return $this->getForeignConstraint()->toArray();
        }

        return null;
    }
    /**
     * Returns current object into proper json format.
     *
     * @return string
     */
    public function toJson()
    {
        return json_encode($this->toArray());
    }

    /**
     * Checks if the field is the primary.
     *
     * @return bool
     */
    public function isPrimary()
    {
        return $this->isAutoIncrement || $this->isPrimary;
    }

    /**
     * Checks if the data type is datetime.
     *
     * @return bool
     */
    public function isDateTime()
    {
        return in_array($this->getEloquentDataMethod(), ['dateTime', 'dateTimeTz'])
        || in_array($this->name, ['created_at', 'updated_at', 'deleted_at']);
    }

    /**
     * Checks if the data type is date.
     *
     * @return bool
     */
    public function isDate()
    {
        return $this->dataType == 'date';
    }

    /**
     * Checks if the data type is time.
     *
     * @return bool
     */
    public function isTime()
    {
        return in_array($this->getEloquentDataMethod(), ['time', 'timeTz']);
    }

    /**
     * Checks if the data type is numeric.
     *
     * @return bool
     */
    public function isNumeric()
    {
        return $this->isDecimal() || in_array($this->getEloquentDataMethod(), ['bigIncrements', 'bigInteger', 'increments', 'integer', 'mediumIncrements', 'mediumInteger', 'smallIncrements', 'smallInteger', 'tinyInteger', 'unsignedBigInteger', 'unsignedInteger', 'unsignedMediumInteger', 'unsignedSmallInteger', 'unsignedTinyInteger']);
    }

    /**
     * Checks if the data type string
     *
     * @return bool
     */
    public function isString()
    {
        return in_array($this->getEloquentDataMethod(), ['char', 'string']);
    }

    /**
     * Checks if the data type is time stamp.
     *
     * @return bool
     */
    public function isTimeStamp()
    {
        return in_array($this->getEloquentDataMethod(), ['timestamp', 'timestampTz']);
    }

    /**
     * Checks if the field's type is any valid date.
     *
     * @return bool
     */
    public function isDateOrTime()
    {
        return $this->isDate() || $this->isDateTime() || $this->isTime() || $this->isTimeStamp();
    }

    /**
     * Gets date validation rule for a datetime field.
     *
     * @return mix (null | string)
     */
    public function getDateValidationRule()
    {
        if ($this->isDateOrTime()) {
            if (!empty($this->dateFormat)) {
                return sprintf('date_format:%s', $this->dateFormat);
            }

            return 'date';
        }

        return null;
    }

    /**
     * Checks if the data type contains decimal.
     *
     * @return bool
     */
    public function isDecimal()
    {
        return in_array($this->getEloquentDataMethod(), ['float', 'decimal', 'double']);
    }

    /**
     * Gets the true label for the boolean field.
     *
     * @return mix(null | restApps\CodeGenerator\Models\Label)
     */
    public function getTrueBooleanOption()
    {
        if (!$this->isBoolean()) {
            return null;
        }

        $options = $this->getOptionsByLang($this->defaultLang);

        if (isset($options[1])) {
            return $options[1];
        }

        if (isset($options[0])) {
            return $options[0];
        }

        $label = new Label('Yes', $this->localeGroup, true, $this->defaultLang, $this->getFieldId(1), 1);

        return $label;
    }

    /**
     * Gets the false label for the boolean field.
     *
     * @return mix(null | restApps\CodeGenerator\Models\Label)
     */
    public function getFalseBooleanOption()
    {
        if (!$this->isBoolean()) {
            return null;
        }

        $options = $this->getOptionsByLang($this->defaultLang);

        if (isset($options[0])) {
            return $options[0];
        }

        return new Label('No', $this->localeGroup, true, 'en', $this->getFieldId(0), 0);
    }

    /**
     * Get the total decimal point
     *
     * @return int
     */
    public function getDecimalPointLength()
    {
        if ($this->isDecimal() && !is_null($value = $this->getMethodParam(1))) {
            return $value / ($value * 100);
        }

        return 0;
    }

    /**
     * Gets the Min length a field.
     *
     * @return int|null
     */
    public function getMinLength()
    {
        if ($this->isRequired() || !$this->isNullable) {
            return 1;
        }

        return 0;
    }

    /**
     * Gets the Min length a field.
     *
     * @return int|null
     */
    public function getMaxLength()
    {
        if ($this->isString() && isset($this->methodParams[0])) {
            return intval($this->methodParams[0]);
        }

        return null;
    }

    /**
     * Gets the maximum value a field can equal.
     *
     * @return int|float|null
     */
    public function getMaxValue()
    {
        if (!$this->isNumeric()) {
            return null;
        }
        $dataType = $this->getEloquentDataMethod();
		$max = 2147483647;

        if ($this->isDecimal()) {
            $length = $this->getMethodParam(0) ?: 1;
            $declimal = $this->getMethodParam(1) ?: 0;
            $max = str_repeat('9', $length);

            if ($declimal > 0) {
                $max = substr_replace($max, '.', $declimal * -1, 0);
            }
            $max = floatval($max);
        } elseif ($dataType == 'integer' || $dataType == 'increments') {
            $max = $this->isUnsigned ? 4294967295 : 2147483647;
        } elseif ($dataType == 'mediumInteger' || $dataType == 'mediumIncrements') {
            $max = $this->isUnsigned ? 16777215 : 8388607;
        } elseif ($dataType == 'smallInteger' || $dataType == 'smallIncrements') {
            $max = $this->isUnsigned ? 65535 : 32767;
        } elseif ($dataType == 'tinyInteger' || $dataType == 'tinyIncrements') {
            $max = $this->isUnsigned ? 255 : 127;
        } elseif ($dataType == 'bigInteger' || $dataType == 'bigIncrements') {
            $max = $this->isUnsigned ? 18446744073709551615 : 9223372036854775807;
        }

        return $max;
    }
    /**
     * Gets the minimum value a field can equal.
     *
     * @return int|float|null
     */
    public function getMinValue()
    {
        if ($this->isUnsigned) {
            return 0;
        }

        if (!is_null($value = $this->getMaxValue())) {
            return ($value * -1) - ($this->isDecimal() ? 0 : 1);
        }

        return null;
    }

    /**
     * Gets method's parameter for a given index.
     *
     * @return mix (int|null)
     */
    protected function getMethodParam($index)
    {
        if (isset($this->methodParams[$index]) && ($value = intval($this->methodParams[$index])) > 0) {
            return $value;
        }

        return null;
    }

    /**
     * Gets current labels in a raw format.
     *
     * @return mix (string|object)
     */
    protected function labelsToRaw(array $labels)
    {
        $final = [];

        foreach ($labels as $label) {
            if ($label->isPlain) {
                return $label->text;
            }

            $final[$label->lang] = $label->text;
        }

        return (object) $final;
    }

    /**
     * Gets current Options into a raw format.
     *
     * @return object
     */
    protected function optionsToRaw(array $options)
    {
        $collections = [];

        foreach ($options as $lang => $labels) {
            $finalWithTranslations = [];
            foreach ($labels as $label) {
                if ($label->isPlain) {
                    $collections[$label->value] = $label->text;
                } else {
                    $collections[$label->lang][$label->value] = $label->text;
                }
            }
        }

        $finals = [];

        foreach ($collections as $value => $collection) {
            $finals[$value] = is_array($collection) ? (object) $collection : $collection;
        }

        return (object) $finals;
    }

    /**
     * Checks if a field contains a valid html-type name
     *
     * @param array $properties
     *
     * @return bool
     */
    public static function isValidHtmlType(array $properties)
    {
        return Arr::isKeyExists($properties, 'html-type') &&
            (
            in_array($properties['html-type'], self::$validHtmlTypes)
            || self::isValidSelectRangeType($properties)
        );
    }

    /**
     * Checks if a properties contains a valid "selectRange" html-type element.
     *
     * @param array $properties
     *
     * @return bool
     */
    public static function isValidSelectRangeType(array $properties)
    {
        return Arr::isKeyExists($properties, 'html-type')
        && starts_with($properties['html-type'], 'selectRange|');
    }

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
        'data-type' => 'dataType',
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
        'api-key' => 'apiKey',
        'is-api-visible' => 'isApiVisible',
    ];

    /**
     * It set the predefined property for a given field.
     * it uses the predefinedKeyMapping array
     *
     * @param array $properties
     *
     * @return $this
     */
    public function setPredefindProperties(array $properties)
    {
        foreach ($this->predefinedKeyMapping as $key => $property) {
            if (Arr::isKeyExists($properties, $key)) {
                if (is_array($property)) {
                    foreach ($property as $name) {
                        $this->{$name} = $properties[$key];
                    }
                } else {
                    $this->{$property} = $properties[$key];
                }
            }
        }

        return $this;
    }

    /**
     * It gets the name of the field from a given array
     *
     * @param array $properties
     * @throws Exception
     *
     * @return $this
     */
    public static function getNameFromArray(array $properties)
    {
        if (!Arr::isKeyExists($properties, 'name')
            || empty($fieldName = Str::removeNonEnglishChars($properties['name']))
        ) {
            throw new Exception("The field 'name' was not provided!");
        }

        return $fieldName;
    }

    /**
     * It set the predefined property for a given field.
     * it uses the predefinedKeyMapping array
     *
     * @param array $properties
     * @param string $localeGroup
     *
     * @return $this
     */
    public static function fromArray(array $properties, $localeGroup)
    {
        $fieldName = self::getNameFromArray($properties);

        if (!Field::isValidHtmlType($properties)) {
            unset($properties['html-type']);
        }

        $field = new self($fieldName, $localeGroup);

        $field->setPredefindProperties($properties)
            ->setLabelsProperty($properties)
            ->setApiDescriptionProperty($properties)
            ->setDataTypeParams($properties)
            ->setOptionsProperty($properties)
            ->setUnsigned($properties)
            ->setRange($properties)
            ->setForeignRelationFromArray($properties)
            ->setForeignConstraintFromArray($properties)
            ->setValidationProperty($properties)
            ->setPlaceholder($properties) // this must come after setForeignRelation
            ->setOnStore($properties)
            ->setOnUpdate($properties);

        if (Field::isValidSelectRangeType($properties)) {
            $field->htmlType = 'selectRange';
        }

        if ($field->getEloquentDataMethod() == 'enum' && empty($field->getOptions())) {
            throw new Exception('To construct an enum data-type field, options must be set. ' . $field->name);
        }

        return $field;
    }
}
