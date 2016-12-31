<?php

namespace CrestApps\CodeGenerator\Commands;

use CrestApps\CodeGenerator\Support\ViewsCommand;
use CrestApps\CodeGenerator\Support\GenerateFormViews;

class CreateFormViewCommand extends ViewsCommand
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:form-view
                            {model-name : The model name that this view will represent.}
                            {--fields= : The fields to define the model.}
                            {--fields-file= : File name to import fields from.}
                            {--views-directory= : The name of the directory to create the views under.}
                            {--routes-prefix= : The routes prefix.}
                            {--layout-name=layouts.app : This will extract the validation into a request form class.}
                            {--force : This option will override the view if one already exists.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'From views for the model.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->stubName = 'form.blade';
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
        $htmlCreator = new GenerateFormViews($fields, $input->modelName);

        $destenationFile = $this->getDestinationViewFullname($input->viewsDirectory, $input->prefix, 'form');
        
        $this->handleNewFilePolicy($destenationFile, $input->force)
             ->createLanguageFile($input->languageFileName, $input->fields, $input->fieldsFile)
             ->replaceCommonTemplates($stub, $input)
             ->replaceFields($stub, $htmlCreator->getHtmlFields())
             ->createViewFile($stub, $destenationFile)
             ->info('Form view was created successfully.');

    }

    /**
     * It Replaces the formFieldsHtml in a giving stub
     *
     * @param string $stub
     * @param string $fields
     *
     * @return $this
     */
    protected function replaceFields(&$stub, $fields)
    {
        $stub = str_replace('{{formFieldsHtml}}', $fields, $stub);

        return $this;
    }

}
