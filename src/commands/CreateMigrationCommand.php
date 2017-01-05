<?php

namespace CrestApps\CodeGenerator\Commands;

use File;
use Exception;
use Illuminate\Console\Command;
use CrestApps\CodeGenerator\Support\Helpers;
use CrestApps\CodeGenerator\Support\Field;
use CrestApps\CodeGenerator\Traits\CommonCommand;

class CreateMigrationCommand extends Command
{
    use CommonCommand;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:migration
                            {table-name : The name of the table that the migration will create.}
                            {--migration-class-name= : The name of the migration class.}
                            {--connection-name= : A specific connection name.}
                            {--indexes= : A list of indexes to be add.}
                            {--foreign-keys= : A list of the foreign-keys to be add.}
                            {--engine-name= : A specific engine name.}
                            {--fields= : The fields to create the validation rules from.}
                            {--fields-file= : File name to import fields from.}
                            {--template-name= : The template name to use when generating the code.}
                            {--force : This option will override the form-request if one already exists.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create form-request file for the model.';

    /**
     * The index types eloquent is capable of creating
     *
     * @var string
     */
    protected $validIndexTypes = ['index','unique','primary'];

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $input = $this->getCommandInput();

        $stub = $this->getStubContent('migration', $input->template);
        $fields = $this->getFields($input->fields, 'migration', $input->fieldsFile);

        $properites = $this->getTableProperties($fields, $input);

        $this->replaceSchemaUp($stub, $this->getSchemaUpCommand($input, $properites))
             ->replaceSchemaDown($stub, $this->getSchemaDownCommand($input))
             ->replaceClassName($stub, $input->className)
             ->makeDirectory($this->getMigrationsPath())
             ->makeFile($this->getMigrationsPath() . $this->makeFileName($input->tableName), $stub, $input->force);
    }
   
    /**
     * Creates the table properties.
     *
     * @return string
     */
    protected function getTableProperties(array $fields, $input)
    {
        $properties = '';

        $this->addEngineName($properties, $input->engine)
             ->addPrimaryField($properties, $this->getPrimaryField($fields))
             ->addFieldProperties($properties, $fields)
             ->addIndexes($properties, $input->indexes)
             ->addForeignConstraints($properties, $input->keys); 

        return $properties;
    }

    /**
     * Adds foreign key constraint to a giving $properties.
     *
     * @param string $properties
     * @param array $keys
     * @return $this
     */
    protected function addForeignConstraints(& $properties, array $keys)
    {
        foreach($keys as $key)
        {
            $this->addForeignConstraint($properties, $key)
                 ->addReferencesConstraint($properties, $key)
                 ->addOnConstraint($properties, $key)
                 ->addOnDeleteConstraint($properties, $key)
                 ->addOnUpdateConstraint($properties, $key)
                 ->addFieldPropertyClousure($properties);
        }

        return $this;
    }


    /**
     * Adds 'foreign' eloquent method to a giving properties.
     *
     * @param string $properties
     * @param (object) $key
     * @return $this
     */
    protected function addForeignConstraint(& $properties, $key)
    {
        $properties .= PHP_EOL . sprintf("%s('%s')", $this->getPropertyBase('foreign'), $key->foreign);

        return $this;
    }

    /**
     * Adds 'references' eloquent method to a giving properties.
     *
     * @param string $properties
     * @param (object) $key
     * @return $this
     */
    protected function addReferencesConstraint(& $properties, $key)
    {
        $properties .= $this->getPropertyBaseSpace(18, true) . sprintf("->references('%s')", $key->references);

        return $this;
    }

    /**
     * Adds 'on' eloquent method to a giving properties.
     *
     * @param string $properties
     * @param (object) $key
     * @return $this
     */
    protected function addOnConstraint(& $properties, $key)
    {
        $properties .= $this->getPropertyBaseSpace(18, true) . sprintf("->on('%s')", $key->on);

        return $this;
    }

