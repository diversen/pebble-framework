<?php

declare(strict_types=1);

namespace Pebble;

use InvalidArgumentException;

class Cookie
{
    /**
     * @var array<mixed> $settings
     */
    private array $settings;

    /**
     * @param array<mixed> $settings
     */
    public function __construct(array $settings)
    {
        $this->settings = $settings;
    }

    /**
     * Set a cookie
     * If time is 0 then the it will be a session cookie
     */
    public function setCookie(string $key, string $value, int $time = 0): bool
    {
        $options = $this->getCookieOptions($time);

        // Little hack for unit testing
        if ($this->isCli()) {
            $_COOKIE[$key] = $value;
            return true;
        }

        return setcookie($key, $value, $options);
    }

    public function clearCookie(string $key): bool
    {
        if ($this->isCli()) {
            unset($_COOKIE[$key]);
            return true;
        }

        $options = $this->getCookieOptions(0);
        $options['expires'] = time() - 3600;
        return setcookie($key, '', $options);
    }

    /**
     * @return array{expires: int, path: string, domain: string, secure: bool, httponly: bool, samesite: string}
     */
    protected function getCookieOptions(int $time): array
    {
        if ($time < 0) {
            throw new InvalidArgumentException('Cookie lifetime cannot be negative');
        }

        $same_site = $this->settings['cookie_samesite'] ?? 'Lax';
        if (!in_array($same_site, ['Lax', 'Strict', 'None'], true)) {
            throw new InvalidArgumentException('cookie_samesite must be Lax, Strict, or None');
        }

        $secure = (bool)($this->settings['cookie_secure'] ?? true);
        if ($same_site === 'None' && !$secure) {
            throw new InvalidArgumentException('SameSite=None cookies must also be Secure');
        }

        return [
            'expires' => $time === 0 ? 0 : time() + $time,
            'path' => $this->settings['cookie_path'] ?? '/',
            'domain' => $this->settings['cookie_domain'] ?? '',
            'secure' => $secure,
            'httponly' => (bool)($this->settings['cookie_http'] ?? true),
            'samesite' => $same_site,
        ];
    }

    private function isCli(): bool
    {
        if (php_sapi_name() === 'cli') {
            return true;
        }
        return false;
    }
}
