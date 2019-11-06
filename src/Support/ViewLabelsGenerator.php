<?php

namespace CrestApps\CodeGenerator\Support;

use App;
use CrestApps\CodeGenerator\Models\Label;
use CrestApps\CodeGenerator\Support\Config;
use CrestApps\CodeGenerator\Traits\CommonCommand;
use CrestApps\CodeGenerator\Traits\GeneratorReplacers;
use CrestApps\CodeGenerator\Traits\LanguageTrait;
use Exception;

class ViewLabelsGenerator
{
    use CommonCommand, GeneratorReplacers, LanguageTrait;

    /**
     * The name of the model
     *
     * @var string
     */
    protected $modelName;

    /**
     * The fields.
     *
     * @var array
     */
    protected $fields;

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
    public function __construct($modelName, array $fields, $isCollectiveTemplate)
    {
        if (empty($modelName)) {
            throw new Exception("$modelName must have a valid value");
        }

        $this->modelName = $modelName;
        $this->fields = $fields;
        $this->localeGroup = self::makeLocaleGroup($modelName);
        $this->defaultLang = App::getLocale();
        $this->isCollectiveTemplate = $isCollectiveTemplate;
    }

    /**
     * Gets translatable labels for the given languages if any
     *
     * @param array $languages
     *
     * @return array
     */
    public function getTranslatedLabels(array $languages)
    {
        $labels = [];
        foreach ($languages as $language) {

            foreach (Config::getCustomModelTemplates() as $key => $properties) {
                $label = $this->makeModelLabel($key, $properties, false, $language);
                $labels[$language][] = $label;
            }
        }

        return $labels;
    }

    /**
     * Gets translatable labels for the given languages if any,
     * otherwise, get plain labels
     *
     * @param array $languages
     *
     * @return array
     */
    public function getLabels()
    {
        $languages = array_keys(self::getLanguageItems($this->fields));

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

        foreach (Config::getCustomModelTemplates() as $key => $properties) {
            $label = $this->makeModelLabel($key, $properties, true, $this->defaultLang);
            $labels[$this->defaultLang][] = $label;
        }

        return $labels;
    }

    /**
     * Makes a label from a model
     *
     * @param string $key
     * @param array $properties
     * @param bool $isPlain
     * @param string $lang
     *
     * @return CrestApps\CodeGenerator\Models\Label
     */
    protected function makeModelLabel($key, array $properties, $isPlain, $lang)
    {
        $text = $properties['text'];

        $this->replaceModelName($text, $this->modelName);

        $label = new Label($text, $this->localeGroup, $isPlain, $lang, $key);
        $label->template = $properties['template'];
        $label->isInFunction = $this->isInFunction($properties);

        return $label;
    }

    /**
     * Checks if the given properties request to put the label in a function.
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
}
