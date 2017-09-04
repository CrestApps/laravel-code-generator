<?php

namespace CrestApps\CodeGenerator\Commands;

use Route;
use Exception;
use Illuminate\Console\Command;
use CrestApps\CodeGenerator\Traits\CommonCommand;
use CrestApps\CodeGenerator\Support\Helpers;
use CrestApps\CodeGenerator\Support\Config;
use CrestApps\CodeGenerator\Support\ResourceTransformer;

class ResourceFileReduceCommand extends Command
{
    use CommonCommand;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'resource-file:reduce
                            {model-name : The model name that these files represent.}
                            {--resource-filename= : The destination file name to reduce.}
                            {--names= : A comma seperate field names.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reduce field(s) from existing resource-file.';

    /**
     * Executes the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $input = $this->getCommandInput();
        $file = $this->getFilename($input->file);

        if (! $this->isFileExists($file)) {
            $this->error('The resource-file does not exists.');

            return false;
        }
        
        if (empty($input->names)) {
            $this->error('No names were provided. Please use the --names option to pass field names to remove.');

            return false;
        }
        $totalReducedFields = 0;
        $resource = $this->reduceFields($file, $input, $totalReducedFields);
        
        if ($resource->isEmpty()) {
            $this->callSilent(
                'resource-file:delete',
                [
                    'model-name'          => $input->modelName,
                    '--resource-filename' => $file
                ]
            );

            $this->info('All fields were removed from the resource-file. The file "' . basename($file) . '" was deleted successfully!');

            return false;
        }

        $content = json_encode($resource->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        $this->putContentInFile($file, $content)
             ->info($totalReducedFields . ' fields where removed from the "' . basename($file) . '" file.');
    }

    /**
     * Reduces the fields from a giving file
     *
     * @param string $file
     * @param object $input
     *
     * @return mixed
     */
    protected function reduceFields($file, $input, &$totalReducedFields)
    {
        $resource = ResourceTransformer::fromJson($this->getFileContent($file), 'crestapps');

        $keep = [];
        
        foreach ($resource->fields as $field) {
            if (in_array($field->name, $input->names) || in_array($field->name, $keep)) {
                $totalReducedFields++;
                continue;
            }

            $keep[] = $field;
        }

        $resource->fields = $keep;

        return $resource;
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

        return (object) compact(
            'modelName',
            'file',
            'names'
        );
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

        return $path . $name;
    }
}
