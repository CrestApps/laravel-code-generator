<?php
namespace CrestApps\CodeGenerator\DatabaseParsers;

use DB;
use App;
use Exception;
use CrestApps\CodeGenerator\Models\Field;
use CrestApps\CodeGenerator\Models\FieldMapper;
use CrestApps\CodeGenerator\Support\FieldsOptimizer;
use CrestApps\CodeGenerator\Support\FieldTransformer;
use CrestApps\CodeGenerator\Traits\CommonCommand;
use CrestApps\CodeGenerator\Support\Config;

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
        'deleted_at'
    ];

    /**
     * The default boolean options to use.
     *
     * @var array
     */
    protected $booleanOptions = [
        '0' => 'No',
        '1' => 'Yes'
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
    public function getFields()
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
     * Set the html type for a giving field.
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

        return 'string';
    }

    /**
     * Checks if the request requires languages.
     *
     * @return bool
    */
    protected function hasLanguages()
    {
        return ! empty($this->languages);
    }

    /**
     * Gets the label(s) from a giving name
     *
     * @param string $name
     *
     * @return mix (string | array)
    */
    protected function getLabel($name)
    {
        if (!$this->hasLanguages()) {
            return $this->getLabelName($name);
        }
        $labels = [];
        $title = $this->getLabelName($name);

        foreach ($this->languages as $language) {
            $labels[$language] = $title;
        }

        return $labels;
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
     * Gets a labe field's label from a giving name.
     *
     * @return string
    */
    protected function getLabelName($name)
    {
        return trim(ucwords(str_replace(['-','_'], ' ', $name)));
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
}
