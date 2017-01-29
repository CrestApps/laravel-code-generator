<?php

namespace CrestApps\CodeGenerator\Commands;

use Illuminate\Console\GeneratorCommand;
use CrestApps\CodeGenerator\Traits\CommonCommand;
use CrestApps\CodeGenerator\Support\Helpers;

class CreateControllerCommand extends GeneratorCommand
{
    use CommonCommand;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:controller
                            {controller-name : The name of the controler.}
                            {--model-name= : The model name that this controller will represent.}
                            {--controller-directory= : The directory where the controller should be created under.}
                            {--model-directory= : The path where the model should be created under.}
                            {--views-directory= : The path where the views should be created under.}
                            {--fields= : Fields to use for creating the validation rules.}
                            {--fields-file= : File name to import fields from.}
                            {--routes-prefix= : Prefix of the route group.}
                            {--models-per-page=25 : The amount of models per page for index pages.}
                            {--lang-file-name= : The languages file name to put the labels in.}
                            {--with-form-request : This will extract the validation into a request form class.}
                            {--template-name= : The template name to use when generating the code.}
                            {--force : This option will override the controller if one already exists.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new resource controller.';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Controller';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return $this->getStubByName('controller', $this->getTemplateName() );
    }

    /**
     * Build the model class with the given name.
     *
     * @param  string  $name
     *
     * @return string
     */
    protected function buildClass($name)
    {
        $stub = $this->files->get($this->getStub());
        $input = $this->getCommandInput();

        $formRequestName = 'Request';

        if($input->formRequest)
        {
            $stub = $this->getStubContent('controller-with-form-request', $input->template);
            $formRequestName = $input->formRequestName;
            $this->makeFormRequest($input);
        }

        $fields = $this->getFields($input->fields, $input->langFile, $input->fieldsFile);

        return $this->replaceNamespace($stub, $name)
                    ->replaceViewNames($stub, $input->viewDirectory, $input->prefix)
                    ->replaceModelName($stub, $input->modelName)
                    ->replaceModelFullName($stub, $this->getModelFullName($input->modelDirectory, $input->modelName))
                    ->replaceRouteNames($stub, $input->modelName, $input->prefix)
                    ->replaceValidationRules($stub, $this->getValidationRules($fields))
                    ->replaceFormRequestName($stub, $formRequestName)
                    ->replaceFormRequestFullName($stub, $this->getRequestsNamespace() . $formRequestName)
                    ->replacePaginationNumber($stub, $input->perPage)
                    ->replaceFileSnippet($stub, $this->getFileReadySnippet($fields))
                    ->replaceFileMethod($stub, $this->getUploadFileMethod($fields))
                    ->replaceClass($stub, $name);
    }

    /**
     * Gets the method's stub that handels the file uploading.
     *
     * @param array $fields
     *
     * @return string
     */
    protected function getUploadFileMethod(array $fields)
    {
        if($this->isContainfile($fields))
        {
            return $this->getStubContent('controller-upload-method', $this->getTemplateName());
        }

        return '';
    }

    /**
     * Gets the Requests namespace
     *
     * @return string
     */
    protected function getRequestsNamespace()
    {
        $path = str_replace(app_path(), '', $this->getRequestsPath());

        return ltrim(Helpers::convertSlashToBackslash($path), '\\');
    }

    /**
     * Checks if a giving fields array conatins at least one multiple answers' field.
     *
     * @param array
     *
     * @return bool
     */
    protected function isContainMultipleAnswers(array $fields)
    {
        $filtered = array_filter($fields, function($field){
            return $field->isMultipleAnswers;
        });

        return count($filtered) > 0;
    }

    /**
     * Calls the create:form-request command
     *
     * @param  CrestApps\CodeGenerator\Support\ViewInput $input
     *
     * @return $this
     */
    protected function makeFormRequest($input)
    {
        $this->callSilent('create:form-request', 
        [
            'class-name' => $input->formRequestName,
            '--fields' => $input->fields,
            '--force' => $input->force,
            '--fields-file' => $input->fieldsFile,
            '--template-name' => $input->template
        ]);

        return $this;
    }

    /**
     * Gets the full model name
     *
     * @param  string $path
     * @param  string $name
     *
     * @return string
     */
    protected function getModelFullName($path, $name)
    {
        $final = !empty($path) ? $this->getModelsPath() . Helpers::getPathWithSlash($path) : $this->getModelsPath();

        return Helpers::convertSlashToBackslash($final . ucfirst($name));
    }

