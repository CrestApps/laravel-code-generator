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
     *
     * @return CrestApps\CodeGenerator\Models\Resource
     */
    public static function fromFile($filename, $localeGroup)
    {
        $content = self::jsonFileContent($filename);

        return self::fromJson($content, $localeGroup);
    }

    /**
     * It transfres a giving JSON string to a collection of field
     *
     * @param string|json $json
     * @param string $localeGroup
     *
     * @return CrestApps\CodeGenerator\Models\Resource
     */
    public static function fromJson($json, $localeGroup)
    {
        if (empty($json) || ($capsule = json_decode($json, true)) === null) {
            throw new Exception("The provided string is not a valid json.");
        }

        if (is_array($capsule) && !Helpers::isAssociative($capsule)) {
            // At this point we know the resource file is using old convention
            // Set the fields
            $fields = FieldTransformer::fromArray($capsule, $localeGroup);

            return new Resource($fields);
        }

        $resource = new Resource();

        if (array_key_exists('fields', $capsule)) {
            $resource->fields = FieldTransformer::fromArray($capsule['fields'], $localeGroup);
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
     *
     * @return CrestApps\CodeGenerator\Models\Resource
     */
    public static function fromArray(array $properties, $localeGroup)
    {
        $resource = new Resource();

        if (array_key_exists('fields', $properties) && is_array($properties['fields'])) {
            $resource->fields = FieldTransformer::fromArray($properties['fields'], $localeGroup);
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
