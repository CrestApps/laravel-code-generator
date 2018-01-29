<?php

namespace CrestApps\CodeGenerator\Commands\Api;

use CrestApps\CodeGenerator\Models\Field;
use CrestApps\CodeGenerator\Models\Resource;
use CrestApps\CodeGenerator\Support\Config;
use CrestApps\CodeGenerator\Support\Helpers;
use CrestApps\CodeGenerator\Support\ViewLabelsGenerator;
use CrestApps\CodeGenerator\Traits\ApiResourceTrait;
use CrestApps\CodeGenerator\Traits\CommonCommand;
use CrestApps\CodeGenerator\Traits\GeneratorReplacers;
use Illuminate\Console\Command;

class CreateApiDocumentationCommand extends Command
{
    use CommonCommand, GeneratorReplacers, ApiResourceTrait;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new API based controller.';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'api-doc:create-view
                            {model-name : The model name that this controller will represent.}
                            {--controller-name= : The name of the controler.}
                            {--controller-directory= : The directory where the controller should be created under.}
                            {--resource-file= : The name of the resource-file to import from.}
                            {--routes-prefix=default-form : Prefix of the route group.}
                            {--language-filename= : The languages file name to put the labels in.}
                            {--with-auth : Generate the controller with Laravel auth middlewear. }
                            {--views-directory= : The name of the directory to create the views under.}
                            {--layout-name=layouts.api-doc-layout : This will extract the validation into a request form class.}
                            {--template-name= : The template name to use when generating the code.}
                            {--force : This option will override the controller if one already exists.}';

    /**
     * Build the model class with the given name.
     *
     * @return string
     */
    public function handle()
    {
        $input = $this->getCommandInput();
        $resource = Resource::fromFile($input->resourceFile, $input->langFile);

        $destenationFile = $this->getDestinationViewFullname($input->viewsDirectory, $input->prefix, 'index');

        if ($this->hasErrors($resource, $destenationFile)) {
            return false;
        }

        $stub = $this->getStubContent('api-documentation-index');

        $viewLabels = new ViewLabelsGenerator($input->modelName, $resource->fields, $this->isCollectiveTemplate());

        return $this->replaceStandardLabels($stub, $viewLabels->getLabels())
            ->replaceApiLabels($stub, $resource->getApiDocLabels())
            ->replaceModelName($stub, $input->modelName)
            ->replaceRouteNames($stub, $input->modelName, $input->prefix)
            ->repaceLayoutName($stub, $input->layoutName)
            ->repacePathToViewHome($stub, $this->getPathToViewHome($input->viewsDirectory, $input->prefix))
            ->makeRequiredSubViews($input, $resource->getApiDocLabels(), $viewLabels->getLabels())
            ->makeFieldsListView($input, $resource, $viewLabels->getLabels())
            ->createFile($destenationFile, $stub)
            ->info('A api-documentation was successfully crafted.');
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
        $path = $this->getPathToViews($viewsDirectory);

        return Config::getViewsPath() . $path;
    }

    /**
     * It creates the failed-authentication sub-view
     *
     * @param object $input
     * @param array $apiDocLabels
     * @param array $standardLabels
     *
     * @return $this
     */
    protected function makeRequiredSubViews($input, array $apiDocLabels, array $standardLabels)
    {
        $stub = $this->getStubContent('api-documentation-index-failed-authentication');
        $this->makeStandardSubView('failed-authentication', $input, $apiDocLabels, $standardLabels)
            ->makeStandardSubView('failed-to-retrieve', $input, $apiDocLabels, $standardLabels)
            ->makeStandardSubView('failed-validation', $input, $apiDocLabels, $standardLabels)
            ->makeStandardSubView('failed-authentication', $input, $apiDocLabels, $standardLabels)
            ->makeStandardSubView('retrieved', $input, $apiDocLabels, $standardLabels);

        return $this;
    }

    protected function makeFieldsListView($input, Resource $resource, array $standardLabels = null)
    {
        $stub = $this->getStubContent('api-documentation-index-fields-list');
        $destenationFile = $this->getDestinationViewFullname($input->viewsDirectory, $input->prefix, 'fields-list');

        $this->replaceStandardLabels($stub, $standardLabels)
            ->replaceApiLabels($stub, $resource->getApiDocLabels())
            ->replaceModelName($stub, $input->modelName)
            ->repaceValidationRuleRequired($stub, $this->getRequiredRule($resource->getApiDocLabels(), $standardLabels))
            ->replaceFieldsListForBody($stub, $this->getFieldsListBody($input->modelName, $resource, $standardLabels))
            ->createFile($destenationFile, $stub);

        return $this;
    }

