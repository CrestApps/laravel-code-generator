<?php

namespace CrestApps\CodeGenerator\Support;

use Illuminate\Translation\Translator;
use Illuminate\Support\Arr;

class CrestAppsTranslator extends Translator
{
    /**
     * Add translation lines to the given locale.
     *
     * @param  array  $lines
     * @param  string  $locale
     * @param  string  $namespace
     * @return void
     */
    public function addLines(array $lines, $locale, $namespace = '*')
    {
        foreach ($lines as $key => $value) {
            list($group, $item) = explode('.', $key, 2);
            Arr::set($this->loaded, "$namespace.$group.$locale.$item", $value);
        }
    }

    /**
    * Adds a new instance of crestapps_translator to the IoC container,
    *
    * @return CrestApps\CodeGenerator\Support\CrestAppsTranslator
    */
    public static function getTranslator()
    {
        $translator = app('translator');

        app()->singleton('crestapps_translator', function ($app) use ($translator) {
            $trans = new CrestAppsTranslator($translator->getLoader(), $translator->getLocale());

            $trans->setFallback($translator->getFallback());

            return $trans;
        });

        return app('crestapps_translator');
    }
}
