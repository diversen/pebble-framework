<?php

declare(strict_types=1);

use Pebble\Session;
use PHPUnit\Framework\TestCase;

final class SessionTest extends TestCase
{
    public function testSessionCookieDefaultsToSameSiteLax(): void
    {
        self::assertTrue(Session::setConfigSettings([]));
        $params = session_get_cookie_params();

        self::assertSame('Lax', $params['samesite']);
        self::assertTrue($params['httponly']);
        self::assertTrue($params['secure']);
    }

    public function testSameSiteNoneRequiresSecureSession(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Session::setConfigSettings(['samesite' => 'None', 'secure' => false]);
    }
}
