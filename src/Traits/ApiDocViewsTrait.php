<?php

namespace CrestApps\CodeGenerator\Traits;

use CrestApps\CodeGenerator\Support\Config;
use CrestApps\CodeGenerator\Support\Helpers;

trait ApiDocViewsTrait
{
    /**
     * It path to view home using the dot notation.
     *
     * @param string $viewsDirectory
     * @param string $routesPrefix
     * @param string $apiVersion
     *
     * @return string
     */
    protected function getPathToViewHome($viewsDirectory, $routesPrefix, $apiVersion = null)
    {
        $path = Config::getApiDocsViewsPath() . $this->getFullViewsPath($viewsDirectory, $routesPrefix, $apiVersion);

        return Helpers::convertToDotNotation($path);
    }

    /**
     * It path to view home using the dot notation.
     *
     * @param $viewsDirectory
     *
     * @return string
     */
    protected function getPathToViews($viewsDirectory)
    {
        $path = Config::getApiDocsViewsPath();

        if (!empty($viewsDirectory)) {
            $path .= Helpers::getPathWithSlash($viewsDirectory);
        }

        return $path;
    }

    /**
     * Gets destenation view path
     *
     * @param string $viewsDirectory
     * @param string $routesPrefix
     * @param string $apiVersion
     *
     * @return $this
     */
    protected function getFullViewsPath($viewsDirectory, $routesPrefix, $apiVersion = null)
    {
        $path = '';

        if (!empty($viewsDirectory)) {
            $path .= Helpers::getPathWithSlash($viewsDirectory);
        }

        if (!empty($apiVersion)) {
            $path .= Helpers::getPathWithSlash($apiVersion);
        }

        if (!empty($routesPrefix)) {
            $path .= Helpers::getPathWithSlash($routesPrefix);
        }

        return str_replace('.', '-', $path);
    }
}
