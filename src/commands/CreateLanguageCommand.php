<?php

namespace CrestApps\CodeGenerator\Commands;

use File;
use Lang;
use Illuminate\Console\Command;
use CrestApps\CodeGenerator\Support\Helpers;
use CrestApps\CodeGenerator\Traits\CommonCommand;

class CreateLanguageCommand extends Command
{
    use CommonCommand;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:language
                            {language-file-name : The name of the file to save the messages in.}
                            {--fields= : The fields for the form.}
                            {--fields-file= : File name to import fields from.}';


    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create language file for the model.';

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
        $fields = $this->getFields($input->fields, $input->fileName, $input->fieldsFile);

        $languages = $this->getLanguageItems($fields);
        
        foreach ($languages as $language => $labels) 
        {
            $fileFullName = $this->getLocalePath($language) . $input->fileName . '.php';

            $messagesToRegister = [];

            $phrases = $this->getLangPhrases($labels, $language, $messagesToRegister);

            $this->createPath($this->getLocalePath($language))
                 ->addMessagesToFile($fileFullName, $phrases, $language)
                 ->registerMessages($messagesToRegister, $language);
        }
    }

    /**
     * It register messages to the loaded in-memory messages
     *
     * @param array $messages
     * @param string $language
     *
     * @return $this
     */
    protected function registerMessages(array $messages, $language)
    {
        if( count($messages) > 0)
        {
            Lang::addLines($messages, $language);
        }

        return $this;
    }

    /**
     * It checks if a languge has a key.
     *
     * @param string $key
     * @param string $language
     *
     * @return bool
     */
    protected function isMessageExists($key, $language)
    {   
        return Lang::has($key, $language, false);
    }

    /**
     * Adds new messages to a language file. It either creates a new file or append to an existsing file.
     *
     * @param string $fileFullName
     * @param string $messages
     * @param string $language
     *
     * @return $this
     */
    protected function addMessagesToFile($fileFullName, $messages, $language)
    {
        if(!empty($messages))
        {
            if(File::exists($fileFullName))
            {
                $this->appendMessageToFile($fileFullName, "\n" . $messages);
            } 
            else
            {
                $this->createMessageToFile($fileFullName, $messages, $language);
            }
        } else {
            $this->info('There was no messages to add the language files');
        }

        return $this;
    }

    /**
     * Appends message to an existing language file
     *
     * @param string $fileFullName
     * @param string $messages
     *
     * @return $this
     */
    protected function appendMessageToFile($fileFullName, $messages)
    {
        $stub = File::get($fileFullName);

        $index = strrpos($stub, ',');

        if($index === false)
        {
            $index = strrpos($stub, '[');
        }

        if($index !== false)
        {
            $stub = substr_replace($stub, $messages, $index + 1, 0);

            $filename = basename($fileFullName);

            if(File::put($fileFullName, $stub))
            {
                $this->info('New messages were added to the ['.$filename.'] file');
            } 
            else 
            {
                $this->error('No messages were added!');
            }
        } else 
        {
            $this->error('Could not find a position in the [' . $filename . '] file to insert the messages.');
        }

        return $this;
    }

    /**
     * Creates a new language file with with a giving messages.
     *
     * @param string $fileFullName
     * @param string $messages
     * @param string $language
     *
     * @return $this
     */
    protected function createMessageToFile($fileFullName, $messages, $language)
    {

        $stub = $this->getStubContent('language');

        $this->replaceFieldName($stub, $messages);

        if(File::put($fileFullName, $stub))
        {
            $this->info('The file  [' . $language . '/' . basename($fileFullName) . '] was created successfully.');
        } 
        else 
        {
            $this->error('The file  [' . $language . '/' . basename($fileFullName) . '] was not created.');
        }

        return $this;
    }

    /**
     * Creates a colection of messages out of a giving fields collection
     *
     * @param array $fields
     *
     * @return array
     */
    protected function getLanguageItems(array $fields)
    {
        $items = [];

        foreach($fields as $field)
        {
            foreach($field->getLabels() as $lang => $title)
            {
                if(!$title->isPlain)
                {
                    $items[$lang][] = (object) array_merge( (array) $title, ['name' => $field->name]);
                }
            }
        }

        return $items;
    }

    /**
     * Gets the full path of a giving language
     *
     * @param string $language
     *
     * @return string
     */
    protected function getLocalePath($language)
    {
        return $this->getLanguagesPath() . $language . '/';
    }

    /**
     * Creates a giving directory if one does not already exists.
     *
     * @param string $path
     *
     * @return $this
     */
    protected function createPath($path)
    {
        if (!File::isDirectory($path)) 
        {
            File::makeDirectory($path, 0755, true);
        }

        return $this;
    }

    /**
     * Gets a clean user inputs.
     *
     * @return object
     */
    protected function getCommandInput()
    {
        $fileName = strtolower(trim($this->argument('language-file-name')));
        $fields =  trim($this->option('fields'));

        $fieldsFile =  trim($this->option('fields-file'));

        return (object) compact('fileName','fields','fieldsFile');
    }

    /**
     * Creates a string on phrases
     *
     * @param array $fields
     *
     * @return string
     */
    protected function getLangPhrases(array $labels, $language, array & $messagesToRegister)
    {
        $messages = [];
        foreach ($labels as $label) 
        {
            if( !$this->isMessageExists($label->langKey, $language))
            {
                $messages[] = sprintf("    '%s' => '%s'", $label->name, $label->value);
                $messagesToRegister[$label->langKey] = $label->value;
            }
        }

        return !isset($messages[0]) ? '' : implode(",\n", $messages) . ",\n";
    }

    /**
     * Replace the messages fo the given stub.
     *
     * @param string $stub
     * @param string $messages
     *
     * @return $this
     */
    protected function replaceFieldName(&$stub, $messages)
    {
        $stub = str_replace('{{messages}}', $messages, $stub);

        return $this;
    }
}
