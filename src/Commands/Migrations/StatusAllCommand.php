<?php

namespace CrestApps\CodeGenerator\Commands\Migrations;

use CrestApps\CodeGenerator\Commands\Bases\MigrationCommandBase;
use Illuminate\Database\Migrations\Migrator;
use Illuminate\Support\Collection;
use Symfony\Component\Console\Input\InputOption;

class StatusAllCommand extends MigrationCommandBase
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'migrate:status-all';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Show the status of each migration from all folders';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->migrator->setConnection($this->option('database'));

        if (!$this->migrator->repositoryExists()) {
            return $this->error('No migrations found.');
        }

        $ran = $this->migrator->getRepository()->getRan();

        if (count($migrations = $this->getStatusFor($ran)) > 0) {
            $this->table(['Ran?', 'Migration'], $migrations);
        } else {
            $this->error('No migrations found');
        }
    }

    /**
     * Get the status for the given ran migrations.
     *
     * @param  array  $ran
     * @return \Illuminate\Support\Collection
     */
    protected function getStatusFor(array $ran)
    {
        return Collection::make($this->getAllMigrationFiles())
            ->map(function ($migration) use ($ran) {
                $migrationName = $this->migrator->getMigrationName($migration);

                return in_array($migrationName, $ran)
                ? ['<info>Y</info>', $migrationName]
                : ['<fg=red>N</fg=red>', $migrationName];
            });
    }

    /**
     * Get an array of all of the migration files.
     *
     * @return array
     */
    protected function getAllMigrationFiles()
    {
        return $this->migrator->getMigrationFiles($this->getMigrationPaths());
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['database', null, InputOption::VALUE_OPTIONAL, 'The database connection to use.'],
        ];
    }
}
