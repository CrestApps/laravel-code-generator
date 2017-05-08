<?php

namespace CrestApps\CodeGenerator\Traits;

trait GeneratorReplacers
{
    /**
     * Replace the modelName fo the given stub.
     *
     * @param string $stub
     * @param string $modelName
     *
     * @return $this
     */
    protected function replaceModelName(&$stub, $modelName)
    {
        $stub = str_replace('{{modelName}}', $this->getModelName($modelName), $stub);
        $stub = str_replace('{{modelNameCap}}', $this->getModelCapName($modelName), $stub);
        $stub = str_replace('{{modelNameClass}}', $this->getModelCapName($modelName), $stub);
        $stub = str_replace('{{modelNamePlural}}', $this->getModelPluralName($modelName), $stub);
        $stub = str_replace('{{modelNamePluralCap}}', $this->getModelNamePluralCap($modelName), $stub);

        return $this;
    }

    protected function replaceControllerName(&$stub, $name)
    {
        $stub = str_replace('{{controllerName}}', $name, $stub);

        return $this;
    }

    protected function replaceAppName(&$stub, $name)
    {
        $stub = str_replace('{{appName}}', $name, $stub);

        return $this;
    }

    protected function replaceNamespace(&$stub, $namespace)
    {
        $stub = str_replace('{{namespace}}', $namespace, $stub);

        return $this;
    }
    
    protected function getModelCapName($name)
    {
        return ucwords($name);
    }

    protected function getModelName($name)
    {
        return strtolower($name);
    }

    protected function getModelPluralName($name)
    {
        return str_plural(strtolower($name));
    }

    protected function getModelNamePluralCap($name)
    {
        return ucwords($this->getModelPluralName($name));
    }

    /**
     * Replace the validation rules for the given stub.
     *
     * @param string $stub
     * @param string $rules
     *
     * @return $this
     */
    protected function replaceValidationRules(&$stub, $rules)
    {
        $stub = str_replace('{{validationRules}}', $rules, $stub);

        return $this;
    }

    /**
     * Replaces the field's name for the given stub.
     *
     * @param $stub
     * @param $nane
     *
     * @return $this
     */
    protected function replaceFieldName(&$stub, $name)
    {
        $stub = str_replace('{{fieldName}}', $name, $stub);

        return $this;
    }
}
