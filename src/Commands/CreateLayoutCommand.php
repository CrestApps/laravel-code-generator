<?php

namespace CrestApps\CodeGenerator\Commands;

use File;
use Exception;
use Illuminate\Console\Command;
use CrestApps\CodeGenerator\Support\Helpers;
use CrestApps\CodeGenerator\Traits\CommonCommand;

class CreateLayoutCommand extends Command
{

    use CommonCommand;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:layout
                            {application-name : The name of your application.}
                            {--layout-filename=app : The layout file name to be created.}
                            {--layout-directory=layouts : The directory of the layouts.}
                            {--without-validation : This option will create a layout without client-side validation.}
                            {--template-name= : The template name to use when generating the code.}
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
        $this->call('create:views-layout',
                [
                    'application-name' => $this->argument('application-name'),
                    '--layout-filename' => $this->option('layout-filename'),
                    '--layout-directory' => $this->option('layout-directory'),
                    '--without-validation' => $this->option('without-validation'),
                    '--template-name' => $this->option('template-name'),
                    '--force' => $this->option('force')
                ]
            );

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

        if( ! File::put($filename, $content))
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
     * @param bool $force
     *
     * @return bool
     */
    protected function fileExists($filename, $force)
    {
        return $force ? false : File::exists($filename);
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
     * Gets a clean user inputs.
     *
     * @return object
     */
    protected function getCommandInput()
    {
        $appName = trim($this->argument('application-name'));
        $layoutFileName =  Helpers::postFixWith(trim($this->option('layout-filename')) ?: 'layout-filename', '.blade.php');
        $layoutDirectory =  trim($this->option('layout-directory'));
        $withoutValidation = $this->option('without-validation');
        $force =  $this->option('force');

        return (object) compact('appName','layoutFileName','layoutDirectory','withoutValidation','force');
    }

    /**
     * Replaces the application's name fo the given stub.
     *
     * @param string $stub
     * @param string $name
     *
     * @return $this
     */
    protected function replaceApplicationName(&$stub, $name)
    {
        $stub = str_replace('{{applicationName}}', $name, $stub);

        return $this;
    }
}
