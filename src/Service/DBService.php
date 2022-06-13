<?php

namespace Pebble\Service;

use Pebble\DB;
use Pebble\Service\ConfigService;

class DBService
{
    /**
     * @var Pebble\DB
     */
    public static $db;

    /**
     * @return \Pebble\DB
     */
    public function getDB()
    {
        if (self::$db) {
            return self::$db;
        }

        $db_config = (new ConfigService())->getConfig()->getSection('DB');
        self::$db = new DB($db_config['url'], $db_config['username'], $db_config['password']);
        return self::$db;
    }
}
