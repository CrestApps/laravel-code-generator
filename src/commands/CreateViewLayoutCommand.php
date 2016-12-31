<?php

namespace CrestApps\CodeGenerator\Commands;

use File;
use Illuminate\Console\Command;
use CrestApps\CodeGenerator\Support\Helpers;

use CrestApps\CodeGenerator\Traits\CommonCommand;
use Exception;

class CreateViewLayoutCommand extends Command
{

    use CommonCommand;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:views-layout
                            {application-name : The name of your application.}
                            {--layout-filename=app : The layout file name to be created.}
                            {--layout-directory=layouts : The directory of the layouts.}
                            {--layout-with-authentication : This option will add links to laravel authentication.}
                            {--force : Override existsing layout.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create layout for the views.';

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
     * @return void
     */
    public function handle()
    {
        $input = $this->getCommandInput();

        $stub = $this->getStubContent('layout');

        $path = $this->getDestenationPath($input->layoutDirectory);

        $this->replaceApplicationName($stub, $input->appName)
             ->createPath($path)
             ->makeFile($path . $input->layoutFileName, $stub, $input->force)
             ->info('The layout have been created!');

    }

    /**
     * Creates a new file.
     *
     * @param string $filename
     * @param string $content
     * @param bool $force
     *
     * @return $this
     */
    protected function makeFile($filename, $content, $force)
    {   
        if($this->fileExists($filename, $force))
        {
            throw new Exception('The destenation file already exists. To override the existing file, pass "--force" option.');
        }

        if(!File::put($filename, $content))
        {
            throw new Exception('Unexpected error occurred while trying to create the file. please try your request again.');
        }

        return $this;
    }

    /**
     * Checks if a file exists or not.
     * if the file exists and $force is set to true the method will return true.
     *
     * @param string $filename
     * @param string $content
     * @param bool $force
     *
     * @return bool
     */
    protected function fileExists($filename, $force)
    {
        if($force)
        {
            return false;
        }

        return File::exists($filename);
    }

    /**
     * Gets the destenation path
     *
     * @param string $path
     *
     * @return string
     */
    protected function getDestenationPath($path)
    {
        if(!empty($path) )
        {
            $path = Helpers::getPathWithSlash($path);
        }

        return $this->getViewsPath() . $path;
    }

    /**
     * Creates a giving path if it does not already exists
     *
     * @param string $path
     *
     * @return $this
     */
    protected function createPath($path)
    {
        if (!File::isDirectory($path)) 
        {
            File::makeDirectory($path, 0755, true);
        }

        return $this;
    }

    /**
     * Gets a clean user inputs.
     *
     * @return object
     */
    protected function getCommandInput()
    {
        $appName = trim($this->argument('application-name'));
        $layoutFileName =  Helpers::postFixWith(trim($this->option('layout-filename')) ?: 'layout-filename', '.blade.php');
        $layoutDirectory =  trim($this->option('layout-directory'));
        $withAuth = trim($this->option('layout-with-authentication'));
        $force =  $this->option('force');

        return (object) compact('appName','layoutFileName','layoutDirectory','withAuth','force');
    }

    /**
     * Replace the applicationName fo the given stub.
     *
     * @param string $stub
     * @param string $appName
     *
     * @return $this
     */
    protected function replaceApplicationName(&$stub, $appName)
    {
        $stub = str_replace('{{applicationName}}', $appName, $stub);

        return $this;
    }
}
