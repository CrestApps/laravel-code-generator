<?php

namespace CrestApps\CodeGenerator\Commands;

use DB;
use File;
use Exception;
use Illuminate\Console\Command;
use CrestApps\CodeGenerator\Models\Field;
use CrestApps\CodeGenerator\Support\Helpers;
use CrestApps\CodeGenerator\traits\CommonCommand;
use CrestApps\CodeGenerator\Support\Config;

class ResourceFileFromDatabaseCommand extends Command
{
    use CommonCommand;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'resource-file:from-database
                            {model-name : The model name that these files represent.}
                            {--table-name= : The database table name to fetch the field from.}
                            {--database-name= : The database name the table is stored in.}
                            {--resource-filename= : The destination file name to create.}
                            {--translation-for= : A comma seperated string of languages to create fields for.}
                            {--force : This option will override the view if one already exists.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates a resource-file from existing table in a database.';

    /**
     * The supported database drivers. lowercase only
     *
     * @var array
     */
    protected $drivers = ['mysql'];

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $destenationFile = $this->getDestinationFullname();

        if ($this->alreadyExists($destenationFile)) {
            $this->error('The resource-file already exists! To override the existing file, use --force option.');

            return false;
        }

        $content = $this->getJsonContent();

        if (Config::autoManageResourceMapper()) {
            $this->appendMapper($this->getModelName(), $this->getFilename());
        }

        $this->createFile($destenationFile, $content)
             ->info('The  "'. basename($destenationFile) .'" file was crafted successfully!');
    }

    /**
     * Removes mapping entry from the default mapping file.
     *
     * @param string $modelName
     * @param string $fieldsFileName
     *
     * @return void
     */
    protected function appendMapper($modelName, $fieldsFileName)
    {
        $file = $path = base_path(Config::getFieldsFilePath(Config::getDefaultMapperFileName()));

        $fields = [];

        if ($this->isFileExists($file)) {
            $content = $this->getFileContent($file);

            $existingFields = json_decode($content, true);

            if (is_null($existingFields)) {
                $this->error('The existing mapping file contains invalid json string. Please fix the file then try again');
                return false;
            }
            
            $existingFields = Collect($existingFields)->filter(function ($resource) use ($modelName) {
                return isset($resource->{'model-name'}) && $resource->{'model-name'} != $modelName;
            });

            $existingFields->push([
                'model-name'  => $modelName,
                'resource-file' => $fieldsFileName,
                'table-name'  => $this->getTableName(),
            ]);

            foreach ($existingFields as $existingField) {
                $fields[] = (object) $existingField;
            }
        }

        $this->putContentInFile($file, json_encode($fields, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    /**
     * Gets the full name of the destination file.
     *
     * @return string
     */
    protected function getDestinationFullname()
    {
        return base_path(Config::getFieldsFilePath($this->getFilename()));
    }

    /**
     * Gets the fields' collection after from using the connection's driver.
     *
     * @return array
     */
    protected function getJsonContent()
    {
        $driver = strtolower(DB::getDriverName());

        if (!in_array($driver, $this->drivers)) {
            throw new Exception('The database driver user is not supported!');
        }

        $class = sprintf('CrestApps\CodeGenerator\DatabaseParsers\%sParser', ucfirst($driver));

        $parser = new $class($this->getTableName(), $this->getDatabaseName(), $this->getLangugaes());

        return $parser->getResourceAsJson();
    }

    /**
     * Checks the options to see if the force command was provided.
     *
     * @return bool
     */
    protected function isForce()
    {
        return $this->option('force');
    }

    /**
     * Gets the destenation filename.
     *
     * @return string
     */
    protected function getFilename()
    {
        $filename = trim($this->option('resource-filename')) ?: Helpers::makeJsonFileName($this->getModelName());

        return str_finish($filename, '.json');
    }

    /**
     * Gets the model name.
     *
     * @return string
     */
    protected function getModelName()
    {
        return trim($this->argument('model-name'));
    }

    /**
     * Gets the database name.
     *
     * @return string
     */
    protected function getDatabaseName()
    {
        return trim($this->option('database-name')) ?: DB::getConfig('database');
    }

    /**
     * Gets the table name.
     *
     * @return string
     */
    protected function getTableName()
    {
        return trim($this->option('table-name')) ?: $this->makeTableName($this->getModelName());
    }

    /**
     * Gets the languages to create lang keys for.
     *
     * @return array
     */
    protected function getLangugaes()
    {
        return Helpers::convertStringToArray($this->option('translation-for'));
    }
}
