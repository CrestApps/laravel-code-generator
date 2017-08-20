<?php

namespace CrestApps\CodeGenerator\Commands;

use Route;
use Exception;
use Illuminate\Console\Command;
use CrestApps\CodeGenerator\Traits\CommonCommand;
use CrestApps\CodeGenerator\Support\Helpers;
use CrestApps\CodeGenerator\Support\Config;
use CrestApps\CodeGenerator\Support\FieldTransformer;

class ResourceFileCreateCommand extends Command
{
    use CommonCommand;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'resource-file:create
                            {model-name : The model name that these files represent.}
                            {--resource-filename= : The destination file name to create.}
                            {--names= : A comma seperate field names.}
                            {--data-types= : A comma seperated data-type for each field.}
                            {--html-types= : A comma seperated html-type for each field.}
                            {--without-primary-key : The directory where the controller is under.}
                            {--translation-for= : A comma seperated string of languages to create fields for.}
                            {--force : Override existing file if one exists.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new resource-file.';

    /**
     * Executes the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $input = $this->getCommandInput();
        $file = $this->getFilename($input->file);

        if ($this->isFileExists($file) && ! $input->force) {
            $this->error('The resource-file already exists! To override the existing file, use --force option to append.');

            return false;
        }

        if (empty($input->names)) {
            $this->error('No names were provided. Please use the --names option to pass field names.');

            return false;
        }

        if (Config::autoManageResourceMapper()) {
            $this->appendMapper($input->modelName, $input->file);
        }

        $fields = $this->getFields($input, $input->withoutPrimaryKey);
        $string = $this->getFieldAsJson($fields);

        $this->createFile($file, $string)
             ->info('New resource-file was crafted!');
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
        $withoutPrimaryKey = $this->option('without-primary-key');
        $transaltionFor = Helpers::convertStringToArray($this->generatorOption('translation-for'));
        $force = $this->option('force');

        return (object) compact('modelName', 'file', 'names', 'dataTypes', 'htmlTypes', 'withoutPrimaryKey', 'transaltionFor', 'force');
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

    /**
     * Get primary key properties.
     *
     * @param object $input
     * @param bool $withoutPrimaryKey
     *
     * @return string
     */
    public static function getFields($input, $withoutPrimaryKey)
    {
        $fields = [];

        if (!$withoutPrimaryKey) {
            $fields[] = self::getPrimaryKey();
        }

        foreach ($input->names as $key => $name) {
            if (!$withoutPrimaryKey && strtolower($name) == 'id') {
                continue;
            }
            
            $properties = ['name' => $name];

            if (isset($input->htmlTypes[$key])) {
                $properties['html-type'] = $input->htmlTypes[$key];
            }

            if (isset($input->dataTypes[$key])) {
                $properties['data-type'] = $input->dataTypes[$key];
            }

            $label = FieldTransformer::convertNameToLabel($name);
            foreach ($input->transaltionFor as $lang) {
                $properties['label'][$lang] = $label;
            }

            $fields[] = $properties;
        }

        return FieldTransformer::fromArray($fields, 'generic');
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
                return isset($resource['model-name']) && $resource['model-name'] != $modelName;
            });

            $existingFields->push([
                'model-name'  => $modelName,
                'resource-file' => $fieldsFileName,
            ]);

            foreach ($existingFields as $existingField) {
                $fields[] = (object) $existingField;
            }
        }

        $this->putContentInFile($file, json_encode($fields, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    /**
     * Converts field's object into a user friendly json string.
     *
     *
     * @return string
     */
    protected function getFieldAsJson($fields)
    {
        $rarField =  array_map(function ($field) {
            return $field->toArray();
        }, $fields);

        return json_encode($rarField, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Get standard properties for primary key field.
     *
     * @return array
     */
    protected static function getPrimaryKey()
    {
        return [
            'name' => 'id',
            'type' => 'integer',
            'is-primary' => true,
            'is-auto-increment' => true
        ];
    }
}
