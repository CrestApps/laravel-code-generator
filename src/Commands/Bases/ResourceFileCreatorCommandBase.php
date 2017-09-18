<?php

namespace CrestApps\CodeGenerator\Commands\Bases;

use CrestApps\CodeGenerator\Commands\Bases\ResourceFileCommandBase;
use CrestApps\CodeGenerator\Models\ForeignRelationship;
use CrestApps\CodeGenerator\Models\Index;
use CrestApps\CodeGenerator\Support\Helpers;

class ResourceFileCreatorCommandBase extends ResourceFileCommandBase
{
    /**
     * Gets a relation collection.
     *
     * @param string $relation
     *
     * @return array
     */
    protected function getRelation($rawRelation)
    {
        //name:assets;type:hasMany;params:App\\Models\\Asset|category_id|id,
        // expected string
        //name|type|params|field
        //assets|hasMany|App\\Models\\Asset,category_id,id|title

        $parts = explode(';', $rawRelation);
        $collection = [];
        foreach ($parts as $part) {
            if (!str_contains($part, ':')) {
                continue;
            }

            list($key, $value) = explode(':', $part);

            if ($key == 'params' || str_contains($value, '|')) {
                $value = explode('|', $value);
            }

            $collection[$key] = $value;
        }

        return ForeignRelationship::get($collection);
    }

    /**
     * Gets an index collection.
     *
     * @param string $index
     *
     * @return array
     */
    protected function getIndex($rawIndex)
    {
        //name:some_name;columns:full_name|last_name;type:index,

        $parts = explode(';', $rawIndex);

        foreach ($parts as $part) {
            if (!str_contains($part, ':')) {
                continue;
            }

            list($key, $value) = explode(':', $part);
            $collection[$key] = $value;
        }

        return Index::get($collection);
    }

    /**
     * Converts the current command's argument and options into an array.
     *
     * @return array
     */
    protected function getCommandOptions($input)
    {
        return [
            'model-name' => $input->modelName,
            '--resource-filename' => $input->file,
            '--fields' => implode(',', $input->fieldNames),
            '--translation-for' => $input->translationFor,
            '--relations' => implode(',', $input->relations),
            '--indexes' => implode(',', $input->indexes),
        ];
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
        $fieldNames = array_unique(Helpers::convertStringToArray($this->generatorOption('fields')));
        $translationFor = array_unique(Helpers::convertStringToArray($this->generatorOption('translation-for')));
        $relations = Helpers::convertStringToArray(trim($this->option('relations')));
        $indexes = Helpers::convertStringToArray(trim($this->option('indexes')));

        return (object) compact(
            'modelName',
            'file',
            'fieldNames',
            'indexes',
            'relations',
            'translationFor'
        );
    }

}
