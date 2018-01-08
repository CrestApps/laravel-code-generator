<?php

namespace CrestApps\CodeGenerator\Commands\Api;

use CrestApps\CodeGenerator\Models\Resource;
use CrestApps\CodeGenerator\Support\Helpers;
use CrestApps\CodeGenerator\Support\ViewLabelsGenerator;
use CrestApps\CodeGenerator\Traits\ApiResourceTrait;
use CrestApps\CodeGenerator\Traits\CommonCommand;
use CrestApps\CodeGenerator\Traits\GeneratorReplacers;
use Illuminate\Console\Command;

class CreateApiResourceCommand extends Command
{
    use CommonCommand, GeneratorReplacers, ApiResourceTrait;

    /**
     * Total white-spaced to eliminate when creating an array string.
     *
     * @var string
     */
    protected $backspaceCount = 8;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:api-resource
                            {model-name : The name of the model.}
                            {--resource-file= : The name of the resource-file to import from.}
                            {--api-resource-directory= : The directory where the api-resource should be created.}
                            {--api-resource-collection-directory= : The directory where the api-resource-collection should be created.}
                            {--api-resource-name= : The api-resource file name.}
                            {--api-resource-collection-name= : The api-resource-collection file name.}
                            {--model-directory= : The path where the model should be created under.}
                            {--template-name= : The template name to use when generating the code.}
                            {--collection : Create a resource collection.}
                            {--force : Override the model if one already exists.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new api-response.';

    /**
     * Builds the model class with the given name.
     *
     * @return string
     */
    public function handle()
    {
        $input = $this->getCommandInput();

        $resource = Resource::fromFile($input->resourceFile, 'CrestApps');
        $apiResourceFileName = $this->getApiResourceClassName($input->modelName);

        $destenationFile = $this->getDestenationFile($apiResourceFileName, $input->isCollection);

        if ($this->hasErrors($resource, $destenationFile, $input->isCollection)) {
            return false;
        }

        $stub = $this->getStubContent($this->getFileTitle($input->isCollection));
        $viewLabels = new ViewLabelsGenerator($input->modelName, $resource->fields, $this->isCollectiveTemplate());

        return $this->replaceNamespace($stub, $this->getClassNamepace($input->modelName, $input->isCollection))
            ->replaceModelApiArray($stub, $this->getModelApiArray($resource->fields, $input->modelName, $input->isCollection))
            ->replaceApiResourceClass($stub, $apiResourceFileName)
            ->replaceApiResourceCollectionClass($stub, $this->getApiResourceCollectionClassName($input->modelName))
            ->replaceTransformMethod($stub, $this->getTransformMethod($input, $resource->fields, $input->isCollection))
            ->replaceStandardLabels($stub, $viewLabels->getLabels())
            ->replaceModelName($stub, $input->modelName)
            ->replaceModelFullname($stub, Helpers::getModelNamespace($input->modelName, $input->modelDirectory))
            ->createFile($destenationFile, $stub)
            ->info('An ' . $this->getFileTitle($input->isCollection) . ' was crafted successfully.');
    }

    /**
     * Gets the namespace for the api class.
     *
     * @param string $modelName
     * @param bool $isCollection
     *
     * @return string
     */
    protected function getClassNamepace($modelName, $isCollection)
    {
        if ($isCollection) {
            return $this->getApiResourceCollectionNamespace(
                $this->getApiResourceCollectionClassName($modelName)
            );
        }

        return $this->getApiResourceNamespace(
            $this->getApiResourceClassName($modelName)
        );
    }

    /**
     * Checks for basic errors
     *
     * @param  CrestApps\CodeGenerator\Models\Resource $resource
     * @param string $destenationFile
     * @param bool $isCollection
     *
     * @return bool
     */
    protected function hasErrors(Resource $resource, $destenationFile, $isCollection)
    {
        $hasErrors = false;
        $title = $this->getFileTitle($isCollection);

        if ($resource->isProtected($title)) {
            $this->warn('The ' . $title . ' is protected and cannot be regenerated. To regenerate the file, unprotect it from the resource-file.');

            $hasErrors = true;
        }

        if ($this->alreadyExists($destenationFile)) {
            $this->error('The ' . $title . ' already exists!');

            $hasErrors = true;
        }

        return $hasErrors;
    }

    /**
     * Gets the destenation file to be created.
     *
     * @param string $name
     * @param bool $isCollection
     *
     * @return string
     */
    protected function getDestenationFile($name, $isCollection = false)
    {
        if ($isCollection) {
            $path = $this->getApiResourceCollectionPath();
        } else {
            $path = $this->getApiResourcePath();
        }

        $path = Helpers::removePreFixWith($path, Helpers::getAppNamespace());

        return app_path($path . $name . '.php');
    }

    /**
     * Gets a clean user inputs.
     *
     * @return object
     */
    protected function getCommandInput()
    {
        $modelName = trim($this->argument('model-name'));
        $resourceFile = trim($this->option('resource-file')) ?: Helpers::makeJsonFileName($modelName);
        $template = $this->getTemplateName();
        $isCollection = $this->option('collection');
        $modelDirectory = trim($this->option('model-directory'));

        return (object) compact(
            'modelName',
            'resourceFile',
            'template',
            'isCollection',
            'modelDirectory'
        );
    }
}
