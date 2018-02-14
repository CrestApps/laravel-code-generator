<?php

namespace CrestApps\CodeGenerator\Models;

use CrestApps\CodeGenerator\Models\Bases\ScaffoldInputBase;

class ApiScaffoldInput extends ScaffoldInputBase
{
    /**
     * generate the resource with api-resource
     *
     * @var bool
     */
    public $withApiResource;

    /**
     * The api-resource directory
     *
     * @var string
     */
    public $apiResourceDirectory;

    /**
     * The api-resource-collection directory
     *
     * @var string
     */
    public $apiResourceCollectionDirectory;

    /**
     * The api-resource name
     *
     * @var string
     */
    public $apiResourceName;

    /**
     * The api-resource-collection name
     *
     * @var string
     */
    public $apiResourceCollectionName;

    /**
     * Should the request also scaffold documentation or not.
     *
     * @var bool
     */
    public $withDocumentations;

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
