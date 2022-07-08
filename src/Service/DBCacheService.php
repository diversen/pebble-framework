<?php

declare(strict_types=1);

namespace Pebble\Service;

use Pebble\Service\DBService;
use Pebble\DBCache;

class DBCacheService
{
    /**
     * @var \Pebble\DBCache
     */
    public static $db_cache;

    /**
     * @return \Pebble\DBCache
     */
    public function getDBCache()
    {
        if (self::$db_cache) {
            return self::$db_cache;
        }

        $db_service = (new DBService())->getDB();
        self::$db_cache = (new DBCache($db_service));
        return self::$db_cache;
    }
}
