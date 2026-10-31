<?php

use Pebble\Headers;
use PHPUnit\Framework\TestCase;

final class HeadersTest extends TestCase
{
    public function test_redirectToHttps(): void
    {
        $_SERVER['HTTP_HOST'] = "attacker.example\r\nX-Injected: yes";
        $_SERVER['REQUEST_URI'] = '/some/url';

        $headers = Headers::getHttpsHeaders('localhost');

        $this->assertEquals($headers[0], 'HTTP/1.1 302 Found');
        $this->assertEquals($headers[1], 'Location: https://localhost/some/url');
    }
}
