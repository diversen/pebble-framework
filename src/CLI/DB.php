<?php

declare(strict_types=1);

namespace Pebble\CLI;

use InvalidArgumentException;
use Pebble\Service\ConfigService;
use Pebble\DB\Utils;
use Diversen\ParseArgv;

class DB
{
    /**
     * @var \Pebble\Config
     */
    private $config;

    private function quoteOptionValue(string $value): string
    {
        $value = str_replace(['\\', '"'], ['\\\\', '\\"'], $value);
        return '"' . $value . '"';
    }

    private function getCredentialsFile(array $db, array $pdo): string
    {
        foreach ([$db['username'], $db['password'], $pdo['host']] as $value) {
            if (preg_match('/[\r\n]/', (string)$value)) {
                throw new InvalidArgumentException('Database credentials cannot contain newlines');
            }
        }

        $file = tempnam(sys_get_temp_dir(), 'pebble-mysql-');
        if ($file === false) {
            throw new \RuntimeException('Could not create temporary MySQL credentials file');
        }

        chmod($file, 0600);
        $contents = "[client]\n";
        $contents .= 'user=' . $this->quoteOptionValue((string)$db['username']) . "\n";
        $contents .= 'password=' . $this->quoteOptionValue((string)$db['password']) . "\n";
        $contents .= 'host=' . $this->quoteOptionValue((string)$pdo['host']) . "\n";
        file_put_contents($file, $contents, LOCK_EX);
        return $file;
    }
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

        $credentials_file = $this->getCredentialsFile($db, $ary);
        $command = ['mysql', "--defaults-extra-file=$credentials_file"];
        if ($database) {
            $command[] = $ary['dbname'];
        }

        if ($verbose) {
            echo 'mysql [protected credentials]' . ($database ? " {$ary['dbname']}" : '') . "\n";
        }

        try {
            $resource = proc_open($command, [0 => STDIN, 1 => STDOUT, 2 => STDERR], $pipes);
            if (is_resource($resource)) {
                proc_close($resource);
            }
        } finally {
            unlink($credentials_file);
        }
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
            mkdir('./backup', 0700);
        }

        $dump_name = 'backup/' . date('Y-m-d_H-i-s') . '.sql';
        $credentials_file = $this->getCredentialsFile($db, $ary);
        $command = ['mysqldump', "--defaults-extra-file=$credentials_file", '--column-statistics=0'];
        if ($no_data) {
            $command[] = $no_data;
        }
        $command[] = $ary['dbname'];

        try {
            $resource = proc_open($command, [0 => STDIN, 1 => ['file', $dump_name, 'w'], 2 => STDERR], $pipes);
            if (!is_resource($resource)) {
                return 1;
            }
            $return_var = proc_close($resource);
            chmod($dump_name, 0600);
            return $return_var;
        } finally {
            unlink($credentials_file);
        }
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
            return $this->backup($args);
        }

        return 0;
    }
}
