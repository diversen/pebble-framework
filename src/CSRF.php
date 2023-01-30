<?php

declare(strict_types=1);

namespace Pebble;

use Pebble\Exception\JSONException;

class CSRF
{

    private $csrf_token;
    /**
     * @var bool
     */
    public static bool $disabled = false;

    /**
     * @var string
     */
    public string $error_message = 'CSRF token is not valid';

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

    public function setErrorMessage(string $message): void
    {
        $this->error_message = $message;
    }

    public function validateTokenJSON(): void
    {
        if (!$this->validateToken()) {
            throw new JSONException($this->error_message, 403);
        }
    }

    /**
     * Set CSRF token. Default is to set token on GET request
     * @param array $verbs
     * @param array $exclude_paths
     */
    public function setCSRFToken(array $verbs = ['GET'], array $exclude_paths = []): void
    {
        $request_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        if (in_array($_SERVER['REQUEST_METHOD'], $verbs) && !in_array($request_path, $exclude_paths)) {
            $this->csrf_token = $this->getToken();
        }
    }

    public function getCSRFToken(): string
    {
        return $this->csrf_token;
    }

    /**
     * Get CSRF form field
     * @return string
     */
    public function getCSRFFormField(): string
    {
        $csrf_token = $this->csrf_token;
        return "<input type='hidden' name='csrf_token' value='$csrf_token'>";
    }
}
