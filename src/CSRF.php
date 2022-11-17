<?php

declare(strict_types=1);

namespace Pebble;

class CSRF
{

    public static bool $disabled = false;

    /**
     * Sets a token in $_SESSION['csrf_token'] token and return it
     */
    public function getToken(): string
    {
        $token = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $token;
        return $token;
    }

    /**
     * Validates the SESSION token against a value or the default value `$_POST['csrf_token']`
     * It also unsets the POST csrf_token
     */
    public function validateToken(string $token = null): bool
    {

        if (self::$disabled) {
            return true;
        }

        if (!$token) {
            $token = $_POST['csrf_token'] ?? null;
        }

        $session_csrf = $_SESSION['csrf_token'] ?? null;

        if (!$token || !$session_csrf) {
            return false;
        }

        if (hash_equals($token, $session_csrf)) {
            return true;
        }

        return false;
    }
}
