<?php

namespace CrestApps\CodeGenerator\Models;

use CrestApps\CodeGenerator\Support\Helpers;

class ViewInput
{
    /**
     * The provided modelName
     *
     * @var string
     */
    public $modelName;

    /**
     * The provided field's file name
     *
     * @var string
     */
    public $resourceFile;

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
        $this->resourceFile = trim($options['resource-file']) ?: Helpers::makeJsonFileName($this->modelName);
        $this->viewsDirectory = trim($options['views-directory']);
        $this->prefix = trim($options['routes-prefix']);
        $this->force = $options['force'];
        $this->languageFileName = trim($options['lang-file-name']) ?: Helpers::makeLocaleGroup($this->modelName);
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
            'model-name'          => $this->modelName,
            '--resource-file'     => $this->resourceFile,
            '--views-directory'   => $this->viewsDirectory,
            '--routes-prefix'     => $this->prefix,
            '--lang-file-name'    => $this->languageFileName,
            '--layout-name'       => $this->layout,
            '--template-name'     => $this->template,
            '--force'             => $this->force,
        ];
    }
}
