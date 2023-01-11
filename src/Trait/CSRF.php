<?php

declare(strict_types=1);

namespace Pebble\Trait;

use Pebble\CSRF as CSRFToken;

/**
 * A trait for CSRF
 */
trait CSRF
{

    private static $csrf_token;

    private function setCSRFToken()
    {        
        if (!self::$csrf_token) {
            self::$csrf_token = (new CSRFToken())->getToken();
        }
    }

    private static function getCSRFToken()
    {
        return self::$csrf_token;
    }

    public static function getCSRFFormField()
    {
        $csrf_token = self::$csrf_token;
        return "<input type='hidden' name='csrf_token' value='$csrf_token'>";
    }
}

