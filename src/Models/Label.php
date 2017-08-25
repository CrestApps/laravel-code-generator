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
     * The label's value
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
     * when translating. All when displaying plain text, the text must
     * be wrapped with a single quote. This flag tell us to do that.
     *
     * @var bool
     */
    public $isInFunction = false;
    
    /**
     * Create a new label instance.
     *
     * @param string $name
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
     * Returns current object into proper json format.
     *
     * @return string
     */
    public function toJson()
    {
        return json_encode([
            $this->lang => $this->text
        ]);
    }
}
