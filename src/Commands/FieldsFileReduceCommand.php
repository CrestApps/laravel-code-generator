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
                            {file-name : The name of the file to create or write fields too.}
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
            $this->deleteFile($file);
            $this->info('No more fields left in the file. The field was deleted!');

            return false;
        }

        $string = json_encode($reducedFields, JSON_PRETTY_PRINT);

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
        $file = trim($this->argument('file-name'));
        $names = array_unique(Helpers::convertStringToArray(trim($this->option('names'))));

        return (object) compact('file', 'names');
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
