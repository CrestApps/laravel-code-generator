<?php

namespace CrestApps\CodeGenerator\Commands;

use File;
use Route;
use Exception;
use Illuminate\Console\Command;
use CrestApps\CodeGenerator\Traits\CommonCommand;
use CrestApps\CodeGenerator\Support\Helpers;
use CrestApps\CodeGenerator\Support\Config;
use CrestApps\CodeGenerator\Traits\GeneratorReplacers;

class CreateRoutesCommand extends Command
{
    use CommonCommand,  GeneratorReplacers;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:routes
                            {controller-name : The name of the controller where the route should be routing to.}
                            {--model-name= : The model name.}
                            {--routes-prefix= : The routes prefix.}
                            {--controller-directory= : The directory where the controller is under.}
                            {--template-name= : The template name to use when generating the code.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create "create, read, update and delete" routes for the model.';

    /**
     * Executes the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $input = $this->getCommandInput();

        if ($this->isRouteNameExists($this->getDotNotationName($input->modelName, $input->prefix, 'index'))) {
            $this->warn("The route is already registred!");
            return;
        }

        $routesFile = $this->getRoutesFileName();

        if (! File::exists($routesFile)) {
            throw new Exception("The routes file does not exists. The expected location was " . $routesFile);
        }

        $stub = File::get($this->getStubByName('routes', $input->template));

        $this->replaceModelName($stub, $input->modelName)
             ->replaceControllerName($stub, $this->getControllerName($input->controllerName, $input->controllerDirectory))
             ->replaceRouteNames($stub, $input->modelName, $input->prefix)
             ->processRoutesGroup($stub, $input->prefix, $input->controllerDirectory, $input->template)
             ->appendToRoutesFile($stub, $routesFile)
             ->info('The routes were added successfully.');
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
        $controllerDirectory = trim($this->option('controller-directory'));

        return (object) compact('modelName', 'controllerName', 'prefix', 'template', 'controllerDirectory');
    }

    /**
     * Appends the new routes to a route file.
     *
     * @param string $stub
     * @param string $routesFile
     *
     * @return $this
     */
    protected function appendToRoutesFile($stub, $routesFile)
    {
        if (! File::append($routesFile, $stub)) {
            throw new Exception('Unable to add the route to ' . $routesFile);
        }

        return $this;
    }

    /**
     * Gets the correct controller name with the namespace.
     *
     * @param string $name
     * @param string $namespace
     *
     * @return string
     */
    protected function getControllerName($name, $namespace)
    {
        return empty($namespace) ? $name : Helpers::convertSlashToBackslash(Helpers::postFixWith($namespace, '\\') . $name);
    }

    /**
     * Replaces the controller name for the given stub.
     *
     * @param string $stub
     * @param string $name
     *
     * @return $this
     */
    protected function replaceControllerName(&$stub, $name)
    {
        $stub = str_replace('{{controllerName}}', $name, $stub);

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
     * Replaces the routes' namespace for the given stub.
     *
     * @param string $stub
     * @param string $name
     *
     * @return $this
     */
    protected function replaceNamespace(&$stub, $name)
    {
        $stub = str_replace('{{namespace}}', $name, $stub);

        return $this;
    }

    /**
     * Replaces the routes for the given stub.
     *
     * @param string $stub
     * @param string $routes
     *
     * @return $this
     */
    protected function replaceRoutes(&$stub, $routes)
    {
        $stub = str_replace('{{routes}}', $routes, $stub);

        return $this;
    }

    /**
     * Replaces the routes' prefix for the given stub.
     *
     * @param string $stub
     * @param string $prefix
     *
     * @return $this
     */
    protected function replacePrefix(&$stub, $prefix)
    {
        $stub = str_replace('{{prefix}}', $prefix, $stub);

        return $this;
    }

    /**
     * Groups the routes with a prefix and namespace if prefix or namespace is provided.
     *
     * @param string $stub
     * @param string $prefix
     * @param string $namespace
     * @param string $template
     *
     * @return $this
     */
    protected function processRoutesGroup(&$stub, $prefix, $namespace, $template)
    {
        $prefix = trim($prefix);

        if (!empty($prefix) || !empty($namespace)) {
            $groupStub = File::get($this->getStubByName('routes-group', $template));

            $this->replacePrefix($groupStub, $this->getGroupPrefix($prefix))
                 ->replaceRoutes($groupStub, $stub);

            $stub = $groupStub;
        }

        return $this;
    }

    /**
     * Gets array ready prefix string.
     *
     * @param string $prefix
     *
     * @return  string
     */
    protected function getGroupPrefix($prefix)
    {
        return empty($prefix) ? '' : sprintf("'prefix' => '%s',", $prefix);
    }

    /**
     * Gets array ready namespace string.
     *
     * @param string $namespace
     *
     * @return  string
     */
    protected function getGroupNamespace($namespace)
    {
        return empty($namespace) ? '' : sprintf("'namespace' => '%s',", $namespace);
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

        foreach ($existingRoutes as $existingRoute) {
            if ($existingRoute->getName() == $name) {
                return true;
            }
        }

        return false;
    }
}
