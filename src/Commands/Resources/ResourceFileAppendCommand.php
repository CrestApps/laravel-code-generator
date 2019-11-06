<?php

namespace CrestApps\CodeGenerator\Commands\Resources;

use CrestApps\CodeGenerator\Commands\Bases\ResourceFileCreatorCommandBase;
use CrestApps\CodeGenerator\Models\ForeignRelationship;
use CrestApps\CodeGenerator\Models\Index;
use CrestApps\CodeGenerator\Models\Resource;
use CrestApps\CodeGenerator\Support\FieldTransformer;
use CrestApps\CodeGenerator\Support\Helpers;

class ResourceFileAppendCommand extends ResourceFileCreatorCommandBase
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'resource-file:append
                            {model-name : The model name that these files represent.}
                            {--resource-filename= : The destination file name to append too.}
                            {--fields= : A comma seperate field names.}
                            {--indexes= : A comma seperated index string.}
                            {--relations= : A comma seperated realtion string.}
                            {--translation-for= : A comma seperated string of languages to create fields for.}';

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

        if (empty($input->fieldNames) && empty($input->relations) && empty($input->indexes)) {
            $this->noResourcesProvided();

            return false;
        }

        if (!$this->isFileExists($file)) {
            $this->warn('The resource-file does not exists.');
            $this->call('resource-file:create', $this->getCommandOptions($input));

            return false;
        }

        $resource = $this->getResources($file, $input->translationFor);

        $totalAddedFields = $this->mergeFields($resource, $input);
        $totalAddedRelations = $this->mergeRelations($resource, $input);
        $totalAddedIndexes = $this->mergeIndexes($resource, $input);

        $content = Helpers::prettifyJson($resource->toArray());

        $this->putContentInFile($file, $content);

        $fileName = basename($file);

        if (!empty($input->fieldNames)) {
            $this->info($totalAddedFields . ' new field(s) where appended to the "' . $fileName . '" file.');
        }

        if (!empty($input->relations)) {
            $this->info($totalAddedRelations . ' new relation(s) where appended to the "' . $fileName . '" file.');
        }

        if (!empty($input->indexes)) {
            $this->info($totalAddedIndexes . ' new index(es) where appended to the "' . $fileName . '" file.');
        }
    }

    /**
     * Merges the given file's content to the new fields.
     *
     * @param CrestApps\CodeGenerator\Models\Resource &$resource
     * @param (object) $input
     *
     * @return int
     */
    protected function mergeFields(&$resource, $input)
    {
        $existingNames = Collect($resource->fields)->pluck('name')->all();
        $fields = FieldTransformer::fromString($this->option('fields'), 'generic', $input->translationFor);
        $mergeFields = 0;
        foreach ($fields as $field) {
            if (in_array($field->name, $existingNames)) {
                $this->warn('The field "' . $field->name . '" already exists in the file.');
                continue;
            }

            $existingNames[] = $field->name;
            $resource->fields[] = $field;
            $mergeFields++;
        }

        return $mergeFields;
    }

    /**
     * Merges the relation to the existing resources.
     *
     * @param CrestApps\CodeGenerator\Models\Resource &$resource
     * @param (object) $input
     *
     * @return int
     */
    protected function mergeRelations(&$resource, $input)
    {
        $existingNames = [];

        foreach ($resource->relations as $relation) {
            $existingNames[] = $relation->getName();
        }
        $mergeRelations = 0;
        foreach ($input->relations as $relation) {
            $newRelation = ForeignRelationship::fromString($relation);

            if (is_null($newRelation)) {
                continue;
            }
            if (!empty($newRelation->name) && in_array($newRelation->name, $existingNames)) {
                $this->warn('The relation "' . $newRelation->name . '" already exists in the file.');
                continue;
            }

            $resource->relations[] = $newRelation;
            $existingNames[] = $newRelation->name;

            $mergeRelations++;
        }

        return $mergeRelations;
    }

    /**
     * Merges the indexes to the existing resources.
     *
     * @param CrestApps\CodeGenerator\Models\Resource &$resource
     * @param (object) $input
     *
     * @return int
     */
    protected function mergeIndexes(&$resource, $input)
    {
        $existingNames = [];

        foreach ($resource->indexes as $index) {
            $existingNames[] = $index->getName();
        }
        $mergeIndexes = 0;
        foreach ($input->indexes as $index) {
            $newIndex = Index::fromString($index);
            if (!empty($newIndex->getName()) && in_array($newIndex->getName(), $existingNames)) {
                $this->warn('The index "' . $newIndex->getName() . '" already exists in the file.');
                continue;
            }

            $resource->indexes[] = $newIndex;
            $existingNames[] = $newIndex->getName();
            $mergeIndexes++;
        }

        return $mergeIndexes;
    }
}
