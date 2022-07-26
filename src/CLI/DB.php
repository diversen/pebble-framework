<?php

namespace Pebble\CLI;

use Pebble\Service\ConfigService;
use Pebble\DB\Utils;
use Diversen\ParseArgv;

class DB
{   
    /**
     * @var \Pebble\Config
     */
    private $config;
    public function __construct()
    {
        $this->config = (new ConfigService())->getConfig();
    }

    /**
     * return main commands help
     * @return array<mixed>
     */
    public function getCommand()
    {
        return
            array(
                'usage' => 'MySQL DB commands used with configuration set in config/DB.php',
                'options' => array(
                    '--connect'            => 'Connect to the database defined in DB.php config',
                    '--server-connect'     => 'Connect to the server. Same as connect but no database is selected',
                    '--backup'             => "Create full backup of the database using mysqldump, which will be placed in './backup'",
                    '--no-data'            => 'Only table definitions in database dumps',
                ),

            );
    }

    private function connect(ParseArgv $args, bool $database = true): void
    {
        $verbose = $args->getOption('verbose');
        $db = $this->config->getSection('DB');
        $ary = Utils::parsePDOString($db['url']);

        $command = "mysql -u $db[username] -p$db[password] -h$ary[host] ";
        if ($database) {
            $command .= $ary['dbname'];
        }

        if ($verbose) {
            echo $command . "\n";
        }

        proc_close(proc_open($command, array(0 => STDIN, 1 => STDOUT, 2 => STDERR), $pipes));
    }

    private function serverConnect(ParseArgv $args): void
    {
        $this->connect($args, false);
    }

    private function backup(ParseArgv $args): int
    {
        $no_data = '';
        if ($args->getOption('no-data')) {
            $no_data = '--no-data';
        }
        $db = $this->config->getSection('DB');
        $ary = Utils::parsePDOString($db['url']);

        if (!file_exists('./backup')) {
            mkdir('./backup');
        }

        $dump_name = 'backup/' . date('Y-m-d_H-i-s') . '.sql';
        $command = "mysqldump $no_data --column-statistics=0 -u $db[username] -p$db[password] -h$ary[host] $ary[dbname] > $dump_name ";

        $return_var = 0;
        passthru($command, $return_var);
        return  $return_var;
    }

    public function runCommand(ParseArgv $args): int
    {
        if ($args->getOption('connect')) {
            $this->connect($args);
        }

        if ($args->getOption('server-connect')) {
            $this->serverConnect($args);
        }

        if ($args->getOption('backup')) {
            $this->backup($args);
        }

        return 0;
    }
}
