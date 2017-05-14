<?php

namespace CrestApps\CodeGenerator\Commands;

use Route;
use Exception;
use Illuminate\Console\Command;
use CrestApps\CodeGenerator\Traits\CommonCommand;
use CrestApps\CodeGenerator\Support\Helpers;
use CrestApps\CodeGenerator\Support\Config;
use CrestApps\CodeGenerator\Support\FieldTransformer;
use CrestApps\CodeGenerator\Commands\FieldsFileCreateCommand;

class FieldsFileAppendCommand extends Command
{
    use CommonCommand;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fields-file:append
                            {file-name : The name of the file to create or write fields too.}
                            {--names= : A comma seperate field names.}
                            {--data-types= : A comma seperated data-type for each field.}
                            {--translation-for= : A comma seperated string of languages to create fields for.}
                            {--html-types= : A comma seperated html-type for each field.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new fields file.';

    /**
     * Executes the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $input = $this->getCommandInput();
        $file = $this->getFilename($input->file);

        if(empty($input->names)) {
            $this->error('No names were provided. Please use the --names option to pass field names.');

            return false;
        }

        if (! $this->isFileExists($file)) {
            $this->warn('The fields-file does not exists.');

            $this->call('fields-file:create', $this->getCommandOptions($input));

            return false;
        }

        $existingFields = $this->mergeFields($file, $input);
        $string = json_encode($existingFields, JSON_PRETTY_PRINT);

        $this->putContentInFile($file, $string)
             ->info('New fields where appended to the file.');
    }

    protected function mergeFields($file, $input)
    {
        $content = $this->getFileContent($file);

        $existingFields = json_decode($content);

        if(is_null($existingFields)) {
            $this->error('The existing file contains invalid json string. Please fix the file then try again');
            return false;
        }
        $existingName = Collect($existingFields)->pluck('name')->all();
        $fields = FieldsFileCreateCommand::getFields($input, true);
        foreach($fields as $field) {

            if(in_array($field->name, $existingName)) {
                $this->warn('the field "' . $field->name . '" already exists in the file.');
                continue;
            }

            $existingName[] = $field->name;
            $existingFields[] = $field->toArray();
        }

        return $existingFields;
    }
    /**
     * Converts the current command's argument and options into an array.
     *
     * @return array
     */
    protected function getCommandOptions($input)
    {
        return [
            'file-name' => $input->file,
            '--names' => implode(',', $input->names),
            '--data-types' => implode(',', $input->dataTypes),
            '--html-types' => implode(',', $input->htmlTypes)
        ];
    }

    /**
     * Gets a clean user inputs.
     *
     * @return object
     */
    protected function getCommandInput()
    {
        $file = trim($this->argument('file-name'));
        $names = array_unique(Helpers::convertStringToArray($this->generatorOption('names')));
        $dataTypes = Helpers::convertStringToArray($this->generatorOption('data-types'));
        $htmlTypes = Helpers::convertStringToArray($this->generatorOption('html-types'));
        $transaltionFor = Helpers::convertStringToArray($this->generatorOption('translation-for'));

        return (object) compact('file', 'names', 'dataTypes', 'htmlTypes','transaltionFor');
    }

    /**
     * Gets the destenation filename.
     *
     * @param string $name
     *
     * @return string
     */
    protected function getFilename($name)
    {
        $path = base_path(Config::getFieldsFilePath());
        $name = Helpers::postFixWith($name, '.json');

        return $path . $name;
    }
}
