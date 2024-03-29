<?php

declare(strict_types=1);

use Pebble\Service\Container;
use Pebble\Service\LogService;
use PHPUnit\Framework\TestCase;

final class LogTest extends TestCase
{
    public function setUp(): void
    {
        //
    }

    public function test_can_get_instance(): void
    {
        $container = new Container();
        $container->unsetAll();

        $log = (new LogService())->getLog();
        $this->assertInstanceOf(Monolog\Logger::class, $log);
    }

    public function test_can_create_log_file(): void
    {
        $log = (new LogService())->getLog();
        $log->notice('This is a test');
        $this->assertFileExists('logs/main.log');
    }


    public function test_can_write_to_log(): void
    {
        $log = (new LogService())->getLog();
        $log->info('This is another test');
        $written_to_log = file_get_contents('logs/main.log');
        if (is_string($written_to_log)) {
            $this->assertStringContainsString('This is another test', $written_to_log);
        }
    }

    public static function tearDownAfterClass(): void
    {
        unlink('logs/main.log');
        rmdir('logs');
    }
}
