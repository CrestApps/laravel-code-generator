<?php

namespace CrestApps\CodeGenerator\Commands;

use CrestApps\CodeGenerator\Support\ViewsCommand;
use CrestApps\CodeGenerator\Support\Helpers;

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
                            {--lang-file-name= : The name of the language file.}
                            {--only-views=form,create,edit,show,index : The only views to be created.}
                            {--layout-name=layouts.app : This will extract the validation into a request form class.}
                            {--template-name= : The template name to use when generating the code.}
                            {--force : This option will override the view if one already exists.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create the "form,create,edit,show and index" views for the model.';

    /**
     * Gets the name of the stub to process.
     *
     * @return string
     */
    protected function getStubName()
    {
        return '';
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

        if ($this->isMetRequirements($fields)) {
            $this->info('Crafting views...');

            foreach ($this->getOnlyViews() as $view) {
                $this->call($this->getViewCommand($view), $input->getArrguments());
            }
        }
    }

    /**
     * It checks if a view file exists and the --force option is not present
     *
     * @param string $file
     * @param bool $force
     * @param array $fields
     *
     * @return bool
     */
    protected function isMetRequirements(array $fields = null)
    {
        if (is_null($fields) || !isset($fields[0])) {
            $this->error('You must provide at least one field to generate the views!');
            return false;
        }

        if (is_null($this->getPrimaryKeyName($fields))) {
            $this->error('None of the fields is set primary! You must assign on of the fields to be a primary field.');
            return false;
        }

        return true;
    }

    /**
     * Gets the valid view names after checking the user's only-views option.
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
