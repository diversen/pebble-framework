<?php

declare(strict_types=1);

namespace Pebble;

class CSRF
{
    /**
     * Sets a SESSION token and returns it
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
    public function validateToken(string $post_csrf = null): bool
    {
        $post_csrf = $_POST['csrf_token'] ?? null;
        $session_csrf = $_SESSION['csrf_token'] ?? null;

        if (!$post_csrf || !$session_csrf) {
            return false;
        }

        if (hash_equals($post_csrf, $session_csrf)) {
            return true;
        }

        return false;
    }
}
