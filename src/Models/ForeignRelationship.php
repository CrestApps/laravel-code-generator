<?php

namespace CrestApps\CodeGenerator\Models;

use CrestApps\CodeGenerator\Support\Arr;
use CrestApps\CodeGenerator\Support\Config;
use CrestApps\CodeGenerator\Support\Contracts\JsonWriter;
use CrestApps\CodeGenerator\Support\Helpers;
use CrestApps\CodeGenerator\Support\ResourceMapper;
use CrestApps\CodeGenerator\Support\Str;
use CrestApps\CodeGenerator\Traits\ModelTrait;
use DB;
use Exception;
use File;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations;

class ForeignRelationship implements JsonWriter
{
    use ModelTrait;

    /**
     * The type of the relation.
     *
     * @var string
     */
    private $type;

    /**
     * The parameters that the type's method accepts.
     *
     * @var array
     */
    public $parameters = [];

    /**
     * The name of the property/field's name on the foreign model to represent the field on display.
     *
     * @var string
     */
    private $field;

    /**
     * The name of the foreign relation.
     *
     * @var string
     */
    public $name;

    /**
     * Instance of the foreign model.
     *
     * @var Illuminate\Database\Eloquent\Model
     */
    private $foreignModel;

    /**
     * Creates a new field instance.
     *
     * @param string $type
     * @param string|array $parameters
     * @param string $name
     * @param string $field
     *
     * @return void
     */
    public function __construct($type, $parameters, $name, $field = null)
    {
        $this->setType($type);
        $this->setParameters($parameters);
        $this->name = $name;
        $this->setField($field);
    }

    /**
     * Sets the parameters for the relation
     *
     * @return void
     */
    public function setParameters($parameters)
    {
        $this->parameters = [];

        $this->parameters = [];
		
        if(!is_array($parameters)){
            $parameters = Arr::fromString($parameters, '|');
        }
		
        foreach ($parameters as $parameter) {
            $this->parameters[] = Str::eliminateDupilcates($parameter, "\\");
        }
    }

    /**
     * Checks if the relation of a single type.
     *
     * @return bool
     */
    public function isSingleRelation()
    {
        return in_array($this->type, [
            'hasOne',
            'belongsTo',
            'morphTo',
        ]);
    }

    /**
     * Sets the type of the relation
     *
     * @param string $type
     *
     * @return void
     */
    public function setType($type)
    {
        if (!self::isValidType($type)) {
            throw new OutOfRangeException();
        }

        $this->type = lcfirst($type);
    }

    /**
     * Check if the relation's type is valid.
     *
     * @param string $type
     *
     * @return bool
     */
    public static function isValidType($type)
    {
        $class = sprintf('Illuminate\Database\Eloquent\Relations\%s', ucfirst($type));

        return is_subclass_of($class, 'Illuminate\Database\Eloquent\Relations\Relation');
    }

    /**
     * Sets the name column name of the foreign relation
     *
     * @param string $name
     *
     * @return void
     */
    public function setField($name)
    {
        $this->field = $name;
    }

    /**
     * Get the foreign field name.
     *
     * @return string
     */
    public function getField()
    {
        if (empty($this->field)) {
            $this->field = $this->guessForeignField();
        }

        return $this->field;
    }

    /**
     * Get the relation name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Guesses the name of the foreign key.
     *
     * @return string
     */
    protected function guessForeignField()
    {
        // First we try to find a column that match the header pattern
        $columns = $this->getModelColumns();
        $names = Config::getHeadersPatterns();

        foreach ($columns as $column) {
            if (Str::match($names, $column)) {
                // At this point a column that match the header patter was found
                return $column;
            }
        }

        // At this point we know no column that match the header patters found.
        // Second we try to find a non-primary/non-foreign key column
        $primary = $this->getPrimaryKeyForForeignModel();
        $idPatterns = Config::getKeyPatterns();
        foreach ($columns as $column) {
            if ($column != $primary && !Str::match($idPatterns, $column)) {
                return $column;
            }
        }

        return $primary;
    }

    /**
     * Gets the relation's type.
     *
     * @param string $name
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Gets the name of the collection.
     *
     * @return string
     */
    public function getCollectionName()
    {
        return Str::plural($this->name);
    }

    /**
     * Gets the name of an item in the collection.
     *
     * @return string
     */
    public function getSingleName()
    {
        return Str::singular($this->name);
    }

    /**
     * Gets the foreign model's full name.
     *
     * @return string
     */
    public function getFullForeignModel()
    {
        return current($this->parameters);
    }

    /**
     * Gets the foreign model name.
     *
     * @return string
     */
    public function getForeignModel()
    {
        $model = $this->getFullForeignModel();

        if ($this->isModel($model)) {
            return class_basename($model);
        }

        $position = strrpos($model, '\\');

        if ($position !== false) {
            return substr($model, $position + 1);
        }

        return '';
    }

    /**
     * Check if a given class is an an instance of Model
     *
     * @return bool
     */
    protected function isModel($model)
    {
        return $model instanceof Model;
    }

    /**
     * Gets the name of the foreign model's primary key.
     *
     * @return sting
     */
    public function getPrimaryKeyForForeignModel()
    {
        $model = $this->getForeignModelInstance();

        if ($this->isModel($model)) {
            return $model->getKeyName();
        }

        return $this->getKeyNameFromResource() ?: 'id';
    }

