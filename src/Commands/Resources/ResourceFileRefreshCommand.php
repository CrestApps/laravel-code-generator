<?php

namespace CrestApps\CodeGenerator\Commands\Resources;

use CrestApps\CodeGenerator\Commands\Bases\ResourceFileCommandBase;
use CrestApps\CodeGenerator\Models\ForeignRelationship;
use CrestApps\CodeGenerator\Models\Index;
use CrestApps\CodeGenerator\Support\Arr;
use CrestApps\CodeGenerator\Support\Helpers;
use CrestApps\CodeGenerator\Traits\LanguageTrait;

class ResourceFileRefreshCommand extends ResourceFileCommandBase
{
    use LanguageTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'resource-file:refresh
                            {model-name : The model name that these files represent.}
                            {--resource-filename= : The destination file name to append too.}
                            {--translation-for= : A comma separated string of languages to create fields for.}';
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

        if (!$this->isFileExists($file)) {
            $this->error('The resource-file does not exists!');

            return false;
        }

        $resource = $this->getResources($file, $input->translationFor);

        $resource->setDefaultApiDocLabels($input->modelName, self::makeLocaleGroup($input->modelName), $input->translationFor);

        dd($resource->toArray());

        $content = Helpers::prettifyJson($resource->toArray());

        $this->putContentInFile($file, $content);
    }

    /**
     * Get the relations from an existing array.
     *
     * @param array $relations
     *
     * @return array
     */
    protected function getRelations($relations)
    {
        $existingNames = [];
        $finalRelations = [];
        foreach ($relations as $relation) {
            $newRelation = ForeignRelationship::fromString($relation);
            if (is_null($newRelation)) {
                continue;
            }

            if (!empty($newRelation->name) && in_array($newRelation->name, $existingNames)) {
                continue;
            }

            $finalRelations[] = $newRelation;
            $existingNames[] = $newRelation->name;
        }

        return $finalRelations;
    }

    /**
     * Get the indexes from an existing array.
     *
     * @param array $relations
     *
     * @return array
     */
    protected function getIndexes($indexes)
    {
        $existingNames = [];
        $finalIndexes = [];
        foreach ($indexes as $index) {
            $newIndex = Index::fromString($index);
            if (!empty($newIndex->getName()) && in_array($newIndex->getName(), $existingNames)) {
                continue;
            }

            $finalIndexes[] = $newIndex;
            $existingNames[] = $newIndex->getName();
        }

        return $finalIndexes;
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
        $translationFor = array_unique(Arr::fromString($this->option('translation-for')));

        return (object) compact(
            'modelName',
            'file',
            'translationFor'
        );
    }
}
