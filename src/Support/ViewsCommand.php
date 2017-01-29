<?php

namespace CrestApps\CodeGenerator\Support;

use Illuminate\Console\GeneratorCommand;
use CrestApps\CodeGenerator\Traits\CommonCommand;
use Illuminate\Filesystem\Filesystem;
use CrestApps\CodeGenerator\Models\ViewInput;
use CrestApps\CodeGenerator\HtmlGenerators\LaravelCollectiveHtml;
use CrestApps\CodeGenerator\HtmlGenerators\StandardHtml;
use Exception;

abstract class ViewsCommand extends GeneratorCommand
{
    use CommonCommand;

    /**
     * The stub name
     *
     * @var string
     */
    protected $stubName;

    public function __construct()
    {
        parent::__construct(new Filesystem());
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    public function getStub()
    {
        return $this->getStubByName($this->stubName, $this->getTemplateName());
    }

    /**
     * It gets the views destenation path
     *
     * @param $viewsDirectory
     *
     * @return string
     */
    protected function getDestinationPath($viewsDirectory)
    {
        $path = $this->getViewsPath();

        if(!empty($viewsDirectory))
        {
            $path .= Helpers::getPathWithSlash($viewsDirectory);
        }

        return $path;
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        if( empty($this->stubName))
        {
            throw new Exception('The stub name cannot be left empty.');
        }

        $this->handleCreateView();
    }

    /**
     * Execute the console Command
     *
     * @return void
     */
    abstract protected function handleCreateView();

    /**
     * Gets a clean user inputs.
     *
     * @return object
     */
    protected function getCommandInput()
    {
        return new ViewInput($this->arguments(), $this->options());
    }

    /**
     * It created the new view. If the target path does not exists one will be created also
     *
     * @param string $stub
     * @param string $viewFullname
     *
     * @return $this
     */
    protected function createViewFile($stub, $viewFullname)
    {
        $this->makeDirectory( $viewFullname );

        $this->files->put( $viewFullname, $stub);

        return $this;
    }

    /**
     * It generate the view including the full path
     *
     * @param string $viewsDirectory
     * @param string $action
     *
     * @return string
     */
    protected function getDestinationViewFullname($viewsDirectory, $routesPrefix, $action)
    {
        $viewsPath = $this->getFullViewsPath($viewsDirectory, $routesPrefix);

        return $this->getDestinationPath($viewsPath) . $this->getDestinationViewName($action);
    }

    /**
     * It generate the destenation view name
     *
     * @param $action
     *
     * @return string
     */
    protected function getDestinationViewName($action)
    {
        return sprintf('%s.blade.php', $action);
    }

    /**
     * It Replaces the primaryKey, modelNames, routeNames in a giving stub
     *
     * @param string $stub
     * @param ViewInput $input
     *
     * @return $this
     */
    protected function replaceCommonTemplates(&$stub, ViewInput $input)
    {
        $this->replaceModelName($stub, $input->modelName)
             ->replaceRouteNames($stub, $input->modelName, $input->prefix)
             ->replaceViewNames($stub, $input->viewsDirectory, $input->prefix)
             ->replaceLayoutName($stub, $input->layout);

        return $this;
    }

    /**
     * It checks if a view file exists and the --force option is not present
     *
     * @param string $file
     * @param bool $force
     * @param array $fields
     *
     * @return bool
     */
    protected function canCreateView($file, $force, array $fields = null)
    {
        if($this->files->exists($file) && !$force)
        {
            $this->error($this->getViewNameFromFile($file) . ' view already exists.');
            return false;
        }

        if( !is_null($fields) && !isset($fields[0]) )
        {
            $this->error('You must provide at least one field to generate the views!');
            return false;
        }

        if(!is_null($fields) && is_null($this->getPrimaryKeyName($fields)) )
        {
            $this->error('None of the fields is set primary! You must assign on of the fields to be a primary field.');
            return false;
        }

        return true;
    }

    /**
     * Get the view's name of a giving file.
     *
     * @param string $file
     *
     * @return string
     */
    protected function getViewNameFromFile($file)
    {
        return ucfirst(strstr(basename($file), '.', true));
    }

    /**
     * It Replaces the layout name in a giving stub
     *
     * @param string $stub
     * @param string $layout
     *
     * @return $this
     */
    protected function replaceLayoutName(&$stub, $layout)
    {
        $stub = str_replace('{{layoutName}}', $layout, $stub);

        return $this;
    }

    /**
     * It Replaces fieldUpload in the giving stub.
     *
     * @param string $stub
     * @param array $fields
     *
     * @return $this
     */
    protected function replaceFileUpload(&$stub, array $fields)
    {
        $code = $this->isContainfile($fields) ? $this->getFileUploadAttribute($this->getTemplateName()) : '';

        $stub = str_replace('{{uploadFiles}}', $code, $stub);

        return $this;
    }

    /**
     * It gets the file attribute based on the giving template type.
     *
     * @param string $template
     *
     * @return string
     */
    protected function getFileUploadAttribute($template)
    {
        if($this->isCollectiveTemplate($template))
        {
            return "'files' => true,";
        }

        return ' enctype="multipart/form-data"';
    }

    /**
     * It Replaces the primary key in a giving stub
     *
     * @param string $stub
     * @param string $primaryKey
     *
     * @return $this
     */
    protected function replacePrimaryKey(&$stub, $primaryKey)
    {
        $stub = str_replace('{{primaryKey}}', $primaryKey, $stub);

        return $this;
    }

    /**
     * It creates given views is they don't already exists
     *
     * @param ViewInput $input
     * @param array $views
     *
     * @return $this
     */
    protected function createMissingViews(ViewInput $input, array $views = ['form'])
    {
        foreach($views as $view)
        {
            if( !$this->isViewExists($input->viewsDirectory,$input->prefix, $view))
            {
                $this->callSilent($this->getViewCommand($view), $input->getArrguments());
            }
        }

        return $this;
    }

    /**
     * It make a valid command for creating a giving view
     *
     * @param string $view
     *
     * @return string
     */
    protected function getViewCommand($view)
    {
        return sprintf('create:%s-view', $view);
    }

    /**
     * It checks of a destination view exists or not
     *
     * @param string $viewsDirectory
     * @param string $viewName
     *
     * @return bool
     */
    protected function isViewExists($viewsDirectory, $routesPrefix, $viewName)
    {
        return $this->files->exists($this->getDestinationViewFullname($viewsDirectory, $routesPrefix, $viewName));
    }

    /**
     * It called tha create-locale command to generate the locale config
     *
     * @param string $langFile
     * @param string $fields
     *
     * @return $this
     */
    protected function createLanguageFile($langFile, $fields, $fieldsFile)
    {
        $this->callSilent('create:language', [
                                    'language-file-name' => $langFile,
                                    '--fields' => $fields,
                                    '--fields-file' => $fieldsFile,
                                    '--template-name' => $this->getTemplateName()
                                   ]);
        return $this;
    }

    /**
     * Gets destenation view path
     *
     * @param string $viewsDirectory
     * @param string $routesPrefix
     *
     * @return $this
     */
    protected function getFullViewsPath($viewsDirectory, $routesPrefix)
    {
        $path = !empty($routesPrefix) ? Helpers::getPathWithSlash($routesPrefix) : '';

        if(!empty($viewsDirectory))
        {
            $path .= Helpers::getPathWithSlash($viewsDirectory);
        }

        return $path;
    }

    /**
     * Gets the primary key name from a giving fields collection
     *
     * @param array $fields
     *
     * @return null | string
     */
    protected function getPrimaryKeyName(array $fields)
    {
        $primaryKey = $this->getPrimaryField($fields);

        return !is_null($primaryKey) ? $primaryKey->name : null;
    }

    /**
     * Gets a new instance of the proper html generator.
     *
     * @param array $fields
     * @param string $modelName
     * @param string $template
     *
     * @return CrestApps\CodeGenerator\HtmlGenerators\HtmlGeneratorBase
     */
    protected function getHtmlGenerator(array $fields, $modelName, $template)
    {
        if($this->isCollectiveTemplate($template))
        {
            return new LaravelCollectiveHtml($fields, $modelName, $template);
        }

        return new StandardHtml($fields, $modelName, $template);
    }

    /**
     * Checks the giving template if it is a Laravel-Collective template or not.
     *
     * @param string $template
     *
     * @return bool
     */
    protected function isCollectiveTemplate($template)
    {
        return in_array($template, $this->getCollectiveTemplates());
    }
}