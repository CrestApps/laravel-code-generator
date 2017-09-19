<?php

namespace CrestApps\CodeGenerator\Traits;

use CrestApps\CodeGenerator\Support\Str;

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
    protected function replaceModelName(&$stub, $modelName, $prefix = 'model_')
    {
        $englishSingle = $this->modelNamePlainEnglish($modelName);
        $plural = Str::plural($englishSingle);

        $stub = $this->strReplace($prefix . 'name', $englishSingle, $stub);
        $stub = $this->strReplace($prefix . 'name_flat', strtolower($modelName), $stub);
        $stub = $this->strReplace($prefix . 'name_sentence', ucfirst($englishSingle), $stub);
        $stub = $this->strReplace($prefix . 'name_plural', $plural, $stub);
        $stub = $this->strReplace($prefix . 'name_plural_title', Str::titleCase($plural), $stub);
        $stub = $this->strReplace($prefix . 'name_snake', snake_case($modelName), $stub);
        $stub = $this->strReplace($prefix . 'name_studly', studly_case($modelName), $stub);
        $stub = $this->strReplace($prefix . 'name_slug', str_slug($englishSingle), $stub);
        $stub = $this->strReplace($prefix . 'name_kebab', Str::kebabCase($modelName), $stub);
        $stub = $this->strReplace($prefix . 'name_title', Str::titleCase($englishSingle), $stub);
        $stub = $this->strReplace($prefix . 'name_title_lower', strtolower($englishSingle), $stub);
        $stub = $this->strReplace($prefix . 'name_title_upper', strtoupper($englishSingle), $stub);
        $stub = $this->strReplace($prefix . 'name_class', $modelName, $stub);
        $stub = $this->strReplace($prefix . 'name_plural_variable', $this->getPluralVariable($modelName), $stub);
        $stub = $this->strReplace($prefix . 'name_singular_variable', $this->getSingularVariable($modelName), $stub);

        return $this;
    }

    /**
     * It Replaces the templates of the givin $labels
     *
     * @param string $stub
     * @param array $items
     *
     * @return $this
     */
    protected function replaceStandardLabels(&$stub, array $items)
    {
        foreach ($items as $labels) {
            foreach ($labels as $label) {
                $text = $label->isPlain ? $label->text : sprintf("{{ trans('%s') }}", $label->localeGroup);

                if ($label->isInFunction) {
                    $text = $label->isPlain ? sprintf("'%s'", $label->text) : sprintf("trans('%s')", $label->localeGroup);
                }

                $stub = $this->strReplace($label->template, $text, $stub);
            }
        }

        return $this;
    }

    /**
     * Gets the
     *
     * @param string $modelName
     *
     * @return string
     */
    protected function modelNamePlainEnglish($modelName)
    {
        return str_replace('_', ' ', snake_case($modelName));
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

    /**
     * Gets a model name.
     *
     * @param string $name
     *
     * @return string
     */
    protected function getModelName($name)
    {
        return snake_case($name);
    }

    /**
     * Gets a model name in a plural formal.
     *
     * @param string $name
     *
     * @return string
     */
    protected function getModelPluralName($name)
    {
        return Str::plural(strtolower($name));
    }

    /**
     * Gets a model name in a plural formal "Caps".
     *
     * @param string $name
     *
     * @return string
     */
    protected function getModelNamePluralCap($name)
    {
        return ucwords($this->getModelPluralName($name));
    }
}
