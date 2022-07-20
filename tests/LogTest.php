<?php

declare(strict_types=1);

use Pebble\Service\Container;
use Pebble\Service\LogService;
use PHPUnit\Framework\TestCase;

final class LogTest extends TestCase
{

    public function test_can_get_instance() {

        $container = new Container();
        $container->unsetAll();

        $log = (new LogService())->getLog();
        $this->assertInstanceOf(Monolog\Logger::class, $log);
    }

    public function test_can_create_log_file() {

        $log = (new LogService())->getLog();
        $log->notice('This is a test');
        $this->assertFileExists('logs/main.log');
    }

    
    public function test_can_write_to_log() {

        $log = (new LogService())->getLog();
        $log->info('This is a test');
        $written_to_log = file_get_contents('logs/main.log');
        $this->assertStringContainsString('This is a test', $written_to_log);
    }

    public static function tearDownAfterClass(): void
    {
        unlink('logs/main.log');
        rmdir('logs');
    }
}
