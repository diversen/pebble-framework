<?php

declare(strict_types=1);

use Pebble\Server;
use PHPUnit\Framework\TestCase;

final class ServerTest extends TestCase
{
    public function test_getSchemeAndHost(): void
    {
        $_SERVER['SERVER_PORT'] = '8000';
        $_SERVER['SERVER_NAME'] = '10kilobyte.com';

        $server = new Server();
        $scheme_host = $server->getSchemeAndHost();

        $this->assertEquals('http://10kilobyte.com:8000', $scheme_host);

        $_SERVER['SERVER_PORT'] = '80';
        $_SERVER['SERVER_NAME'] = '10kilobyte.com';

        $scheme_host = $server->getSchemeAndHost();

        $this->assertEquals('http://10kilobyte.com', $scheme_host);

        $_SERVER['SERVER_PORT'] = '443';
        $_SERVER['SERVER_NAME'] = '10kilobyte.com';

        $scheme_host = $server->getSchemeAndHost();

        $this->assertEquals('https://10kilobyte.com', $scheme_host);
    }
}
