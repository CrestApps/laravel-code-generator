<?php

namespace CrestApps\CodeGenerator\Commands;

use CrestApps\CodeGenerator\Support\ViewsCommand;

class CreateEditViewCommand extends ViewsCommand
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:edit-view
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
    protected $description = 'Edit views for the model.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->stubName = 'edit.blade';
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    protected function handleCreateView()
    {
        $input = $this->getCommandInput();
        $stub = $this->getStubContent($this->stubName);
        $fields = $this->getFields($input->fields, $input->languageFileName, $input->fieldsFile);
        $destenationFile = $this->getDestinationViewFullname($input->viewsDirectory, $input->prefix, 'edit');

        if ($this->canCreateView($destenationFile, $input->force, $fields)) {
            $this->createLanguageFile($input->languageFileName, $input->fields, $input->fieldsFile)
                 ->createMissingViews($input)
                 ->replaceCommonTemplates($stub, $input)
                 ->replaceFileUpload($stub, $fields)
                 ->replacePrimaryKey($stub, $this->getPrimaryKeyName($fields))
                 ->replaceModelHeader($stub, $this->getHeaderFieldAccessor($fields, $input->modelName))
                 ->createViewFile($stub, $destenationFile)
                 ->info('Edit view view was crafted successfully.');
        }
    }
}
