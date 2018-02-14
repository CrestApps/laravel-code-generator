<?php

namespace CrestApps\CodeGenerator\Traits;

use CrestApps\CodeGenerator\Support\Str;

trait RouteTrait
{

    /**
     * Gets the route name prefix.
     *
     * @param string $prefix
     * @param string $type
     * @param string $apiVersion
     *
     * @return string
     **/
    protected function getNamePrefix($prefix, $type, $apiVersion)
    {
        $final = '';

        if ($type == 'api') {
            $final = $this->getApiPrefix($apiVersion);
        } else if ($type == 'api-docs') {
            $final = 'api-docs/';
        }

        $final .= $prefix;

        return $final;
    }

    /**
     * Gets prefix for the api.
     *
     * @param string $apiVersion
     *
     * @return string
     **/
    protected function getApiPrefix($apiVersion)
    {
        $prefix = 'api/';

        if (!empty($apiVersion)) {
            return Str::postfix($prefix, $apiVersion) . '/';
        }

        return $prefix;
    }
}
