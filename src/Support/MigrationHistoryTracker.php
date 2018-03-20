<?php

namespace CrestApps\CodeGenerator\Support;

use CrestApps\CodeGenerator\Models\MigrationCapsule;
use CrestApps\CodeGenerator\Models\MigrationTrackerCapsule;
use CrestApps\CodeGenerator\Support\Helpers;
use CrestApps\CodeGenerator\Traits\Migration;
use File;

class MigrationHistoryTracker
{
    use Migration;

    /**
     * The provided field's file name
     *
     * @var string
     */
    public $capsuleFile;

    /**
     * The provided modelName
     *
     * @var string
     */
    private $capsules;

    /**
     * Create a new migration tracker instance.
     *
     * @return void
     */
    public function __construct()
    {
        $file = base_path(Config::getSystemPath('migration_tracker.json'));

        if (!File::exists($file)) {

            if (!File::exists($path = dirname($file))) {
                File::makeDirectory($path);
            }

            File::put($file, '[]');
        }

        $this->capsuleFile = $file;
    }

    /**
     * Gets all the migration capsules from the file.
     *
     * @return array
     */
    public function all()
    {
        $rawCapsules = json_decode($this->read(), true);
        $capsules = [];

        foreach ($rawCapsules as $rawCapsule) {
            $capsules[] = new MigrationTrackerCapsule($rawCapsule);
        }

        return $capsules;
    }

    /**
     * Gets a migration capsule from the file if any
     *
     * @return mix (null || CrestApps\CodeGenerator\Models\MigrationTrackerCapsule)
     */
    public function get($tableName)
    {
        $capsules = $this->all();

        foreach ($capsules as $capsule) {
            if ($capsule->tableName == $tableName) {
                return $capsule;
            }
        }

        return null;
    }

    /**
     * Updates the migration entry using the $tablename.
     *
     * @return $this
     */
    public function update($tableName, array $properties)
    {
        $capsules = $this->all();

        foreach ($capsules as $capsule) {
            if ($capsule->tableName == $tableName) {
                $capsule = new MigrationTrackerCapsule($properties);
            }
        }

        return $this;
    }

    /**
     * Checks if a migration tracker exists for a given datatable
     *
     * @param string $tableName
     *
     * @return bool
     */
    public function has($tableName)
    {
        $capsules = $this->all();

        foreach ($capsules as $capsule) {
            if ($capsule->tableName == $tableName) {
                return true;
            }
        }

        return false;
    }

    /**
     * Adds properties to the migration tracker
     *
     * @param CrestApps\CodeGenerator\Models\MigrationTrackerCapsule
     * @param CrestApps\CodeGenerator\Models\MigrationCapsule $migration
     *
     * @return array
     */
    public function add(MigrationTrackerCapsule $capsule, MigrationCapsule $migration = null)
    {
        $capsules = $this->all();

        if (!is_null($migration)) {
            $capsule->addMigration($migration);
        }

        $capsules[] = $capsule;

        $this->write($capsules);

        return $capsules;
    }

    /**
     * Writes array of capsules to the file.
     *
     * @param array $capsules
     *
     * @return void
     */
    protected function write(array $capsules)
    {
        $converted = array_map(function ($capsule) {
            return $capsule->toArray();
        }, $capsules);

        File::put($this->capsuleFile, Helpers::prettifyJson($converted));
    }

    /**
     * Reads the current content of the capsule file
     *
     * @return string
     */
    protected function read()
    {
        return File::get($this->capsuleFile) ?: '[]';
    }

    /**
     * Removeds a migration entry from the file given the table name.
     *
     * @param string $tableName
     *
     * @return array
     */
    public function forget($tableName)
    {
        $capsules = $this->all();
        $keep = [];

        foreach ($capsules as $capsule) {
            if ($capsule->tableName == $tableName) {
                $capsule->forgetAllMigrations();

                continue;
            }

            $keep[] = $capsule;
        }

        $this->write($keep);

        return $keep;
    }

    /**
     * Updates the content of the migration.
     *
     * @return $this
     */
    public function updateMigration($tableName, MigrationCapsule $migrationCapsule)
    {
        $capsules = $this->all();

        foreach ($capsules as $capsule) {
            if ($capsule->tableName == $tableName) {
                $capsule->updateMigration($migrationCapsule);
            }
        }

        $this->write($capsules);

        return $this;
    }

    /**
     * Updates the content of the migration.
     *
     * @return $this
     */
    public function addMigration($tableName, MigrationCapsule $migrationCapsule)
    {
        $capsules = $this->all();

        foreach ($capsules as $capsule) {
            if ($capsule->tableName == $tableName) {
                $capsule->addMigration($migrationCapsule);
            }
        }

        $this->write($capsules);

        return $this;
    }

    /**
     * Remove a migration entry from the file given the table name and migration name.
     *
     * @param string $tableName
     * @param string $name
     *
     * @return array
     */
    public function forgetMigration($tableName, $name)
    {
        $capsules = $this->all();
        $keep = [];

        foreach ($capsules as $capsule) {
            if ($capsule->tableName == $tableName) {
                $key = $capsule->getMigrationIndex($name);
                $capsule->forgetMigration($key);
            }

            $keep[] = $capsule;
        }

        $this->write($keep);

        return $keep;
    }

}
