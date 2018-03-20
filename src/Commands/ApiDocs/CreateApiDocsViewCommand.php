<?php

namespace CrestApps\CodeGenerator\Commands\ApiDocs;

use CrestApps\CodeGenerator\Models\Field;
use CrestApps\CodeGenerator\Models\Label;
use CrestApps\CodeGenerator\Models\Resource;
use CrestApps\CodeGenerator\Support\Config;
use CrestApps\CodeGenerator\Support\Helpers;
use CrestApps\CodeGenerator\Support\Str;
use CrestApps\CodeGenerator\Support\ViewLabelsGenerator;
use CrestApps\CodeGenerator\Traits\ApiDocViewsTrait;
use CrestApps\CodeGenerator\Traits\ApiResourceTrait;
use CrestApps\CodeGenerator\Traits\CommonCommand;
use CrestApps\CodeGenerator\Traits\GeneratorReplacers;
use CrestApps\CodeGenerator\Traits\LanguageTrait;
use CrestApps\CodeGenerator\Traits\RouteTrait;
use Illuminate\Console\Command;

class CreateApiDocsViewCommand extends Command
{
    use CommonCommand, GeneratorReplacers, ApiResourceTrait, ApiDocViewsTrait, LanguageTrait, RouteTrait;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create view to render the api-documenations.';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'api-docs:create-view
                            {model-name : The model name that this controller will represent.}
                            {--controller-name= : The name of the controler.}
                            {--controller-directory= : The directory where the controller should be created under.}
                            {--resource-file= : The name of the resource-file to import from.}
                            {--routes-prefix=default-form : Prefix of the route group.}
                            {--language-filename= : The languages file name to put the labels in.}
                            {--with-auth : Generate the controller with Laravel auth middlewear. }
                            {--views-directory= : The name of the directory to create the views under.}
                            {--api-version= : The api version to prefix your resurces with.}
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

