<?php

namespace CrestApps\CodeGenerator\Commands\Bases;

use CrestApps\CodeGenerator\Models\Bases\ScaffoldInputBase;
use CrestApps\CodeGenerator\Models\Field;
use CrestApps\CodeGenerator\Support\Helpers;
use CrestApps\CodeGenerator\Traits\CommonCommand;
use CrestApps\CodeGenerator\Traits\ScaffoldTrait;
use Exception;
use Illuminate\Console\Command;

class CreateScaffoldCommandBase extends Command
{
    use CommonCommand, ScaffoldTrait;

    /**
     * Runs any logic before scaffolding
     *
     * @param CrestApps\CodeGenerator\Models\Bases\ScaffoldInputBase $input
     *
     * @return void
     */
    public function beforeScaffold(ScaffoldInputBase $input)
    {
        if ($input->tableExists) {
            $input->withMigration = false;

            $this->createResourceFile($input);
        } else if (!empty($input->fields)) {
            $this->createResourceFileFromString($input);
        }
    }

    /**
     * Ensured fields contains at least one field.
     *
     * @param array $fields
     *
     * @return $this
     */
    protected function validateField($fields)
    {
        if (empty($fields) || !isset($fields[0])) {
            throw new Exception('You must provide at least one field to generate resources!');
        }

        return $this;
    }

    /**
     * Executes the command that generates a migration.
     *
     * @param CrestApps\CodeGenerator\Models\Bases\ScaffoldInputBase $input
     *
     * @return $this
     */
    protected function createMigration(ScaffoldInputBase $input)
    {
        if ($input->withMigration) {
            $this->call(
                'create:migration',
                [
                    'model-name' => $input->modelName,
                    '--table-name' => $input->table,
                    '--migration-class-name' => $input->migrationClass,
                    '--connection-name' => $input->connectionName,
                    '--engine-name' => $input->engineName,
                    '--resource-file' => $input->resourceFile,
                    '--template-name' => $input->template,
                    '--without-timestamps' => $input->withoutTimeStamps,
                    '--with-soft-delete' => $input->withSoftDelete,
                    '--force' => $input->force,
                ]
            );
        }

        return $this;
    }

    /**
     * Executes the command that generate fields' file.
     *
     * @param CrestApps\CodeGenerator\Models\Bases\ScaffoldInputBase $input
     *
     * @return $this
     */
    protected function createResourceFile(ScaffoldInputBase $input)
    {

        $this->call(
            'resource-file:from-database',
            [
                'model-name' => $input->modelName,
                '--table-name' => $input->table,
                '--resource-filename' => $input->resourceFile,
                '--translation-for' => $input->translationFor,
                '--force' => $input->force,
            ]
        );

        return $this;
    }

    /**
     * Executes the command that generate fields' file.
     *
     * @param CrestApps\CodeGenerator\Models\Bases\ScaffoldInputBase $input
     *
     * @return $this
     */
    protected function createResourceFileFromString(ScaffoldInputBase $input)
    {
        $this->call(
            'resource-file:create',
            [
                'model-name' => $input->modelName,
                '--fields' => $input->fields,
                '--resource-filename' => $input->resourceFile,
                '--translation-for' => $input->translationFor,
                '--force' => $input->force,
            ]
        );

        return $this;
    }

    /**
     * Executes the command that generates a language files.
     *
     * @param CrestApps\CodeGenerator\Models\Bases\ScaffoldInputBase $input
     *
     * @return $this
     */
    protected function createLanguage(ScaffoldInputBase $input)
    {
        if (!$input->withoutLanguages) {
            $this->call(
                'create:language',
                [
                    'model-name' => $input->modelName,
                    '--language-filename' => $input->languageFileName,
                    '--resource-file' => $input->resourceFile,
                    '--template-name' => $input->template,
                    '--force' => $input->force,
                ]
            );
        }

        return $this;
    }

    /**
     * Executes the command that generates a model.
     *
     * @param CrestApps\CodeGenerator\Models\Bases\ScaffoldInputBase $input
     *
     * @return $this
     */
    protected function createModel(ScaffoldInputBase $input)
    {
        if (!$input->withoutModel) {
            $this->call(
                'create:model',
                [
                    'model-name' => $input->modelName,
                    '--table-name' => $input->table,
                    '--primary-key' => $input->primaryKey,
                    '--resource-file' => $input->resourceFile,
                    '--model-extends' => $input->modelExtends,
                    '--model-directory' => $input->modelDirectory,
                    '--with-soft-delete' => $input->withSoftDelete,
                    '--without-timestamps' => $input->withoutTimeStamps,
                    '--template-name' => $input->template,
                    '--force' => $input->force,
                ]
            );
        }

        return $this;
    }

    /**
     * Gets a clean user inputs.
     *
     * @return CrestApps\CodeGenerator\Models\ScaffoldInputBase
     */
    protected function getCommandInput()
    {
        $input = new ScaffoldInputBase(trim($this->argument('model-name')));

        $input->prefix = trim($this->option('routes-prefix'));
        $input->languageFileName = trim($this->option('language-filename'));
        $input->table = trim($this->option('table-name'));
        $input->controllerName = trim($this->option('controller-name')) ?: Helpers::makeControllerName($input->modelName);
        $input->perPage = intval($this->option('models-per-page'));
        $input->resourceFile = trim($this->option('resource-file')) ?: Helpers::makeJsonFileName($input->modelName);
        $input->fields = trim($this->option('fields'));
        $input->withFormRequest = $this->option('with-form-request');
        $input->controllerDirectory = $this->option('controller-directory');
        $input->controllerExtends = $this->option('controller-extends') ?: null;
        $input->modelExtends = $this->option('model-extends') ?: null;
        $input->withMigration = $this->option('with-migration');
        $input->force = $this->option('force');
        $input->modelDirectory = $this->option('model-directory');
        $input->primaryKey = $this->option('primary-key');
        $input->withSoftDelete = $this->option('with-soft-delete');
        $input->withoutTimeStamps = $this->option('without-timestamps');
        $input->withoutLanguages = $this->option('without-languages');
        $input->withoutModel = $this->option('without-model');
        $input->withoutController = $this->option('without-controller');
        $input->withoutFormRequest = $this->option('without-form-request');
        $input->migrationClass = $this->option('migration-class-name');
        $input->connectionName = $this->option('connection-name');
        $input->engineName = $this->option('engine-name');
        $input->template = $this->getTemplateName();
        $input->tableExists = $this->option('table-exists');
        $input->translationFor = $this->option('translation-for');
        $input->withAuth = $this->option('with-auth');
        $input->formRequestDirectory = $this->option('form-request-directory');

        return $input;
    }
}
