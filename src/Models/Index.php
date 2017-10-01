<?php

namespace CrestApps\CodeGenerator\Models;

use CrestApps\CodeGenerator\Support\Contracts\JsonWriter;
use Exception;

class Index implements JsonWriter
{
    /**
     * The default index type.
     *
     * @var string
     */
    protected $defaultIndexType = 'index';

    /**
     * The index types eloquent is capable of creating.
     *
     * @var string
     */
    protected $validIndexTypes = ['index', 'unique', 'primary'];

    /**
     * The type of the index.
     *
     * @var string
     */
    protected $type;

    /**
     * The name of the index.
     *
     * @var string
     */
    protected $name;

    /**
     * The name of the columns.
     *
     * @var array
     */
    protected $columns = [];

    /**
     * If the index is flagged to be deleted during migration changes.
     *
     * @var bool
     */
    public $flaggedForDelete = false;

    /**
     * Create a new index instance.
     *
     * @param string $name
     *
     * @return void
     */
    public function __construct($name = null)
    {
        $this->setName($name);
    }

    /**
     * Checks if the type is a valid type.
     *
     * @var bool
     */
    public function isValidType($type)
    {
        return in_array($type, $this->validIndexTypes);
    }

    /**
     * Gets the index type.
     *
     * @var string
     */
    public function getType()
    {
        return $this->type ?: $this->defaultIndexType;
    }

    /**
     * Checks if the type is a valid type.
     *
     * @var bool
     */
    public function getColumns()
    {
        return $this->columns;
    }

    /**
     * Get the first column.
     *
     * @var string | null
     */
    public function getFirstColumn()
    {
        $columns = $this->getColumns();

        if (isset($columns[0])) {
            return $columns[0];
        }

        return null;
    }

    /**
     * Adds a column to the index
     *
     * @var void
     */
    public function addColumn($name)
    {
        if (!in_array($name, $this->columns)) {
            $this->columns[] = $name;
        }
    }

    /**
     * Adds a columns to the index
     *
     * @var void
     */
    public function addColumns(array $names)
    {
        foreach ($names as $name) {
            $this->addColumn($name);
        }
    }

    /**
     * Checks if the index has at least one columns.
     *
     * @var bool
     */
    public function hasColumns()
    {
        return !empty($this->getColumns());
    }

    /**
     * Checks if the index has multiple columns.
     *
     * @var bool
     */
    public function hasMultipleColumns()
    {
        return $this->getTotalColumns() > 1;
    }

    /**
     * Get the total available columns
     *
     * @var int
     */
    public function getTotalColumns()
    {
        return count($this->getColumns());
    }

    /**
     * Sets the name of the index.
     *
     * @var void
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Gets the name of the index.
     *
     * @var void
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Checks if the index has a defined name.
     *
     * @var void
     */
    public function hasName()
    {
        return !empty(trim($this->getName()));
    }

    /**
     * Converts the index to an array
     *
     * @var array
     */
    public function toArray()
    {
        $final = [
            'name' => $this->getName(),
            'type' => $this->getType(),
            'columns' => $this->getColumns(),
        ];

        return $final;
    }

    /**
     * Sets the type of the index.
     *
     * @var void
     */
    public function setType($type)
    {
        if (!$this->isValidType($type)) {
            throw new Exception('Invalid index type provided!');
        }

        $this->type = $type;
    }

    /**
     * Gets an index from an array.
     *
     * @var CrestApps\CodeGenerator\Models\Index
     */
    public static function get(array $properties)
    {
        if (!array_key_exists('columns', $properties)) {
            throw new Exception('An index requires at least one column.');
        }

        $index = new Index();

        foreach ((array) $properties['columns'] as $column) {
            $index->addColumn($column);
        }

        if (array_key_exists('name', $properties) && !empty($properties['name'])) {
            $index->setName($properties['name']);
        }

        if (array_key_exists('type', $properties) && !empty($properties['type'])) {
            $index->setType($properties['type']);
        }

        return $index;
    }

    /**
     * Gets an index from a raw string.
     *
     * @var CrestApps\CodeGenerator\Models\Index
     */
    public static function fromString($rawIndex)
    {
        // The expected string format is this
        //name:some_name;columns:full_name|last_name;type:index,

        $parts = explode(';', $rawIndex);

        foreach ($parts as $part) {
            if (!str_contains($part, ':')) {
                continue;
            }

            list($key, $value) = explode(':', $part);
            $collection[$key] = $value;
        }

        return self::get($collection);
    }
}
