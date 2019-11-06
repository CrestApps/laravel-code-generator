<?php

namespace CrestApps\CodeGenerator\Commands\Views;

use CrestApps\CodeGenerator\Traits\CommonCommand;
use Illuminate\Console\Command;

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
    protected $description = 'Create a layout for the views.';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->call(
            'create:views-layout',
            [
                'application-name' => $this->argument('application-name'),
                '--layout-filename' => $this->option('layout-filename'),
                '--layout-directory' => $this->option('layout-directory'),
                '--without-validation' => $this->option('without-validation'),
                '--template-name' => $this->option('template-name'),
                '--force' => $this->option('force'),
            ]
        );
    }
}