    /**
     * Adds 'onDelete' eloquent method to a giving properties.
     *
     * @param string $properties
     * @param (object) $key
     * @return $this
     */
    protected function addOnDeleteConstraint(& $properties, $key)
    {
        if(!empty($key->onDelete))
        {
            $properties .= $this->getPropertyBaseSpace(18, true) . sprintf("->onDelete('%s')", $key->onDelete) ;
        }

        return $this;
    }

    /**
     * Adds 'onUpdate' eloquent method to a giving properties.
     *
     * @param string $properties
     * @param (object) $key
     * @return $this
     */
    protected function addOnUpdateConstraint(& $properties, $key)
    {
        if(!empty($key->onUpdate))
        {
            $properties .= $this->getPropertyBaseSpace(18, true) . sprintf("->onUpdate('%s')", $key->onUpdate);
        }

        return $this;
    }

    /**
     * Parses a giving key string
     *
     * @param string $keysString
     * @return array
     */
    protected function getKeysCollections($keysString)
    {
        $keys = [];

        $constraints = Helpers::removeEmptyItems(explode('#', $keysString));

        foreach($constraints as $constraint)
        {
            $keyParts = Helpers::removeEmptyItems(explode('|', $constraint));

            if(isset($keyParts[4]))
            {
                //At this point we know there are foreign, references, on, onDelete, onUpdate
                $keys[] = $this->getReferenceObject($keyParts[0],$keyParts[1],$keyParts[2],$keyParts[3],$keyParts[4]);
            } elseif(isset($keyParts[3]))
            {
                //At this point we know there are foreign, references, onDelete
                $keys[] = $this->getReferenceObject($keyParts[0],$keyParts[1],$keyParts[2],$keyParts[3]);
            }elseif(isset($keyParts[2]))
            {
                //At this point we know there are foreign, references
                $keys[] = $this->getReferenceObject($keyParts[0],$keyParts[1],$keyParts[2]);
            } else {

                throw new Exception('The foreign key relation is not configured correctly.');
            }
        }

        return $keys;
    }

    /**
     * Created foreign relation object
     *
     * @param string $foreign
     * @param string $refrerences
     * @param string $on
     * @param string $onDelete
     * @param string $onUpdate
     * @return object
     */
    protected function getReferenceObject($foreign, $refrerences, $on, $onDelete = null, $onUpdate = null)
    {
        return (object) [
                            'foreign' => $foreign,
                            'references' => $refrerences,
                            'on' => $on,
                            'onDelete' => $onDelete,
                            'onUpdate' => $onUpdate,
                       ];
    }

    /**
     * Adds index method to a giving $properties
     *
     * @param string $properties
     * @param array $indexes
     * @return $this
     */
    protected function addIndexes(& $properties, array $indexes)
    {
        foreach($indexes as $index)
        {
            $properties .= sprintf('%s([%s])', $this->getPropertyBase($index->type), implode(',', $index->columns) );
            $this->addFieldPropertyClousure($properties);
        }

        return $this;
    }

    /**
     * Parses the indexes string
     *
     * @param string $properties
     * @param array $indexes
     * @return array
     */
    protected function getIndexColelction($indexesString)
    {
        $finalIndexes = [];
        $indexes = explode('#', $indexesString);

        foreach($indexes as $index)
        {
            $indexParts = Helpers::removeEmptyItems(explode('=', $index));

            if(isset($indexParts[1]) && in_array( strtolower($indexParts[0]), $this->validIndexTypes))
            {
                $columns = $this->getCleanColumns($indexParts[1]);

                if(isset($columns[0])) 
                {
                    $finalIndexes[] = $this->getForeignObject(strtolower($indexParts[0]), $columns);
                }
            }
        }

        return $finalIndexes;
    }

    /**
     * Creats foreign relation object.
     *
     * @param string $type
     * @param array $columns
     * @return object
     */
    protected function getForeignObject($type, array $columns)
    {
        return (object) [
                            'type' => $type,
                            'columns' => $columns
                        ];
    }

