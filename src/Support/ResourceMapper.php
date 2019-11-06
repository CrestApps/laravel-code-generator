<?php

namespace CrestApps\CodeGenerator\Support;

use CrestApps\CodeGenerator\Models\Resource;
use CrestApps\CodeGenerator\Support\Helpers;
use Exception;
use File;
use Illuminate\Console\Command;
use CrestApps\CodeGenerator\Traits\CommonCommand;

class ResourceMapper
{
	use CommonCommand;
	
    /**
     * @param Illuminate\Console\Command
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
     * Gets the first map with the given model name
     *
     * @param string $value
     * @param string $key
     *
     * @return mix (null | array)
     */
    public static function first($value, $key = 'model-name')
    {
        $file = self::getMapper();

        if (File::Exists($file)) {

            $maps = self::getMaps($file);

            return Collect($maps)->first(function ($map) use ($key, $value) {
                return isset($map[$key]) && $map[$key] == $value;
            });
        }

        return null;
    }

    /**
     * Gets the first map with the given model name then get a specific property/key
     *
     * @param string $value
     * @param string $key
     * @param string $propertyName
     *
     * @return string
     */
    public static function pluckFirst($value, $key = 'model-name', $propertyName = 'resource-file')
    {
        $first = self::first($value, $key);

        if (!is_null($first) && isset($first[$propertyName])) {
            return $first[$propertyName];
        }

        return null;
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
        $file = self::getMapper();

        $finalMaps = [];

        if (File::Exists($file)) {

            $maps = self::getMaps($file);

            $existingMaps = Collect($maps)->filter(function ($map) use ($modelName) {
                return !$this->mapExists($map, $modelName);
            });

            foreach ($existingMaps as $existingMap) {
                $finalMaps[] = (object) $existingMap;
            }
        }

        File::put($file, Helpers::prettifyJson($finalMaps));
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
        $file = self::getMapper();

        $finalMaps = [];

        if (File::Exists($file)) {
            $maps = self::getMaps($file);

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
		
        $this->putContentInFile($file, Helpers::prettifyJson($finalMaps));
    }

    /**
     * Gets the resources
     *
     * @return CrestApps\CodeGenerator\Models\Resource
     */
    protected function getResources()
    {
        $content = File::get($file);

        return Resource::fromJson($content, 'crestapps');
    }

    /**
     * Checks if a map exists
     *
     * @param array $map
     * @param string $modelName
     * @param string $tableName
     *
     * @return bool
     */
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

    /**
     * Get the full mapper file
     *
     * @return string
     */
    protected static function getMapper()
    {
        $filename = Config::getDefaultMapperFileName();

        return base_path(Config::getResourceFilePath($filename));
    }

    /**
     * Get mapps
     *
     * @return array
     */
    protected static function getMaps($file)
    {
        $maps = json_decode(File::get($file), true);

        if (is_null($maps)) {
            throw new Exception('The existing mapping file contains invalid json string. Please fix the file then try again');
        }

        return $maps;
    }
}
