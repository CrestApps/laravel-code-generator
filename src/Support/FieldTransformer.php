<?php

namespace CrestApps\CodeGenerator\Support;

use CrestApps\CodeGenerator\Models\Field;
use CrestApps\CodeGenerator\Models\FieldMapper;
use CrestApps\CodeGenerator\Support\FieldsOptimizer;
use CrestApps\CodeGenerator\Traits\CommonCommand;
use CrestApps\CodeGenerator\Traits\GeneratorReplacers;
use Exception;

class FieldTransformer
{
    use CommonCommand, GeneratorReplacers;

    /**
     * The name of the file where labels will reside
     *
     * @var string
     */
    protected $localeGroup;

    /**
     * When the read only flag is set the predefine functionality is disabled
     *
     * @var bool
     */
    protected $isReadOnly = false;

    /**
     * The field after transformation
     *
     * @var array
     */
    protected $fields = [];

    /**
     * The raw field before transformation
     *
     * @var array
     */
    protected $rawFields = [];

    /**
     * The languages
     *
     * @var array
     */
    protected $languages = [];

    /**
     * It transfres a gining array to a collection of field
     *
     * @param array $collection
     * @param string $localeGroup
     * @param array $languages
     * @param bool $isReadOnly
     *
     * @return array
     */
    public static function fromArray(array $collection, $localeGroup, array $languages = [], $isReadOnly = false)
    {
        $transformer = new self($collection, $localeGroup, $languages, $isReadOnly);

        return $transformer->transfer()->getFields();
    }

    /**
     * It transfres a gining array to a collection of field
     *
     * @param string $str
     * @param string $localeGroup
     * @param array $languages
     * @param bool $isReadOnly
     *
     * @return array
     */
    public static function fromString($str, $localeGroup = 'generic', array $languages = [], $isReadOnly = false)
    {
        // The following are the expected two formats
        // a,b,c
        // OR
        // name:a;html-type:select;options:first|second|third|fourth
        $fields = [];
        $fieldNames = array_unique(Helpers::convertStringToArray($str));
        foreach ($fieldNames as $fieldName) {
            $field = [];

            if (str_contains($fieldName, ':')) {
                // Handle the following format
                // name:a;html-type:select;options:first|second|third|fourth
                if (!str_is('*name*:*', $fieldName)) {
                    throw new Exception('The "name" property was not provided and is required!');
                }

                $parts = Helpers::convertStringToArray($fieldName, ';');

                foreach ($parts as $part) {
                    if (!str_is('*:*', $part) || count($properties = Helpers::convertStringToArray($part, ':')) < 2) {
                        throw new Exception('Each provided property should use the following format "key:value"');
                    }
                    list($key, $value) = $properties;
                    $field[$key] = $value;
                    if ($key == 'options') {
                        $options = Helpers::convertStringToArray($value, '|');

                        if (count($options) == 0) {
                            throw new Exception('You must provide at least one option where each option is seperated by "|".');
                        }

                        $field['options'] = [];
                        foreach ($options as $option) {
                            $field['options'][$option] = $option;
                        }
                    }
                }
            } else {
                $field['name'] = $fieldName;
            }

            $fields[] = $field;
        }

        return self::fromArray($fields, $localeGroup, $languages, $isReadOnly);
    }

    /**
     * It transfres a gining string to a collection of field
     *
     * @param string|json $json
     * @param string $localeGroup
     *
     * @return array
     */
    public static function fromJson($json, $localeGroupm, array $languages = [])
    {
        if (empty($json) || ($fields = json_decode($json, true)) === null) {
            throw new Exception("The provided string is not a valid json.");
        }

        $transformer = new self($fields, $localeGroup, $languages);

        return $transformer->transfer()->getFields();
    }

