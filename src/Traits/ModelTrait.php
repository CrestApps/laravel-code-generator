<?php

namespace CrestApps\CodeGenerator\Traits;

use CrestApps\CodeGenerator\Support\Config;
use CrestApps\CodeGenerator\Support\Helpers;
use CrestApps\CodeGenerator\Support\Str;

trait ModelTrait
{
    /**
     * Gets the namespace of the model
     *
     * @param  string $modelName
     * @param  string $modelDirectory
     *
     * @return string
     */
    public static function getModelNamespace($modelName, $modelDirectory)
    {
        $namespace = Helpers::getAppNamespace(Config::getModelsPath(), $modelDirectory, $modelName);

        return Helpers::fixNamespace($namespace);
    }

    /**
     * Gets the model full path.
     *
     * @return string
     */
    public static function getModelsPath()
    {
        return Helpers::getAppNamespace(Config::getModelsPath());
    }

    /**
     * Guesses the model full name using the given field's name
     *
     * @param string $name
     * @param string $modelsPath
     *
     * @return string
     */
    public static function guessModelFullName($name, $modelsPath)
    {
        $model = $modelsPath . ucfirst(self::extractModelName($name));

        return Helpers::convertSlashToBackslash($model);
    }

    /**
     * Extracts the model name from the given field's name.
     *
     * @param string $name
     *
     * @return string
     */
    public static function extractModelName($name)
    {
        $name = Str::trimEnd($name, '_id');

        return ucfirst(Str::studly(Str::singular($name)));
    }

}
