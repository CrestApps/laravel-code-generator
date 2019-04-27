<?php

namespace CrestApps\CodeGenerator\Commands\Resources;

use CrestApps\CodeGenerator\Commands\Bases\ResourceFileCreatorCommandBase;
use CrestApps\CodeGenerator\Models\ForeignRelationship;
use CrestApps\CodeGenerator\Models\Index;
use CrestApps\CodeGenerator\Models\Resource;
use CrestApps\CodeGenerator\Support\Config;
use CrestApps\CodeGenerator\Support\FieldTransformer;
use CrestApps\CodeGenerator\Support\Helpers;
use CrestApps\CodeGenerator\Support\ResourceMapper;
use CrestApps\CodeGenerator\Traits\LanguageTrait;

class ResourceFileCreateCommand extends ResourceFileCreatorCommandBase
{
    use LanguageTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'resource-file:create
                            {model-name : The model name that these files represent.}
                            {--resource-filename= : The destination file name to append too.}
                            {--fields= : A comma seperate field names.}
                            {--indexes= : A comma seperated index string.}
                            {--relations= : A comma seperated realtion string.}
                            {--translation-for= : A comma seperated string of languages to create fields for.}
                            {--force : Override existing file if one exists.}';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new resource-file.';

    /**
     * Executes the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $input = $this->getCommandInput();
        $file = $this->getFilename($input->file);

        if ($this->isFileExists($file) && !$this->option('force')) {
            $this->error('The resource-file already exists! To override the existing file, use --force option to append.');

            return false;
        }

        if (empty($input->fieldNames) && empty($input->relations) && empty($input->indexes)) {
            $this->noResourcesProvided();

            return false;
        }

        if (Config::autoManageResourceMapper()) {
            $mapper = new ResourceMapper($this);
            $mapper->append($input->modelName, $input->file);
        }

        $fields = FieldTransformer::fromString($this->option('fields'), 'generic', $input->translationFor);
        $relations = $this->getRelations($input->relations);
        $indexes = $this->getIndexes($input->indexes);

        $resource = new Resource($fields, $relations, $indexes);

        $resource->setDefaultApiDocLabels($input->modelName, self::makeLocaleGroup($input->modelName), $input->translationFor);
	
        $this->createFile($file, Helpers::prettifyJson($resource->toArray()))
            ->info('The "' . basename($file) . '" file was crafted successfully!');
    }

    /**
     * Get the relations from an existing array.
     *
     * @param array $relations
     *
     * @return array
     */
    protected function getRelations($relations)
    {
        $existingNames = [];
        $finalRelations = [];
        foreach ($relations as $relation) {
            $newRelation = ForeignRelationship::fromString($relation);
            if (is_null($newRelation)) {
                continue;
            }

            if (!empty($newRelation->name) && in_array($newRelation->name, $existingNames)) {
                continue;
            }

            $finalRelations[] = $newRelation;
            $existingNames[] = $newRelation->name;
        }

        return $finalRelations;
    }

    /**
     * Get the indexes from an existing array.
     *
     * @param array $relations
     *
     * @return array
     */
    protected function getIndexes($indexes)
    {
        $existingNames = [];
        $finalIndexes = [];
        foreach ($indexes as $index) {
            $newIndex = Index::fromString($index);
            if (!empty($newIndex->getName()) && in_array($newIndex->getName(), $existingNames)) {
                continue;
            }

            $finalIndexes[] = $newIndex;
            $existingNames[] = $newIndex->getName();
        }

        return $finalIndexes;
    }
}
