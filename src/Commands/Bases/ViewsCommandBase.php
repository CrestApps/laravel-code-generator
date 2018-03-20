<?php

namespace CrestApps\CodeGenerator\Commands\Bases;

use CrestApps\CodeGenerator\HtmlGenerators\LaravelCollectiveHtml;
use CrestApps\CodeGenerator\HtmlGenerators\StandardHtml;
use CrestApps\CodeGenerator\Models\Resource;
use CrestApps\CodeGenerator\Models\ViewInput;
use CrestApps\CodeGenerator\Support\Config;
use CrestApps\CodeGenerator\Support\Helpers;
use CrestApps\CodeGenerator\Support\Str;
use CrestApps\CodeGenerator\Support\ViewLabelsGenerator;
use CrestApps\CodeGenerator\Traits\CommonCommand;
use CrestApps\CodeGenerator\Traits\GeneratorReplacers;
use Illuminate\Console\Command;

abstract class ViewsCommandBase extends Command
{
    use CommonCommand, GeneratorReplacers;

    /**
     * Gets the name of the stub to process.
     *
     * @return string
     */
    abstract protected function getStubName();

    /**
     * Execute the console Command
     *
     * @return void
     */
    abstract protected function handleCreateView();

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return $this->getStubContent($this->getStubName(), $this->getTemplateName());
    }

    /**
     * Get the view type
     *
     * @return string
     */
    protected function getViewType()
    {
        return Str::trimEnd($this->getStubName(), '.blade');
    }

    /**
     * Get the view name
     *
     * @return string
     */
    protected function getViewName()
    {
        return sprintf('%s-view', $this->getViewType());
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
        $path = Config::getViewsPath();

        if (!empty($viewsDirectory)) {
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
        $this->handleCreateView();
    }

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
     * It generate the view including the full path
     *
     * @param string $viewsDirectory
     * @param string $routesPrefix
     * @param string $viewName
     *
     * @return string
     */
    protected function getDestinationViewFullname($viewsDirectory, $routesPrefix, $viewName = null)
    {
        $viewsPath = $this->getFullViewsPath($viewsDirectory, $routesPrefix);

        $filename = $this->getDestinationViewName($viewName ?: $this->getViewType());

        return $this->getDestinationPath($viewsPath) . $filename;
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
     * It Replaces the primaryKey, modelNames, routeNames in a given stub
     *
     * @param string $stub
     * @param CrestApps\CodeGenerator\Models\ViewInput $input
     *
     * @return $this
     */
    protected function replaceCommonTemplates(&$stub, ViewInput $input, array $fields)
    {
        $viewLabels = new ViewLabelsGenerator($input->modelName, $fields, $this->isCollectiveTemplate());

        $standardLabels = $viewLabels->getLabels();

        $this->replaceModelName($stub, $input->modelName)
            ->replaceRouteNames($stub, $this->getModelName($input->modelName), $input->prefix)
            ->replaceViewNames($stub, $input->viewsDirectory, $input->prefix)
            ->replaceLayoutName($stub, $input->layout)
            ->replaceStandardLabels($stub, $standardLabels);

        return $this;
    }

    /**
     * It checks if a view file exists and the --force option is not present
     *
     * @param string $file
     * @param bool $force
     * @param CrestApps\CodeGenerator\Models\Resource
     *
     * @return bool
     */
    protected function canCreateView($file, $force, Resource $resource)
    {
        $viewName = $this->getViewName();

        if ($resource->isProtected($viewName)) {
            $this->warn('The ' . $viewName . ' is protected and cannot be regenerated. To regenerate the file, unprotect it from the resource file.');

            return false;
        }

        if ($this->alreadyExists($file) && !$force) {
            $this->error($this->getViewNameFromFile($file) . ' view already exists.');

            return false;
        }

        if (!$resource->hasFields()) {
            $this->error('You must provide at least one field to generate the views!');

            return false;
        }

        if (!$resource->hasPrimaryField()) {
            $this->error('None of the fields is set primary! You must assign on of the fields to be a primary field.');

            return false;
        }

        return true;
    }

    /**
     * Replace the create_form_id
     *
     * @param string $stub
     * @param string $name
     *
     * @return $this
     */
    protected function replaceFormId(&$stub, $name)
    {
        return $this->replaceTemplate('form_id', $name, $stub);
    }

    /**
     * Replace the create_form_name
     *
     * @param string $stub
     * @param string $name
     *
     * @return $this
     */
    protected function replaceFormName(&$stub, $name)
    {
        return $this->replaceTemplate('form_name', $name, $stub);
    }

    /**
     * Get the view's name of a given file.
     *
     * @param string $fillname
     *
     * @return string
     */
    protected function getViewNameFromFile($filename)
    {
        $file = basename($filename);

        return ucfirst(strstr($file, '.', true));
    }

    /**
     * It Replaces the layout name in a given stub
     *
     * @param string $stub
     * @param string $layout
     *
     * @return $this
     */
    protected function replaceLayoutName(&$stub, $layout)
    {
        return $this->replaceTemplate('layout_name', $layout, $stub);
    }

    /**
     * It Replaces fieldUpload in the given stub.
     *
     * @param string $stub
     * @param array $fields
     *
     * @return $this
     */
    protected function replaceFileUpload(&$stub, array $fields)
    {
        $code = $this->containsfile($fields) ? $this->getFileUploadAttribute($this->getTemplateName()) : '';

        return $this->replaceTemplate('upload_files', $code, $stub);
    }

    /**
     * It gets the file attribute based on the given template type.
     *
     * @param string $template
     *
     * @return string
     */
    protected function getFileUploadAttribute($template)
    {
        if ($this->isCollectiveTemplate($template)) {
            return "'files' => true,";
        }

        return ' enctype="multipart/form-data"';
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
        foreach ($views as $view) {
            if (!$this->isViewExists($input->viewsDirectory, $input->prefix, $view)) {
                $this->callSilent($this->getViewCommand($view), $input->getArrguments());
            }
        }

        return $this;
    }

    /**
     * It make a valid command for creating a given view
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
     * @param string $routesPrefix
     * @param string $viewName
     *
     * @return bool
     */
    protected function isViewExists($viewsDirectory, $routesPrefix, $viewName)
    {
        $destenatioFile = $this->getDestinationViewFullname($viewsDirectory, $routesPrefix, $viewName);

        return $this->alreadyExists($destenatioFile);
    }

    /**
     * It called tha create-locale command to generate the locale config
     *
     * @param string $langFile
     * @param string $fields
     * @param string $resourceFile
     * @param string $modelName
     *
     * @return $this
     */
    protected function createLanguageFile($langFile, $resourceFile, $modelName)
    {
        $this->callSilent('create:language', [
            'model-name' => $modelName,
            '--language-filename' => $langFile,
            '--resource-file' => $resourceFile,
            '--template-name' => $this->getTemplateName(),
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
        $path = !empty($routesPrefix) ? Helpers::getPathWithSlash(str_replace('.', '-', $routesPrefix)) : '';

        if (!empty($viewsDirectory)) {
            $path .= Helpers::getPathWithSlash($viewsDirectory);
        }

        return $path;
    }

    /**
     * Gets the primary key name from a given fields collection
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
     * Gets the primary key name from a given fields collection
     *
     * @param array $fields
     * @param string $default
     *
     * @return null | string
     */
    protected function getHeaderFieldAccessor(array $fields, $modelName)
    {
        $field = $this->getHeaderField($fields);

        return !is_null($field) ? sprintf('$%s->%s', $this->getSingularVariable($modelName), $field->name) : '$title';
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
        if ($this->isCollectiveTemplate($template)) {
            return new LaravelCollectiveHtml($fields, $modelName, $template);
        }

        return new StandardHtml($fields, $modelName, $template);
    }

    /**
     * Replace the modele's header fo the given stub.
     *
     * @param string $stub
     * @param string $title
     *
     * @return $this
     */
    protected function replaceModelHeader(&$stub, $title)
    {
        return $this->replaceTemplate('model_header', $title, $stub);
    }
}
