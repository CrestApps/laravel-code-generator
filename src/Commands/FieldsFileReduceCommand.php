<?php

namespace CrestApps\CodeGenerator\Commands;

use Route;
use Exception;
use Illuminate\Console\Command;
use CrestApps\CodeGenerator\Traits\CommonCommand;
use CrestApps\CodeGenerator\Support\Helpers;
use CrestApps\CodeGenerator\Support\Config;
use CrestApps\CodeGenerator\Support\FieldTransformer;

class FieldsFileReduceCommand extends Command
{
    use CommonCommand;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fields-file:reduce
                            {model-name : The model name that these files represent.}
                            {--fields-filename= : The destination file name to reduce.}
                            {--names= : A comma seperate field names.}';

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

        if (! $this->isFileExists($file)) {
            $this->error('The fields-file does not exists.');

            return false;
        }
        
        if(empty($input->names)) {
            $this->error('No names were provided. Please use the --names option to pass field names to remove.');

            return false;
        }

        $reducedFields = $this->reduceFields($file, $input);
        
        if(empty($reducedFields))
        {
            $this->callSilent('fields-file:delete',
                [
                    'model-name'        => $input->modelName,
                    '--fields-filename' => $file
                ]);

            $this->info('No more fields left in the file. The file (' . $file . ') was deleted!');

            return false;
        }

        $string = json_encode($reducedFields, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        $this->putContentInFile($file, $string)
             ->info('Fields where removed from exising file');
    }

    protected function reduceFields($file, $input)
    {
        $content = $this->getFileContent($file);

        $existingFields = json_decode($content);

        if(is_null($existingFields)) {
            $this->error('The existing file contains invalid json string. Please fix the file then try again');
            return false;
        }

        $keep = [];

        foreach($existingFields as $field) {

            if(!in_array($field->name, $input->names)) {
                $keep[] = $field;
            }
        }

        return $keep;
    }

    /**
     * Gets a clean user inputs.
     *
     * @return object
     */
    protected function getCommandInput()
    {
        $modelName = trim($this->argument('model-name'));
        $filename = trim($this->option('fields-filename'));
        $file = $filename ? str_finish($filename, '.json') : Helpers::makeJsonFileName($modelName);
        $names = array_unique(Helpers::convertStringToArray($this->generatorOption('names')));

        return (object) compact('modelName','file', 'names');
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
