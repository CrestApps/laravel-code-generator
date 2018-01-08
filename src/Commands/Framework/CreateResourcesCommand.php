<?php

namespace CrestApps\CodeGenerator\Commands\Framework;

use CrestApps\CodeGenerator\Traits\CommonCommand;
use Illuminate\Console\Command;

class CreateResourcesCommand extends Command
{
    use CommonCommand;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:resources
                            {model-name : The model name that this resource will represent.}
                            {--controller-name= : The name of the controler.}
                            {--controller-directory= : The directory where the controller should be created under. }
                            {--controller-extends=default-controller : The base controller to be extend.}
                            {--model-directory= : The path of the model.}
                            {--views-directory= : The name of the view path.}
                            {--form-request-directory= : The directory of the form-request.}
                            {--resource-file= : The name of the resource-file to import from.}
                            {--fields= : If the resource-file does not exists, passing list of fields here will create it first.}
                            {--routes-prefix=default-form : Prefix of the route group.}
                            {--models-per-page=25 : The amount of models per page for index pages.}
                            {--language-filename= : The languages file name to put the labels in.}
                            {--with-form-request : This will extract the validation into a request form class.}
                            {--with-auth : Generate the controller with Laravel auth middlewear. }
                            {--table-name= : The name of the table.}
                            {--primary-key=id : The name of the primary key.}
                            {--with-soft-delete : Enables softdelete future should be enable in the model.}
                            {--without-languages : Generate the resource without the language files. }
                            {--without-model : Generate the resource without the model file. }
                            {--without-controller : Generate the resource without the controller file. }
                            {--without-form-request : Generate the resource without the form-request file. }
                            {--without-views : Generate the resource without the views. }
                            {--without-timestamps : Prevent Eloquent from maintaining both created_at and the updated_at properties.}
                            {--with-migration : Prevent creating a migration for this resource.}
                            {--migration-class-name= : The name of the migration class.}
                            {--connection-name= : A specific connection name.}
                            {--engine-name= : A specific engine name.}
                            {--layout-name=layouts.app : This will extract the validation into a request form class.}
                            {--template-name= : The template name to use when generating the code.}
                            {--table-exists : This option will attempt to fetch the field from existing database table.}
                            {--translation-for= : A comma seperated string of languages to create fields for.}
                            {--force : This option will override the controller if one already exists.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command has been renamed to create:scaffold.';

    /**
     * Executes the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->warn('*** This command is obsolete and will be removed in future releases. Instead, please use create:scaffold. ***');
        $this->info('Calling create:scaffold to process your request.');

        $this->call(
            'create:scaffold',
            $this->getCallableArray()
        );
    }

    /**
     * Get a commbined array of arguments and callable options.
     *
     * @return array
     */
    public function getCallableArray()
    {
        $options = [];

        foreach ($this->options() as $option => $value) {
            $options['--' . $option] = $value;
        }

        return array_merge($this->arguments(), $options);
    }
}
