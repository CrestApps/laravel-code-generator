<?php

namespace CrestApps\CodeGenerator\Models;

use CrestApps\CodeGenerator\Models\ForeignRelationship;
use CrestApps\CodeGenerator\Models\Index;
use CrestApps\CodeGenerator\Models\Relation;
use CrestApps\CodeGenerator\Support\Arr;
use CrestApps\CodeGenerator\Support\Config;
use CrestApps\CodeGenerator\Support\Contracts\JsonWriter;
use CrestApps\CodeGenerator\Support\FieldTransformer;
use CrestApps\CodeGenerator\Traits\CommonCommand;
use CrestApps\CodeGenerator\Traits\GeneratorReplacers;
use CrestApps\CodeGenerator\Traits\LabelTransformerTrait;
use Exception;
use File;

class Resource implements JsonWriter
{
    use CommonCommand, GeneratorReplacers, LabelTransformerTrait;

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
     * Should eloquent auto manage the created_at and updated_at fields or not.
     *
     * @var bool
     */
    protected $manageCreateAndUpdateAt = true;

    /**
     * The name of the database's table name.
     *
     * @var string
     */
    private $tableName;

    /**
     * Array of the protected resources.
     *
     * @var array
     */
    private $protection = [];

    /**
     * Array of the protected resources.
     *
     * @var array
     */
    private $apiDocumentationLabels = [];

    /**
     * Array of the protectable resource names.
     *
     * @var array
     */
    protected $protectableResources = [
        'model',
        'controller',
        'api-resource',
        'api-resource-collection',
        'api-documentation',
        'api-documentation-controller',
        'form-request',
        'languages',
        'form-view',
        'index-view',
        'create-view',
        'edit-view',
        'show-view',
    ];

    /**
     * Create a new input instance.
     *
     * @return void
     */
    public function __construct(array $fields = [], array $relations = [], array $indexes = [], $manageCreateAndUpdateAt = true)
    {
        $this->fields = $fields;
        $this->relations = $relations;
        $this->indexes = $indexes;
        $this->manageCreateAndUpdateAt = $manageCreateAndUpdateAt;
    }

    /**
     * Checks if created_at and updated_at are auto managed by eloquent or not.
     *
     * @return bool
     */
    public function isCreateAndUpdateAtManaged()
    {
        return $this->manageCreateAndUpdateAt;
    }

    /**
     * Sets the database's table name.
     *
     * @return void
     */
    public function setTableName($name)
    {
        $this->tableName = $name;
    }

    /**
     * Gets the database's table name if one otherwise it returns the default value
     *
     * @param string $default
     *
     * @return string
     */
    public function getTableName($default = null)
    {
        return $this->tableName ?: $default;
    }

    /**
     * Sets the database's table name.
     *
     * @param string $name
     * @param bool $isProtected
     *
     * @return void
     */
    public function setProtection($name, $isProtected)
    {
        $key = $this->getProtectionKey($name);

        $this->protection[$key] = (bool) $isProtected;
    }

    /**
     * Gets a valid protection key
     *
     * @param string $name
     *
     * @return string
     */
    protected function getProtectionKey($name)
    {
        $key = str_is('is-*-protected', $name) ? $name : sprintf('is-%s-protected', $name);

        return $key;
    }

    /**
     * Get the protections collection.
     *
     * @return array
     */
    public function getProtections()
    {
        return $this->protection ?: [];
    }

    /**
     * Checks if the given resource is protected or not.
     *
     * @return bool
     */
    public function isProtected($name)
    {
        $key = $this->getProtectionKey($name);

        if (array_key_exists($key, $this->getProtections())) {
            return (bool) $this->getProtections()[$key];
        }

        return false;
    }

    /**
     * Checks if created_at and updated_at are auto managed by eloquent or not.
     *
     * @param bool $autoManage
     *
     * @return void
     */
    public function setCreateAndUpdateAtManaged($autoManage)
    {
        return $this->manageCreateAndUpdateAt = (bool) $autoManage;
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
        foreach ($this->fields as $field) {
            if ($field->isHeader()) {
                return $field;
            }
        }

        return null;
    }

    /**
     * Get the first primary field if available
     *
     * @return mix (null | CrestApps\CodeGenerator\Models\Field)
     */
    public function getPrimaryField()
    {
        foreach ($this->fields as $field) {
            if ($field->isPrimary()) {
                return $field;
            }
        }

        return null;
    }

