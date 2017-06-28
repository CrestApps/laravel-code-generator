<?php

namespace CrestApps\CodeGenerator\Commands;

use Illuminate\Console\Command;
use CrestApps\CodeGenerator\Support\Helpers;
use CrestApps\CodeGenerator\Models\Label;
use CrestApps\CodeGenerator\Traits\CommonCommand;
use CrestApps\CodeGenerator\Support\CrestAppsTranslator;
use CrestApps\CodeGenerator\Support\Config;
use CrestApps\CodeGenerator\Support\ViewLabelsGenerator;

class CreateLanguageCommand extends Command
{
    use CommonCommand;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:language
                            {model-name : The model name.}
                            {--language-file-name= : The name of the file to save the messages in.}
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
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $input = $this->getCommandInput();
        $fields = $this->getFields($input->fields, $input->fileName, $input->fieldsFile);
        $languages = Helpers::getLanguageItems($fields);
        $viewLabels = new ViewLabelsGenerator($input->modelName, $this->isCollectiveTemplate());

        $standardLabels = $viewLabels->getTranslatedLabels(array_keys($languages));

        //Merge the standard labels to the fields label
        foreach ($standardLabels as $lang => $standardLabel) {
            $languages[$lang] = array_merge($languages[$lang], $standardLabel);
        }

        foreach ($languages as $language => $labels) {
            $file = $this->getDestenationFile($language, $input->fileName);
            $messagesToRegister = [];
            $phrases = $this->getLangPhrases($labels, $messagesToRegister);

            $this->addMessagesToFile($file, $phrases, $language)
                 ->registerMessages($messagesToRegister, $language);
        }
    }

    /**
     * Gets the destenation file.
     *
     * @param array $path
     * @param string $name
     *
     * @return string
     */
    protected function getDestenationFile($language, $name)
    {
        $path = $this->getLocalePath($language);

        return sprintf('%s%s.php', $path, $name);
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
        if (! Helpers::isNewerThan('5.3')) {
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
     *
     * @return $this
     */
    protected function addMessagesToFile($fileFullname, $messages, $language)
    {
        if (empty($messages)) {
            $this->info('There was no messages to add the language files');
            return $this;
        }

        if ($this->isFileExists($fileFullname)) {
            $this->appendMessageToFile($fileFullname, $messages);
        } else {
            $this->createMessageToFile($fileFullname, $messages, $language);
        }
        
        return $this;
    }

    /**
     * Appends message to an existing language's file.
     *
     * @param string $fileFullname
     * @param string $messages
     *
     * @return void
     */
    protected function appendMessageToFile($fileFullname, $messages)
    {
        $content = $this->getFileContent($fileFullname);
        $index = $this->getCursorPosition($content);
        $baseFile = basename($fileFullname);

        if ($index === false) {
            throw new Exception('Could not find a position in the [' . $baseFile . '] file to insert the messages.');
        }

        $newContent = substr_replace($content, PHP_EOL . $messages, $index + 1, 1);

        $this->putContentInFile($fileFullname, $newContent)
             ->info('New messages were added to the [' . $baseFile . '] file');
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
        $position = strrpos($stub, ',');

        return $position !== false ? $position : strrpos($stub, '[');
    }

    /**
     * Creates a new language file with with a giving messages.
     *
     * @param string $fileFullname
     * @param string $messages
     * @param string $language
     *
     * @return void
     */
    protected function createMessageToFile($fileFullname, $messages, $language)
    {
        $stub = $this->getStubContent('language');

        $this->replaceFieldName($stub, $messages)
             ->createFile($fileFullname, $stub);

        $this->info('The file  [' . $language . '/' . basename($fileFullname) . '] was created successfully.');
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
        return base_path(Config::getLanguagesPath() . $language . '/');
    }

    /**
     * Gets a clean user inputs.
     *
     * @return object
     */
    protected function getCommandInput()
    {
        $modelName = trim($this->argument('model-name'));
        $fileName = trim($this->option('language-file-name')) ?: Helpers::makeLocaleGroup($modelName);
        $fields = trim($this->option('fields'));
        $fieldsFile = trim($this->option('fields-file')) ?: Helpers::makeJsonFileName($modelName);
        $template = trim($this->option('template-name'));

        return (object) compact('modelName', 'fileName', 'fields', 'fieldsFile', 'template');
    }

    /**
     * Creates a string on phrases.
     *
     * @param array $fields
     * @param array & $messagesToRegister
     *
     * @return string
     */
    protected function getLangPhrases(array $labels, array & $messagesToRegister)
    {
        $messages = [];

        foreach ($labels as $label) {
            if (! $this->isMessageExists($label->localeGroup, $label->lang)) {
                $messages[] = $this->getMessage($label);
                $messagesToRegister[$label->localeGroup] = $label->text;
            }
        }

        $glue =  "," . PHP_EOL;

        return !isset($messages[0]) ? '' : implode($glue, $messages) . $glue;
    }

    /**
     * Get file ready message.
     *
     * @param CrestApps\CodeGenerator\Models\Label
     *
     * @return string
     */
    protected function getMessage(Label $label)
    {
        return sprintf("    '%s' => '%s'", $label->id, $label->text);
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
        $stub = $this->strReplace('messages', $messages, $stub);

        return $this;
    }
}
