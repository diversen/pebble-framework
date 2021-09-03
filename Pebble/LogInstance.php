<?php declare (strict_types = 1);

namespace Pebble;

use Pebble\Log;

/**
 * Used for holding a single instance of Pebble\Log
 */
class LogInstance {

    /**
     * Var holding the log
     */
    public static $log = null;

    /**
     * Init the log instance
     */
    public static function init(Log $log) {
        self::$log = $log;
    }

    /**
     * @return Log
     */
    public static function get() {
        return self::$log;
    }
}
