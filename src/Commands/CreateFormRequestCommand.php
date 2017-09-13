<?php

namespace CrestApps\CodeGenerator\Commands;

use CrestApps\CodeGenerator\Commands\Bases\ControllerCommand;
use CrestApps\CodeGenerator\Support\Config;
use CrestApps\CodeGenerator\Support\Helpers;
use CrestApps\CodeGenerator\Support\ResourceTransformer;

class CreateFormRequestCommand extends ControllerCommand
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

        $stub = $this->getStubContent('form-request', $input->template);
        $resources = ResourceTransformer::fromFile($input->resourceFile, 'crestapps');
        $destenationFile = $this->getDestenationFile($input->fileName, $input->formRequestDirectory);

        $validations = $this->getValidationRules($resources->fields, $input->modelName, $input->formRequestDirectory);

        if ($this->alreadyExists($destenationFile)) {
            $this->error('The form-request already exists! To override the existing file, use --force option.');

            return false;
        }
        $this->replaceFormRequestClass($stub, $input->fileName)
            ->replaceValidationRules($stub, $validations)
            ->replaceClassNamespace($stub, $this->getRequestsNamespace($input->formRequestDirectory))
            ->replaceGetDataMethod($stub, $this->getDataMethod($resources->fields))
            ->replaceRequestVariable($stub, '$this')
            ->replaceAuthBoolean($stub, $this->getAuthBool($input->withAuth))
            ->replaceAuthNamespace($stub, $this->getAuthNamespace($input->withAuth))
            ->replaceTypeHintedRequestName($stub, '')
            ->replaceFileMethod($stub, $this->getUploadFileMethod($resources->fields))
            ->createFile($destenationFile, $stub)
            ->info('A new form-request have been crafted!');
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

        $this->replaceFileSnippet($stub, $this->getFileSnippet($fields))
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
     *
     * @return string
     */
    protected function getAuthNamespace($withAuth)
    {
        return $withAuth ? 'use Auth;' : '';
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

        $path = $this->getAppNamespace() . Config::getRequestsPath($path);

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
        $force = $this->option('force');
        $withAuth = $this->option('with-auth');
        $template = $this->option('template-name');
        $formRequestDirectory = trim($this->option('form-request-directory'));

        return (object) compact(
            'formRequestDirectory',
            'withAuth',
            'modelName',
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
        $stub = $this->strReplace('class_namespace', $snippet, $stub);

        return $this;
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
        $stub = $this->strReplace('type_hinted_request_name', $code, $stub);

        return $this;
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
        $stub = $this->strReplace('autherized_boolean', $code, $stub);

        return $this;
    }

    /**
     * Replaces the autherized_boolean for the given stub.
     *
     * @param $stub
     * @param $code
     *
     * @return $this
     */
    protected function replaceAuthNamespace(&$stub, $code)
    {
        $stub = $this->strReplace('use_auth_namespace', $code, $stub);

        return $this;
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
        $stub = $this->strReplace('request_variable', $variable, $stub);

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
        $stub = $this->strReplace('upload_method', $method, $stub);

        return $this;
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
        $stub = $this->strReplace('form_request_class', $name, $stub);

        return $this;
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
        $stub = $this->strReplace('class_namespace', $snippet, $stub);

        return $this;
    }
}
