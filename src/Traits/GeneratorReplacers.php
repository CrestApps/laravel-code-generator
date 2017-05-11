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
        $stub = $this->strReplace('model_name', $this->getModelName($modelName), $stub);
        $stub = $this->strReplace('model_name_cap', $this->getModelCapName($modelName), $stub);
        $stub = $this->strReplace('model_name_class', $this->getModelCapName($modelName), $stub);
        $stub = $this->strReplace('model_name_plural', $this->getModelPluralName($modelName), $stub);
        $stub = $this->strReplace('model_name_plural_cap', $this->getModelNamePluralCap($modelName), $stub);

        return $this;
    }

    /**
     * Replace the controller_name fo the given stub.
     *
     * @param string $stub
     * @param string $modelName
     *
     * @return $this
     */
    protected function replaceControllerName(&$stub, $name)
    {
        $stub = $this->strReplace('controller_name', $name, $stub);

        return $this;
    }
    
    /**
     * It Replaces the primary key in a giving stub
     *
     * @param string $stub
     * @param string $primaryKey
     *
     * @return $this
     */
    protected function replacePrimaryKey(&$stub, $primaryKey)
    {
        $stub = $this->strReplace('primary_key', $primaryKey, $stub);

        return $this;
    }
    /**
     * Replace the app_name fo the given stub.
     *
     * @param string $stub
     * @param string $modelName
     *
     * @return $this
     */
    protected function replaceAppName(&$stub, $name)
    {
        $stub = $this->strReplace('app_name', $name, $stub);

        return $this;
    }

    /**
     * Replace the namespace fo the given stub.
     *
     * @param string $stub
     * @param string $modelName
     *
     * @return $this
     */
    protected function replaceNamespace(&$stub, $namespace)
    {
        $stub = $this->strReplace('namespace', $namespace, $stub);

        return $this;
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
        $stub = $this->strReplace('validation_rules', $rules, $stub);

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
        $stub = $this->strReplace('field_name', $name, $stub);

        return $this;
    }

    /**
     * Gets a model.
     *
     * @param $name
     *
     * @return string
     */
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
}
