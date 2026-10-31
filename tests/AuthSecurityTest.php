<?php

declare(strict_types=1);

use Pebble\Auth;
use Pebble\DB;
use PHPUnit\Framework\TestCase;

final class AuthSecurityDB extends DB
{
    /** @var array<mixed> */
    public array $fetchRow = [];

    public string $lastSql = '';

    /** @var array<mixed> */
    public array $lastParams = [];

    public function __construct()
    {
    }

    public function prepareFetch(string $sql, array $params = []): mixed
    {
        $this->lastSql = $sql;
        $this->lastParams = $params;
        return $this->fetchRow;
    }

    public function prepareExecute(string $sql, array $values = []): bool
    {
        $this->lastSql = $sql;
        $this->lastParams = $values;
        return true;
    }
}

final class AuthSecurityTest extends TestCase
{
    protected function tearDown(): void
    {
        unset($_COOKIE['auth']);
    }

    public function testAuthenticationChecksExpiryAndCurrentAccountStatus(): void
    {
        $db = new AuthSecurityDB();
        $db->fetchRow = ['auth_id' => 42, 'expires' => time() + 60];
        $auth = new Auth($db, []);
        $_COOKIE['auth'] = 'token';

        self::assertTrue($auth->isAuthenticated());
        self::assertStringContainsString('INNER JOIN auth', $db->lastSql);
        self::assertStringContainsString('auth_cookie.expires > ?', $db->lastSql);
        self::assertStringContainsString('auth.verified = 1', $db->lastSql);
        self::assertStringContainsString('auth.locked = 0', $db->lastSql);
        self::assertSame(hash('sha256', 'token'), $db->lastParams[0]);
        self::assertEqualsWithDelta(time(), $db->lastParams[1], 1);
    }

    public function testSessionCookieHasBoundedServerSideLifetime(): void
    {
        $db = new AuthSecurityDB();
        $auth = new Auth($db, [
            'cookie_session_seconds' => 600,
            'cookie_samesite' => 'Lax',
        ]);

        $before = time();
        self::assertTrue($auth->setCookie(['id' => 42]));

        self::assertStringContainsString('INSERT INTO auth_cookie', $db->lastSql);
        self::assertSame(64, strlen($db->lastParams[0]));
        self::assertNotSame($_COOKIE['auth'] ?? null, $db->lastParams[0]);
        self::assertSame(42, $db->lastParams[1]);
        self::assertGreaterThanOrEqual($before + 600, $db->lastParams[2]);
        self::assertLessThanOrEqual(time() + 600, $db->lastParams[2]);
    }

    public function testPersistentCookieStoresAbsoluteExpiry(): void
    {
        $db = new AuthSecurityDB();
        $auth = new Auth($db, ['cookie_samesite' => 'Lax']);

        $before = time();
        self::assertTrue($auth->setCookie(['id' => 42], 3600));

        self::assertGreaterThanOrEqual($before + 3600, $db->lastParams[2]);
        self::assertLessThanOrEqual(time() + 3600, $db->lastParams[2]);
    }
}
