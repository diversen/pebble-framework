<?php

use Pebble\Config;
use PHPUnit\Framework\TestCase;

final class ConfigTest extends TestCase
{
    public function test_readConfig()
    {
        $config = new Config();
        $config_dir = dirname(__FILE__) . '/../../config-test';
        $config->readConfig($config_dir);
        $test_config = $config->getSection('Test');
        $this->assertEquals('Test username', $test_config['username']);
    }

    public function test_getSection()
    {
        $config = new Config();
        $config_dir = dirname(__FILE__) . '/../../config-test';
        $config->readConfig($config_dir);
        $test_config = $config->getSection('Test');
        $this->assertEquals('Test username', $test_config['username']);
    }

    public function test_get()
    {
        $config = new Config();
        $config_dir = dirname(__FILE__) . '/../../config-test';
        $config->readConfig($config_dir);
        $test_config = $config->get('Test.username');
        $this->assertEquals('Test username', $test_config);
    }
}
