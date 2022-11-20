<?php

declare(strict_types=1);

namespace Pebble;

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
        $res = session_set_cookie_params(
            $session_config["lifetime"],
            $session_config["path"],
            $session_config['domain'],
            $session_config["secure"],
            $session_config["httponly"]
        );
        return $res;
    }
}
