<?php


namespace CrestApps\CodeGenerator\Commands\Resources;

use CrestApps\CodeGenerator\Support\Config;
use CrestApps\CodeGenerator\Support\Helpers;
use CrestApps\CodeGenerator\Support\ResourceMapper;
use CrestApps\CodeGenerator\Traits\LanguageTrait;
use CrestApps\CodeGenerator\Traits\Migration;
use DB;

class ResourceFileFromDatabaseAllCommand extends ResourceFileFromDatabaseCommand
{
    use Migration, LanguageTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'resource-file:from-database-all
                            {--database-name= : The database name the table is stored in.}
                            {--resource-filename= : The destination file name to create.}
                            {--translation-for= : A comma separated string of languages to create fields for.}
                            {--force : This option will override the view if one already exists.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates all resource-files from existing tables in a database.';

    /**
     * The supported database drivers. lowercase only
     *
     * @var array
     */
    protected $drivers = ['mysql'];

    private $table;
    private $model;

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $parser = $this->getParser();
        $database = $this->getDatabaseName();

        $tables = $parser->getTableNames($database);

        foreach ($tables as $table => $model) {
            $this->table = $table;
            $this->model = $model;

            $this->generate();
        }
    }

    protected function getParser()
    {
        $driver = strtolower(DB::getDriverName());

        if (!in_array($driver, $this->drivers)) {
            throw new \Exception("The database driver [$driver] is not supported!");
        }

        $class = sprintf('CrestApps\CodeGenerator\DatabaseParsers\%sParser', ucfirst($driver));

        return new $class($this->table, $this->getDatabaseName(), $this->getLanguages());
    }

    protected function getModelName()
    {
        return $this->model;
    }

    protected function getTableName()
    {
        return $this->table;
    }


}
