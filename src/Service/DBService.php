<?php

declare(strict_types=1);

namespace Pebble\Service;

use Pebble\DB;
use Pebble\Service\Container;
use Pebble\Service\ConfigService;

class DBService extends Container
{
    /**
     * @return \Pebble\DB
     */
    public function getDB()
    {
        if (!$this->has('db')) {
            $db_config = (new ConfigService())->getConfig()->getSection('DB');
            $options = $db_config['options'] ?? [];
            $db = new DB($db_config['url'], $db_config['username'], $db_config['password'], $options);
            
            $this->set('db', $db);
        }

        return $this->get('db');
    }
}
