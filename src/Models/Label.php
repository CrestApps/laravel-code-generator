<?php

namespace CrestApps\CodeGenerator\Models;

class Label
{

    /**
     * The label's text
     *
     * @var string
     */
    public $text;

    /**
     * The localeGroup for the label
     *
     * @var string
     */
    public $localeGroup;

    /**
     * Is the label plain text or not
     *
     * @var vool
     */
    public $isPlain = true;

    /**
     * The language
     *
     * @var string
     */
    public $lang;

    /**
     * The label's value
     *
     * @var string
     */
    public $value;

    /**
     * The label's id
     *
     * @var string
     */
    public $id;

    /**
     * The template to use for replacment.
     *
     * @var string
     */
    public $template;

    /**
     * When a label is inside a function we need to ignore adding {{ }}
     * when translating. Also, when displaying plain text, the text must
     * be wrapped with a single quote. This flag tell us to do that.
     *
     * @var bool
     */
    public $isInFunction = false;

    /**
     * Create a new label instance.
     *
     * @param string $text
     * @param string $localeGroup
     * @param bool $isPlain
     * @param string $lang
     * @param string $id
     * @param string $value
     *
     * @return void
     */
    public function __construct($text, $localeGroup, $isPlain = true, $lang = 'en', $id = null, $value = null)
    {
        $this->text = $text;
        $this->localeGroup = $localeGroup;
        $this->isPlain = $isPlain;
        $this->lang = $lang;
        $this->id = $id;
        $this->value = $value;
    }

    /**
     * Encode the current object into JSON format.
     *
     * @return string
     */
    public function toJson()
    {
        return json_encode([
            $this->lang => $this->text,
        ]);
    }

    /**
     * Gets the translation accessor.
     *
     * @return string
     */
    public function getAccessor()
    {
        return sprintf('%s.%s', $this->localeGroup, $this->id);
    }
}
