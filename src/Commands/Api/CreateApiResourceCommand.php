<?php

namespace CrestApps\CodeGenerator\Commands\Api;

use CrestApps\CodeGenerator\Models\Resource;
use CrestApps\CodeGenerator\Support\Helpers;
use CrestApps\CodeGenerator\Support\Str;
use CrestApps\CodeGenerator\Support\ViewLabelsGenerator;
use CrestApps\CodeGenerator\Traits\ApiResourceTrait;
use CrestApps\CodeGenerator\Traits\CommonCommand;
use CrestApps\CodeGenerator\Traits\GeneratorReplacers;
use CrestApps\CodeGenerator\Traits\ModelTrait;
use Illuminate\Console\Command;

class CreateApiResourceCommand extends Command
{
    use CommonCommand, GeneratorReplacers, ApiResourceTrait, ModelTrait;

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
                            {--api-version= : The api version to prefix your resources with.}
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
        $apiResourceFileName = $this->getApiFileName($input->modelName);

        $destenationFile = $this->getDestenationFile($apiResourceFileName);

        if ($this->hasErrors($resource, $destenationFile)) {
            return false;
        }

        $stub = $this->getStubContent($this->getFileTitle());
        $viewLabels = new ViewLabelsGenerator($input->modelName, $resource->fields);

        return $this->replaceNamespace($stub, $this->getClassNamepace())
            ->replaceModelApiArray($stub, $this->getModelApiArray($resource->fields, $input->modelName))
            ->replaceApiResourceClass($stub, $apiResourceFileName)
            ->replaceApiResourceCollectionClass($stub, $this->getApiResourceCollectionClassName($input->modelName))
            ->replaceTransformMethod($stub, $this->getTransformMethod($input, $resource->fields))
            ->replaceStandardLabels($stub, $viewLabels->getLabels())
            ->replaceModelName($stub, $input->modelName)
            ->replaceModelFullname($stub, self::getModelNamespace($input->modelName, $input->modelDirectory))
            ->createFile($destenationFile, $stub)
            ->info('An ' . $this->getFileTitle($input->isCollection) . ' was crafted successfully.');
    }

    /**
     * Gets the namespace for the API class.
     *
     * @return string
     */
    protected function getClassNamepace()
    {
        return $this->getApiResourceNamespace();
    }

    /**
     * Gets the file name for the API class.
     *
     * @param string $modelName
     *
     * @return string
     */
    protected function getApiFileName($modelName)
    {
        return $this->getApiResourceClassName($modelName);
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
    protected function hasErrors(Resource $resource, $destenationFile)
    {
        $hasErrors = false;
        $title = $this->getFileTitle();

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
     * Gets the destination file to be created.
     *
     * @param string $name
     *
     * @return string
     */
    protected function getDestenationFile($name)
    {    
        $path = Str::trimStart($this->getApiResourcePath(), Helpers::getAppNamespace());

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
        $modelDirectory = trim($this->option('model-directory'));
        $apiVersion = trim($this->option('api-version'));

        return (object) compact(
            'modelName',
            'resourceFile',
            'template',
            'modelDirectory'
        );
    }
}
