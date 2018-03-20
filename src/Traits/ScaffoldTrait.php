<?php

namespace CrestApps\CodeGenerator\Traits;

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
     * Gets the model name in plain english from a given model name.
     *
     * @param string $modelName
     *
     * @return string
     */
    protected function modelNamePlainEnglish($modelName)
    {
        return str_replace('_', ' ', snake_case($modelName));
    }
}
