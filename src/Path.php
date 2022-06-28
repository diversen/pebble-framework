<?php

namespace Pebble;

class Path
{
    /**
     * return an app's base path. This is one dir above 'vendor'
     */
    public static function getBasePath()
    {
        $dir = dirname(__DIR__);

        // If not installed as dependency
        if (file_exists($dir . '/vendor')) {
            return $dir;
        }

        // If installed as dependency
        return dirname(dirname(dirname(dirname(__dir__))));
    }
}
