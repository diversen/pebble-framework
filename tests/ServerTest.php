<?php

declare(strict_types=1);

use Pebble\Server;
use PHPUnit\Framework\TestCase;

final class ServerTest extends TestCase
{
    public function test_getSchemeAndHost(): void
    {
        $_SERVER['HTTP_HOST'] = 'attacker.example';
        $server = new Server('http://10kilobyte.com:8000');
        $scheme_host = $server->getSchemeAndHost();

        $this->assertEquals('http://10kilobyte.com:8000', $scheme_host);

        $server = new Server('http://10kilobyte.com');
        $scheme_host = $server->getSchemeAndHost();

        $this->assertEquals('http://10kilobyte.com', $scheme_host);

        $server = new Server('https://10kilobyte.com');
        $scheme_host = $server->getSchemeAndHost();

        $this->assertEquals('https://10kilobyte.com', $scheme_host);
    }
}
