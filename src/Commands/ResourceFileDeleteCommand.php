<?php

namespace CrestApps\CodeGenerator\Commands;

use Route;
use Exception;
use Illuminate\Console\Command;
use CrestApps\CodeGenerator\Traits\CommonCommand;
use CrestApps\CodeGenerator\Support\Helpers;
use CrestApps\CodeGenerator\Support\Config;
use CrestApps\CodeGenerator\Support\FieldTransformer;

class ResourceFileDeleteCommand extends Command
{
    use CommonCommand;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'resource-file:delete
                            {model-name : The model name that these files represent.}
                            {--resource-filename= : The destination file name to delete.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete existing resource-file.';

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
            $this->error('The resource-file does not exists.');

            return false;
        }

        $this->deleteFile($file);
        $this->info('The "'. basename($file) .'" file was successfully deleted!');
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

        $maps = [];

        if ($this->isFileExists($file)) {
            $existingMaps = json_decode($this->getFileContent($file));

            if (is_null($existingMaps)) {
                $this->error('The existing mapping file contains invalid json string. Please fix the file then try again');
                return false;
            }
            
            $maps = Collect($existingMaps)->filter(function ($map) use ($modelName) {
                return isset($map->{'model-name'}) && $map->{'model-name'} != $modelName;
            });
        }

        $content = json_encode($maps, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        $this->putContentInFile($file, $content);
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
