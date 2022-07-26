<?php

declare(strict_types=1);

namespace Pebble;

class Headers
{   
    /**
     * @return array<string>
     */
    public static function getHttpsHeaders()
    {
        $headers[] = 'HTTP/1.1 302 Found';

        $location = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        $headers[] = 'Location: ' . $location;

        return $headers;
    }

    public static function redirectToHttps(): void
    {
        if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === "off") {
            foreach (self::getHttpsHeaders() as $header) {
                header($header);
            }
        }
    }
}