    protected function getRequiredRule(array $apiDocLabels, array $standardLabels = null)
    {
        $stub = $this->getStubContent('api-documentation-field-validation-required');

        $this->replaceStandardLabels($stub, $standardLabels)
            ->replaceApiLabels($stub, $apiDocLabels);

        return $stub;
    }

    /**
     * It creates the failed-authentication sub-view
     *
     * @param object $input
     * @param array $apiDocLabels
     * @param array $standardLabels
     *
     * @return $this
     */
    protected function makeStandardSubView($name, $input, array $apiDocLabels, array $standardLabels)
    {
        $stub = $this->getStubContent('api-documentation-index-' . $name);
        $destenationFile = $this->getDestinationViewFullname($input->viewsDirectory, $input->prefix, $name);

        $this->replaceStandardLabels($stub, $standardLabels)
            ->replaceApiLabels($stub, $apiDocLabels)
            ->replaceModelName($stub, $input->modelName)

            ->createFile($destenationFile, $stub);

        return $this;
    }

    /**
     * It path to view home using the dot notation.
     *
     * @param string $viewsDirectory
     * @param string $routesPrefix
     *
     * @return string
     */
    protected function getPathToViewHome($viewsDirectory, $routesPrefix)
    {
        $path = Config::getApiDocsViewsPath() . $this->getFullViewsPath($viewsDirectory, $routesPrefix);

        return Helpers::convertToDotNotation($path);
    }

    /**
     * It path to view home using the dot notation.
     *
     * @param $viewsDirectory
     *
     * @return string
     */
    protected function getPathToViews($viewsDirectory)
    {
        $path = Config::getApiDocsViewsPath();

        if (!empty($viewsDirectory)) {
            $path .= Helpers::getPathWithSlash($viewsDirectory);
        }

        return $path;
    }

