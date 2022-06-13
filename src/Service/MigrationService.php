<?php

namespace Pebble\Service;

use Pebble\Path;
use Pebble\Service\DBService;
use Pebble\Migration;

class MigrationService
{

    /**
     * @return \Pebble\Migration
     */
    public function getMigration()
    {

        $base_path = Path::getBasePath();

        $db = (new DBService())->getDB();
        $pdo_con = $db->getDbh();

        $path_to_migrations = $base_path . '/migrations';
        $migration_file = $base_path . '/.migration';

        $migrate = new Migration($pdo_con, $path_to_migrations, $migration_file);
        
        return $migrate;
    }
}
