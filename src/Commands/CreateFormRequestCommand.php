<?php

namespace CrestApps\CodeGenerator\Commands;

use File;
use Exception;
use Illuminate\Console\Command;
use CrestApps\CodeGenerator\Support\Helpers;
use CrestApps\CodeGenerator\Traits\CommonCommand;

class CreateFormRequestCommand extends Command
{
    use CommonCommand;

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
    protected $description = 'Create form-request file for the model.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

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
        $fileFullName = $this->getRequestsPath() . $input->fileName . '.php';
        $validations = $this->getValidationRules($fields);

        $this->replaceFormRequestClass($stub, $input->fileName)
             ->replaceValidationRules($stub, $validations)
             ->makeFile($fileFullName, $stub, $input->force);
    }
    
     /**
     * Creates a file
     *
     * @param  string  $fileFullname
     * @param  string  $stub
     * @return $this
     */
    protected function makeFile($fileFullname, $stub, $force = false)
    {
        $this->createDirectory(dirname($fileFullname));
        
        if (File::exists($fileFullname) && !$force) {
            throw new Exception('There is a form-request class with the same name! To override existing file try passing "--force" command');
        }

        if (! File::put($fileFullname, $stub)) {
            throw new Exception('New form-request have been created');
        }

        $this->error('The form-request failed to create');

        return $this;
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
        $stub = str_replace('{{formRequestClass}}', $name, $stub);

        return $this;
    }

    /**
     * Replace the validation rules for the given stub.
     *
     * @param string $stub
     * @param string $rules
     *
     * @return $this
     */
    protected function replaceValidationRules(&$stub, $rules)
    {
        $stub = str_replace('{{validationRules}}', $rules, $stub);

        return $this;
    }
}
