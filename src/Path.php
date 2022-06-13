<?php

namespace Pebble;

class Path
{
    /**
     * return an app's base path. This is one dir above 'vendor'
     */
    public static function getBasePath()
    {
        return dirname(dirname(dirname(dirname(__dir__))));
    }
}