    /**
     * Checks if a primary field is available or not.
     *
     * @return bool
     */
    public function hasPrimaryField()
    {
        return !is_null($this->getPrimaryField());
    }

    /**
     * Extracts the given property name from the fields
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
     * Extracts the given property name from the fields
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
     * Sets the api-doc labels.
     *
     * @param string $modelName
     * @param string $localeGroup
     * @param array $languages
     *
     * @return $this
     */
    public function setDefaultApiDocLabels($modelName, $localeGroup, array $languages = null)
    {

        foreach (Config::getApiDocumentationLabels() as $key => $text) {

            $this->replaceModelName($text, $modelName);

            if (!empty($languages)) {
                foreach ($languages as $language) {
                    $this->addApiDocLabel($text, $localeGroup, $key, false, $language);
                }

                continue;
            }

            $this->addApiDocLabel($text, $localeGroup, $key);
        }

        return $this;
    }

    /**
     * Adds the given key and value to the apiDocumentationLabels collection
     *
     * @param string $key
     * @param mix (string|array) $text
     *
     * @return void
     */
    public function addApiDocLabel($text, $localeGroup, $key, $isPlain = true, $lang = 'en')
    {
        $this->apiDocumentationLabels[$lang][] = new Label($text, $localeGroup, $isPlain, $lang, $key);
    }

    /**
     * Gets current api-doc-labels
     *
     * @return array
     */
    public function getApiDocLabels()
    {
        return $this->apiDocumentationLabels ?: [];
    }

    /**
     * Gets translatable api-doc-labels
     *
     * @return array
     */
    public function getTranslatedApiDocLabels()
    {
        $output = [];

        foreach ($this->getApiDocLabels() as $lang => $labels) {

            foreach ($labels as $label) {
                if (!$label->isPlain) {
                    $output[$lang][] = $label;
                }
            }

        }

        return $output;
    }

    /**
     * Converts the object into a json-ready array
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'fields' => $this->getFieldsToArray(),
            'relations' => $this->getRelationsToArray(),
            'indexes' => $this->getIndexesToArray(),
            'auto-manage-created-and-updated-at' => $this->isCreateAndUpdateAtManaged(),
            'table-name' => $this->getTableName(),
            'protection' => $this->getRawProtections(),
            'api-documentation' => $this->getRawApiDocLabels(),
        ];
    }

    /**
     * Converts the api-documentation labels object into json-ready array.
     *
     * @return array
     */
    protected function getRawApiDocLabels()
    {
        $output = [];

        foreach ($this->getApiDocLabels() as $lang => $labels) {

            foreach ($labels as $label) {
                if ($label->isPlain) {
                    $output[$label->id] = $label->text;

                    continue;
                }

                $output[$label->id][$label->lang] = $label->text;
            }

        }

        return $output;
    }

    /**
     * Converts the protection into a json-ready array
     *
     * @return array
     */
    protected function getRawProtections()
    {
        $protected = [];

        foreach ($this->protectableResources as $name) {
            $key = $this->getProtectionKey($name);

            $protected[$key] = $this->isProtected($key);
        }

        return $protected;
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
     * It transfres a given string to a collection of field
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
     * It transfres a given JSON string to a collection of field
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

        if (is_array($capsule) && !Arr::isAssociative($capsule)) {
            // At this point we know the resource file is` using old convention
            // Set the fields
            $fields = FieldTransformer::fromArray($capsule, $localeGroup, $languages, true);

            return new Resource($fields);
        }

        return self::fromArray($capsule, $localeGroup, $languages);
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

        if (array_key_exists('auto-manage-created-and-updated-at', $properties)) {
            $resource->setCreateAndUpdateAtManaged($properties['auto-manage-created-and-updated-at']);
        }

        if (array_key_exists('table-name', $properties)) {
            $resource->setTableName($properties['table-name']);
        }

        if (array_key_exists('protection', $properties) && is_array($properties['protection'])) {
            foreach ($properties['protection'] as $name => $value) {
                $resource->setProtection($name, $value);
            }
        }

        if (array_key_exists('api-documentation', $properties) && is_array($properties['api-documentation'])) {
            foreach ($properties['api-documentation'] as $key => $text) {
                if (is_array($text)) {

                    foreach ($text as $lang => $msg) {
                        $resource->addApiDocLabel($msg, $localeGroup, $key, false, $lang);
                    }
                    continue;
                }

                $resource->addApiDocLabel($text, $localeGroup, $key);
            }
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
