<?php

namespace CrestApps\CodeGenerator\Models;

class ViewInput
{
    /**
     * The provided modelName
     *
     * @var string
     */
	public $modelName;

    /**
     * The provided fields
     *
     * @var string
     */
	public $fields;

    /**
     * The provided field's file name
     *
     * @var string
     */
    public $fieldsFile;

    /**
     * The provided views directory name
     *
     * @var string
     */
	public $viewsDirectory;

    /**
     * The provided route's pre-fix
     *
     * @var string
     */
	public $prefix;

    /**
     * Overrides existing view.
     *
     * @var string
     */
    public $force;

    /**
     * The provided language's file name
     *
     * @var string
     */
    public $languageFileName;

     /**
     * The provided layout name
     *
     * @var string
     */
    public $layout;

    /**
     * Create a new input instance.
     *
     * @return void
     */
	public function __construct(array $arguments, array $options = [])
	{
        $this->modelName = trim($arguments['model-name']);
        $this->fields = trim($options['fields']);
        $this->fieldsFile = trim($options['fields-file']);
        $this->viewsDirectory = trim($options['views-directory']);
        $this->prefix = trim($options['routes-prefix']);
        $this->force = $options['force'];
        $this->languageFileName = strtolower(str_plural($this->modelName));
        $this->layout = trim($options['layout-name']);
        $this->template = trim($options['template-name']);
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
            '--fields' => $this->fields, 
            '--fields-file' => $this->fieldsFile,
            '--views-directory' => $this->viewsDirectory,
            '--routes-prefix' => $this->prefix,
            '--force' => $this->force,
            '--layout-name' => $this->layout,
            '--template-name' => $this->template
        ];
    }

}