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
                            {--controller-directory= : The directory where the controller should be created under. }
                            {--model-directory= : The path of the model.}
                            {--views-directory= : The name of the view path.}
                            {--fields= : Fields to use for creating the validation rules.}
                            {--fields-file= : File name to import fields from.}
                            {--routes-prefix= : Prefix of the route group.}
                            {--models-per-page=25 : The amount of models per page for index pages.}
                            {--lang-file-name= : The languages file name to put the labels in.}
                            {--form-request : This will extract the validation into a request form class.}
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
        return $this->getStubByName('controller');
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
            $stub = $this->getStubContent('controller-with-form-request');
            $formRequestName = $input->formRequestName;
            $this->makeFormRequest($input);
        }

        $fields = $this->getFields($input->fields, $input->langFile, $input->fieldsFile);

        return $this->replaceNamespace($stub, $name)
                    ->replaceViewNames($stub, $input->viewDirectory, $input->prefix)
                    ->replaceModelName($stub, $input->modelName)
                    ->replaceModelFullName($stub, $this->getModelFullName($input->modelDirectory, $input->modelName))
                    ->replaceRouteNames($stub, $input->modelName, $input->prefix)
                    ->replaceValidationRules($stub, $this->getValdiationRules($fields))
                    ->replaceFormRequestName($stub, $formRequestName)
                    ->replaceFormRequestFullName($stub, $this->getRequestsPath() . $formRequestName)
                    ->replacePaginationNumber($stub, $input->perPage)
                    ->replaceFileSnippet($stub, $this->getFileReadySnippet($fields))
                    ->replaceClass($stub, $name);
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
            'form-request-name' => $input->formRequestName,
            '--fields' => $input->fields,
            '--force' => $input->force,
            '--fields-file' => $input->fieldsFile
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
        $final = $this->getModelsPath();

        if(!empty($path))
        {
            $final .= Helpers::getPathWithSlash($path);
        }

        return Helpers::convertSlashToBackslash($final . ucfirst($name));
    }

    /**
     * Gets a clean user inputs.
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
        $formRequest = $this->option('form-request');

        $force = $this->option('force');
        
        $modelDirectory = trim($this->option('model-directory'));

        $formRequestName = ucfirst($modelName) . 'FormRequest';

        return (object) compact('viewDirectory','viewName','modelName','prefix','perPage','fileSnippet','modelDirectory',
                                'langFile','fields','formRequest','formRequestName','force','fieldsFile');
    }

    /**
     * Replace the modelFullName for the given stub.
     *
     * @param  string  $stub
     * @param  string  $modelFullName
     *
     * @return $this
     */
    protected function replaceModelFullName(&$stub, $modelFullName)
    {
        $stub = str_replace('{{modelFullName}}', $modelFullName, $stub);

        return $this;
    }

    /**
     * Replace the formRequestName for the given stub.
     *
     * @param  string  $stub
     * @param  string  $formRequestName
     *
     * @return $this
     */
    protected function replaceFormRequestName(&$stub, $formRequestName)
    {
        $stub = str_replace('{{formRequestName}}', $formRequestName, $stub);

        return $this;
    }
    
    /**
     * Replace the formRequestFullName for the given stub.
     *
     * @param  string  $stub
     * @param  string  $formRequestFullName
     *
     * @return $this
     */
    protected function replaceFormRequestFullName(&$stub, $formRequestFullName)
    {
        $stub = str_replace('{{formRequestFullName}}', $formRequestFullName, $stub);

        return $this;
    }

    /**
     * Replace the validationRules for the given stub.
     *
     * @param  string  $stub
     * @param  string  $validationRules
     *
     * @return $this
     */
    protected function replaceValidationRules(&$stub, $validationRules)
    {
        $stub = str_replace('{{validationRules}}', $validationRules, $stub);

        return $this;
    }

    /**
     * Replace the pagination placeholder for the given stub
     *
     * @param $stub
     * @param $perPage
     *
     * @return $this
     */
    protected function replacePaginationNumber(&$stub, $perPage)
    {
        $stub = str_replace('{{modelsPerPage}}', $perPage, $stub);

        return $this;
    }

    /**
     * Replace the file snippet for the given stub
     *
     * @param $stub
     * @param $fileSnippet
     *
     * @return $this
     */
    protected function replaceFileSnippet(&$stub, $fileSnippet)
    {
        $stub = str_replace('{{fileSnippet}}', $fileSnippet, $stub);

        return $this;
    }

    /**
     * Replace the fieldName for the given stub
     *
     * @param $stub
     * @param $fileSnippet
     *
     * @return $this
     */
    protected function replaceFieldName(&$stub, $fieldName)
    {
        $stub = str_replace('{{fieldName}}', $fieldName, $stub);

        return $this;
    }

    /**
     * Gets the desired class name from the input.
     *
     * @return string
     */
    public function getNameInput()
    {
        $nameFromArrgument = Helpers::upperCaseEveyWord(trim($this->argument('controller-name')));
        
        $path = $this->getControllersPath();

        $direcoty = trim($this->option('controller-directory'));

        if(!empty($directory))
        {
            $path .= Helpers::getPathWithSlash($directory);
        }

        return Helpers::convertSlashToBackslash($path . Helpers::postFixWith($nameFromArrgument, 'Controller'));
    }

    /**
     * Gets laravel ready field validation format from a giving string
     *
     * @param string $validations
     *
     * @return string
     */
    protected function getValdiationRules(array $fields)
    {
        $validations = '';

        foreach($fields as $field)
        {
            if(!empty($field->validationRules))
            {
                $validations .= sprintf("        '%s' => '%s',\n    ", $field->name, implode('|', $field->validationRules));
            }
        }

        return $validations;
    }


    protected function getFileReadySnippet(array $fields)
    {
        $fileSnippet = '';

        foreach ($fields as $field) 
        {
            //To Do
        }

        return $fileSnippet;
    }

    protected function getSnippet()
    {
        return <<<EOD
if (\$request->hasFile('{{fieldName}}')) {
    \$uploadPath = public_path('/uploads/');

    \$extension = \$request->file('{{fieldName}}')->getClientOriginalExtension();
    \$fileName = rand(11111, 99999) . '.' . \$extension;

    \$request->file('{{fieldName}}')->move(\$uploadPath, \$fileName);
    \$requestData['{{fieldName}}'] = \$fileName;
}
EOD;
    }
}
