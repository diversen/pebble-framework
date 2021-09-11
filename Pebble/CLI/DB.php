<?php

namespace Pebble\CLI;

use Pebble\Config;
use Pebble\DB\Helpers;

class DB
{

    // Return main commands help
    public function getCommand()
    {
        return
            array(
                'usage' => 'MySQL DB commands used with configuration set in config/DB.php',
                'options' => array(
                    '--connect'    => 'Connect to the database defined in DB.php config',
                    '--backup'     => "Create full backup of the database using mysqldump, which will be placed in './backup'",
                    '--no-data'    => 'Only table definitions in database dumps',
                    '-v' => 'Output some more info',
                ),

                // //
                // 'arguments' => array(
                //     'File' => 'Read from a file and out put to stdout',
                // ),

            );
    }

    private function connect($args)
    {

        $verbose = $args->getFlag('v');
        $db = Config::getSection('DB');
        $ary = Helpers::parsePDOString($db['url']);

        $command = "mysql -u $db[username] -p$db[password] -h$ary[host] $ary[dbname]";
        if ($verbose) {
            echo $command . "\n";
        }
        proc_close(proc_open($command, array(0 => STDIN, 1 => STDOUT, 2 => STDERR), $pipes));
    }

    private function backup($args)
    {


        $no_data = '';
        if ($args->getFlag('no-data')) {
            $no_data = '--no-data';

        }
        $db = Config::getSection('DB');
        $ary = Helpers::parsePDOString($db['url']);

        if (!file_exists('./backup')) {
            mkdir('./backup');
        }

        $dump_name = 'backup/' . date('Y-m-d_H-i-s') . '.sql';
        $command = "mysqldump $no_data --column-statistics=0 -u $db[username] -p$db[password] -h$ary[host] $ary[dbname] > $dump_name ";

        $return_var = 0;
        passthru ( $command, $return_var);
        exit($return_var);
    }


    public function runCommand($args)
    {

        if ($args->getFlag('connect')) {
            $this->connect($args);
        }

        if ($args->getFlag('backup')) {
            $this->backup($args);
        }

        return 0;
    }
}
