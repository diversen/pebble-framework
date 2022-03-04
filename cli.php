<?php declare (strict_types = 1);

require_once "vendor/autoload.php";

use Diversen\MinimalCli;
use Pebble\CLI\User;
use Pebble\CLI\DB;
use Pebble\CLI\Migrate;
use Pebble\CLI\Translate;
use Pebble\Autoloader;
use Pebble\Config;

$autoloader = new Autoloader();
$autoloader->setPath(__DIR__);

$config = new Config();

// Load config settings
$config->readConfig('./config');
if (!file_exists('./config-locale')) {
    mkdir('./config-locale');
}

$config->readConfig('./config-locale');

$cli = new MinimalCli();
$cli->commands = [
    'db' => new DB($config),
    'migrate' => new Migrate($config),
];

$cli->runMain();
