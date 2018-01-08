<?php

namespace CrestApps\CodeGenerator\Commands\Framework;

use CrestApps\CodeGenerator\Commands\Bases\ControllerRequestCommandBase;
use CrestApps\CodeGenerator\Models\Resource;
use CrestApps\CodeGenerator\Support\Config;
use CrestApps\CodeGenerator\Support\Helpers;

class CreateFormRequestCommand extends ControllerRequestCommandBase
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:form-request
                            {model-name : The model name.}
                            {--class-name= : The name of the form-request class.}
                            {--resource-file= : The name of the resource-file to import from.}
                            {--routes-prefix=default-form : Prefix of the route group.}
                            {--template-name= : The template name to use when generating the code.}
                            {--with-auth : Generate the form-request with Laravel auth middlewear. }
                            {--form-request-directory= : The directory of the form-request.}
                            {--force : This option will override the form-request if one already exists.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a form-request class for the model.';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $input = $this->getCommandInput();

        $resources = Resource::fromFile($input->resourceFile, 'crestapps');

        $destenationFile = $this->getDestenationFile($input->fileName, $input->formRequestDirectory);

        if ($this->hasErrors($resources, $destenationFile)) {
            return false;
        }

        $stub = $this->getStubContent('form-request', $input->template);

        $validations = $this->getValidationRules($resources->fields, $input->modelName, $input->formRequestDirectory);

        $this->replaceFormRequestClass($stub, $input->fileName)
            ->replaceValidationRules($stub, $validations)
            ->replaceFileValidationSnippet($stub, $this->getFileValidationSnippet($resources->fields, $input))
            ->replaceClassNamespace($stub, $this->getRequestsNamespace($input->formRequestDirectory))
            ->replaceGetDataMethod($stub, $this->getDataMethod($resources->fields, $input))
            ->replaceRequestVariable($stub, '$this')
            ->replaceAuthBoolean($stub, $this->getAuthBool($input->withAuth))
            ->replaceUseCommandPlaceholder($stub, $this->getRequiredUseClasses($resources->fields, $input->withAuth))
            ->replaceTypeHintedRequestName($stub, '')
            ->replaceFileMethod($stub, $this->getUploadFileMethod($resources->fields))
            ->createFile($destenationFile, $stub)
            ->info('A new form-request have been crafted!');
    }

    /**
     * Build the model class with the given name.
     *
     * @param  CrestApps\CodeGenerator\Models\Resource $resource
     * @param string $destenationFile
     *
     * @return bool
     */
    protected function hasErrors(Resource $resource, $destenationFile)
    {
        $hasErrors = false;

        if ($resource->isProtected('form-request')) {
            $this->warn('The form-request is protected and cannot be regenerated. To regenerate the file, unprotect it from the resource file.');

            $hasErrors = true;
        }

        if ($this->alreadyExists($destenationFile)) {
            $this->error('The form-request already exists! To override the existing file, use --force option.');

            $hasErrors = true;
        }

        return $hasErrors;

    }
    /**
     * Gets the signature of the getData method.
     *
     * @param array $fields
     *
     * @return string
     */
    protected function getDataMethod(array $fields)
    {
        $stub = $this->getStubContent('controller-getdata-method');

        $this->replaceFileSnippet($stub, $this->getFileSnippet($fields, '$this'))
            ->replaceValidationRules($stub, $this->getValidationRules($fields))
            ->replaceFillables($stub, $this->getFillables($fields))
            ->replaceBooleadSnippet($stub, $this->getBooleanSnippet($fields))
            ->replaceStringToNullSnippet($stub, $this->getStringToNullSnippet($fields))
            ->replaceRequestNameComment($stub, '')
            ->replaceMethodVisibilityLevel($stub, 'public');

        $stub = str_replace(PHP_EOL . PHP_EOL, PHP_EOL, $stub);

        return $stub;
    }

    /**
     * Gets the boolean value that the autherize() method will return.
     *
     * @param array $fields
     *
     * @return string
     */
    protected function getAuthBool($withAuth)
    {
        return $withAuth ? 'Auth::check()' : 'false';
    }

    /**
     * Gets the using statement for Auth.
     *
     * @param array $fields
     * @param bool $withAuth
     *
     * @return string
     */
    protected function getRequiredUseClasses(array $fields, $withAuth)
    {
        $commands = [];
        if ($withAuth) {
            $commands[] = $this->getUseClassCommand('Auth');
        }

        foreach ($fields as $field) {
            // Extract the name spaces fromt he custom rules
            $customRules = $this->extractCustomValidationRules($field->getValidationRule());
            $namespaces = $this->extractCustomValidationNamespaces($customRules);
            foreach ($namespaces as $namespace) {
                $commands[] = $this->getUseClassCommand($namespace);
            }
        }

        usort($commands, function ($a, $b) {
            return strlen($a) - strlen($b);
        });

        return implode(PHP_EOL, array_unique($commands));
    }

    /**
     * Checks if the ConvertEmptyStringsToNull middleware is registered or not
     *
     * @param string $string
     *
     * @return string
     */
    protected function isConvertEmptyStringsToNullRegistered()
    {
        $kernal = $this->getLaravel()->make(\App\Http\Kernel::class);

        return $kernal->hasMiddleware(\Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class);
    }

    /**
     * Gets the destenation's fullname
     *
     * @param string $name
     * @param string $path
     *
     * @return string
     */
    protected function getDestenationFile($name, $path)
    {
        return str_finish(app_path(Config::getRequestsPath($path)), '/') . $name . '.php';
    }

    /**
     * Gets the Requests namespace
     *
     * @param string $path
     *
     * @return string
     */
    protected function getRequestsNamespace($path)
    {
        $path = str_finish($path, '\\');

        $path = Helpers::getAppNamespace() . Config::getRequestsPath($path);

        return rtrim(Helpers::convertSlashToBackslash($path), '\\');
    }

    /**
     * Gets a clean user inputs.
     *
     * @return object
     */
    protected function getCommandInput()
    {
        $modelName = trim($this->argument('model-name'));
        $fileName = trim($this->option('class-name')) ?: Helpers::makeFormRequestName($modelName);
        $resourceFile = trim($this->option('resource-file')) ?: Helpers::makeJsonFileName($modelName);
        $prefix = ($this->option('routes-prefix') == 'default-form') ? Helpers::makeRouteGroup($modelName) : $this->option('routes-prefix');

        $force = $this->option('force');
        $withAuth = $this->option('with-auth');
        $template = $this->option('template-name');
        $formRequestDirectory = trim($this->option('form-request-directory'));

        return (object) compact(
            'formRequestDirectory',
            'withAuth',
            'modelName',
            'prefix',
            'fileName',
            'resourceFile',
            'force',
            'template'
        );
    }

    /**
     * Replaces the class name space
     *
     * @param $stub
     * @param $snippet
     *
     * @return $this
     */
    protected function replaceNamespace(&$stub, $snippet)
    {
        return $this->replaceTemplate('class_namespace', $snippet, $stub);
    }

    /**
     * Replaces the type_hinted_request_name for the given stub.
     *
     * @param $stub
     * @param $code
     *
     * @return $this
     */
    protected function replaceTypeHintedRequestName(&$stub, $code)
    {
        return $this->replaceTemplate('type_hinted_request_name', $code, $stub);
    }

    /**
     * Replaces the autherized_boolean for the given stub.
     *
     * @param $stub
     * @param $code
     *
     * @return $this
     */
    protected function replaceAuthBoolean(&$stub, $code)
    {
        return $this->replaceTemplate('autherized_boolean', $code, $stub);
    }

    /**
     * Replaces request variable.
     *
     * @param  string  $stub
     * @param  string  $variable
     *
     * @return $this
     */
    protected function replaceRequestVariable(&$stub, $variable)
    {
        return $this->replaceTemplate('request_variable', $variable, $stub);
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
        return $this->replaceTemplate('upload_method', $method, $stub);
    }

    /**
     * Replaces the form-request class for the given stub.
     *
     * @param string $stub
     * @param string $name
     *
     * @return $this
     */
    protected function replaceFormRequestClass(&$stub, $name)
    {
        return $this->replaceTemplate('form_request_class', $name, $stub);
    }

    /**
     * Replaces the class name space
     *
     * @param $stub
     * @param $snippet
     *
     * @return $this
     */
    protected function replaceClassNamespace(&$stub, $snippet)
    {
        return $this->replaceTemplate('class_namespace', $snippet, $stub);
    }
}
