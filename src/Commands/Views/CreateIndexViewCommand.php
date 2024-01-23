<?php

namespace CrestApps\CodeGenerator\Commands\Views;

use CrestApps\CodeGenerator\Commands\Bases\ViewsCommandBase;
use CrestApps\CodeGenerator\Models\Resource;
use CrestApps\CodeGenerator\Support\Helpers;
use CrestApps\CodeGenerator\Traits\LanguageTrait;

class CreateIndexViewCommand extends ViewsCommandBase
{
    use LanguageTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:index-view
                            {model-name : The model name that this view will represent.}
                            {--resource-file= : The name of the resource-file to import from.}
                            {--views-directory= : The name of the directory to create the views under.}
                            {--routes-prefix=default-form : Prefix of the route group.}
                            {--language-filename= : The name of the language file.}
                            {--layout-name=layouts.app : This will extract the validation into a request form class.}
                            {--pagination-view-name=pagination : the name of the view to use for pagination.}
                            {--template-name= : The template name to use when generating the code.}
                            {--force : This option will override the view if one already exists.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create an index-views for the model.';

    /**
     * Gets the name of the stub to process.
     *
     * @return string
     */
    protected function getStubName()
    {
        return 'index.blade';
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

        $paginationFileName = $this->getDestinationViewFullname($input->viewsDirectory, '', $input->paginationViewName);

        if(!$this->alreadyExists($paginationFileName))
        {
            $pagerStub = $this->getStubContent('pagination.blade', $this->getTemplateName());
            
            $this->createFile($paginationFileName, $pagerStub);
        }

        if ($this->canCreateView($destenationFile, $input->force, $resources)) {
            $stub = $this->getStub();
            $htmlCreator = $this->getHtmlGenerator($resources->fields, $input->modelName, $this->getTemplateName());

            $this->replaceCommonTemplates($stub, $input, $resources->fields)
                ->replacePrimaryKey($stub, $this->getPrimaryKeyName($resources->fields))
                ->replaceHeaderCells($stub, $htmlCreator->getIndexHeaderCells())
                ->replaceBodyCells($stub, $htmlCreator->getIndexBodyCells())
                ->replaceModelHeader($stub, $this->getHeaderFieldAccessor($resources->fields, $input->modelName))
                ->replacePaginationViewName($stub, $input->paginationViewName)
                ->createFile($destenationFile, $stub)
                ->info('Index view was crafted successfully.');
        }
    }

    /**
     * Replaces the fillable for the given stub.
     *
     * @param  string  $stub
     * @param  string  $fillable
     *
     * @return $this
     */
    protected function replacePaginationViewName(&$stub, $name)
    {
        return $this->replaceTemplate('pagination_view_name', $name, $stub);
    }

    /**
     * Gets a clean user inputs.
     *
     * @return object
     */
    protected function getCommandInput()
    {
        $options = $this->options();

        $modelName = trim($this->arguments()['model-name']);
        $resourceFile = trim($options['resource-file']) ?: Helpers::makeJsonFileName($modelName);
        $viewsDirectory = trim($options['views-directory']);
        $prefix = trim($options['routes-prefix']);
        $prefix = ($prefix == 'default-form') ? Helpers::makeRouteGroup($modelName) : $prefix;
        $force = $options['force'];
        $languageFileName = trim($options['language-filename']) ?: self::makeLocaleGroup($modelName);
        $layout = trim($options['layout-name']);
        $template = trim($options['template-name']);
        $paginationViewName = trim($options['pagination-view-name']);

        return (object) compact(
            'modelName',
            'prefix',
            'force',
            'resourceFile',
            'languageFileName',
            'layout',
            'template',
            'paginationViewName',
            'viewsDirectory'
        );
    }

    /**
     * Replaces the column headers in a given stub.
     *
     * @param string $stub
     * @param string $header
     *
     * @return $this
     */
    protected function replaceHeaderCells(&$stub, $header)
    {
        return $this->replaceTemplate('header_cells', $header, $stub);
    }

    /**
     * Replaces the column cells in a given stub.
     *
     * @param string $stub
     * @param string $body
     *
     * @return $this
     */
    protected function replaceBodyCells(&$stub, $body)
    {
        return $this->replaceTemplate('body_cells', $body, $stub);
    }
}
