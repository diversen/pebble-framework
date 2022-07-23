<?php

declare(strict_types=1);

use Pebble\Service\ConfigService;
use Pebble\SMTP;
use PHPUnit\Framework\TestCase;

final class SMTPTest extends TestCase
{
    /**
     * @var \Pebble\Config
     */
    private $config;
    private function __setup(): void
    {
        $this->config = (new ConfigService())->getConfig();
    }

    public function test_sendWithException(): void
    {
        $this->__setup();
        $this->expectException(PHPMailer\PHPMailer\Exception::class);
        $smtp = new SMTP($this->config->getSection('SMTP'));
        $file = __DIR__ . '/file_test_files/a_file.txt';
        $smtp->send('test@test.dk', 'test mail', 'Hello world', '<p>Hello world</p>', [$file]);
    }
}
