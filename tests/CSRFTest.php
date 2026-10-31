<?php

declare(strict_types=1);

use Pebble\CSRF;
use PHPUnit\Framework\TestCase;

final class CSRFTest extends TestCase
{
    protected function tearDown(): void
    {
        $_POST = [];
        unset($_SESSION['csrf_token']);
    }

    public function test_getToken(): void
    {
        $token = (new CSRF())->getToken();
        $this->assertEquals($token, $_SESSION['csrf_token']);
    }

    public function test_validateToken(): void
    {
        $csrf = new CSRF();
        $token = $csrf->getToken();
        $_POST['csrf_token'] = $token;
        $this->assertEquals(true, $csrf->validateToken());
    }

    public function testSetCSRFTokenReusesSessionToken(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/account/signin';

        $first = new CSRF();
        $first->setCSRFToken();
        $token = $first->getCSRFToken();

        $_SERVER['REQUEST_URI'] = '/account/signup';
        $second = new CSRF();
        $second->setCSRFToken();

        $this->assertSame($token, $second->getCSRFToken());
        $this->assertSame($token, $_SESSION['csrf_token']);
    }
}
