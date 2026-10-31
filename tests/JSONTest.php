<?php

use Pebble\JSON;
use PHPUnit\Framework\TestCase;

final class JSONTest extends TestCase
{
    protected function tearDown(): void
    {
        JSON::$debug = false;
        $_POST = [];
        $_GET = [];
    }

    public function test_response(): void
    {
        $json = JSON::response(['hello world'], 0, 512, false);
        $this->assertEquals('["hello world"]', $json);
    }

    public function testDebugResponseDoesNotLeakRequestValues(): void
    {
        JSON::$debug = true;
        $_POST = ['password' => 'top-secret'];
        $_GET = ['token' => 'secret-token'];

        $json = JSON::response(['ok' => true], 0, 512, false);

        $this->assertStringContainsString('password', $json);
        $this->assertStringContainsString('token', $json);
        $this->assertStringNotContainsString('top-secret', $json);
        $this->assertStringNotContainsString('secret-token', $json);
    }
}
