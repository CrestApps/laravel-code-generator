<?php

namespace CrestApps\CodeGenerator\Commands;

use CrestApps\CodeGenerator\Support\ViewsCommand;

class CreateCreateViewCommand extends ViewsCommand
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:create-view
                            {model-name : The model name that this view will represent.}
                            {--fields= : The fields to define the model.}
                            {--fields-file= : File name to import fields from.}
                            {--views-directory= : The name of the directory to create the views under.}
                            {--routes-prefix= : The routes prefix.}
                            {--lang-file-name= : The name of the language file.}
                            {--layout-name=layouts.app : This will extract the validation into a request form class.}
                            {--template-name= : The template name to use when generating the code.}
                            {--force : This option will override the view if one already exists.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a create-views for the model.';

    /**
     * Gets the name of the stub to process.
     *
     * @return string
     */
    protected function getStubName()
    {
        return 'create.blade';
    }
    
    /**
     * Execute the console command.
     *
     * @return void
     */
    protected function handleCreateView()
    {
        $input = $this->getCommandInput();
        $fields = $this->getFields($input->fields, $input->languageFileName, $input->fieldsFile);
        $destenationFile = $this->getDestinationViewFullname($input->viewsDirectory, $input->prefix, 'create');

        if ($this->canCreateView($destenationFile, $input->force, $fields)) {
            $stub = $this->getStub();
            $headers = $this->getHeaderFieldAccessor($fields, $input->modelName);

            $this->createLanguageFile($input->languageFileName, $input->fields, $input->fieldsFile, $input->modelName)
                 ->createMissingViews($input)
                 ->replaceCommonTemplates($stub, $input, $fields)
                 ->replaceFileUpload($stub, $fields)
                 ->replaceModelHeader($stub, $headers)
                 ->createFile($destenationFile, $stub)
                 ->info('Create view was crafted successfully.');
        }
    }
}
