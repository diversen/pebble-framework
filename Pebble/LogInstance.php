<?php declare (strict_types = 1);

namespace Pebble;

/**
 * Used for holding a single instance of Pebble\Log
 */
class LogInstance {

    /**
     * @var Pebble\Log\File
     */
    public static $log = null;

    /**
     * Init the log instance
     */
    public static function init($log) {
        self::$log = $log;
    }

    /**
     * @return \Pebble\Log\File
     */
    public static function get() {
        return self::$log;
    }
}
