<?php

declare(strict_types=1);

namespace Pebble\Service;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Pebble\Path;
use Pebble\Service\ConfigService;

class LogService
{
    /**
     * @var Monolog\Logger
     */
    public static $log;

    /**
     * Returns a logger that will log to logs/main.log
     * Debug level can be set in setting `App.debug_level`
     * If not set `App.debug_level` is `Logger::DEBUG` (100)
     *  
     * @return \Monolog\Logger
     */
    public function getLog()
    {
        if (self::$log) {
            return self::$log;
        }

        $base_path = Path::getBasePath();
        $debug_level = (new ConfigService())->getConfig()->get('App.debug_level');
        if (!$debug_level) $debug_level = Logger::DEBUG;

        self::$log = new Logger('base');
        self::$log->pushHandler(new StreamHandler($base_path . '/logs/main.log', $debug_level));
        return self::$log;
    }
}
