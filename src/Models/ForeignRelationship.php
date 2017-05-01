<?php

namespace CrestApps\CodeGenerator\Models;

use Exception;

class ForeignRelationship
{
    /**
     * The allowed relation types.
     *
     * @var array
     */
    private $allowedTypes =
    [
        'hasOne',
        'belongsTo',
        'hasMany',
        'belongsToMany',
        'hasManyThrough',
        'morphTo',
        'morphMany',
        'morphToMany'
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
    public $field;

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
    public function __construct($type, $parameters, $name, $field = 'id')
    {
        $this->setType($type);
        $this->parameters = (array) $parameters;
        $this->name = $name;
        $this->field = $field;
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
            'morphTo'
        ]);
    }


    /**
     * Sets the type of the relation
     *
     * @param string $name
     *
     * @return void
     */
    public function setType($type)
    {
        if (!in_array($type, $this->allowedTypes)) {
            throw new OutOfRangeException();
        }

        $this->type = $type;
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
        return str_plural($this->name);
    }

    /**
     * Gets the name of an item in the collection.
     *
     * @return string
     */
    public function getSingleName()
    {
        return str_singular($this->name);
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

        if ($model) {
            $index = strripos($model, '\\');

            if ($index !== false) {
                return substr($model, $index + 1);
            }
            
            return $model;
        }

        return '';
    }

    /**
     * Gets the name of the foreign model's primary key name.
     *
     * @return sting
     */
    public function getPrimaryKeyForForeignModel()
    {
        $model = $this->getForeignModelInstance();

        if ($model) {
            return $model->getKeyName();
        }

        return 'id';
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

                if ($model) {
                    $this->foreignModel = new $model();
                }
            }

            return $this->foreignModel;
        } catch (Exception $e) {
            return null;
        }
    }
}
