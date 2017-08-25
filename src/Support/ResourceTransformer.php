<?php

namespace CrestApps\CodeGenerator\Support;

use File;
use Exception;
use CrestApps\CodeGenerator\Support\Config;
use CrestApps\CodeGenerator\Support\Helpers;
use CrestApps\CodeGenerator\Support\FieldTransformer;
use CrestApps\CodeGenerator\Models\Index;
use CrestApps\CodeGenerator\Models\Resource;
use CrestApps\CodeGenerator\Models\ForeignRelationship;

class ResourceTransformer
{
    /**
     * It transfres a gining string to a collection of field
     *
     * @param string|json $json
     * @param string $localeGroup
     *
     * @return array
    */
    public static function fromFile($filename, $localeGroup)
    {
        $content = self::jsonFileContent($filename);

        return self::fromJson($content, $localeGroup);
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
        $fileFullname = Config::pathToFieldFiles($filename);

        if (!File::exists($fileFullname)) {
            throw new Exception('The file ' . $fileFullname . ' was not found!');
        }

        return File::get($fileFullname);
    }
}
