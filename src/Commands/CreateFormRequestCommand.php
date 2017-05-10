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
                            {class-name : The name of the form-request class.}
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
        $destenationFile = Config::getRequestsPath() . $input->fileName . '.php';
        $validations = $this->getValidationRules($fields);

        if ($this->alreadyExists($destenationFile)) {
            $this->error('The form-request already exists! To override the existing file, use --force option.');

            return false;
        }

        $this->replaceFormRequestClass($stub, $input->fileName)
             ->replaceValidationRules($stub, $validations)
             ->replaceAppName($stub, $this->getAppName())
             ->createFile($destenationFile, $stub)
             ->info('A new form-request have been crafted!');
    }

    /**
     * Gets a clean user inputs.
     *
     * @return object
     */
    protected function getCommandInput()
    {
        $fileName = trim($this->argument('class-name'));
        $fields =  trim($this->option('fields'));
        $fieldsFile = trim($this->option('fields-file'));
        $force = $this->option('force');
        $template = $this->option('template-name');

        return (object) compact('fileName', 'fields', 'fieldsFile', 'force', 'template');
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
}
