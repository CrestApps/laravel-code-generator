<?php

namespace CrestApps\CodeGenerator\Traits;

use CrestApps\CodeGenerator\Models\Field;
use CrestApps\CodeGenerator\Support\Config;
use CrestApps\CodeGenerator\Support\Helpers;
use CrestApps\CodeGenerator\Support\Str;
use Exception;
use File;

trait CommonCommand
{
    /**
     * Values when passed to the option, means no value.
     *
     * @var array
     */
    protected $noValues = [
        'empty',
        'no_value',
        'blank',
        'none',
    ];

    /**
     * The default route actions
     *
     * @var array
     */
    protected $actions = [
        'index',
        'create',
        'show',
        'update',
        'edit',
        'destroy',
        'store',
    ];

    /**
     * The default views actions
     *
     * @var array
     */
    protected $views = [
        'form',
        'index',
        'create',
        'show',
        'edit',
    ];

    /**
     * Replaces a template variable in the given subject.
     *
     * @return string
     */
    protected function strReplace($search, $replace, $subject)
    {
        $template = $this->getTemplateVariable($search);

        return str_replace($template, $replace, $subject);
    }

    /**
     * Replaces a template variable in the given subject.
     *
     * @param string $search
     * @param string $replace
     * @param string $subject
     *
     * @return $this
     */
    protected function replaceTemplate($search, $replace, &$subject)
    {
        $subject = $this->strReplace($search, $replace, $subject);

        return $this;
    }

    /**
     * convert a key to a template variable.
     *
     * @return string
     */
    protected function getTemplateVariable($key)
    {
        return '[% ' . $key . ' %]';
    }

    /**
     * Gets the relation accessor for the given foreign renationship.
     *
     * @param string $name
     *
     * @return string
     */
    protected function getUseClassCommand($name)
    {
        if (empty($name)) {
            return '';
        }

        return sprintf('use %s;', $name);
    }

    /**
     * Gets all command's arguments depending on the current framework version.
     *
     * @return string
     */
    public function arguments()
    {
        if (Helpers::isNewerThanOrEqualTo()) {
            return parent::arguments();
        }

        return parent::argument();
    }

    /**
     * Gets plural variable instance of a given model.
     *
     * @param  string  $name
     *
     * @return string
     */
    public function getPluralVariable($name)
    {
        $snake = snake_case($name);

        $variableName = camel_case(Str::plural($snake));

        if ($variableName == $this->getSingularVariable($name)) {
            $variableName .= 'Objects';
        }

        return $variableName;
    }

    /**
     * Gets singular variable instance of a given model.
     *
     * @param  string  $name
     *
     * @return string
     */
    public function getSingularVariable($name)
    {
        $snake = snake_case($name);

        return camel_case($snake);
    }

    /**
     * Gets the indentation count.
     *
     * @param  string  $stub
     * @param  string  $template
     *
     * @return $this
     */
    protected function getIndent($stub, $template)
    {
        $lines = explode("\n", $stub);

        foreach ($lines as $line) {
            if (($index = strpos($line, $template)) !== false) {
                return $index;
            }
        }

        return 0;
    }

    /**
     * Gets white-spaces times the multiplier.
     *
     * @param int $multiplier
     *
     * @return string
     */
    protected function indent($multiplier)
    {
        if ($multiplier < 1) {
            return '';
        }

        return str_repeat(' ', $multiplier);
    }

    /**
     * Gets all command's options depending on the current framework version.
     *
     * @return string
     */
    public function options()
    {
        if (Helpers::isNewerThanOrEqualTo()) {
            return parent::options();
        }

        return parent::option();
    }

    /**
     * Override Laravel's option method
     *
     * @param string $key
     *
     * @return string
     */
    public function generatorOption($key)
    {
        $value = $this->option($key);

        if (is_string($value)) {
            $value = trim($value);

            return in_array($value, $this->noValues) ? null : $value;
        }

        return $value;
    }

    /**
     * It Replaces the view names in a given stub
     *
     * @param string $stub
     * @param string $viewDirectory
     * @param string $routesPrefix
     * @param array $views
     *
     * @return $this
     */
    protected function replaceViewNames(&$stub, $viewDirectory, $routesPrefix, array $views = null)
    {
        foreach (($views ?: $this->views) as $view) {
            $viewName = $this->getDotNotationName($viewDirectory, $routesPrefix, $view);
            $viewTemplate = $this->getViewTemplateName($view);
            $stub = $this->strReplace($viewTemplate, $viewName, $stub);
        }

        return $this;
    }

    /**
     * Gets a full name with dot notation
     *
     * @param string $viewDirectory
     * @param string $routesPrefix
     * @param string $name
     *
     * @return $this
     */
    protected function getDotNotationName($viewDirectory, $routesPrefix, $name = 'index')
    {
        if (!empty($viewDirectory)) {
            $name = Helpers::getWithDotPostFix(Helpers::convertToDotNotation($viewDirectory)) . $name;
        }

        if (!empty($routesPrefix)) {
            $routesPrefix = str_replace('.', '-', $routesPrefix);

            $name = Helpers::getWithDotPostFix(Helpers::convertToDotNotation($routesPrefix)) . $name;
        }

        return strtolower($name);
    }

    /**
     * Gets the stub file.
     *
     *@param string $name
     * @param string $template
     *
     * @return string
     */
    protected function getStubByName($name, $template = null)
    {
        return sprintf('%s%s.stub', $this->getPathToTemplates($template), $name);
    }