    /**
     * Create a new transformer instance.
     *
     * @param array $properties
     * @param string $localeGroup
     *
     * @return void
     */
    protected function __construct(array $properties, $localeGroup, array $languages = [], $isReadOnly = false)
    {
        if (empty($localeGroup)) {
            throw new Exception('LocaleGroup must have a valid value.');
        }

        $this->rawFields = $properties;
        $this->localeGroup = $localeGroup;
        $this->languages = array_unique($languages);
        $this->isReadOnly = $isReadOnly;
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
     * It transfres the raw fields into Fields by setting the $this->fields array
     *
     * @return $this
     */
    protected function transfer()
    {
        $names = array_column($this->rawFields, 'name');
        if (array_unique($names) !== $names) {
            throw new Exception('Each field name must be unique. Please check the profided field names');
        }

        $mappers = [];
        foreach ($this->rawFields as $rawField) {
            $properties = (array) $rawField;

            if (!$this->isReadOnly) {
                //This make sure the field name is updated
                $properties['name'] = Field::getNameFromArray($properties);

                $this->presetProperties($properties)
                    ->setLabels($properties)
                    ->setPlaceholder($properties)
                    ->setOptions($properties);
            }

            $field = Field::fromArray($properties, $this->localeGroup, $this->languages);

            $mappers[] = new FieldMapper($field, (array) $rawField);
        }

        $optimizer = new FieldsOptimizer($mappers);
        $this->fields = $optimizer->optimize()->getFields();

        return $this;
    }

    /**
     * Sets the labels property
     *
     * @param array $properties
     *
     * @return $this
     */
    protected function setLabels(&$properties)
    {
        $label = $properties['name'];

        if (Helpers::isKeyExists($properties, 'labels')) {
            $label = $properties['labels'];
        }

        $properties['labels'] = $this->getLabels($label, $properties['name']);

        return $this;
    }

    /**
     * Sets the placeholder property
     *
     * @param array $properties
     *
     * @return $this
     */
    protected function setPlaceholder(&$properties)
    {
        if (!Helpers::isKeyExists($properties, 'placeholder')) {
            $properties['placeholder'] = $this->getPlaceholders($properties['name'], $this->getHtmlType($properties));
        }

        return $this;
    }

    protected function setOptions(&$properties)
    {
        if (Helpers::isKeyExists($properties, 'options')) {
            $properties['options'] = $this->getOptions((array) $properties['options']);
        }

        return $this;
    }

    /**
     * Get the properties after applying the predefined keys.
     *
     * @param array $properties
     * @param array $languages
     *
     * @return $this
     */
    public function presetProperties(array &$properties)
    {
        $definitions = Config::getCommonDefinitions();

        foreach ($definitions as $definition) {
            $patterns = $this->getArrayByKey($definition, 'match');

            if (Helpers::strIs($patterns, $properties['name'])) {
                //auto add any config from the master config
                $settings = $this->getArrayByKey($definition, 'set');

                foreach ($settings as $key => $setting) {
                    if (!Helpers::isKeyExists($properties, $key) || empty($properties[$key])) {
                        $properties[$key] = $setting;
                    }
                }
            }
        }

        return $this;
    }

    /**
     * Gets labels from a giving title and field name.
     *
     * @param string $title
     * @param string $name
     *
     * @return mix (string | array)
     */
    protected function getLabels($title, $name)
    {
        if (is_array($title)) {
            $title = $this->getFirstElement($title);
        }

        $name = Helpers::removePostFixWith($name, '_id');

        $this->replaceModelName($title, $name, 'field_');

        if ($this->hasLanguages()) {
            $labels = [];

            foreach ($this->languages as $language) {
                $labels[$language] = $title;
            }

            return $labels;
        }

        return $title;
    }

    /**
     * Gets options from a giving array of options
     *
     * @param string $name
     *
     * @return mix (string|array)
     */
    protected function getFirstElement(array $array)
    {
        $value = reset($array);

        if (is_array($value)) {
            return $this->getFirstElement($value);
        }

        return $value;
    }

    /**
     * Gets options from a giving array of options
     *
     * @param string $name
     *
     * @return mix (string|array)
     */
    protected function getOptions(array $options)
    {
        $labels = [];
        if ($this->hasLanguages()) {
            // As this point we are construction the options for multiple languages
            foreach ($this->languages as $language) {
                foreach ($options as $key => $option) {
                    $labels[$language][$key] = Helpers::convertNameToLabel($option);
                }
            }
        } else {

            // At this point we are just formatting the labels
            foreach ($options as $key => $option) {
                if (is_array($option)) {
                    dd($option, $this->hasLanguages(), $this->languages);
                }

                $labels[$key] = Helpers::convertNameToLabel($option);
            }
        }

        return $labels;
    }

    /**
     * Gets labels from a giving title and field name.
     *
     * @param string $name
     *
     * @return mix (string|array)
     */
    protected function getPlaceholders($name, $htmlType)
    {
        $templates = Config::getPlaceholderByHtmlType();

        foreach ($templates as $type => $template) {
            if ($type == $htmlType) {
                return $this->getLabels($template, $name);
            }
        }

        return '';
    }

    /**
     * Gets the html-type from the giving array
     *
     * @param array $properties
     *
     * @return string
     */
    protected function getHtmlType(array $properties)
    {
        return Field::isValidHtmlType($properties) ? $properties['html-type'] : 'text';
    }

    /**
     * Get the properties after applying the predefined keys.
     *
     * @param array $array
     * @param string $key
     *
     * @return array
     */
    protected function getArrayByKey(array $array, $key)
    {
        return Helpers::isKeyExists($array, $key) ? (array) $array[$key] : [];
    }

    /**
     * Checks if there are languages the are required
     *
     * @return bool
     */
    protected function hasLanguages()
    {
        return !empty($this->languages);
    }

}
