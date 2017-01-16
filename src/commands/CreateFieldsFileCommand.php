<?php

namespace CrestApps\CodeGenerator\Commands;

use Illuminate\Console\Command;
use CrestApps\CodeGenerator\Support\GenerateFormViews;
use CrestApps\CodeGenerator\Support\MysqlParser;
use CrestApps\CodeGenerator\Support\Field;
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
                            {--force : This option will override the view if one already exists.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create json fields file from existing database table.';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $fields = $this->getFields();
        $final = [];
        foreach($fields as $field)
        {
            $final[] = $field->toArray();
        }

        if(File::exists($this->getDestinationFullname()) && !$this->isForce())
        {
            throw new Exception('The file ' . $this->getFilename() . ' already exists. To override it try passing the --force option to override it.' );
        }

        if($this->createFile($this->getDestinationFullname(), json_encode($final, JSON_PRETTY_PRINT)))
        {
            $this->info('A new Fields file have been created.');
        } 
        else 
        {
            $this->error('Something went wrong while trying to create the fields file.');
        }

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
     * Gets the full name of the file to be created.
     *
     * @return string
     */
    protected function getDestinationFullname()
    {
        return $this->getFieldsFilePath() . $this->getFilename();
    }

    /**
     * Gets the fields array.
     *
     * @return array
     */
    protected function getFields()
    {
        $driver = strtolower(DB::getDriverName());

        if($driver == 'mysql')
        {
            return (new MysqlParser($this->getTableName(), $this->getDatabaseName()))->getFields();
        } 
        else 
        {
            throw new Exception('The database driver user is not supported!');
        }

        return [];

    }

    /**
     * Checks the options to see if the force command is provided
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
     * Gets the database name to use.
     *
     * @return string
     */
    protected function getDatabaseName()
    {
        return trim($this->option('database-name')) ?: DB::getConfig('database');
    }

    /**
     * Gets the table name to use.
     *
     * @return string
     */
    protected function getTableName()
    {
        return trim($this->argument('table-name'));
    }
}