    /**
     * Gets the destenation file to be created.
     *
     * @param string $name
     * @param string $path
     *
     * @return string
     */
    protected function getDestenationFile($name, $path)
    {
        if (!empty($path)) {
            $path = Helpers::getPathWithSlash($path);
        }

        return app_path(Config::getModelsPath($path . $name . '.php'));
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
    protected function getDestinationViewFullname($viewsDirectory, $routesPrefix, $viewName = 'index')
    {
        $viewsPath = $this->getFullViewsPath($viewsDirectory, $routesPrefix);

        $filename = $this->getDestinationViewName($viewName);

        return $this->getDestinationPath($viewsPath) . $filename;
    }

    protected function getFieldsListBody($modelName, Resource $resource, array $standardLabels = null)
    {
        $final = [];
        $template = $this->getStubContent('api-documentation-index-fields-list-body-row');
        foreach ($resource->getFields() as $field) {
            $stub = $template;

            $requiredTemplate = '';

            if ($field->isRequired()) {
                $requiredTemplate = $this->getRequiredRule($resource->getApiDocLabels(), $standardLabels);
            }

            // TO DO
            // description must be converted into a label instead of a string
            $this->replaceStandardLabels($stub, $standardLabels)
                ->replaceApiLabels($stub, $resource->getApiDocLabels())
                ->replaceModelName($stub, $modelName)
                ->replaceFieldDescription($stub, $field->description)
                ->replaceValidationRules($stub, $this->getValidationRules($field))
                ->replaceModelName($stub, $field->name, 'field_')
                ->repaceValidationRuleRequired($stub, $requiredTemplate);

            // replace field_description
            // replace validation_rules
            // replace field_type_title

            $final[] = $stub;
        }

        return implode(PHP_EOL, $final);
    }

    protected function getValidationRules(Field $field)
    {
        $updatedRules = array_filter($field->getValidationRules(), function ($rule) {
            return !in_array($rule, ['required', 'nullable']);
        });

        $hasString = in_array('string', $field->getValidationRules());

        if ($hasString) {
            foreach ($field->getValidationRules() as $rule) {
                if (starts_with($rule, 'min:')) {
                    $updatedRules[] = 'Minimum Length: ' . Helpers::removePreFixWith($rule, 'min:');
                }

                if (starts_with($rule, 'max:')) {
                    $updatedRules[] = 'Maximum Length: ' . Helpers::removePreFixWith($rule, 'max:');
                }
            }
        }

        foreach ($field->getValidationRules() as $rule) {
            if ($hasString && starts_with($rule, 'min:') && starts_with($rule, 'max:')) {
                continue;
            }
            $updatedRules[] = ucfirst($rule);
        }

        return implode('; ', $updatedRules);
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
        $path = '';

        if (!empty($viewsDirectory)) {
            $path .= Helpers::getPathWithSlash($viewsDirectory);
        }

        $path .= !empty($routesPrefix) ? Helpers::getPathWithSlash(str_replace('.', '-', $routesPrefix)) : '';

        return $path;
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
     * Build the model class with the given name.
     *
     * @param CrestApps\CodeGenerator\Models\Resource $resource
     * @param string $destenationFile
     *
     * @return bool
     */
    protected function hasErrors(Resource $resource, $destenationFile)
    {
        $hasErrors = false;

        if ($resource->isProtected('api-documentation')) {
            $this->warn('The api-documentation is protected and cannot be regenerated. To regenerate the file, unprotect it from the resource file.');

            $hasErrors = true;
        }

        if ($this->alreadyExists($destenationFile)) {
            $this->error('The api-documentation already exists!');

            $hasErrors = true;
        }

        return $hasErrors;
    }

    /**
     * Replaces get validator method for the given stub.
     *
     * @param  string  $stub
     * @param  array  $labels
     *
     * @return $this
     */
    protected function replaceApiLabels(&$stub, array $labels)
    {
        foreach ($labels as $lang => $labelsCollection) {

            foreach ($labelsCollection as $label) {
                $text = $label->text;
                if (!$label->isPlain) {
                    $text = sprintf("{{ trans('%s') }}", $label->getAccessor());
                }

                $this->replaceTemplate($label->id, $text, $stub);
            }
        }

        return $this;
    }

    /**
     * Replaces the layout_name for the giving stub,
     *
     * @param  string  $stub
     * @param  string  $name
     *
     * @return $this
     */
    protected function repaceLayoutName(&$stub, $name)
    {
        return $this->replaceTemplate('layout_name', $name, $stub);
    }

    /**
     * Replaces the layout_name for the giving stub,
     *
     * @param  string  $stub
     * @param  string  $name
     *
     * @return $this
     */
    protected function repacePathToViewHome(&$stub, $name)
    {
        return $this->replaceTemplate('path_to_view_home', $name, $stub);
    }

    /**
     * Replaces the layout_name for the giving stub,
     *
     * @param  string  $stub
     * @param  string  $name
     *
     * @return $this
     */
    protected function repaceValidationRuleRequired(&$stub, $name)
    {
        return $this->replaceTemplate('validation_rule_required', $name, $stub);
    }

    /**
     * Replaces the fields_list_for_body for the giving stub,
     *
     * @param  string  $stub
     * @param  string  $name
     *
     * @return $this
     */
    protected function replaceFieldsListForBody(&$stub, $name)
    {
        return $this->replaceTemplate('fields_list_for_body', $name, $stub);
    }

    /**
     * Replaces the field_description for the giving stub,
     *
     * @param  string  $stub
     * @param  string  $name
     *
     * @return $this
     */
    protected function replaceFieldDescription(&$stub, $name)
    {
        return $this->replaceTemplate('field_description', $name, $stub);
    }

    /**
     * Gets a clean command-line arguments and options.
     *
     * @return object
     */
    protected function getCommandInput()
    {
        $modelName = trim($this->argument('model-name'));
        $cName = trim($this->option('controller-name'));
        $controllerName = $cName ? str_finish($cName, Config::getControllerNamePostFix()) : Helpers::makeControllerName($modelName);
        $controllerDirectory = trim($this->option('controller-directory'));
        $viewsDirectory = trim($this->option('views-directory'));
        $layoutName = trim($this->option('layout-name'));
        $resourceFile = trim($this->option('resource-file')) ?: Helpers::makeJsonFileName($modelName);
        $prefix = ($this->option('routes-prefix') == 'default-form') ? Helpers::makeRouteGroup($modelName) : $this->option('routes-prefix');
        $langFile = $this->option('language-filename') ?: Helpers::makeLocaleGroup($modelName);
        $withAuth = $this->option('with-auth');
        $force = $this->option('force');

        return (object) compact(
            'modelName',
            'controllerName',
            'controllerDirectory',
            'resourceFile',
            'prefix',
            'langFile',
            'withAuth',
            'viewsDirectory',
            'layoutName',
            'force'
        );
    }
}
