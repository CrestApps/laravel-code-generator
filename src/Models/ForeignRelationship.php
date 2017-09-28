<?php

namespace CrestApps\CodeGenerator\Models;

use CrestApps\CodeGenerator\Support\Config;
use CrestApps\CodeGenerator\Support\Contracts\JsonWriter;
use CrestApps\CodeGenerator\Support\Helpers;
use CrestApps\CodeGenerator\Support\Str;
use DB;
use Exception;
use Illuminate\Database\Eloquent\Model;

class ForeignRelationship implements JsonWriter
{
    /**
     * The allowed relation types.
     *
     * @var array
     */
    public static $allowedTypes =
        [
        'hasOne',
        'belongsTo',
        'hasMany',
        'belongsToMany',
        'hasManyThrough',
        'morphTo',
        'morphMany',
        'morphToMany',
    ];

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
        $this->parameters = (array) $parameters;
        $this->name = $name;
        $this->setField($field);
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

        $this->type = $type;
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
        return in_array($type, self::$allowedTypes);
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
        $columns = $this->getModelColumns();
        $names = Config::getHeadersPatterns();

        foreach ($columns as $column) {
            $matchedPattern = '';
            if (Helpers::strIs($names, $column, $matchedPattern)) {
                return $column;
            }
        }

        $primary = $this->getPrimaryKeyForForeignModel();
        $idPatterns = Config::getKeyPatterns();

        $columns = array_filter($columns, function ($column) use ($primary, $idPatterns) {
            return $column != $primary && !Helpers::strIs($idPatterns, $column);
        });

        if (count($columns) == 1) {
            return $columns[0];
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
     * Check if a giving class is an an instance of Model
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

        return 'id';
    }

    /**
     * Gets the foreign model columns.
     *
     * @return array
     */
    public function getModelColumns()
    {
        $model = $this->getForeignModelInstance();

        if ($this->isModel($model)) {
            $tableName = $model->getTable();
            return DB::getSchemaBuilder()->getColumnListing($tableName);
        }

        return [];
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
     * Get a foreign relationship from giving array
     *
     * @param array $options
     *
     * @return null | CrestApps\CodeGenerator\Model\ForeignRelationship
     */
    public static function get(array $options)
    {
        if (!array_key_exists('type', $options) || !array_key_exists('params', $options) || !array_key_exists('name', $options)) {
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
}
