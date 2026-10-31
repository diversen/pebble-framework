<?php

declare(strict_types=1);

namespace Pebble;

use InvalidArgumentException;

/**
 * Session class just sets default parameters for sessions
 */
class Session
{
    /**
     * Set SESSION defaults from Session Configuration
     * @param array<mixed> $session_config
     */
    public static function setConfigSettings(array $session_config): bool
    {
        $same_site = $session_config['samesite'] ?? 'Lax';
        if (!in_array($same_site, ['Lax', 'Strict', 'None'], true)) {
            throw new InvalidArgumentException('Session samesite must be Lax, Strict, or None');
        }

        $secure = (bool)($session_config['secure'] ?? true);
        if ($same_site === 'None' && !$secure) {
            throw new InvalidArgumentException('SameSite=None session cookies must also be Secure');
        }

        $res = session_set_cookie_params([
            'lifetime' => (int)($session_config['lifetime'] ?? 0),
            'path' => $session_config['path'] ?? '/',
            'domain' => $session_config['domain'] ?? '',
            'secure' => $secure,
            'httponly' => (bool)($session_config['httponly'] ?? true),
            'samesite' => $same_site,
        ]);
        return $res;
    }
}
