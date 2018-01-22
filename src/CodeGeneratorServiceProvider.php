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
        $commands =
            [
            'CrestApps\CodeGenerator\Commands\Framework\CreateControllerCommand',
            'CrestApps\CodeGenerator\Commands\Framework\CreateModelCommand',
            'CrestApps\CodeGenerator\Commands\Framework\CreateLanguageCommand',
            'CrestApps\CodeGenerator\Commands\Framework\CreateFormRequestCommand',
            'CrestApps\CodeGenerator\Commands\Framework\CreateRoutesCommand',
            'CrestApps\CodeGenerator\Commands\Framework\CreateMigrationCommand',
            'CrestApps\CodeGenerator\Commands\Framework\CreateScaffoldCommand',
            'CrestApps\CodeGenerator\Commands\Framework\CreateResourcesCommand',
            'CrestApps\CodeGenerator\Commands\Framework\CreateMappedResourcesCommand',
            'CrestApps\CodeGenerator\Commands\Resources\ResourceFileFromDatabaseCommand',
            'CrestApps\CodeGenerator\Commands\Resources\ResourceFileCreateCommand',
            'CrestApps\CodeGenerator\Commands\Resources\ResourceFileDeleteCommand',
            'CrestApps\CodeGenerator\Commands\Resources\ResourceFileAppendCommand',
            'CrestApps\CodeGenerator\Commands\Resources\ResourceFileReduceCommand',
            'CrestApps\CodeGenerator\Commands\Views\CreateIndexViewCommand',
            'CrestApps\CodeGenerator\Commands\Views\CreateCreateViewCommand',
            'CrestApps\CodeGenerator\Commands\Views\CreateFormViewCommand',
            'CrestApps\CodeGenerator\Commands\Views\CreateEditViewCommand',
            'CrestApps\CodeGenerator\Commands\Views\CreateShowViewCommand',
            'CrestApps\CodeGenerator\Commands\Views\CreateViewsCommand',
            'CrestApps\CodeGenerator\Commands\Views\CreateViewLayoutCommand',
            'CrestApps\CodeGenerator\Commands\Views\CreateLayoutCommand',
            'CrestApps\CodeGenerator\Commands\Api\CreateApiControllerCommand',
            'CrestApps\CodeGenerator\Commands\Api\CreateApiScaffoldCommand',
            'CrestApps\CodeGenerator\Commands\Api\CreateApiDocumentationCommand',
        ];

        if (Helpers::isNewerThanOrEqualTo()) {
            $commands = array_merge($commands,
                [
                    'CrestApps\CodeGenerator\Commands\Migrations\MigrateAllCommand',
                    'CrestApps\CodeGenerator\Commands\Migrations\RefreshAllCommand',
                    'CrestApps\CodeGenerator\Commands\Migrations\ResetAllCommand',
                    'CrestApps\CodeGenerator\Commands\Migrations\RollbackAllCommand',
                    'CrestApps\CodeGenerator\Commands\Migrations\StatusAllCommand',
                ]);
        }

        if (Helpers::isNewerThanOrEqualTo('5.5')) {
            $commands = array_merge($commands,
                [
                    'CrestApps\CodeGenerator\Commands\Api\CreateApiResourceCommand',
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