    /**
     * Cleans the columns by removing any non-english chares and wrapps each column with a single quote
     *
     * @param string $properties
     * @param array $indexes
     * @return $this
     */
    protected function getCleanColumns($columnsString)
    {
        $columns = Helpers::removeEmptyItems(explode(',', $columnsString), function($column){
            return trim(Helpers::removeNonEnglishChars($column));
        });

        return Helpers::wrapItems($columns);
    }

    /**
     * Adds the standard field method.
     *
     * @param string $properties
     * @param array $fields
     * @return $this
     */
    protected function addFieldProperties(& $properties, array $fields)
    {
        $primaryField = $this->getPrimaryField($fields);

        foreach($fields as $field)
        {
            if($field instanceof Field && $field != $primaryField)
            {
                $this->addFieldType($properties, $field)
                     ->addFieldComment($properties, $field)
                     ->addFieldUnsigned($properties, $field)
                     ->addFieldNullable($properties, $field)
                     ->addFieldIndex($properties, $field)
                     ->addFieldUnique($properties, $field)
                     ->addFieldPropertyClousure($properties);
            }
        }

        return $this;
    }

    /**
     * Adds a line closure to a property
     *
     * @param string $properties
     * @return $this
     */
    protected function addFieldPropertyClousure(& $property)
    {
        $property .= ';' . PHP_EOL;

        return $this;
    }

    /**
     * Adds a 'field type' to the property
     *
     * @param string $property
     * @param Field $field
     * @return $this
     */
    protected function addFieldType(& $property, Field $field)
    {
        $type = strtolower(Helpers::removeNonEnglishChars($field->dataType));
        if(isset($this->getTypeToMethodMap()[$type]) )
        {
            $params = $this->getMethodParamerters($field);
            $property .= sprintf("%s('%s'%s)", $this->getPropertyBase($this->getTypeToMethodMap()[$type]), $field->name, $params);
        }

        return $this;
    }

    /**
     * Gets the type to method map
     *
     * @return array
     */
    protected function getTypeToMethodMap()
    {
        return config('codegenerator.eloquent_type_to_method');
    }

    /**
     * Constructs the second parameter to the type method
     *
     * @param CrestApps\CodeGenerator\Support\Field $field
     *
     * @return string
     */
    protected function getMethodParamerters(Field $field)
    {
        $params = '';


        if( count($field->methodParams) > 0)
        {
            $params = ', ' . implode(',', $field->methodParams);
        }

        if( $field->dataType == 'enum')
        {
            $params = ', ' . $this->getEnumParams($field);
        }

        return $params;
    }

    /**
     * Constructs the second parameter to the enum type method
     *
     * @param CrestApps\CodeGenerator\Support\Field $field
     *
     * @return string
     */
    protected function getEnumParams(Field $field)
    {

        if( $field->dataType != 'enum')
        {  
            throw new Exception('The field type is not enum! Cannot create an enum column with no options.');
        }

        $values = [];

        foreach($field->getOptionsByLang() as $option)
        {
            if($field->isRequired() && $option->value == '')
            {
                continue;
            }

            $values[] = $option->value;
        }

        if( count($values) == 0)
        {  
            throw new Exception('Could not find any option values to construct the enum values from. ' .
                                'It is possible that this field is required but only have option available has an empty string.');
        }

        return sprintf('[%s]', implode(',', Helpers::wrapItems($values)));
        
    }

    /**
     * Creates the base property from a giving method.
     *
     * @param string $method
     * @return string
     */
    protected function getPropertyBase($method)
    {
        return sprintf('%s$table->%s', $this->getPropertyBaseSpace(), $method);
    }

    /**
     * Creates a leading space to keep the lines alligned the same in the output file
     *
     * @param int $multiplier
     * @param bool $prependNewline
     * @return string
     */
    protected function getPropertyBaseSpace($multiplier = 12, $prependNewline = false)
    {
        return ($prependNewline ? PHP_EOL . '' : '') . str_repeat(' ', $multiplier);
    }

    /**
     * Constructs the schema down command
     *
     * @param (object) $input
     * @return string
     */
    protected function getSchemaDownCommand($input)
    {

        $stub = $this->getStubContent('schema-down', $input->template);

        $this->replaceConnectionName($stub, $input->connection)
             ->replaceTableName($stub, $input->tableName);

        return $stub;
    }


