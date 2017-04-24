<?php

namespace CrestApps\CodeGenerator\Models;

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
     * The name of the columns on the foreign model to represent the field on display.
     *
     * @var array
     */
    public $foreignColumns = [];

    /**
     * The name of the foreign relation.
     *
     * @var string
     */
    public $name;

    /**
     * Creates a new field instance.
     *
     * @param string $type
     * @param string|array $parameters
     * @param string $foreignName
     * @param string|array $foreignColumns
     *
     * @return void
     */
    public function __construct($type, $parameters, $foreignName, $foreignColumns = 'id')
    {
        $this->setType($type);
        $this->parameters = (array) $parameters;
        $this->name = $foreignName;
        $this->setColumns($foreignColumns);
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
     * Sets the foreign columns
     *
     * @param string|array $columns
     *
     * @return void
     */
    public function setColumns($columns)
    {
        if (!is_array($columns)) {
            $columns = (array) $columns;
        }

        foreach ($columns as $column) {
            $this->foreignColumns[] = $column;
        }
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
}
