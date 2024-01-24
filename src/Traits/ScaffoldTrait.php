<?php

namespace CrestApps\CodeGenerator\Traits;

use CrestApps\CodeGenerator\Support\Str;

trait ScaffoldTrait
{
    /**
     * Prints a message
     *
     * @param string $message
     *
     * @return $this
     */
    protected function printInfo($message)
    {
        $this->info($message);

        return $this;
    }

    /**
     * Gets the model name in plain English from a given model name.
     *
     * @param string $modelName
     *
     * @return string
     */
    protected function modelNamePlainEnglish($modelName)
    {
        return str_replace('_', ' ', Str::snake($modelName));
    }
}
