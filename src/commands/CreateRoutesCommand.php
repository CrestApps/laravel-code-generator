<?php

namespace CrestApps\CodeGenerator\Commands;

use File;
use Route;
use Exception;
use Illuminate\Console\Command;
use CrestApps\CodeGenerator\Traits\CommonCommand;
use CrestApps\CodeGenerator\Support\Helpers;

class CreateRoutesCommand extends Command
{
    use CommonCommand;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:routes
                            {controller-name : The name of the controller where the route should be routing to.}
                            {--model-name= : The model name.}
                            {--routes-prefix= : The routes prefix.}
                            {--template-name= : The template name to use when generating the code.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create routes for the crud';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
    
        $input = $this->getCommandInput();

        if($this->isRouteNameExists($this->getDotNotationName($input->modelName, $input->prefix, 'index')))
        {
            $this->warn("The route is already registred!");
            return;
        }

        $routesFile = $this->getRoutesFileName();

        if (!File::exists($routesFile))
        {
            throw new Exception("The routes file does not exists. The expected location was " . $routesFile);
        }

        $stub = File::get($this->getStubByName('routes', $input->template));

        $this->replaceModelName($stub, $input->modelName)
             ->replaceControllerName($stub, $input->controllerName)
             ->replaceRouteNames($stub, $input->modelName, $input->prefix)
             ->processRoutePrefix($stub, $input->prefix, $input->template)
             ->appendToRoutesFile($stub, $routesFile);
    }

    /**
     * Gets a clean user inputs.
     *
     * @return object
     */
    protected function getCommandInput()
    {
        $name = trim($this->argument('controller-name'));
        $controllerName = Helpers::postFixWith($name, 'Controller');
        $modelName = trim($this->option('model-name')) ?: str_singular($name);
        $prefix = trim($this->option('routes-prefix'));
        $template = $this->getTemplateName();

        return (object) compact('modelName','controllerName','prefix','template');
    }

    /**
     * Appends the new routes to a route file
     *
     * @param string $stub
     * @param string $routesFile
     *
     * @return $this
     */
    protected function appendToRoutesFile($stub, $routesFile)
    {
        if (File::append($routesFile, $stub)) 
        {
            $this->info('The routes were added successfully.');
        } else 
        {
            $this->info('Unable to add the route to ' . $routesFile);
        }

        return $this;
    }

    /**
     * Replaces the controller name for the given stub.
     *
     * @param string $stub
     * @param string $controllerName
     *
     * @return $this
     */
    protected function replaceControllerName(&$stub, $controllerName)
    {
        $stub = str_replace('{{controllerName}}', $controllerName, $stub);

        return $this;
    }

    /**
     * Replaces the prefix for the given stub.
     *
     * @param string $stub
     * @param string $prefix
     *
     * @return $this
     */
    protected function replaceRoutePrefix(&$stub, $prefix)
    {
        $stub = str_replace('{{prefix}}', $prefix, $stub);

        return $this;
    }

    /**
     * Groups the routes with a prefix value if a prefix is not an empty string
     *
     * @param string $stub
     * @param string $prefix
     * @param string $template
     *
     * @return $this
     */
    protected function processRoutePrefix(&$stub, $prefix, $template)
    {
        $prefix = trim($prefix);

        if(!empty($prefix))
        {
            $groupStub = File::get($this->getStubByName('routes-group', $template));

            $groupStub = str_replace('{{prefix}}', $prefix, $groupStub);
            $stub = str_replace('{{routes}}', $stub, $groupStub);
        }
        
        return $this;
    }

    /**
     * Checks if a route name is already registred.
     *
     * @param string $name
     *
     * @return  bool
     */
    protected function isRouteNameExists($name)
    {
        $existingRoutes = Route::getRoutes();

        foreach ($existingRoutes as $existingRoute) 
        {
            if($existingRoute->getName() == $name)
            {
                return true;
            }
        }

        return false;
    }
}