    /**
     * Gets a clean command-line arguments and options.
     *
     * @return object
     */
    protected function getCommandInput()
    {
        $controllerName = trim($this->argument('controller-name'));
        $modelName = strtolower(trim($this->option('model-name')) ?: str_singular(Helpers::removePostFixWith($controllerName, 'Controller')));
        $viewDirectory = trim($this->option('views-directory'));
        $prefix = trim($this->option('routes-prefix'));
        $perPage = intval($this->option('models-per-page'));
        $fields = trim($this->option('fields'));
        $fieldsFile = trim($this->option('fields-file'));
        $langFile = trim($this->option('lang-file-name')) ?: strtolower(str_plural($modelName));
        $formRequest = $this->option('with-form-request');
        $force = $this->option('force');
        $modelDirectory = trim($this->option('model-directory'));
        $formRequestName = ucfirst($modelName) . 'FormRequest';
        $template = $this->getTemplateName();

        return (object) compact('viewDirectory','viewName','modelName','prefix','perPage','fileSnippet','modelDirectory',
                                'langFile','fields','formRequest','formRequestName','force','fieldsFile','template');
    }

    /**
     * Replaces the model's fullname for the given stub.
     *
     * @param  string  $stub
     * @param  string  $name
     *
     * @return $this
     */
    protected function replaceModelFullName(&$stub, $name)
    {
        $stub = str_replace('{{modelFullName}}', $name, $stub);

        return $this;
    }

    /**
     * Replaces the form-request's name for the given stub.
     *
     * @param  string  $stub
     * @param  string  $name
     *
     * @return $this
     */
    protected function replaceFormRequestName(&$stub, $name)
    {
        $stub = str_replace('{{formRequestName}}', $name, $stub);

        return $this;
    }
    
    /**
     * Replace sthe form-request's fullname for the given stub.
     *
     * @param  string  $stub
     * @param  string  $name
     *
     * @return $this
     */
    protected function replaceFormRequestFullName(&$stub, $name)
    {
        $stub = str_replace('{{formRequestFullName}}', $name, $stub);

        return $this;
    }

    /**
     * Replaces the validation rules for the given stub.
     *
     * @param  string  $stub
     * @param  string  $rules
     *
     * @return $this
     */
    protected function replaceValidationRules(&$stub, $rules)
    {
        $stub = str_replace('{{validationRules}}', $rules, $stub);

        return $this;
    }

    /**
     * Replaces the models per page total for the given stub.
     *
     * @param $stub
     * @param $total
     *
     * @return $this
     */
    protected function replacePaginationNumber(&$stub, $total)
    {
        $stub = str_replace('{{modelsPerPage}}', $total, $stub);

        return $this;
    }

    /**
     * Replaces the file snippet for the given stub.
     *
     * @param $stub
     * @param $snippet
     *
     * @return $this
     */
    protected function replaceFileSnippet(&$stub, $snippet)
    {
        $stub = str_replace('{{fileSnippet}}', $snippet, $stub);

        return $this;
    }

    /**
     * Replaces the upload-method's code for the given stub.
     *
     * @param $stub
     * @param $method
     *
     * @return $this
     */
    protected function replaceFileMethod(&$stub, $method)
    {
        $stub = str_replace('{{uploadMethod}}', $method, $stub);

        return $this;
    }

    /**
     * Replaces the field's name for the given stub.
     *
     * @param $stub
     * @param $nane
     *
     * @return $this
     */
    protected function replaceFieldName(&$stub, $name)
    {
        $stub = str_replace('{{fieldName}}', $name, $stub);

        return $this;
    }

    /**
     * Gets the desired class name from the command-line.
     *
     * @return string
     */
    public function getNameInput()
    {
        $name = Helpers::upperCaseEveyWord(trim($this->argument('controller-name')));
        $path = $this->getControllersPath();
        $directory = trim($this->option('controller-directory'));

        if(!empty($directory))
        {
            $path .= Helpers::getPathWithSlash($directory);
        }

        return Helpers::convertSlashToBackslash($path . Helpers::postFixWith($name, 'Controller'));
    }
    
    /**
     * Gets the code that call the file-upload's method.
     *
     * @param array $fields
     *
     * @return string
     */
    protected function getFileReadySnippet(array $fields)
    {
        $code = '';

        foreach($fields as $field)
        {
            if($field->isFile())
            {
                $code = ($code) ?: '$this';
                $code .= sprintf("->uploadFile('%s', \$data)", $field->name);
            }
        }

        return $code != '' ? $code . ';' : $code;
    }

}
