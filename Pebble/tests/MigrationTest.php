<?php declare (strict_types = 1);

use Pebble\Auth;
use Pebble\Config;
use Pebble\DBInstance;
use PHPUnit\Framework\TestCase;
use Pebble\Migration;

$config_dir = __DIR__ . '/../../config';
Config::readConfig($config_dir);

$config_dir = __DIR__ . '/../../config-locale';
if (file_exists($config_dir)) {
    Config::readConfig($config_dir);
}

final class MigrationTest extends TestCase
{


}
