<?php

declare(strict_types=1);

namespace Pebble\Service;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Pebble\Path;

class LogService
{
    /**
     * @var Monolog\Logger
     */
    public static $log;

    /**
     * @return \Monolog\Logger
     */
    public function getLog()
    {
        if (self::$log) {
            return self::$log;
        }

        $base_path = Path::getBasePath();

        self::$log = new Logger('base');
        self::$log->pushHandler(new StreamHandler($base_path . '/logs/main.log', Logger::DEBUG));
        return self::$log;
    }
}
