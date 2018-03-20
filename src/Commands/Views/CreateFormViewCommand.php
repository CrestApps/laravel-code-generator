<?php

namespace CrestApps\CodeGenerator\Commands\Views;

use CrestApps\CodeGenerator\Commands\Bases\ViewsCommandBase;
use CrestApps\CodeGenerator\Models\Resource;

class CreateFormViewCommand extends ViewsCommandBase
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:form-view
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
    protected $description = 'Create a from-view for the model.';

    /**
     * Gets the name of the stub to process.
     *
     * @return string
     */
    protected function getStubName()
    {
        return 'form.blade';
    }

    /**
     * Execute the console command.
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
            $headers = $this->getHeaderFieldAccessor($resources->fields, $input->modelName);

            $this->createLanguageFile($input->languageFileName, $input->resourceFile, $input->modelName)
                ->replaceCommonTemplates($stub, $input, $resources->fields)
                ->replaceFields($stub, $htmlCreator->getHtmlFields())
                ->replaceModelHeader($stub, $headers)
                ->createFile($destenationFile, $stub)
                ->info('Form view was crafted successfully.');
        }
    }

    /**
     * Replaces the form field's html code in a given stub.
     *
     * @param string $stub
     * @param string $fields
     *
     * @return $this
     */
    protected function replaceFields(&$stub, $fields)
    {
        return $this->replaceTemplate('form_fields_html', $fields, $stub);
    }
}