    /**
     * Constructs the schema up command
     *
     * @param (object) $input
     * @return string
     */
    protected function getSchemaUpCommand($input, $blueprintBody)
    {

        $stub = $this->getStubContent('schema-up', $input->template);

        $this->replaceConnectionName($stub, $input->connection)
             ->replaceTableName($stub, $input->tableName)
             ->replaceBlueprintBodyName($stub, $blueprintBody);

        return $stub;
    }



    /**
     * Replace the className of the given stub.
     *
     * @param  string  $stub
     * @param  string  $className
     *
     * @return $this
     */
    protected function replaceClassName(&$stub, $className)
    {
        $stub = str_replace('DummyClass', $className, $stub);

        return $this;
    }

    /**
     * Replace the blueprintBody for the given stub.
     *
     * @param  string  $stub
     * @param  string  $tableName
     *
     * @return $this
     */
    protected function replaceBlueprintBodyName(&$stub, $blueprintBody)
    {
        $stub = str_replace('{{blueprintBody}}', $blueprintBody, $stub);

        return $this;
    }

    /**
     * Replace the tableName for the given stub.
     *
     * @param  string  $stub
     * @param  string  $tableName
     *
     * @return $this
     */
    protected function replaceTableName(&$stub, $tableName)
    {
        $stub = str_replace('{{tableName}}', $tableName, $stub);

        return $this;
    }

    /**
     * Replace the connectionName for the given stub.
     *
     * @param  string  $stub
     * @param  string  $connectionName
     *
     * @return $this
     */
    protected function replaceConnectionName(&$stub, $connectionName)
    {
        $connectionLine = !empty($connectionName) ? sprintf("connection('%s')->", $connectionName) : '';

        $stub = str_replace('{{connectionName}}', $connectionLine, $stub);

        return $this;
    }

    /**
     * Adds the fields' "default" value to the giving property
     *
     * @param string $property
     * @param Field $field
     * @return $this
     */
    protected function addFieldDefaultValue(& $property, Field $field)
    {
        if( !is_null($field->dataValue) && !$field->nullable)
        {
            $property .= sprintf("->default('%s')", $field->dataValue);
        }
    
        return $this;
    }

    /**
     * Adds the fields' "unsigned" value to the giving property
     *
     * @param string $property
     * @param Field $field
     * @return $this
     */
    protected function addFieldUnsigned(& $property, Field $field)
    {
        if($field->isUnsigned)
        {
            $property .= '->unsigned()';
        }
    
        return $this;
    }

    /**
     * Adds the fields' "unique" value to the giving property
     *
     * @param string $property
     * @param Field $field
     * @return $this
     */
    protected function addFieldUnique(& $property, Field $field)
    {
        if($field->isUnique)
        {
            $property .= '->unique()';
        }
    
        return $this;
    }

    /**
     * Adds the fields' "index" value to the giving property
     *
     * @param string $property
     * @param Field $field
     * @return $this
     */
    protected function addFieldIndex(& $property, Field $field)
    {
        if($field->isIndex && !$field->isUnique)
        {
            $property .= '->index()';
        }
    
        return $this;
    }

    /**
     * Adds the fields' "nullable" value to the giving property
     *
     * @param string $property
     * @param Field $field
     * @return $this
     */
    protected function addFieldNullable(& $property, Field $field)
    {
        if($field->isNullable)
        {
            $property .= '->nullable()';
        }
    
        return $this;
    }

    /**
     * Adds the fields' "comment" value to the giving property
     *
     * @param string $property
     * @param Field $field
     * @return $this
     */
    protected function addFieldComment(& $property, Field $field)
    {
        if(!empty($field->comment))
        {
            $property .= sprintf("->comment('%s')", $field->comment);
        }
    
        return $this;
    }

