<?php

namespace CrestApps\CodeGenerator;

use CrestApps\CodeGenerator\Support\Helpers;
use File;
use Illuminate\Support\ServiceProvider;

class CodeGeneratorServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        $dir = __DIR__ . '/../';

        $this->publishes([
            $dir . 'config/codegenerator.php' => config_path('codegenerator.php'),
            $dir . 'templates/default' => $this->codeGeneratorBase('templates/default'),
        ], 'default');

        if (!File::exists(config_path('codegenerator_custom.php'))) {
            $this->publishes([
                $dir . 'config/codegenerator_custom.php' => config_path('codegenerator_custom.php'),
            ], 'default');
        }

        $this->publishes([
            $dir . 'templates/default-collective' => $this->codeGeneratorBase('templates/default-collective'),
        ], 'default-collective');

        $this->createDirectory($this->codeGeneratorBase('sources'));
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $commands = [
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
            'CrestApps\CodeGenerator\Commands\ResourceFileFromDatabaseCommand',
            'CrestApps\CodeGenerator\Commands\ResourceFileCreateCommand',
            'CrestApps\CodeGenerator\Commands\ResourceFileDeleteCommand',
            'CrestApps\CodeGenerator\Commands\ResourceFileAppendCommand',
            'CrestApps\CodeGenerator\Commands\ResourceFileReduceCommand',
        ];

        if (Helpers::isNewerThanOrEqualTo()) {

            $commands = array_merge($commands, [
                'CrestApps\CodeGenerator\Commands\Migrations\MigrateAllCommand',
                'CrestApps\CodeGenerator\Commands\Migrations\RefreshAllCommand',
                'CrestApps\CodeGenerator\Commands\Migrations\ResetAllCommand',
                'CrestApps\CodeGenerator\Commands\Migrations\RollbackAllCommand',
                'CrestApps\CodeGenerator\Commands\Migrations\StatusAllCommand',
            ]);
        }

        $this->commands($commands);
    }

    /**
     * Create a directory if one does not already exists
     *
     * @param string $path
     *
     * @return void
     */
    protected function createDirectory($path)
    {
        if (!File::exists($path)) {
            File::makeDirectory($path, 0777, true);
        }
    }

    /**
     * Get the laravel-code-generator base path
     *
     * @param string $path
     *
     * @return string
     */
    protected function codeGeneratorBase($path = null)
    {
        return base_path('resources/laravel-code-generator/') . $path;
    }
}
