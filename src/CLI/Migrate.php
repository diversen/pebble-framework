<?php

declare(strict_types=1);

namespace Pebble\CLI;

use Diversen\ParseArgv;
use Pebble\Migration;
use Pebble\DB;
use Pebble\Config;

class Migrate
{
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    // Return main commands help
    public function getCommand()
    {
        return
        array(
            'usage' => "Command to run SQL migrations found in the './migration' folder",
            'options' => array(
                '--up' => 'Migrate up',
                '--down' => 'Migrate down',
            ),

            'arguments' => array(
                'version' => 'Set version to migrate up to or down to',
            ),
        );
    }


    public function runCommand(ParseArgv $args)
    {

        // Get DB configuration
        $db_config = $this->config->getSection('DB');
        if (!$db_config) {
            echo "You will need to create a DB.php in a loaded cofiguration folder\n";
            return 1;
        }

        // Connect to DB and create an instance
        $db = new DB($db_config['url'], $db_config['username'], $db_config['password']);


        $migrate = new Migration($db->getDbh());

        $version = $args->getArgument(0);
        if ($args->getOption('up')) {
            if (!$version) {
                $version = $migrate->getLatestVersion();
            }

            $migrate->up((int)$version);
        }

        if ($args->getOption('down')) {
            if (!$version) {
                $version = 0;
            }
            $migrate->down((int)$version);
        }
    }
}
