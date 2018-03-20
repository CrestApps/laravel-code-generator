<?php
namespace CrestApps\CodeGenerator\DatabaseParsers;

use App;
use CrestApps\CodeGenerator\Models\Field;
use CrestApps\CodeGenerator\Models\FieldMapper;
use CrestApps\CodeGenerator\Models\Resource;
use CrestApps\CodeGenerator\Support\Config;
use CrestApps\CodeGenerator\Support\FieldsOptimizer;
use CrestApps\CodeGenerator\Support\Helpers;
use CrestApps\CodeGenerator\Support\ResourceMapper;
use CrestApps\CodeGenerator\Support\Str;
use CrestApps\CodeGenerator\Traits\CommonCommand;
use CrestApps\CodeGenerator\Traits\ModelTrait;
use Exception;

abstract class ParserBase
{
    use CommonCommand, ModelTrait;

    /**
     * List of fields to be excluded from all views.
     *
     * @var array
     */
    protected $exclude = [
        'id',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    /**
     * The table name.
     *
     * @var array
     */
    protected $tableName;

    /**
     * The databasename
     *
     * @var array
     */
    protected $databaseName;

    /**
     * The locale value
     *
     * @var array
     */
    protected $locale;

    /**
     * The final fields.
     *
     * @var array
     */
    protected $fields;

    /**
     * The languages to create labels form.
     *
     * @var array
     */
    protected $languages;

    /**
     * Creates a new field instance.
     *
     * @param string $tableName
     * @param string $databaseName
     * @param array $languages
     *
     * @return void
     */
    public function __construct($tableName, $databaseName, array $languages = [])
    {
        $this->tableName = $tableName;
        $this->databaseName = $databaseName;
        $this->languages = $languages;
        $this->locale = App::getLocale();
    }

    /**
     * Gets the final fields.
     *
     * @return array
     */
    protected function getFields()
    {
        if (is_null($this->fields)) {
            $columns = $this->getColumns();

            if (empty($columns)) {
                throw new Exception('The table ' . $this->tableName . ' was not found in the ' . $this->databaseName . ' database.');
            }

            $this->fields = $this->transfer($columns);
        }

        return $this->fields;
    }

    /**
     * Gets the final resource.
     *
     * @return CrestApps\CodeGenerator\Models\Resource
     */
    public function getResource()
    {
        $fields = $this->getFields();
        $autoManage = $this->containsUpdateAtAndCreatedAt($fields);
        $resource = new Resource($fields, $this->getRelations(), $this->getIndexes(), $autoManage);
        $resource->setTableName($this->tableName);

        return $resource;
    }

    /**
     * Check if the given fields contains autoManagedFields
     *
     * @return CrestApps\CodeGenerator\Models\Resource
     */
    protected function containsUpdateAtAndCreatedAt($fields)
    {
        $autoManagedFields = array_filter($fields, function ($field) {
            return $field->isAutoManagedOnUpdate();
        });

        return count($autoManagedFields) == 2;
    }
    /**
     * Gets the final resource.
     *
     * @return CrestApps\CodeGenerator\Models\Resource
     */
    public function getResourceAsJson()
    {
        $resource = $this->getResource();

        return Helpers::prettifyJson($resource->toArray());
    }

    /**
     * Gets array of field after transfering each column meta into field.
     *
     * @param array $fields
     *
     * @return array
     */
    protected function transfer(array $fields)
    {
        $mappers = array_map(function ($field) {
            return new FieldMapper($field);
        }, $this->getTransfredFields($fields));

        $optimizer = new FieldsOptimizer($mappers);

        return $optimizer->optimize()->getFields();
    }

    /**
     * Get the html type for a given field.
     *
     * @param CrestApps\CodeGenerator\Models\Field $field
     * @param string $type
     *
     * @return string
     */
    protected function getHtmlType($type)
    {
        $map = Config::getEloquentToHtmlMap();

        if (array_key_exists($type, $map)) {
            return $map[$type];
        }

        return 'text';
    }

    /**
     * Set the html type for a given field.
     *
     * @param CrestApps\CodeGenerator\Models\Field $field
     * @param string $type
     *
     * @return $this
     */
    protected function setHtmlType(array &$fields)
    {
        foreach ($fields as $field) {
            $field->htmlType = $this->getHtmlType($field->getEloquentDataMethod());
        }

        return $this;
    }

    /**
     * Gets the model's name from a given table name
     *
     * @param string $tableName
     *
     * @return string
     */
    protected function getModelName($tableName)
    {
        $modelName = ResourceMapper::pluckFirst($tableName, 'table-name', 'model-name');

        return $modelName ?: $this->makeModelName($tableName);
    }

    /**
     * Make a model name from the given table name
     *
     * @param string $tableName
     *
     * @return string
     */
    protected function makeModelName($tableName)
    {
        $name = Str::singular($tableName);

        return ucfirst(camel_case($name));
    }

    /**
     * Gets column meta info from the information schema.
     *
     * @return array
     */
    abstract protected function getColumns();

    /**
     * Transfers every column in the given array to a collection of fields.
     *
     * @return array of CrestApps\CodeGenerator\Models\Field;
     */
    abstract protected function getTransfredFields(array $columns);

    /**
     * Get all available indexed
     *
     * @return array of CrestApps\CodeGenerator\Models\Index;
     */
    abstract protected function getIndexes();

    /**
     * Get all available relations
     *
     * @return array of CrestApps\CodeGenerator\Models\ForeignRelationship;
     */
    abstract protected function getRelations();
}
