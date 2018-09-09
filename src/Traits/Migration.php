<?php

namespace CrestApps\CodeGenerator\Traits;

use App;
use CrestApps\CodeGenerator\Support\Str;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

trait Migration
{
    /**
     * The migrator instance.
     *
     * @var \Illuminate\Database\Migrations\Migrator
     */
    protected $migrator;

    /**
     * Recursively get all of the migration paths.
     *
     * @return array
     */
    protected function getPaths()
    {
        $directory = new RecursiveDirectoryIterator($this->getMigrationPath());
        $iterator = new RecursiveIteratorIterator($directory);
        $paths = [];
        foreach ($iterator as $info) {
            if ($info->isDir()) {
                $paths[] = $info->getPath();
            }
        }

        return array_unique($paths);
    }

    /**
     * Get all of the migration paths.
     *
     * @return array
     */
    protected function getMigrationPaths()
    {
        return array_merge(
            $this->getPaths(), $this->migrator->paths()
        );
    }

    /**
     * Get the path to the migration directory.
     *
     * @return string
     */
    protected function getMigrationPath($path = null)
    {
        $name = $this->laravel->databasePath() . DIRECTORY_SEPARATOR . 'migrations';

        if (!empty($path)) {
            $name .= DIRECTORY_SEPARATOR . $path;
        }

        return $name;
    }

    /**
     * Create a new index instance.
     *
     * @param string $name
     *
     * @return void
     */
    protected function setMigrator()
    {
        $this->migrator = App::make('migrator');
    }

    /**
     * Get all the ran migrations is a key value array where the key in the
     * migration file name and the value is the fullname of the class.
     *
     *
     * @return array
     */
    protected function getRan()
    {
        if ($this->migrator->repositoryExists()) {
            return $this->migrator->getRepository()->getRan();
        }

        return [];
    }

    /**
     * Makes a file name for the migration.
     *
     * @param string $name
     * @param int $count
     *
     * @return string
     */
    protected function getAlterMigrationName($name, $count)
    {
        $filename = sprintf('%s_alter_%s_%s_table.php', date('Y_m_d_His'), strtolower($name), $count);

        return Str::postfix($filename, '.php');
    }

    /**
     * Makes a file name for the migration.
     *
     * @param  string  $name
     *
     * @return string
     */
    protected function getCreateMigrationName($name)
    {
        $filename = sprintf('%s_create_%s_table.php', date('Y_m_d_His'), strtolower($name));

        return Str::postfix($filename, '.php');
    }

    /**
     * Get the name of the create-table's class
     *
     * @param string $tableName
     *
     * @return string
     */
    protected function makeCreateTableClassName($tableName)
    {
        return sprintf('Create%sTable', studly_case($tableName));
    }

    /**
     * Get the name of the alter-table's class
     *
     * @param string $tableName
     * @param int $id
     *
     * @return string
     */
    protected function makeAlterTableClassName($tableName, $id)
    {
        return sprintf('Alter%s%sTable', studly_case($tableName), $id);
    }
}
