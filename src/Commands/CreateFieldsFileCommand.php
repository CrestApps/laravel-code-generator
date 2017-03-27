<?php

namespace CrestApps\CodeGenerator\Commands;

use Illuminate\Console\Command;
use CrestApps\CodeGenerator\Models\Field;
use CrestApps\CodeGenerator\Support\Helpers;
use CrestApps\CodeGenerator\traits\CommonCommand;
use DB;
use File;
use Exception;

class CreateFieldsFileCommand extends Command
{
    use CommonCommand;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:fields-file
                            {table-name : The dtabase table name to fetch the field from.}
                            {--database-name= : The database name the table is stored in.}
                            {--fields-filename= : The destination file name to create.}
                            {--translation-for= : A comma seperated string of languages to create fields for.}
                            {--force : This option will override the view if one already exists.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create json fields file from existing database table.';

    /**
     * The supported database drivers. lowercase only
     *
     * @var array
     */
    protected $drivers = ['mysql'];

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        if (File::exists($this->getDestinationFullname()) && !$this->isForce()) {
            throw new Exception('The file ' . $this->getFilename() . ' already exists. To override it try passing the --force option to override it.');
        }

        if (!$this->createFile($this->getDestinationFullname(), $this->getFieldAsJson())) {
            throw new Exception('Something went wrong while trying to create the fields file.');
        }

        $this->info('A new Fields file have been created.');
    }

    /**
     * Converts field's object into a user friendly json string.
     *
     *
     * @return string
     */
    protected function getFieldAsJson()
    {
        $fields =  array_map(function ($field) {
            return $field->toArray();
        }, $this->getFields());

        return json_encode($fields, JSON_PRETTY_PRINT);
    }

    /**
     * Create the destenation file.
     *
     * @param string $fullname
     * @param string $content
     *
     * @return bool
     */
    protected function createFile($fullname, $content)
    {
        $this->createDirectory(dirname($fullname));

        return File::put($fullname, $content);
    }

    /**
     * Gets the full name of the destination file.
     *
     * @return string
     */
    protected function getDestinationFullname()
    {
        return $this->getFieldsFilePath() . $this->getFilename();
    }

    /**
     * Gets the fields' collection after from using the connection's driver.
     *
     * @return array
     */
    protected function getFields()
    {
        $driver = strtolower(DB::getDriverName());

        if (!in_array($driver, $this->drivers)) {
            throw new Exception('The database driver user is not supported!');
        }

        $class = sprintf('CrestApps\CodeGenerator\DatabaseParsers\%sParser', ucfirst($driver));

        $parser = new $class($this->getTableName(), $this->getDatabaseName(), $this->getLangugaes());

        return $parser->getFields();
    }

    /**
     * Checks the options to see if the force command was provided.
     *
     * @return bool
     */
    protected function isForce()
    {
        return $this->option('force');
    }

    /**
     * Gets the destenation filename.
     *
     * @return string
     */
    protected function getFilename()
    {
        return Helpers::postFixWith(trim($this->option('fields-filename')) ?: $this->getTableName(), '.json');
    }

    /**
     * Gets the database name.
     *
     * @return string
     */
    protected function getDatabaseName()
    {
        return trim($this->option('database-name')) ?: DB::getConfig('database');
    }

    /**
     * Gets the table name.
     *
     * @return string
     */
    protected function getTableName()
    {
        return trim($this->argument('table-name'));
    }

    /**
     * Gets the languages to create lang keys for.
     *
     * @return array
     */
    protected function getLangugaes()
    {
        return Helpers::convertStringToArray($this->option('translation-for'));
    }
}
