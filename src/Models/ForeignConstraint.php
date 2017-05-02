<?php

namespace CrestApps\CodeGenerator\Models;

use CrestApps\CodeGenerator\Traits\CommonCommand;

class ForeignConstraint
{
    use CommonCommand;

    /**
     * The name of the foreign field/column.
     *
     * @var string
     */
    public $column;

    /**
     * The name of the column being referenced on the foreign model.
     *
     * @var string
     */
    public $references;
    
    /**
     * The name of the foreign model being referenced.
     *
     * @var string
     */
    public $on;
    
    /**
     * The action to take when the model is updated.
     *
     * @var string
     */
    public $onUpdate;

    /**
     * The action to take when the model is deleted.
     *
     * @var string
     */
    public $onDelete;

    /**
     * The model this key references
     *
     * @var string
     */
    protected $referencesModel;

    /**
     * Creates a new field instance.
     *
     * @param string $column
     * @param string $references
     * @param string $on
     * @param string $onDelete
     * @param string $onUpdate
     * @param string $model
     *
     * @return void
     */
    public function __construct($column, $references, $on, $onDelete = null, $onUpdate = null, $model = null)
    {
        $this->column = $column;
        $this->references = $references;
        $this->on = $on;
        $this->onUpdate = $onUpdate;
        $this->onDelete = $onDelete;
        $this->referencesModel = $model;
    }


    public function getReferencesModel()
    {
        if (empty($this->referencesModel)) {
            $this->referencesModel = FieldTransformer::guessModelFullName($name, $this->getAppNamespace() . $this->getModelsPath());
        }

        return $this->referencesModel;
    }

    public function getForeignRelation()
    {
        $params = [
            $this->getReferencesModel(),
            $this->column,
            $this->on
        ];

        return new ForeignRelationship(
                                    'belongsTo',
                                    $params,
                                    camel_case(str_singular($this->references)),
                                    $this->on
                                );
    }

    public function hasDeleteAction()
    {
        return ! empty($this->onDelete);
    }

    public function hasUpdateAction()
    {
        return ! empty($this->onUpdate);
    }
}
