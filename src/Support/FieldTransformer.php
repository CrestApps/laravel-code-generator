<?php

namespace CrestApps\CodeGenerator\Support;

use CrestApps\CodeGenerator\Models\Field;
use CrestApps\CodeGenerator\Models\FieldMapper;
use CrestApps\CodeGenerator\Support\FieldsOptimizer;
use Exception;

class FieldTransformer
{
    /**
     * The name of the file where labels will reside
     *
     * @var string
     */
    protected $localeGroup;

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

        $finalFields = [];
        foreach ($this->rawFields as $rawField) {

            $field = Field::fromArray((array) $rawField, $this->localeGroup);

            $finalFields[] = new FieldMapper($field, $field->toArray());
        }

        $optimizer = new FieldsOptimizer($finalFields);
        $this->fields = $optimizer->optimize()->getFields();

        return $this;
    }
}
