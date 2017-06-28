<?php

namespace CrestApps\CodeGenerator\Commands;

use Route;
use Exception;
use Illuminate\Console\Command;
use CrestApps\CodeGenerator\Traits\CommonCommand;
use CrestApps\CodeGenerator\Support\Helpers;
use CrestApps\CodeGenerator\Support\Config;
use CrestApps\CodeGenerator\Support\FieldTransformer;

class FieldsFileDeleteCommand extends Command
{
    use CommonCommand;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fields-file:delete
                            {model-name : The model name that these files represent.}
                            {--fields-filename= : The destination file name to delete.}';

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

        if (Config::autoManageResourceMapper()) {
            $this->reduceMapper($input->modelName);
        }

        if (! $this->isFileExists($file)) {
            $this->error('The fields-file does not exists.');

            return false;
        }

        $this->deleteFile($file);
        $this->info('Fields where removed from exising file');
    }

    /**
     * Removes mapping entry from the default mapping file.
     *
     * @param string $modelName
     *
     * @return void
     */
    protected function reduceMapper($modelName)
    {
        $file = $path = base_path(Config::getFieldsFilePath(Config::getDefaultMapperFileName()));

        $fields = [];

        if ($this->isFileExists($file)) {
            $content = $this->getFileContent($file);

            $existingFields = json_decode($content);

            if (is_null($existingFields)) {
                $this->error('The existing mapping file contains invalid json string. Please fix the file then try again');
                return false;
            }
            
            $fields = Collect($existingFields)->filter(function ($resource) use ($modelName) {
                return isset($resource->{'model-name'}) && $resource->{'model-name'} != $modelName;
            });
        }

        $this->putContentInFile($file, json_encode($fields, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
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

        return (object) compact('modelName', 'file');
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
