<?php

namespace CrestApps\CodeGenerator\Models;

use CrestApps\CodeGenerator\Models\Bases\ScaffoldInputBase;

class ScaffoldInput extends ScaffoldInputBase
{
    /**
     * The views directory
     *
     * @var string
     */
    public $viewsDirectory;

    /**
     * The name of the connection
     *
     * @var the name of the layout
     */
    public $layoutName;

    /**
     * Creates a new class instance.
     *
     * @param CrestApps\CodeGenerator\Models\Bases\ScaffoldInputBase $model
     *
     * @return void
     */
    public function __construct(ScaffoldInputBase $model)
    {
        foreach ($model as $key => $value) {
            $this->{$key} = $value;
        }
    }
}