    /**
     * Adds the table's "engine" type to the giving property
     *
     * @param string $property
     * @param string $engineName
     * @return $this
     */
    protected function addEngineName(& $property, $engineName)
    {
        if(!empty($engineName))
        {
            $property .= sprintf("%s = '%s'", $this->getPropertyBase('engine'), $engineName);
            $this->addFieldPropertyClousure($property);
        }
    
        return $this;
    }

    /**
     * Adds the table's "primary column" to the giving property
     *
     * @param string $property
     * @param Field $field
     * @return $this
     */
    protected function addPrimaryField(& $property, Field $field)
    {
        if(!is_null($field))
        {
            $eloquentMethodName = $this->getPrimaryMethodName($field->dataType);
            $property .= sprintf("%s('%s')", $this->getPropertyBase($eloquentMethodName), $field->name);
            $this->addFieldPropertyClousure($property);
        }
    
        return $this;
    }

    /**
     * Gets the correct eloquent increment method from the giving type
     *
     * @param string $type
     * @return string
     */
    protected function getPrimaryMethodName($type)
    {
        $type = strtolower(Helpers::removeNonEnglishChars($type));

        $methodName = 'primary';

        if(in_array($type, ['int','integer']))
        {
            $methodName = 'increments';
        } 
        elseif(in_array($type, ['bigint','biginteger']))
        {
            $methodName = 'bigIncrements';
        } 
        elseif(in_array($type, ['mediuminteger','mediumincrements']))
        {
            $methodName = 'mediumincrements';
        }

        return $methodName;
    }


    /**
     * Gets a clean user inputs.
     *
     * @return object
     */
    protected function getCommandInput()
    {
        $tableName = trim($this->argument('table-name'));

        $className = trim($this->option('migration-class-name')) ?: sprintf('Create%sTable', ucfirst($tableName));
        $connection =  trim($this->option('connection-name'));
        $engine =  trim($this->option('engine-name'));
        $fields = trim($this->option('fields'));
        $fieldsFile = trim($this->option('fields-file'));
        $indexes = $this->getIndexColelction(trim($this->option('indexes')));
        $force = $this->option('force');
        $keys = $this->getKeysCollections(trim($this->option('foreign-keys')));
        $template = $this->getTemplateName();
        return (object) compact('tableName','className','connection','engine','fields','fieldsFile','force','indexes','keys','template');
    }


    /**
     * Makes a file name for the migration
     *
     * @param  string  $path
     * @return $this
     */
    protected function makeFileName($tableName)
    {
        $filename = sprintf('%s_create_%s_table.php', date('Y_m_d_His'), strtolower($tableName));

        return Helpers::postFixWith($filename, '.php');
    }


     /**
     * Build the directory for the class if necessary.
     *
     * @param  string  $path
     * @return $this
     */
    protected function makeDirectory($path)
    {
        if (!File::isDirectory($path)) 
        {
            File::makeDirectory($path, 0755, true, true);
        }

        return $this;
    }


    /**
     * Replace the schema_up for the given stub.
     *
     * @param  string  $stub
     * @param  string  $schemaUp
     *
     * @return $this
     */
    protected function replaceSchemaUp(&$stub, $schemaUp)
    {
        $stub = str_replace('{{schema_up}}', $schemaUp, $stub);

        return $this;
    }

    /**
     * Replace the schema_down for the given stub.
     *
     * @param  string  $stub
     * @param  string  $schemaDown
     *
     * @return $this
     */
    protected function replaceSchemaDown(&$stub, $schemaDown)
    {
        $stub = str_replace('{{schema_down}}', $schemaDown, $stub);

        return $this;
    }

     /**
     * Creates a file
     *
     * @param  string  $fileFullName
     * @param  string  $stub
     * @return $this
     */
    protected function makeFile($fileFullName, $stub, $force = false)
    {
        if(File::exists($fileFullName) && !$force)
        {
            throw new Exception('There is a migration exists with the same name! To override existing file try passing "--force" command');
        }

        if(File::put($fileFullName, $stub))
        {
            $this->info('New migrations have been created');
        } 
        else 
        {
            $this->error('The migration failed to create');
        }

        return $this;
    }


}
