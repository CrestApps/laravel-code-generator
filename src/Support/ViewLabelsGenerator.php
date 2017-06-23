<?php

namespace CrestApps\CodeGenerator\Support;

use App;
use Exception;
use CrestApps\CodeGenerator\Models\Label;
use CrestApps\CodeGenerator\Support\Helpers;
use CrestApps\CodeGenerator\Traits\CommonCommand;
use CrestApps\CodeGenerator\Traits\GeneratorReplacers;

class ViewLabelsGenerator
{
    use CommonCommand, GeneratorReplacers;

    /**
     * The name of the model
     *
     * @var string
     */
    protected $modelName;

    /**
     * The name of the file where labels will reside
     *
     * @var string
     */
    protected $localeGroup;

    /**
     * The apps default language
     *
     * @var string
     */
    protected $defaultLang;

    /**
     * Generate labels for collective template?
     *
     * @var bool
     */
    protected $isCollectiveTemplate;

    /**
     * Create a new transformer instance.
     *
     * @return void
     */
    public function __construct($modelName, $isCollectiveTemplate)
    {
        if (empty($modelName)) {
            throw new Exception("$modelName must have a valid value");
        }

        $this->modelName = $modelName;
        $this->localeGroup = Helpers::makeLocaleGroup($modelName);
        $this->defaultLang = App::getLocale();
        $this->isCollectiveTemplate = $isCollectiveTemplate;
    }

    /**
     * Gets translatable labels for the giving languages if any
     *
     * @param array $languages
     *
     * @return array
     */
    public function getTranslatedLabels(array $languages)
    {
        $labels = [];
        foreach ($languages as $language) {
            foreach ($this->getTemplates() as $key => $properties) {
                $label = $this->makeLabel($key, $properties, false, $language);
                $labels[$language][] = $label;
            }
        }
        
        return $labels;
    }

    /**
     * Gets translatable labels for the giving languages if any,
     * otherwise, get plain labels
     *
     * @param array $languages
     *
     * @return array
     */
    public function getLabels(array $languages)
    {
        if (count($languages) > 0) {
            return $this->getTranslatedLabels($languages);
        }
        
        return $this->getPlainLabels();
    }

    /**
     * Get plain labels.
     *
     * @return array
     */
    protected function getPlainLabels()
    {
        $labels = [];

        foreach ($this->getTemplates() as $key => $properties) {
            $label = $this->makeLabel($key, $properties, true, $this->defaultLang);
            $labels[$this->defaultLang][] = $label;
        }
        
        return $labels;
    }

    /**
     * Makes a label
     *
     * @param string $key
     * @param array $properties
     * @param bool $isPlain
     * @param string $lang
     *
     * @return CrestApps\CodeGenerator\Models\Label
     */
    protected function makeLabel($key, array $properties, $isPlain, $lang)
    {
        $text = $properties['text'];

        $this->replaceModelName($text, $this->modelName);

        $localeKey = sprintf('%s.%s', $this->localeGroup, $key);
        
        $label = new Label($text, $localeKey, $isPlain, $lang, $key);
        $label->template = $properties['template'];
        $label->isInFunction = $this->isInFunction($properties);

        return $label;
    }

    /**
     * Checks if the giving properties request to put the label in a function.
     *
     * @param array $properties
     *
     * @return bool
     */
    protected function isInFunction(array $properties)
    {
        return $this->isCollectiveTemplate 
            && (isset($properties['in-function-with-collective']) && $properties['in-function-with-collective']);
    }

    /**
     * Get the default template.
     *
     * @return array
     */
    protected function getTemplates()
    {
        return config('codegenerator.generic_view_labels', [
            'create' => [
                'text'     => 'Create New [% model_name_title %]',
                'template' => 'create_model',
            ],
            'delete' => [
                'text'     => 'Delete [% model_name_title %]',
                'template' => 'delete_model',
                'in-function-with-collective' => true,
            ],
            'edit'   => [
                'text'     => 'Edit [% model_name_title %]',
                'template' => 'edit_model',
            ],
            'show'   => [
                'text'     => 'Show [% model_name_title %]',
                'template' => 'show_model',
            ],
            'show_all' => [
                'text'     => 'Show All [% model_name_title %]',
                'template' => 'show_all_models',
            ],
            'add' => [
                'text'     => 'Add',
                'template' => 'add',
                'in-function-with-collective' => true,
            ],
            'update' => [
                'text'     => 'Update',
                'template' => 'update',
                'in-function-with-collective' => true,
            ],
            'confirm_delete' => [
                'text'     => 'Delete [% model_name_title %]?',
                'template' => 'confirm_delete',
                'in-function-with-collective' => true,
            ],
            'none_available' => [
                'text'     => 'No [% model_name_plural_title %] Available!',
                'template' => 'no_models_available',
            ],
            'model_plural' => [
                'text'     => '[% model_name_plural_title %]',
                'template' => 'model_plural',
            ],
            'model_was_added' => [
                'text'     => '[% model_name_title %] was successfully added!',
                'template' => 'model_was_added',
            ],
            'model_was_updated' => [
                'text'     => '[% model_name_title %] was successfully updated!',
                'template' => 'model_was_updated',
            ],
            'model_was_deleted' => [
                'text'     => '[% model_name_title %] was successfully deleted!',
                'template' => 'model_was_deleted',
            ],
            'unexpected_error' => [
                'text'     => 'Unexpected error occurred while trying to process your request',
                'template' => 'unexpected_error',
            ],
        ]);
    }
}
