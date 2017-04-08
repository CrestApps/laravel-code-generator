<?php

namespace CrestApps\CodeGenerator\Commands;

use File;
use Illuminate\Console\Command;
use CrestApps\CodeGenerator\Support\Helpers;
use CrestApps\CodeGenerator\Traits\CommonCommand;
use CrestApps\CodeGenerator\Models\Label;
use CrestApps\CodeGenerator\Support\CrestAppsTranslator;

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
                            {--fields-file= : File name to import fields from.}
                            {--template-name= : The template name to use when generating the code.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a language file for the model.';

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

        foreach ($languages as $language => $labels) {
            $fileFullName = $this->getLocalePath($language) . $input->fileName . '.php';
            $messagesToRegister = [];
            $phrases = $this->getLangPhrases($labels, $messagesToRegister);

            $this->createPath($this->getLocalePath($language))
                 ->addMessagesToFile($fileFullName, $phrases, $language, $input->template)
                 ->registerMessages($messagesToRegister, $language);
        }
    }

    /**
     * Registers messages to the in-memory collection.
     *
     * @param array $messages
     * @param string $language
     *
     * @return $this
     */
    protected function registerMessages(array $messages, $language)
    {
        if (count($messages) > 0) {
            $this->getTranslator()->addLines($messages, $language);
        }

        return $this;
    }

    /**
     * Checks if a languge has a key int he in-memory collection.
     *
     * @param string $key
     * @param string $language
     *
     * @return bool
     */
    protected function isMessageExists($key, $language)
    {
        return $this->getTranslator()->has($key, $language, false);
    }

    /**
     * Gets a singleton instance of a translator based ont he current framework's version.
     *
     * @return CrestApps\CodeGenerator\Support\CrestAppsTranslator | Illuminate\Translation\Translator
     */
    protected function getTranslator()
    {
        if (!$this->isNewerThan('5.3')) {
            return CrestAppsTranslator::getTranslator();
        }

        return app('translator');
    }

    /**
     * Adds new messages to a language file. It either creates a new file or append to an existsing file.
     *
     * @param string $fileFullname
     * @param string $messages
     * @param string $language
     * @param string $template
     *
     * @return $this
     */
    protected function addMessagesToFile($fileFullname, $messages, $language, $template)
    {
        if (empty($messages)) {
            $this->info('There was no messages to add the language files');
        } else {
            if (File::exists($fileFullname)) {
                $this->appendMessageToFile($fileFullname, "\n" . $messages);
            } else {
                $this->createMessageToFile($fileFullname, $messages, $language, $template);
            }
        }

        return $this;
    }

    /**
     * Appends message to an existing language's file.
     *
     * @param string $fileFullname
     * @param string $messages
     *
     * @return $this
     */
    protected function appendMessageToFile($fileFullname, $messages)
    {
        $stub = File::get($fileFullname);
        $index = $this->getCursorPosition($stub);

        if ($index === false) {
            throw new Exception('Could not find a position in the [' . basename($fileFullname) . '] file to insert the messages.');
        }

        if (! File::put($fileFullname, substr_replace($stub, $messages, $index + 1, 0))) {
            throw new Exception('An error occurred! No messages were added!');
        }

        $this->info('New messages were added to the [' . basename($fileFullname) . '] file');
        
        return $this;
    }

    /**
     * Gets the index on where to append messages to of a giving stub.
     *
     * @param string $stub
     *
     * @return mix (int | false)
     */
    protected function getCursorPosition($stub)
    {
        return strrpos($stub, ',') !== false ?: strrpos($stub, '[');
    }

    /**
     * Creates a new language file with with a giving messages.
     *
     * @param string $fileFullname
     * @param string $messages
     * @param string $language
     * @param string $template
     *
     * @return $this
     */
    protected function createMessageToFile($fileFullname, $messages, $language, $template)
    {
        $stub = $this->getStubContent('language', $template);

        $this->replaceFieldName($stub, $messages);

        if (! File::put($fileFullname, $stub)) {
            throw new Exception('An error occurred! The file  [' . $language . '/' . basename($fileFullname) . '] was not created.');
        }

        $this->info('The file  [' . $language . '/' . basename($fileFullname) . '] was created successfully.');

        return $this;
    }

    /**
     * Creates a colection of messages out of a giving fields collection.
     *
     * @param array $fields
     *
     * @return array
     */
    protected function getLanguageItems(array $fields)
    {
        $items = [];

        foreach ($fields as $field) {
            foreach ($field->getLabels() as $label) {
                if (!$label->isPlain) {
                    $items[$label->lang][] = $label;
                }
            }

            foreach ($field->getOptions() as $lang => $labels) {
                foreach ($labels as $label) {
                    if (!$label->isPlain) {
                        $items[$label->lang][] = $label;
                    }
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
        if (! File::isDirectory($path)) {
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
        $template = trim($this->option('template-name'));

        return (object) compact('fileName', 'fields', 'fieldsFile', 'template');
    }

    /**
     * Creates a string on phrases
     *
     * @param array $fields
     *
     * @return string
     */
    protected function getLangPhrases(array $labels, array & $messagesToRegister)
    {
        $messages = [];
        foreach ($labels as $label) {
            if (! $this->isMessageExists($label->localeGroup, $label->lang)) {
                $messages[] = sprintf("    '%s' => '%s'", $label->id, $label->text);
                $messagesToRegister[$label->localeGroup] = $label->text;
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
