<?php

namespace CrestApps\CodeGenerator\Commands;

use CrestApps\CodeGenerator\Support\Helpers;
use CrestApps\CodeGenerator\Traits\CommonCommand;
use CrestApps\CodeGenerator\Traits\GeneratorReplacers;
use Exception;
use Illuminate\Console\Command;
use Route;

class CreateRoutesCommand extends Command
{
    use CommonCommand, GeneratorReplacers;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:routes
                            {model-name : The model name.}
                            {--controller-name= : The name of the controller where the route should be routing to.}
                            {--routes-prefix=default-form : Prefix of the route group.}
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

        if ($this->isRouteNameExists($this->getDotNotationName($this->getModelName($input->modelName), $input->prefix, 'index'))) {
            $this->warn("The routes already registered!");
            return;
        }

        $routesFile = $this->getRoutesFileName();

        if (!$this->isFileExists($routesFile)) {
            throw new Exception("The routes file does not exists. The expected location was " . $routesFile);
        }

        $stub = $this->getStubContent('routes');

        $controllnerName = $this->getControllerName($input->controllerName, $input->controllerDirectory);

        $this->replaceModelName($stub, $input->modelName)
            ->replaceControllerName($stub, $controllnerName)
            ->replaceRouteNames($stub, $this->getModelName($input->modelName), $input->prefix)
            ->processRoutesGroup($stub, $input->prefix, $input->controllerDirectory)
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
        $modelName = trim($this->argument('model-name'));
        $controllerName = trim($this->option('controller-name')) ?: Helpers::postFixWith($modelName, 'Controller');
        $prefix = ($this->option('routes-prefix') == 'default-form') ? Helpers::makeRouteGroup($modelName) : $this->option('routes-prefix');
        $prefix = str_replace('\\', '/', $prefix);
        $controllerDirectory = trim($this->option('controller-directory'));

        return (object) compact(
            'modelName',
            'controllerName',
            'prefix',
            'controllerDirectory'
        );
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
        $this->appendContentToFile($routesFile, $stub);

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
        if (empty($namespace)) {
            return $name;
        }

        $path = Helpers::postFixWith($namespace, '\\');

        return Helpers::convertSlashToBackslash($path . $name);
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
        return $this->replaceTemplate('routes', $routes, $stub);
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
        return $this->replaceTemplate('prefix', $prefix, $stub);
    }

    /**
     * Groups the routes with a prefix and namespace if prefix or namespace is provided.
     *
     * @param string $stub
     * @param string $prefix
     * @param string $namespace
     *
     * @return $this
     */
    protected function processRoutesGroup(&$stub, $prefix, $namespace)
    {
        $prefix = trim($prefix);

        if (!empty($prefix) || !empty($namespace)) {
            $groupStub = $this->getStubContent('routes-group');

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
