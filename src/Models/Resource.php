<?php

namespace CrestApps\CodeGenerator\Models;

use CrestApps\CodeGenerator\Models\ForeignRelationship;
use CrestApps\CodeGenerator\Models\Index;
use CrestApps\CodeGenerator\Models\Relation;
use CrestApps\CodeGenerator\Support\Config;
use CrestApps\CodeGenerator\Support\Contracts\JsonWriter;
use CrestApps\CodeGenerator\Support\FieldTransformer;
use CrestApps\CodeGenerator\Support\Helpers;
use Exception;
use File;

class Resource implements JsonWriter
{
    /**
     * The resource fields
     *
     * @var array
     */
    public $fields;

    /**
     * The resource relations
     *
     * @var array
     */
    public $relations;

    /**
     * The resource indexes
     *
     * @var array
     */
    public $indexes;

    /**
     * Create a new input instance.
     *
     * @return void
     */
    public function __construct(array $fields = [], array $relations = [], array $indexes = [])
    {
        $this->fields = $fields;
        $this->relations = $relations;
        $this->indexes = $indexes;
    }

    /**
     * Checks if the resource is empty.
     *
     * @return bool
     */
    public function isEmpty()
    {
        return empty($this->fields) && empty($this->relations) && empty($this->indexes);
    }

    /**
     * Checks if the resources has fields
     *
     * @return int
     */
    public function hasFields()
    {
        return $this->totalFields() > 0;
    }

    /**
     * Get the first header field if available
     *
     * @return min (null | CrestApps\CodeGenerator\Models\Field)
     */
    public function getHeaderField()
    {
        return collect($this->fields)->first(function ($field) {
            return $field->isHeader();
        });
    }

    /**
     * Get the first primary field if available
     *
     * @return min (null | CrestApps\CodeGenerator\Models\Field)
     */
    public function getPrimaryField()
    {
        return collect($this->fields)->first(function ($field) {
            return $field->isPrimary();
        });
    }

    /**
     * Extracts the giving property name from the fields
     *
     * @return array
     */
    public function pluckFields($property = 'name')
    {
        $names = [];

        if ($this->hasFields()) {
            foreach ($this->getFields() as $field) {

                if (property_exists($field, $property)) {
                    $names[] = $field->{$property};
                }
            }
        }

        return $names;
    }

    /**
     * Extracts the giving property name from the fields
     *
     * @return array
     */
    public function pluckKey($property = 'name')
    {
        $names = [];

        if ($this->hasFields()) {
            foreach ($this->getFields() as $field) {

                if (property_exists($field, $property)) {
                    $names[] = $field->{$property};
                }
            }
        }

        return $names;
    }

    /**
     * Checks if the resources has relations
     *
     * @return int
     */
    public function hasRelations()
    {
        return $this->totalRelations() > 0;
    }

    /**
     * Checks if the resources has indexes
     *
     * @return int
     */
    public function hasIndexes()
    {
        return $this->totalIndexes() > 0;
    }

    /**
     * Get the total available fields in he fields collection.
     *
     * @return int
     */
    public function totalFields()
    {
        return count($this->fields);
    }

    /**
     * Get the total available relations in he relation collection.
     *
     * @return int
     */
    public function totalRelations()
    {
        return count($this->fields);
    }

    /**
     * Get the total available indexes in he indexes collection.
     *
     * @return int
     */
    public function totalIndexes()
    {
        return count($this->fields);
    }

    /**
     * Converts the object into a json-ready array
     *
     * @return array
     */
    public function toArray()
    {
        return
            [
            'fields' => $this->getFieldsToArray(),
            'relations' => $this->getRelationsToArray(),
            'indexes' => $this->getIndexesToArray(),
        ];
    }

    /**
     * Get the fields
     *
     * @return array
     */
    public function getFields()
    {
        return $this->fields ?: [];
    }

    /**
     * Converts the fields into a json-ready array
     *
     * @return array
     */
    public function getFieldsToArray()
    {
        return array_map(function ($field) {
            return $field->toArray();
        }, $this->fields);
    }

