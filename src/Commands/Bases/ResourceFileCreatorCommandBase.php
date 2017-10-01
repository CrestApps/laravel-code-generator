<?php

namespace CrestApps\CodeGenerator\Commands\Bases;

use CrestApps\CodeGenerator\Commands\Bases\ResourceFileCommandBase;
use CrestApps\CodeGenerator\Support\Helpers;

class ResourceFileCreatorCommandBase extends ResourceFileCommandBase
{
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
        $translationFor = array_unique(Helpers::convertStringToArray($this->option('translation-for')));
        $fieldNames = array_unique(Helpers::convertStringToArray($this->option('fields')));
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