        // The replaceAuthorizedRequestForIndex() method must be executed before replaceAuthorizationCall()
        return $this->replaceAuthorizedRequestForIndex($stub, $this->getAuthorizedRequestForIndex($input->withAuth, $resource->getApiDocLabels(), $viewLabels->getLabels()))
            ->replaceAuthorizationCall($stub, $this->getAuthorizationCall($input->withAuth, $resource->getApiDocLabels(), $viewLabels->getLabels()))
            ->replaceFailedAuthorizationCall($stub, $this->getFailedAuthorizationCall($input->withAuth))
            ->replaceFieldsListForBody($stub, $this->getFieldsListBody($input->modelName, $resource, $viewLabels->getLabels(), true))
            ->replaceStandardLabels($stub, $viewLabels->getLabels())
            ->replaceApiLabels($stub, $resource->getApiDocLabels())
            ->replaceModelName($stub, $input->modelName)
            ->replaceRouteNames($stub, $input->modelName, $this->getNamePrefix($input->prefix, 'api', $input->apiVersion))
            ->replaceLayoutName($stub, $input->layoutName)
            ->replacePathToViewHome($stub, $this->getPathToViewHome($input->viewsDirectory, $input->prefix))
            ->makeRequiredSubViews($input, $resource->getApiDocLabels(), $viewLabels->getLabels())
            ->makeFieldsListView($input, $resource, $viewLabels->getLabels())
            ->createFile($destenationFile, $stub)
            ->info('The views for the api-documentation were successfully crafted.');
    }

    protected function getAuthorizationCall($withAuth, array $apiDocLabels, array $standardLabels = null)
    {
        if ($withAuth) {
            $stub = $this->getStubContent('api-documentation-index-authentication');

            $this->replaceStandardLabels($stub, $standardLabels)
                ->replaceApiLabels($stub, $apiDocLabels);

            return $stub;
        }

        return '';
    }

    protected function getAuthorizedRequestForIndex($withAuth, array $apiDocLabels, array $standardLabels = null)
    {
        if ($withAuth) {
            $stub = $this->getStubContent('api-documentation-index-request');

            $this->replaceStandardLabels($stub, $standardLabels)
                ->replaceApiLabels($stub, $apiDocLabels);

            return $stub;
        }

        return $this->getTemplateVariable('no_parameters');
    }

    protected function getFailedAuthorizationCall($withAuth)
    {
        if ($withAuth) {
            return '@include(\'[% path_to_view_home %]failed-authentication\')';
        }

        return '';
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
        $this->makeStandardSubView('failed-authentication', $input, $apiDocLabels, $standardLabels)
            ->makeStandardSubView('failed-to-retrieve', $input, $apiDocLabels, $standardLabels)
            ->makeStandardSubView('failed-validation', $input, $apiDocLabels, $standardLabels)
            ->makeStandardSubView('failed-authentication', $input, $apiDocLabels, $standardLabels)
            ->makeStandardSubView('authentication', $input, $apiDocLabels, $standardLabels)
            ->makeStandardSubView('retrieved', $input, $apiDocLabels, $standardLabels);

        return $this;
    }

    protected function makeFieldsListView($input, Resource $resource, array $standardLabels = null)
    {
        $stub = $this->getStubContent('api-documentation-index-fields-list');
        $destenationFile = $this->getDestinationViewFullname($input->viewsDirectory, $input->prefix, 'fields-list');

        $this->replaceAuthorizationCall($stub, $this->getAuthorizationCall($input->withAuth, $resource->getApiDocLabels(), $standardLabels))
            ->replaceStandardLabels($stub, $standardLabels)
            ->replaceApiLabels($stub, $resource->getApiDocLabels())
            ->replaceModelName($stub, $input->modelName)
            ->replaceValidationRuleRequired($stub, $this->getRequiredRule($resource->getApiDocLabels(), $standardLabels))
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

    protected function getFieldsListBody($modelName, Resource $resource, array $standardLabels = null, $modelDefinition = false)
    {
        $stubName = 'api-documentation-index-fields-list-body-row';
        if ($modelDefinition) {
            $stubName = 'api-documentation-index-fields-list-body-row-for-model';
        }
        $template = $this->getStubContent($stubName);

        $final = [];
        foreach ($resource->getFields() as $field) {
            $stub = $template;

            $requiredTemplate = '';

            if ($field->isRequired()) {
                $requiredTemplate = $this->getRequiredRule($resource->getApiDocLabels(), $standardLabels);
            }

            // The replaceFieldType() must be executed before replaceStandardLabels
            // to replace the types in a title form.
            $this->replaceFieldType($stub, $this->getFileTypeTitle($field))
                ->replaceApiFieldDescription($stub, $this->getFieldDescription($field))
                ->replaceStandardLabels($stub, $standardLabels)
                ->replaceApiLabels($stub, $resource->getApiDocLabels())
                ->replaceModelName($stub, $modelName)
                ->replaceValidationRules($stub, $this->getValidationRules($field))
                ->replaceModelName($stub, $field->name, 'field_')
                ->replaceValidationRuleRequired($stub, $requiredTemplate);

            $final[] = $stub;
        }

        return implode(PHP_EOL, $final);
    }

    protected function getFieldDescription(Field $field)
    {
        $label = current($field->getApiDescription());

        if (!empty($label)) {
            return $this->getViewReadyAccessor($label);
        }

        return '';
    }

    protected function getFileTypeTitle(Field $field)
    {
        $type = 'string';

        if ($field->isNumeric()) {
            $type = 'integer';
        } elseif ($field->isBoolean()) {
            $type = 'boolean';
        } elseif ($field->isDateTime()) {
            $type = 'datetime';
        } elseif ($field->isTime()) {
            $type = 'time';
        } elseif ($field->isDate()) {
            $type = 'date';
        } elseif ($field->isDecimal()) {
            $type = 'decimal';
        } elseif ($field->isFile()) {
            $type = 'file';
        }

        return $this->getTemplateVariable($type . '_title');
    }

    protected function getValidationRules(Field $field)
    {
        $hasString = in_array('string', $field->getValidationRules());
        $hasNumber = empty(array_intersect(['integer', 'numeric'], $field->getValidationRules()));

        $rules = [];
        foreach ($field->getValidationRules() as $rule) {
            if (in_array($rule, ['required', 'nullable'])) {
                continue;
            }

            if ($hasString && starts_with($rule, 'min:')) {
                $rules[] = 'Minimum Length: ' . Str::trimEnd($rule, 'min:');
                continue;
            }

            if ($hasString && starts_with($rule, 'max:')) {
                $rules[] = 'Maximum Length: ' . Str::trimEnd($rule, 'max:');
                continue;
            }

            if ($hasNumber && starts_with($rule, 'min:')) {
                $rules[] = 'Minimum Value: ' . Str::trimEnd($rule, 'min:');
                continue;
            }

            if ($hasNumber && starts_with($rule, 'max:')) {
                $rules[] = 'Maximum Value: ' . Str::trimEnd($rule, 'max:');
                continue;
            }

            $rules[] = ucfirst($rule);
        }

        return implode('; ', $rules);
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
                $text = $this->getViewReadyAccessor($label);

                $this->replaceTemplate($label->id, $text, $stub);
            }
        }

        return $this;
    }

    /**
     * Get a view ready label accessor.
     *
     * @param  CrestApps\CodeGenerator\Model\Label $label
     *
     * @return string
     */
    protected function getViewReadyAccessor(Label $label)
    {
        if (!$label->isPlain) {
            return sprintf("{{ trans('%s') }}", $label->getAccessor());
        }

        return $label->text;
    }

    /**
     * Replaces the layout_name for the given stub,
     *
     * @param  string  $stub
     * @param  string  $name
     *
     * @return $this
     */
    protected function replaceLayoutName(&$stub, $name)
    {
        return $this->replaceTemplate('layout_name', $name, $stub);
    }

    /**
     * Replaces the field_type_title for the given stub,
     *
     * @param  string  $stub
     * @param  string  $name
     *
     * @return $this
     */
    protected function replaceFieldType(&$stub, $name)
    {
        return $this->replaceTemplate('field_type_title', $name, $stub);
    }

    /**
     * Replaces the include_parameter_for_authorized_request for the given stub,
     *
     * @param  string  $stub
     * @param  string  $name
     *
     * @return $this
     */
    protected function replaceAuthorizationCall(&$stub, $name)
    {
        return $this->replaceTemplate('include_parameter_for_authorized_request', $name, $stub);
    }

    /**
     * Replaces the include_failed_authentication_for_authorized_request for the given stub,
     *
     * @param  string  $stub
     * @param  string  $name
     *
     * @return $this
     */
    protected function replaceFailedAuthorizationCall(&$stub, $name)
    {
        return $this->replaceTemplate('include_failed_authentication_for_authorized_request', $name, $stub);
    }

    /**
     * Replaces the layout_name for the given stub,
     *
     * @param  string  $stub
     * @param  string  $name
     *
     * @return $this
     */
    protected function replacePathToViewHome(&$stub, $name)
    {
        return $this->replaceTemplate('path_to_view_home', $name, $stub);
    }

    /**
     * Replaces the authorized_request_for_index for the given stub,
     *
     * @param  string  $stub
     * @param  string  $name
     *
     * @return $this
     */
    protected function replaceAuthorizedRequestForIndex(&$stub, $name)
    {
        return $this->replaceTemplate('authorized_request_for_index', $name, $stub);
    }

    /**
     * Replaces the layout_name for the given stub,
     *
     * @param  string  $stub
     * @param  string  $name
     *
     * @return $this
     */
    protected function replaceValidationRuleRequired(&$stub, $name)
    {
        return $this->replaceTemplate('validation_rule_required', $name, $stub);
    }

    /**
     * Replaces the fields_list_for_body for the given stub,
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
     * Replaces the field_description for the given stub,
     *
     * @param  string  $stub
     * @param  string  $name
     *
     * @return $this
     */
    protected function replaceApiFieldDescription(&$stub, $name)
    {
        return $this->replaceTemplate('api_field_description', $name, $stub);
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
        $langFile = $this->option('language-filename') ?: self::makeLocaleGroup($modelName);
        $withAuth = $this->option('with-auth');
        $apiVersion = trim($this->option('api-version'));
        $force = $this->option('force');
        $viewsDirectory = ($apiVersion) ? Str::prefix($viewsDirectory, $apiVersion) : $viewsDirectory;

        return (object) compact(
            'modelName',
            'controllerName',
            'controllerDirectory',
            'resourceFile',
            'prefix',
            'langFile',
            'withAuth',
            'viewsDirectory',
            'apiVersion',
            'layoutName',
            'force'
        );
    }
}
