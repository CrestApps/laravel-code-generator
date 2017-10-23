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
use Exception;

abstract class ParserBase
{
    use CommonCommand;

    /**
     * List of the field to exclude from all views.
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
     * The default boolean options to use.
     *
     * @var array
     */
    protected $booleanOptions = [
        '0' => 'No',
        '1' => 'Yes',
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
     * The langugaes to create labels form.
     *
     * @var array
     */
    protected $langugaes;

    /**
     * Creates a new field instance.
     *
     * @param string $tableName
     * @param string $databaseName
     * @param array $langugaes
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
            $columns = $this->getColumn();

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
        $resource = new Resource($this->getFields());
        $resource->indexes = $this->getIndexes();
        $resource->relations = $this->getRelations();

        return $resource;
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
     * @param array $columns
     *
     * @return array
     */
    protected function transfer(array $columns)
    {
        $fields = array_map(function ($field) {
            return new FieldMapper($field);
        }, $this->getTransfredFields($columns));

        $optimizer = new FieldsOptimizer($fields);

        return $optimizer->optimize()->getFields();
    }

    /**
     * Get the html type for a giving field.
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
     * Set the html type for a giving field.
     *
     * @param CrestApps\CodeGenerator\Models\Field $field
     * @param string $type
     *
     * @return string
     */
    protected function setHtmlType(array &$fields)
    {
        foreach ($fields as $field) {
            $field->htmlType = $this->getHtmlType($field->getEloquentDataMethod());
        }
    }

    /**
     * Gets the models namespace
     *
     * @return string
     */
    protected function getModelNamespace()
    {
        return $this->getAppNamespace() . Config::getModelsPath();
    }

    /**
     * Gets the model's name from a giving table name
     *
     * @param string $tableName
     *
     * @return string
     */
    protected function getModelName($tableName)
    {
        $modelName = ResourceMapper::pluckFirst($tableName, 'table-name', 'model-name');

        return $modelName ?: ucfirst(camel_case(Str::singular($tableName)));
    }

    /**
     * Gets column meta info from the information schema.
     *
     * @return array
     */
    abstract protected function getColumn();

    /**
     * Transfers every column in the giving array to a collection of fields.
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
