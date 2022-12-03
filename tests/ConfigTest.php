<?php

use Pebble\Service\Container;
use Pebble\Service\ConfigService;
use Pebble\Config;
use PHPUnit\Framework\TestCase;

final class ConfigTest extends TestCase
{
    public function test_can_get_service_instance(): void
    {
        $container = new Container();
        $container->unsetAll();

        $config = (new ConfigService())->getConfig();
        $this->assertInstanceOf(Pebble\Config::class, $config);
    }

    public function test_readConfig(): void
    {
        $config = new Config();
        $config_dir = dirname(__FILE__) . '/../config-test';
        $config->readConfig($config_dir);
        $test_config = $config->getSection('Test');
        $this->assertEquals('Test username', $test_config['username']);
    }

    public function test_getSection(): void
    {
        $config = new Config();
        $config_dir = dirname(__FILE__) . '/../config-test';
        $config->readConfig($config_dir);
        $test_config = $config->getSection('Test');
        $this->assertEquals('Test username', $test_config['username']);
    }

    public function test_get(): void
    {
        $config = new Config();
        $config_dir = dirname(__FILE__) . '/../config-test';
        $config->readConfig($config_dir);
        $test_config = $config->get('Test.username');
        $this->assertEquals('Test username', $test_config);
    }
}
