<?php

declare(strict_types=1);

use Pebble\Cookie;
use PHPUnit\Framework\TestCase;

final class TestableCookie extends Cookie
{
    /** @return array<string, mixed> */
    public function options(int $time = 0): array
    {
        return $this->getCookieOptions($time);
    }
}

final class CookieTest extends TestCase
{
    public function testSecureDefaultsAndSameSiteOption(): void
    {
        $cookie = new TestableCookie([]);
        $options = $cookie->options();

        self::assertSame('/', $options['path']);
        self::assertTrue($options['secure']);
        self::assertTrue($options['httponly']);
        self::assertSame('Lax', $options['samesite']);
        self::assertSame(0, $options['expires']);
    }

    public function testPersistentExpiryIsAbsolute(): void
    {
        $cookie = new TestableCookie([]);
        $before = time();
        $options = $cookie->options(60);

        self::assertGreaterThanOrEqual($before + 60, $options['expires']);
        self::assertLessThanOrEqual(time() + 60, $options['expires']);
    }

    public function testSameSiteNoneRequiresSecureCookie(): void
    {
        $cookie = new TestableCookie([
            'cookie_samesite' => 'None',
            'cookie_secure' => false,
        ]);

        $this->expectException(InvalidArgumentException::class);
        $cookie->options();
    }
}