    /**
     * Gets the foreign model columns.
     *
     * @return array
     */
    public function getModelColumns()
    {
        $model = $this->getForeignModelInstance();
        $columns = [];
        if ($this->isModel($model)) {
            // At this point we know a model class exists
            // Try to get the database column listing from the database directly
            $tableName = $model->getTable();
            $columns = DB::getSchemaBuilder()->getColumnListing($tableName);
        }

        if (count($columns) == 0) {
            // At this poing we know the column have not yet been identified
            // which also mean that the model does not exists or the table
            // does not existing in the database.
            // Try to find the columns from the resource-file if one found.
            $columns = $this->getFieldNamesFromResource();
        }

        return $columns;
    }

    /**
     * Gets the foreign model columns from the resource file if one exists
     *
     * @return null | string
     */
    protected function getKeyNameFromResource()
    {
        $resource = $this->getForeignResource();

        if (!is_null($resource) && (($field = $resource->getPrimaryField()) != null)) {
            return $field->name;
        }

        return null;
    }

    /**
     * Gets the foreign model columns from the resource file if one exists
     *
     * @return array
     */
    protected function getFieldNamesFromResource()
    {
        $resource = $this->getForeignResource();

        if (!is_null($resource)) {
            return $resource->pluckFields();
        }

        return [];
    }

    /**
     * Gets the foreign model fields from resource file
     *
     * @return mix (null | CrestApps\CodeGenerator\Models\Resource)
     */
    protected function getForeignResource()
    {
        $modelName = $this->getForeignModelName();
        // Find the resource file from the resource-map or make a standard name.
        $resourceFile = ResourceMapper::pluckFirst($modelName) ?: Helpers::makeJsonFileName($modelName);

        if (File::exists(Config::getResourceFilePath($resourceFile))) {
            return Resource::fromFile($resourceFile, 'crestapps');
        }

        return null;
    }

    /**
     * Gets the foreign model's class name
     *
     * @return string
     */
    protected function getForeignModelName()
    {
        return class_basename($this->getFullForeignModel());
    }

    /**
     * Gets a single instance of the foreign mode.
     *
     * @return Illuminate\Database\Eloquent\Model
     */
    private function getForeignModelInstance()
    {
        try {
            if (!$this->foreignModel) {
                $model = $this->getFullForeignModel();

                if (class_exists($model)) {
                    $this->foreignModel = new $model();
                } else {
                    $this->foreignModel = '';
                }
            }

            return $this->foreignModel;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Gets the relation in an array format.
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'name' => $this->name,
            'type' => $this->getType(),
            'params' => $this->parameters,
            'field' => $this->getField(),
        ];
    }

    /**
     * Get a foreign relationship from given array
     *
     * @param array $options
     * @throws Exception
     *
     * @return mix (null | CrestApps\CodeGenerator\Model\ForeignRelationship)
     */
    public static function get(array $options)
    {
        if (!self::isValid($options)) {
			if(count($options) >= 3) {
				$values = array_values($options);
				$field = isset($values[3]) ? $values[3] : null;
				return new ForeignRelationship(
					$values[1],
					$values[2],
					$values[0],
					$field
				);				
			}
			
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
     * Get a foreign relationship from given array
     *
     * @param array $options
     *
     * @return boolean
     */
    public static function isValid(array $options)
    {
        return Arr::isKeyExists($options, 'name', 'type', 'params');
    }

    /**
     * Get a foreign relationship from given string
     *
     * @param string $rawRelation
     *
     * @return null | CrestApps\CodeGenerator\Model\ForeignRelationship
     */
    public static function fromString($rawRelation)
    {
        //name:assets;type:hasMany;params:App\\Models\\Asset|category_id|id,
        // expected string
        //name|type|params|field
        //assets|hasMany|App\\Models\\Asset,category_id,id|title

        $parts = explode(';', $rawRelation);
        $collection = [];
        foreach ($parts as $part) {
            if (!str_contains($part, ':')) {
                continue;
            }

            list($key, $value) = Str::split([':', '='], $part);

            if (($isParams = in_array($key, ['params', 'param'])) || str_contains($value, '|')) {
                $value = explode('|', $value);

                if ($isParams) {
                    $key = 'params';
                }
            }

            $collection[$key] = $value;
        }

        if (!self::isValid($collection)) {
            throw new Exception('Each relation must be in the following format "name:assets;type:hasMany;params:App\\Models\\Asset|category_id|id"');

        }

        return self::get($collection);
    }

    /**
     * Get a predictable foreign relation using the given field's name
     *
     * @param string $fieldName
     * @param string $modelPath
     *
     * @return null | CrestApps\CodeGenerator\Model\ForeignRelationship
     */
    public static function predict($fieldName, $modelPath)
    {
        $patterns = Config::getKeyPatterns();

        if (Str::match($patterns, $fieldName)) {
            $relationName = self::makeRelationName($fieldName);
            $model = self::guessModelFullName($fieldName, $modelPath);
            $parameters = [$model, $fieldName];

            return new self('belongsTo', $parameters, $relationName);
        }

        return null;
    }

    /**
     * Makes a relation name from the given field name
     *
     * @param string $fieldName
     *
     * @return string
     */
    public static function makeRelationName($fieldName)
    {
        $modelName = self::extractModelName($fieldName);

        return camel_case($modelName);
    }
}