    /**
     * Converts the relations into a json-ready array
     *
     * @return array
     */
    public function getRelationsToArray()
    {
        return array_map(function ($relation) {
            return $relation->toArray();
        }, $this->relations);
    }

    /**
     * Converts the indexes into a json-ready array
     *
     * @return array
     */
    public function getIndexesToArray()
    {
        return array_map(function ($index) {
            return $index->toArray();
        }, $this->indexes);
    }

    /**
     * It transfres a giving string to a collection of field
     *
     * @param string|json $json
     * @param string $localeGroup
     * @param array $languages
     *
     * @return CrestApps\CodeGenerator\Models\Resource
     */
    public static function fromFile($filename, $localeGroup, array $languages = [])
    {
        $content = self::jsonFileContent($filename);

        return self::fromJson($content, $localeGroup, $languages);
    }

    /**
     * It transfres a giving JSON string to a collection of field
     *
     * @param string|json $json
     * @param string $localeGroup
     * @param array $languages
     *
     * @return CrestApps\CodeGenerator\Models\Resource
     */
    public static function fromJson($json, $localeGroup, array $languages = [])
    {
        if (empty($json) || ($capsule = json_decode($json, true)) === null) {
            throw new Exception("The provided string is not a valid json.");
        }

        if (is_array($capsule) && !Helpers::isAssociative($capsule)) {
            // At this point we know the resource file is using old convention
            // Set the fields
            $fields = FieldTransformer::fromArray($capsule, $localeGroup, $languages, true);

            return new Resource($fields);
        }

        $resource = new Resource();

        if (array_key_exists('fields', $capsule)) {
            $resource->fields = FieldTransformer::fromArray($capsule['fields'], $localeGroup, $languages, true);
        }

        if (array_key_exists('relations', $capsule)) {
            $resource->relations = self::getRelations($capsule['relations']);
        }

        if (array_key_exists('indexes', $capsule)) {
            $resource->indexes = self::getIndexes($capsule['indexes']);
        }

        return $resource;
    }

    /**
     * It transfres a gving array to a resource
     *
     * @param array $properties
     * @param string $localeGroup
     * @param array $languages
     *
     * @return CrestApps\CodeGenerator\Models\Resource
     */
    public static function fromArray(array $properties, $localeGroup, array $languages = [])
    {
        $resource = new Resource();

        if (array_key_exists('fields', $properties) && is_array($properties['fields'])) {
            $resource->fields = FieldTransformer::fromArray($properties['fields'], $localeGroup, $languages, true);
        }

        if (array_key_exists('relations', $properties) && is_array($properties['relations'])) {
            $resource->relations = self::getRelations($properties['relations']);
        }

        if (array_key_exists('indexes', $properties) && is_array($properties['indexes'])) {
            $resource->indexes = self::getIndexes($properties['indexes']);
        }

        return $resource;
    }

    /**
     * Gets a relation collection from a peoperties collection.
     *
     * @param $filename
     *
     * @return string
     */
    protected static function getRelations(array $properties)
    {
        $relations = [];

        foreach ($properties as $property) {
            if (!is_null($relation = ForeignRelationship::get($property))) {
                $relations[] = $relation;
            }
        }

        return $relations;
    }

    /**
     * Gets a relation collection from a peoperties collection.
     *
     * @param $filename
     *
     * @return string
     */
    protected static function getIndexes(array $properties)
    {
        $indexes = [];

        foreach ($properties as $property) {
            $indexes[] = Index::get($property);
        }

        return $indexes;
    }

    /**
     * Gets the content of a json file.
     *
     * @param $filename
     *
     * @return string
     */
    public static function jsonFileContent($filename)
    {
        $fileFullname = Config::getResourceFilePath($filename);

        if (!File::exists($fileFullname)) {
            throw new Exception('The file ' . $fileFullname . ' was not found!');
        }

        return File::get($fileFullname);
    }
}
