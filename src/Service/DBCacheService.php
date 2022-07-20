<?php

declare(strict_types=1);

namespace Pebble\Service;

use Pebble\Service\Container;
use Pebble\Service\DBService;
use Pebble\DBCache;

class DBCacheService extends Container
{
    /**
     * @return \Pebble\DBCache
     */
    public function getDBCache()
    {
        if (!$this->get('db_cache')) {
            $db_service = (new DBService())->getDB();
            $db_cache = (new DBCache($db_service));
            $this->set('db_cache', $db_cache);
        }

        return $this->get('db_cache');
    }
}
