<?php

namespace CrestApps\CodeGenerator\Commands;

use File;
use Exception;
use Illuminate\Console\Command;
use CrestApps\CodeGenerator\Support\Helpers;
use CrestApps\CodeGenerator\Traits\CommonCommand;
use CrestApps\CodeGenerator\Traits\GeneratorReplacers;
use CrestApps\CodeGenerator\Support\Config;

class CreateFormRequestCommand extends Command
{
    use CommonCommand, GeneratorReplacers;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:form-request
                            {model-name : The model name.}
                            {--class-name= : The name of the form-request class.}
                            {--fields= : The fields to create the validation rules from.}
                            {--fields-file= : File name to import fields from.}
                            {--template-name= : The template name to use when generating the code.}
                            {--force : This option will override the form-request if one already exists.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a form-request file for the model.';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $input = $this->getCommandInput();

        $stub = $this->getStubContent('form-request', $input->template);
        $fields = $this->getFields($input->fields, 'crestapps', $input->fieldsFile);
        $destenationFile = $this->getDestenationFile($input->fileName);
        $validations = $this->getValidationRules($fields);

        if ($this->alreadyExists($destenationFile)) {
            $this->error('The form-request already exists! To override the existing file, use --force option.');

            return false;
        }

        $this->replaceFormRequestClass($stub, $input->fileName)
             ->replaceValidationRules($stub, $validations)
             ->replaceAppName($stub, $this->getAppName())
             ->replaceGetDataMethod($stub, $this->getDataMethod($fields))
             ->replaceRequestVariable($stub, '$this')
             ->replaceTypeHintedRequestName($stub, '')
             ->replaceFileMethod($stub, $this->getUploadFileMethod($fields))
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
             ->replaceBooleadSnippet($stub, $this->getBooleanSnippet($fields))
             ->replaceStringToNullSnippet($stub, $this->getStringToNullSnippet($fields))
             ->replaceRequestNameComment($stub, '')
             ->replaceMethodVisibilityLevel($stub, 'public');

        return $stub;
    }

    /**
     * Gets the code that call the file-upload's method.
     *
     * @param array $fields
     *
     * @return string
     */
    protected function getFileSnippet(array $fields)
    {
        $code = '';
        $template = <<<EOF
        if (\$this->hasFile('%s')) {
            \$data['%s'] = \$this->moveFile(\$this->file('%s'));
        }
EOF;

        foreach ($fields as $field) {
            if ($field->isFile()) {
                $code .= sprintf($template, $field->name, $field->name, $field->name);
            }
        }

        return $code;
    }

    /**
     * Gets the code that is needed to check for bool property.
     *
     * @param array $fields
     *
     * @return string
     */
    protected function getBooleanSnippet(array $fields)
    {
        $code = '';

        foreach ($fields as $field) {
            if ($field->isBoolean() && $field->isCheckbox()) {
                $code .= sprintf("        \$data['%s'] = \$this->has('%s');", $field->name, $field->name) . PHP_EOL;
            }
        }

        return $code;
    }

    /**
     * Gets the code that is needed to convert empty string to null.
     *
     * @param array $fields
     *
     * @return string
     */
    protected function getStringToNullSnippet(array $fields)
    {
        $code = '';

        foreach ($fields as $field) {
            if ($field->isNullable && !$field->isPrimary && !$field->isAutoIncrement && !$field->isRequired() && !$field->isBoolean() && !$field->isFile()) {
                $code .= sprintf("        \$data['%s'] = !empty(\$this->input('%s')) ? \$this->input('%s') : null;", $field->name, $field->name, $field->name) . PHP_EOL;
            }
        }

        return $code;
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
        if ($this->containsfile($fields)) {
            return $this->getStubContent('controller-upload-method', $this->getTemplateName());
        }

        return '';
    }

    /**
     * Gets the destenation's fullname
     *
     * @return string
     */
    protected function getDestenationFile($name)
    {
        $path = app_path(Config::getRequestsPath());

        return Helpers::postFixWith($path, '/') . $name . '.php';
    }

    /**
     * Gets a clean user inputs.
     *
     * @return object
     */
    protected function getCommandInput()
    {
        $modelName = trim($this->argument('model-name'));
        $fileName = trim($this->option('class-name')) ?: $modelName . 'FormRequest';
        $fields = trim($this->option('fields'));
        $fieldsFile = trim($this->option('fields-file')) ?: Helpers::makeJsonFileName($modelName);
        $force = $this->option('force');
        $template = $this->option('template-name');

        return (object) compact('modelName','fileName', 'fields', 'fieldsFile', 'force', 'template');
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
        $stub = $this->strReplace('file_snippet', $snippet, $stub);

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
     * Replaces the request_name_comment for the given stub.
     *
     * @param $stub
     * @param $comment
     *
     * @return $this
     */
    protected function replaceRequestNameComment(&$stub, $comment)
    {
        $stub = $this->strReplace('request_name_comment', $comment, $stub);

        return $this;
    }

    /**
     * Replaces the visibility level of a giving stub
     *
     * @param  string  $stub
     * @param  string  $level
     *
     * @return $this
     */
    protected function replaceMethodVisibilityLevel(&$stub, $level)
    {
        $stub = $this->strReplace('visibility_level', $level, $stub);

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
     * Replaces the boolean snippet for the given stub.
     *
     * @param  string  $stub
     * @param  string  $snippet
     *
     * @return $this
     */
    protected function replaceBooleadSnippet(&$stub, $snippet)
    {
        $stub = $this->strReplace('boolean_snippet', $snippet, $stub);

        return $this;
    }

    /**
     * Replaces the form-request's name for the given stub.
     *
     * @param  string  $stub
     * @param  string  $snippet
     *
     * @return $this
     */
    protected function replaceStringToNullSnippet(&$stub, $snippet)
    {
        $stub = $this->strReplace('string_to_null_snippet', $snippet, $stub);

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
     * Replaces get_data_method template.
     *
     * @param  string  $stub
     * @param  string  $code
     *
     * @return $this
     */
    protected function replaceGetDataMethod(&$stub, $code)
    {
        $stub = $this->strReplace('get_data_method', $code, $stub);

        return $this;
    }
}
