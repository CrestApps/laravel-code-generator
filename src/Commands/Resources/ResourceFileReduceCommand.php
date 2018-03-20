<?php

namespace CrestApps\CodeGenerator\Commands\Resources;

use CrestApps\CodeGenerator\Commands\Bases\ResourceFileCommandBase;
use CrestApps\CodeGenerator\Support\Arr;
use CrestApps\CodeGenerator\Support\Helpers;

class ResourceFileReduceCommand extends ResourceFileCommandBase
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'resource-file:reduce
                            {model-name : The model name that these files represent.}
                            {--resource-filename= : The destination file name to reduce.}
                            {--fields= : A comma seperate field names.}
                            {--indexes= : A comma seperated index string.}
                            {--relations= : A comma seperated realtion string.}';

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

        if (!$this->isFileExists($file)) {
            $this->error('The resource-file does not exists.');

            return false;
        }

        if (empty($input->fieldNames) && empty($input->relations) && empty($input->indexes)) {
            $this->noResourcesProvided();

            return false;
        }

        $resource = $this->getResources($file);
        $totalReducedFields = $this->reduceFields($resource, $input->fieldNames);
        $totalReducedRelations = $this->reduceRelations($resource, $input->relations);
        $totalReducedIndexes = $this->reduceIndexes($resource, $input->indexes);

        if ($resource->isEmpty()) {
            $this->callSilent(
                'resource-file:delete',
                [
                    'model-name' => $input->modelName,
                    '--resource-filename' => $input->file,
                ]
            );

            $this->info('All resources were removed from the resource-file. The file "' . basename($file) . '" was removed successfully!');

            return false;
        }

        $this->putContentInFile($file, Helpers::prettifyJson($resource->toArray()));
        $filename = basename($file);

        if (!empty($input->fieldNames)) {
            $this->info($totalReducedFields . ' field(s) where removed from the "' . $filename . '" file.');
        }

        if (!empty($input->relations)) {
            $this->info($totalReducedRelations . ' relation(s) where removed from the "' . $filename . '" file.');
        }

        if (!empty($input->indexes)) {
            $this->info($totalReducedIndexes . ' index(es) where removed from the "' . $filename . '" file.');
        }
    }

    /**
     * Reduces the fields from a given resource.
     *
     * @param CrestApps\CodeGenerator\Models\Resource &$resource
     * @param array $$fieldNames
     *
     * @return mixed
     */
    protected function reduceFields(&$resource, array $fieldNames)
    {
        $keep = [];
        $totalReduced = 0;
        foreach ($resource->fields as $field) {
            if (in_array($field->name, $fieldNames) || in_array($field->name, $keep)) {
                $totalReduced++;
                continue;
            }

            $keep[] = $field;
        }

        $resource->fields = $keep;

        return $totalReduced;
    }

    /**
     * Reduces the indexes from a given resource.
     *
     * @param CrestApps\CodeGenerator\Models\Resource &$resource
     * @param array $indexNames
     *
     * @return mixed
     */
    protected function reduceIndexes(&$resource, array $indexNames)
    {
        $keep = [];
        $keepName = [];
        $totalReduced = 0;
        foreach ($resource->indexes as $index) {
            if (in_array($index->getName(), $indexNames) || in_array($index->getName(), $keepName)) {
                $totalReduced++;
                continue;
            }

            $keep[] = $index;
            $keepName[] = $index->getName();
        }

        $resource->indexes = $keep;

        return $totalReduced;
    }

    /**
     * Reduces the relations from a given resource.
     *
     * @param CrestApps\CodeGenerator\Models\Resource &$resource
     * @param array $relationNames
     *
     * @return mixed
     */
    protected function reduceRelations(&$resource, array $relationNames)
    {
        $keep = [];
        $keepName = [];
        $totalReduced = 0;
        foreach ($resource->relations as $relation) {

            if (in_array($relation->getName(), $relationNames) || in_array($relation->getName(), $keepName)) {
                $totalReduced++;
                continue;
            }

            $keep[] = $relation;
            $keepName[] = $relation->getName();
        }

        $resource->relations = $keep;

        return $totalReduced;
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
        $fieldNames = array_unique(Arr::fromString($this->generatorOption('fields')));
        $relations = Arr::fromString(trim($this->option('relations')));
        $indexes = Arr::fromString(trim($this->option('indexes')));

        return (object) compact(
            'modelName',
            'file',
            'fieldNames',
            'relations',
            'indexes'
        );
    }
}
