<?php declare (strict_types = 1);

namespace Pebble\CLI;

use diversen\parseArgv;
use Pebble\Migration;
use Pebble\DBInstance;
use Pebble\Config;


class Migrate
{

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


    public function runCommand(parseArgv $args)
    {

        // Get DB configuration
        $db_config = Config::getSection('DB');
        if (!$db_config) {
            echo "You will need to create a DB.php in a loaded cofiguration folder\n";
            return 1;
        }

        // Connect to DB and create an instance
        DBInstance::connect($db_config['url'], $db_config['username'], $db_config['password']);

        

        $migrate = new Migration();
        
        $version = $args->getValueByKey(0);
        if ($args->getFlag('up')) {
            
            if (!$version) {
                $version = $migrate->getLatestVersion();
            }
            
            $migrate->up($version);
        }

        if ($args->getFlag('down')) {
            if (!$version) {
                $version = 0;
            }
            $migrate->down( (int)$version);
        }
    }
}
