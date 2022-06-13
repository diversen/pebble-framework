<?php

namespace Pebble\Service;

use Pebble\Config;
use Pebble\Path;

class ConfigService
{

    /**
     * @var \Pebble\Config
     */
    public static $config;

    /**
     * @return \Pebble\Config
     */
    public function getConfig()
    {
        if (self::$config) {
            return self::$config;
        }

        $base_path = Path::getBasePath();
        
        self::$config = new Config();

        // Config is read from src/config
        self::$config->readConfig($base_path . '/src/config');
        
        // Config is read from src/config-locale
        //
        // Any settings set in any config file here will override
        // The settings found in src/config
        self::$config->readConfig($base_path . '/src/config-locale');
        return self::$config;
    }
}
