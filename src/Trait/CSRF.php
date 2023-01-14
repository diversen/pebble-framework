<?php

declare(strict_types=1);

namespace Pebble\Trait;

use Pebble\CSRF as CSRFToken;

/**
 * A trait for CSRF
 *
 * You may want to add this to a AppMain class in order to auto-generate CSRF tokens
 * and use the getCSRFFormField() method to add a CSRF token to a form
 *
 * Example:
 *
 * $this->setCSRFToken(verbs:['GET'], exclude_paths: ['/account/captcha']);
 *
 * Then in you form you may add: <?=AppMain::getCSRFFormField()?>
 */
trait CSRF
{
    private static $csrf_token;

    /**
     * Set CSRF token. Default is to set token on GET request
     * @param array $verbs
     * @param array $exclude_paths
     */
    private function setCSRFToken(array $verbs = ['GET'], array $exclude_paths = []): void
    {
        $request_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        if (in_array($_SERVER['REQUEST_METHOD'], $verbs) && !in_array($request_path, $exclude_paths)) {
            self::$csrf_token = (new CSRFToken())->getToken();
        }
    }

    private static function getCSRFToken(): string
    {
        return self::$csrf_token;
    }

    /**
     * Get CSRF form field
     * @return string
     */
    public static function getCSRFFormField(): string
    {
        $csrf_token = self::$csrf_token;
        return "<input type='hidden' name='csrf_token' value='$csrf_token'>";
    }
}
