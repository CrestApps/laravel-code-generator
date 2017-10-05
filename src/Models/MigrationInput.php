<?php

namespace CrestApps\CodeGenerator\Models;

use CrestApps\CodeGenerator\Support\Helpers;

class MigrationInput
{
    /**
     * The provided modelName
     *
     * @var string
     */
    public $modelName;

    /**
     * The table name for the migration
     *
     * @var string
     */
    public $tableName;

    /**
     * The name of the connection
     *
     * @var string
     */
    public $connectionName;

    /**
     * The name of the engine to use
     *
     * @var string
     */
    public $engineName;

    /**
     * The resource file name
     *
     * @var string
     */
    public $resourceFile;

    /**
     * The tamplate to use
     *
     * @var string
     */
    public $template;

    /**
     * create migration with timestamps
     *
     * @var bool
     */
    public $withoutTimestamps = false;

    /**
     * Create migration with soft-delete
     *
     * @var bool
     */
    public $withSoftDelete = false;

    /**
     * Create a new input instance.
     *
     * @return void
     */
    public function __construct(array $arguments, array $options = [])
    {
        $this->modelName = trim($arguments['model-name']);
        $this->tableName = trim($options['table-name']) ?: Helpers::makeTableName($this->modelName);
        $this->connectionName = trim($options['connection-name']);
        $this->engineName = trim($options['engine-name']);
        $this->resourceFile = trim($options['resource-file']) ?: Helpers::makeJsonFileName($this->modelName);
        $this->template = trim($options['template-name']);
        $this->withoutTimestamps = $options['without-timestamps'];
        $this->withSoftDelete = $options['with-soft-delete'];
    }

    /**
     * Gets array of the paramets
     *
     * @return array
     */
    public function getArrguments()
    {
        return [
            'model-name' => $this->modelName,
            '--table-name' => $this->tableName,
            '--connection-name' => $this->connectionName,
            '--engine-name' => $this->engineName,
            '--resource-file' => $this->resourceFile,
            '--template-name' => $this->template,
            '--without-timestamps' => $this->withoutTimestamps,
            '--with-soft-delete' => $this->withSoftDelete,
        ];
    }
}
