<?php

declare(strict_types=1);

namespace Pebble;

class Path
{
    /**
     * Return an app's base path. This is at the same level as 'vendor/'
     */
    public static function getBasePath(): string
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
