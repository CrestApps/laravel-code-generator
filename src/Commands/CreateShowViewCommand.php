<?php

namespace CrestApps\CodeGenerator\Commands;

use CrestApps\CodeGenerator\Support\ViewsCommand;
use CrestApps\CodeGenerator\Support\GenerateFormViews;

class CreateShowViewCommand extends ViewsCommand
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:show-view
                            {model-name : The model name that this view will represent.}
                            {--fields= : The fields to define the model.}
                            {--fields-file= : File name to import fields from.}
                            {--views-directory= : The name of the directory to create the views under.}
                            {--routes-prefix= : The routes prefix.}
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
        $fields = $this->getFields($input->fields, $input->languageFileName, $input->fieldsFile);
        $destenationFile = $this->getDestinationViewFullname($input->viewsDirectory, $input->prefix, 'show');
        
        if ($this->canCreateView($destenationFile, $input->force, $fields)) {
            $stub = $this->getStub();
            $htmlCreator = $this->getHtmlGenerator($fields, $input->modelName, $this->getTemplateName());

            $this->replaceCommonTemplates($stub, $input)
                 ->replacePrimaryKey($stub, $this->getPrimaryKeyName($fields))
                 ->replaceTableRows($stub, $htmlCreator->getShowRowsHtml())
                 ->replaceModelHeader($stub, $this->getHeaderFieldAccessor($fields, $input->modelName))
                 ->createFile($destenationFile, $stub)
                 ->info('Show view was crafted successfully.');
        }
    }

    /**
     * Replaces the table rows for the giving stub.
     *
     * @param string $stub
     * @param string $rows
     *
     * @return $this
     */
    protected function replaceTableRows(&$stub, $rows)
    {
        $stub = $this->strReplace('table_rows', $rows, $stub);

        return $this;
    }
}
