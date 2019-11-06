<?php

namespace CrestApps\CodeGenerator\Commands\Views;

use CrestApps\CodeGenerator\Commands\Bases\ViewsCommandBase;
use CrestApps\CodeGenerator\Models\Resource;

class CreateShowViewCommand extends ViewsCommandBase
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:show-view
                            {model-name : The model name that this view will represent.}
                            {--resource-file= : The name of the resource-file to import from.}
                            {--views-directory= : The name of the directory to create the views under.}
                            {--routes-prefix=default-form : Prefix of the route group.}
                            {--language-filename= : The name of the language file.}
                            {--layout-name=layouts.app : This will extract the validation into a request form class.}
                            {--template-name= : The template name to use when generating the code.}
                            {--force : This option will override the view if one already exists.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a show-view for the model.';

    /**
     * Gets the name of the stub to process.
     *
     * @return string
     */
    protected function getStubName()
    {
        return 'show.blade';
    }

    /**
     * Executes the console command.
     *
     * @return void
     */
    protected function handleCreateView()
    {
        $input = $this->getCommandInput();
        $resources = Resource::fromFile($input->resourceFile, $input->languageFileName);
        $destenationFile = $this->getDestinationViewFullname($input->viewsDirectory, $input->prefix);

        if ($this->canCreateView($destenationFile, $input->force, $resources)) {
            $stub = $this->getStub();
            $htmlCreator = $this->getHtmlGenerator($resources->fields, $input->modelName, $this->getTemplateName());

            $this->replaceCommonTemplates($stub, $input, $resources->fields)
                ->replacePrimaryKey($stub, $this->getPrimaryKeyName($resources->fields))
                ->replaceTableRows($stub, $htmlCreator->getShowRowsHtml())
                ->replaceModelHeader($stub, $this->getHeaderFieldAccessor($resources->fields, $input->modelName))
                ->createFile($destenationFile, $stub)
                ->info('Show view was crafted successfully.');
        }
    }

    /**
     * Replaces the table rows for the given stub.
     *
     * @param string $stub
     * @param string $rows
     *
     * @return $this
     */
    protected function replaceTableRows(&$stub, $rows)
    {
        return $this->replaceTemplate('table_rows', $rows, $stub);
    }
}
