<?php

namespace CrestApps\CodeGenerator\Support;

use File;
use Illuminate\Console\Command;

class ResourceMapper
{
    /**
     * * @param Illuminate\Console\Command
     */
    protected $command;

    /**
     * Creates a new mapper instance.
     *
     * @param Illuminate\Console\Command $command
     *
     * @return void
     */
    public function __construct(Command $command)
    {
        $this->command = $command;
    }

    /**
     * Removes mapping entry from the default mapping file.
     *
     * @param string $modelName
     *
     * @return void
     */
    public function reduce($modelName)
    {
        $file = $this->getMapper();

        $finalMaps = [];

        if (File::Exists($file)) {

            $maps = $this->getMaps($file);

            $existingMaps = Collect($maps)->filter(function ($map) use ($modelName) {
                return !$this->mapExists($map, $modelName);
            });

            foreach ($existingMaps as $existingMap) {
                $finalMaps[] = (object) $existingMap;
            }
        }

        File::put($file, $this->getJson($finalMaps));
    }

    /**
     * Removes mapping entry from the default mapping file.
     *
     * @param string $modelName
     * @param string $fieldsFileName
     *
     * @return void
     */
    public function append($modelName, $fieldsFileName, $tableName = null)
    {
        $file = $this->getMapper();

        $finalMaps = [];

        if (File::Exists($file)) {
            $maps = $this->getMaps($file);

            $existingMaps = Collect($maps)->filter(function ($map) use ($modelName, $tableName) {
                return !$this->mapExists($map, $modelName, $tableName);
            });

            $newMap = [
                'model-name' => $modelName,
                'resource-file' => $fieldsFileName,
            ];

            if (!empty($tableName)) {
                $newMap['table-name'] = $tableName;
            }

            $existingMaps->push($newMap);

            foreach ($existingMaps as $existingMap) {
                $finalMaps[] = (object) $existingMap;
            }
        }

        File::put($file, $this->getJson($finalMaps));
    }

    protected function getResources()
    {
        $content = File::get($file);

        return ResourceTransformer::fromJson($content, 'crestapps');
    }

    protected function mapExists(array $map, $modelName, $tableName = null)
    {
        // First we try to find an entry by model-name
        // if not found, we try to find one using the table-name if a value is provided
        $found = isset($map['model-name']) && $map['model-name'] == $modelName;

        if (!$found && !empty($tableName)) {
            $found = isset($map['table-name']) && $map['table-name'] == $tableName;
        }

        return $found;
    }

    protected function getMapper()
    {
        $filename = Config::getDefaultMapperFileName();

        return base_path(Config::getResourceFilePath($filename));
    }

    public function getJson($maps)
    {
        return json_encode($maps, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    protected function getMaps($file)
    {
        $maps = json_decode(File::get($file), true);

        if (is_null($maps)) {
            $this->command->error('The existing mapping file contains invalid json string. Please fix the file then try again');
            throw new Exception();
        }

        return $maps;
    }
}
