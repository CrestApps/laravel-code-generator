<?php

namespace CrestApps\CodeGenerator\Models;

use CrestApps\CodeGenerator\Models\Resource;
use CrestApps\CodeGenerator\Support\Contracts\JsonWriter;
use Exception;

class MigrationCapsule implements JsonWriter
{
    /**
     * The migration filename without the extension
     *
     * @var string
     */
    public $name;

    /**
     * The migration classname
     *
     * @var string
     */
    public $className;

    /**
     * The migration path
     *
     * @var string
     */
    public $path;

    /**
     * Is this a create migration
     *
     * @var bool
     */
    public $isCreate = false;

    /**
     * Is this a virtual migration or not
     * A virtual migration is a one that does not have an actual migration
     *
     * @var bool
     */
    public $isVirtual = false;

    /**
     * The resources associated with the migration
     *
     * @var CrestApps\CodeGenerator\Models\Resource
     */
    public $resource;

    /**
     * Create a new input instance.
     *
     * @return void
     */
    public function __construct(array $properties = [])
    {
        if (!isset($properties['name']) || empty($properties['name'])) {
            throw new Exception('A migration name is required to construct a migration capsule!');
        }

        $this->name = str_replace('.php', '', $properties['name']);

        if (isset($properties['path'])) {
            $this->path = $properties['path'];
        }

        if (isset($properties['resource']) && is_array($properties['resource'])) {
            $this->resource = Resource::fromArray($properties['resource'], 'migration');
        }

        if (isset($properties['is-create'])) {
            $this->isCreate = (bool) $properties['is-create'];
        }

        if (isset($properties['is-virtual'])) {
            $this->isVirtual = (bool) $properties['is-virtual'];
        }

        if (isset($properties['class-name'])) {
            $this->className = $properties['class-name'];
        }
    }

    /**
     * Sets the instance of resource
     *
     * @return void
     */
    public function setResource(Resource $resource)
    {
        $this->resource = $resource;
    }

    /**
     * Gets array of the paramets
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'name' => $this->name,
            'class-name' => $this->className,
            'path' => $this->path,
            'is-create' => $this->isCreate,
            'is-virtual' => $this->isVirtual,
            'resource' => ($this->resource ?: new Resource())->toArray(),
        ];
    }

    /**
     * Gets an instance of Migration Capsule using the giving name
     *
     * @param string $name
     *
     * @return CrestApps\CodeGenerator\Models\MigrationCapsule
     */
    public static function get($name)
    {
        return new MigrationCapsule(['name' => $name]);
    }

}
