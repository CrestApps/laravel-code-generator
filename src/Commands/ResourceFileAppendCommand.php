<?php

namespace CrestApps\CodeGenerator\Commands;

use Route;
use Exception;
use Illuminate\Console\Command;
use CrestApps\CodeGenerator\Traits\CommonCommand;
use CrestApps\CodeGenerator\Support\Helpers;
use CrestApps\CodeGenerator\Support\Config;
use CrestApps\CodeGenerator\Support\FieldTransformer;
use CrestApps\CodeGenerator\Support\ResourceTransformer;
use CrestApps\CodeGenerator\Commands\FieldsFileCreateCommand;

class ResourceFileAppendCommand extends Command
{
    use CommonCommand;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'resource-file:append
                            {model-name : The model name that these files represent.}
                            {--resource-filename= : The destination file name to append too.}
                            {--names= : A comma seperate field names.}
                            {--data-types= : A comma seperated data-type for each field.}
                            {--translation-for= : A comma seperated string of languages to create fields for.}
                            {--html-types= : A comma seperated html-type for each field.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Append new field(s) to existing resource-file.';

    /**
     * Executes the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $input = $this->getCommandInput();
        $file = $this->getFilename($input->file);

        if (empty($input->names)) {
            $this->error('No names were provided. Please use the --names option to pass field names.');

            return false;
        }

        if (! $this->isFileExists($file)) {
            $this->warn('The resource-file does not exists.');

            $this->call('resource-file:create', $this->getCommandOptions($input));

            return false;
        }
        $totalAddedFields = 0;
        $resource = $this->mergeFields($file, $input, $totalAddedFields);
        
        $content = json_encode($resource->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        $this->putContentInFile($file, $content)
             ->info($totalAddedFields . ' new fields where appended to the "' . basename($file) . '" file.');
    }

    /**
     * Merges the giving file's content to the new fields.
     *
     * @param string $file
     * @param (object) $input
     *
     * @return CrestApps\CodeGenerator\Models\Resource
     */
    protected function mergeFields($file, $input, &$mergeFields)
    {
        $resource = ResourceTransformer::fromJson($this->getFileContent($file), 'crestapps');
        
        $existingNames = Collect($resource->fields)->pluck('name')->all();
        $fields = ResourceFileCreateCommand::getFields($input, true);
        foreach ($fields as $field) {
            if (in_array($field->name, $existingNames)) {
                $this->warn('The field "' . $field->name . '" already exists in the file.');
                continue;
            }

            $existingName[] = $field->name;
            $resource->fields[] = $field;
            $mergeFields++;
        }

        return $resource;
    }

    /**
     * Converts the current command's argument and options into an array.
     *
     * @return array
     */
    protected function getCommandOptions($input)
    {
        return [
            'model-name'          => $input->modelName,
            '--resource-filename' => $input->file,
            '--names'             => implode(',', $input->names),
            '--data-types'        => implode(',', $input->dataTypes),
            '--html-types'        => implode(',', $input->htmlTypes)
        ];
    }

    /**
     * Gets a clean user inputs.
     *
     * @return object
     */
    protected function getCommandInput()
    {
        $modelName = trim($this->argument('model-name'));
        $filename = trim($this->option('resource-filename'));
        $file = $filename ? str_finish($filename, '.json') : Helpers::makeJsonFileName($modelName);
        $names = array_unique(Helpers::convertStringToArray($this->generatorOption('names')));
        $dataTypes = Helpers::convertStringToArray($this->generatorOption('data-types'));
        $htmlTypes = Helpers::convertStringToArray($this->generatorOption('html-types'));
        $transaltionFor = Helpers::convertStringToArray($this->generatorOption('translation-for'));

        return (object) compact('modelName', 'file', 'names', 'dataTypes', 'htmlTypes', 'transaltionFor');
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
