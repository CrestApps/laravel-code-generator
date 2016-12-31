<?php

namespace CrestApps\CodeGenerator\Commands;

use CrestApps\CodeGenerator\Support\ViewsCommand;

use CrestApps\CodeGenerator\Support\Helpers;

use Schema;
use DB;

class CreateViewsCommand extends ViewsCommand
{
    
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:views
                            {model-name : The model name that this view will represent.}
                            {--fields= : The fields to define the model.}
                            {--fields-file= : File name to import fields from.}
                            {--views-directory= : The name of the directory to create the views under.}
                            {--routes-prefix= : The routes prefix.}
                            {--only-views=create,edit,index,show,form : The only views to be created.}
                            {--layout-name=layouts.app : This will extract the validation into a request form class.}
                            {--force : This option will override the view if one already exists.}';


    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create views for the model.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->stubName = 'create.blade';
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    protected function handleCreateView()
    {

        $input = $this->getCommandInput();
        $views = $this->getOnlyViews();

        foreach($views as $view)
        {
            $command = sprintf('create:%s-view', $view);
            $this->callSilent($command, $input->getArrguments());
        }

        $this->info('Views were created successfully.');
    }

    /**
     * Gets the valid view names after checking the user's only-views option
     *
     * @param string $path
     *
     * @return $this
     */
    protected function getOnlyViews()
    {
        $viewsToCreate = Helpers::removeEmptyItems(explode(',', $this->option('only-views')));

        return array_intersect($this->views, $viewsToCreate);
    }

}
