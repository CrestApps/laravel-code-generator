<?php

namespace CrestApps\CodeGenerator\Commands\Views;

use CrestApps\CodeGenerator\Support\Config;
use CrestApps\CodeGenerator\Support\Helpers;
use CrestApps\CodeGenerator\Support\Str;
use CrestApps\CodeGenerator\Traits\CommonCommand;
use Exception;
use File;
use Illuminate\Console\Command;

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
                            {--without-validation : This option will create a layout without client-side validation.}
                            {--template-name= : The template name to use when generating the code.}
                            {--force : Override existsing layout.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a layout for the views.';

    /**
     * Executes the console command.
     *
     * @return void
     */
    public function handle()
    {
        $input = $this->getCommandInput();
        $path = $this->getDestenationPath($input->layoutDirectory);
        $destenationFile = $path . $input->layoutFileName;

        if ($this->alreadyExists($destenationFile)) {
            $this->error('The layout already exists!');

            return false;
        }

        $stubName = $input->withoutValidation ? 'layout' : 'layout-with-validation';
        $stub = $this->getStubContent($stubName, $this->getTemplateName());

        $this->replaceApplicationName($stub, $input->appName)
            ->createFile($destenationFile, $stub)
            ->info('A layout have been crafted!');
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
        if ($this->fileExists($filename, $force)) {
            throw new Exception('The destenation file already exists. To override the existing file, pass "--force" option.');
        }

        if (!File::put($filename, $content)) {
            throw new Exception('Unexpected error occurred while trying to create the file. please try your request again.');
        }

        return $this;
    }

    /**
     * Checks if the given file exists or not.
     * If the file exists and $force is set to true the method will return true.
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
     * Gets the destenation path.
     *
     * @param string $path
     *
     * @return string
     */
    protected function getDestenationPath($path)
    {
        if (!empty($path)) {
            $path = Helpers::getPathWithSlash($path);
        }

        return Config::getViewsPath() . $path;
    }

    /**
     * Gets a clean user inputs.
     *
     * @return object
     */
    protected function getCommandInput()
    {
        $appName = trim($this->argument('application-name'));
        $layoutFileName = Str::postfix(trim($this->option('layout-filename')) ?: 'layout-filename', '.blade.php');
        $layoutDirectory = trim($this->option('layout-directory'));
        $withoutValidation = $this->option('without-validation');
        $force = $this->option('force');

        return (object) compact('appName', 'layoutFileName', 'layoutDirectory', 'withoutValidation', 'force');
    }

    /**
     * Replaces the application'd name fo the given stub.
     *
     * @param string $stub
     * @param string $appName
     *
     * @return $this
     */
    protected function replaceApplicationName(&$stub, $appName)
    {
        return $this->replaceTemplate('application_name', $appName, $stub);
    }
}
