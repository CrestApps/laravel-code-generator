<?php

namespace CrestApps\CodeGenerator\Commands;

use CrestApps\CodeGenerator\Support\ViewsCommand;
use CrestApps\CodeGenerator\Support\GenerateFormViews;

class CreateIndexViewCommand extends ViewsCommand
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:index-view
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
    protected $description = 'Index views for the model.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->stubName = 'index.blade';
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

        $fields = $this->getFields($input->fields,$input->languageFileName, $input->fieldsFile);
        
        $htmlCreator = new GenerateFormViews($fields, $input->modelName);

        $destenationFile = $this->getDestinationViewFullname($input->viewsDirectory, $input->prefix, 'index');

        $this->handleNewFilePolicy($destenationFile, $input->force, $fields)
             ->replaceCommonTemplates($stub, $input)
             ->replacePrimaryKey($stub, $this->getPrimaryKeyName($fields))
             ->replaceHeaderCells($stub, $htmlCreator->getIndexHeaderCells())
             ->replaceBodyCells($stub, $htmlCreator->getIndexBodyCells())
             ->createViewFile($stub, $destenationFile)
             ->info('Index view was created successfully.');
    }

    /**
     * It Replaces the headerCells in a giving stub
     *
     * @param string $stub
     * @param string $cells
     *
     * @return $this
     */
    protected function replaceHeaderCells(&$stub, $cells)
    {
        $stub = str_replace('{{headerCells}}', $cells, $stub);

        return $this;
    }

    /**
     * It Replaces the bodyCells in a giving stub
     *
     * @param string $stub
     * @param string $cells
     *
     * @return $this
     */
    protected function replaceBodyCells(&$stub, $cells)
    {
        $stub = str_replace('{{bodyCells}}', $cells, $stub);

        return $this;
    }

}
