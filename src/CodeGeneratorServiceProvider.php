<?php

namespace CrestApps\CodeGenerator;

use Illuminate\Support\ServiceProvider;

class CodeGeneratorServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/codegenerator.php' => config_path('codegenerator.php'),
        ], 'default');

        $this->publishes([
            __DIR__ . '/../templates/default' => base_path('resources/codegenerator-templates/default'),
        ], 'default');

        $this->publishes([
            __DIR__ . '/../templates/default-collective' => base_path('resources/codegenerator-templates/default-collective'),
        ], 'collective-template');

        $this->createDirectory(base_path('resources/codegenerator-files'));
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->commands(
            'CrestApps\CodeGenerator\Commands\CreateControllerCommand',
            'CrestApps\CodeGenerator\Commands\CreateModelCommand',
            'CrestApps\CodeGenerator\Commands\CreateIndexViewCommand',
            'CrestApps\CodeGenerator\Commands\CreateCreateViewCommand',
            'CrestApps\CodeGenerator\Commands\CreateFormViewCommand',
            'CrestApps\CodeGenerator\Commands\CreateEditViewCommand',
            'CrestApps\CodeGenerator\Commands\CreateShowViewCommand',
            'CrestApps\CodeGenerator\Commands\CreateViewsCommand',
            'CrestApps\CodeGenerator\Commands\CreateLanguageCommand',
            'CrestApps\CodeGenerator\Commands\CreateFormRequestCommand',
            'CrestApps\CodeGenerator\Commands\CreateRoutesCommand',
            'CrestApps\CodeGenerator\Commands\CreateMigrationCommand',
            'CrestApps\CodeGenerator\Commands\CreateResourcesCommand',
            'CrestApps\CodeGenerator\Commands\CreateMappedResourcesCommand',
            'CrestApps\CodeGenerator\Commands\CreateViewLayoutCommand',
            'CrestApps\CodeGenerator\Commands\CreateLayoutCommand',
            'CrestApps\CodeGenerator\Commands\CreateFieldsFileCommand',
            'CrestApps\CodeGenerator\Commands\FieldsFileCreateCommand',
            'CrestApps\CodeGenerator\Commands\FieldsFileDeleteCommand',
            'CrestApps\CodeGenerator\Commands\FieldsFileAppendCommand',
            'CrestApps\CodeGenerator\Commands\FieldsFileReduceCommand'
        );
    }

    /**
     * create a directory if one does not already exists
     *
     * @param string $path
     * @param string $mode
     *
     * @return void
     */
    protected function createDirectory($path, $mode = '0777')
    {
        if (!file_exists($path)) {
            mkdir($path, $mode);
        }
    }
}
