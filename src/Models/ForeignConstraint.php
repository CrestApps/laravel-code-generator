<?php

namespace CrestApps\CodeGenerator\Models;

use CrestApps\CodeGenerator\Traits\CommonCommand;
use CrestApps\CodeGenerator\Support\Config;
use CrestApps\CodeGenerator\Support\FieldTransformer;

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

    /**
     * Get the model that is being referenced.
     *
     * @return string
     */
    public function getReferencesModel()
    {
        if (empty($this->referencesModel)) {
            $this->referencesModel = $this->getModelNamespace() . '\\' . ucfirst($this->getForeignModelName());
        }

        return $this->referencesModel;
    }

    /**
     * Get a foreign relation.
     *
     * @return CrestApps\CodeGenerator\Models\ForeignRelatioship
     */
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
                                    $this->getForeignModelName(),
                                    $this->on
                                );
    }

    /**
     * Get the namecpase of the foreign model.
     *
     * @return string
     */
    protected function getModelNamespace()
    {
        return $this->getAppNamespace() . rtrim(Config::getModelsPath(), '/');
    }

    /**
     * Get the name of the foreign model.
     *
     * @return string
     */
    protected function getForeignModelName()
    {
        return camel_case(str_singular($this->references));
    }

    /**
     * Checks if the constraint has a delete action.
     *
     * @return bool
     */
    public function hasDeleteAction()
    {
        return ! empty($this->onDelete);
    }

    /**
     * Checks if the constraint has an update action.
     *
     * @return bool
     */
    public function hasUpdateAction()
    {
        return ! empty($this->onUpdate);
    }

    /**
     * Gets the constrain in an array format.
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'field' => $this->column,
            'references' => $this->references,
            'on' => $this->on,
            'on-delete' => $this->onDelete,
            'on-update' => $this->onUpdate,
            'references-model' => $this->getReferencesModel()
       ];
    }
}