    /**
     * Replaces the route names for all the provided actions
     *
     * @param string $stub
     * @param string $modelName
     * @param string $routesPrefix
     * @param array $actions
     *
     * @return $this
     */
    protected function replaceRouteNames(&$stub, $modelName, $routesPrefix, array $actions = null)
    {
        foreach (($actions ?: $this->actions) as $action) {
            $routeName = $this->getDotNotationName($modelName, $routesPrefix, $action);
            $routeTemplate = $this->getRouteName($action);
            $stub = $this->strReplace($routeTemplate, $routeName, $stub);
        }

        return $this;
    }

    /**
     * Gets a route name
     *
     * @param string $action
     *
     * @return string
     */
    protected function getRouteName($action)
    {
        return sprintf('%s_route_name', $action);
    }

    /**
     * Gets a view template name
     *
     * @param string $view
     *
     * @return string
     */
    protected function getViewTemplateName($view)
    {
        return sprintf('%s_view_name', $view);
    }

    /**
     * Gets the content of a stub
     *
     * @param string $name
     * @param string $template
     *
     * @return string
     */
    protected function getStubContent($name, $template = null)
    {
        return $this->getFileContent($this->getStubByName($name, $template));
    }

    /**
     * Determine if a file already exists after checking for a --force option in the command.
     *
     * @param  string  $file
     * @return bool
     */
    protected function alreadyExists($file)
    {
        if ($this->option('force')) {
            return false;
        }

        return $this->isFileExists($file);
    }

    /**
     * Determine if a file already exists.
     *
     * @param  string  $file
     * @return bool
     */
    protected function isFileExists($file)
    {
        return File::exists($file);
    }

    /**
     * Get the givin file content.
     *
     * @param  string  $file
     *
     * @return string
     */
    protected function getFileContent($file)
    {
        return File::get($file);
    }

    /**
     * Get the givin file content.
     *
     * @param  string  $file
     *
     * @return string
     */
    protected function deleteFile($file)
    {
        return File::delete($file);
    }

    /**
     * Adds content to a given file.
     *
     * @param  string  $file
     *
     * @return $this
     */
    protected function putContentInFile($file, $content)
    {
		$path = dirname($file);
				
		if(!$this->isFileExists($path)) {
			File::makeDirectory($path, 0755, true);
		}
		
        File::put($file, $content);

        return $this;
    }

    /**
     * Adds content to a given file.
     *
     * @param  string  $file
     *
     * @return $this
     */
    protected function appendContentToFile($file, $content)
    {
        File::append($file, $content);

        return $this;
    }

    /**
     * Determine the primary field in a given array
     *
     * @param array $fields
     *
     * @return CrestApps\CodeGenerator\Models\Field
     */
    protected function getPrimaryField(array $fields)
    {
        foreach ($fields as $field) {
            if ($this->isField($field) && ($field->isPrimary || $field->isAutoIncrement)) {
                return $field;
            }
        }

        return null;
    }

    /**
     * Determine the field to be used for header from the givin fields.
     *
     * @param array $fields
     * @return CrestApps\CodeGenerator\Models\Field || null
     */
    protected function getHeaderField(array $fields)
    {
        foreach ($fields as $field) {
            if ($this->isField($field) && $field->isHeader) {
                return $field;
            }
        }

        return null;
    }

    /**
     * Build the directory for the class if necessary.
     *
     * @param  string  $path
     * @return $this
     */
    protected function createDirectory($path)
    {
        if (!File::isDirectory($path)) {
            File::makeDirectory($path, 0777, true, true);
        }

        return $this;
    }

    /**
     * It creates a new file. If the target path does not exists one will be created.
     *
     * @param string $file
     * @param string $stub
     *
     * @return $this
     */
    protected function createFile($file, $stub)
    {
        $path = dirname($file);
        $this->createDirectory($path);

        $this->putContentInFile($file, $stub);

        return $this;
    }

    /**
     * Checks if the givin field is an instance of a field or not.
     *
     * @return string
     */
    protected function isField($field)
    {
        return $field instanceof Field;
    }

    /**
     * Gets the path to templates
     *
     * @param string $templateName
     *
     * @return string
     */
    protected function getPathToTemplates($templateName = null)
    {
        $templateName = $templateName ?: Config::getDefaultTemplateName();
        $path = base_path(Config::getTemplatesPath() . Helpers::getPathWithSlash($templateName));

        if (!File::isDirectory($path) && in_array($templateName, ['default', 'default-collective'])) {
            // If the default templates are not published, utilize the default package path.

            $path = __DIR__ . '/../../templates/' . $templateName;
        }

        if (!File::isDirectory($path)) {
            throw new Exception('Invalid template. Make sure the following path exists: "' . $path . '"');
        }

        return Helpers::getPathWithSlash($path);
    }

    /**
     * Checks the given template if it is a Laravel-Collective template or not.
     *
     * @param string $template
     *
     * @return bool
     */
    protected function isCollectiveTemplate($template = null)
    {
        return in_array($template ?: $this->getTemplateName(), Config::getCollectiveTemplates());
    }

    /**
     * Checks if a given fields array conatins at least one file field
     *
     * @param array
     *
     * @return bool
     */
    protected function containsfile(array $fields)
    {
        $filtered = array_filter($fields, function ($field) {
            return $field->isFile();
        });

        return !empty($filtered);
    }

    /**
     * Gets the template name from the options line.
     *
     * @return string
     */
    public function getTemplateName()
    {
        return trim($this->option('template-name')) ?: Config::getDefaultTemplateName();
    }
}
