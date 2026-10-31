<?php

declare(strict_types=1);

namespace Pebble;

use InvalidArgumentException;
use Pebble\Service\ConfigService;

class Headers
{
    /**
     * @return array<string>
     */
    public static function getHttpsHeaders(?string $canonical_host = null)
    {
        $headers[] = 'HTTP/1.1 302 Found';

        if ($canonical_host === null) {
            $server_url = (new ConfigService())->getConfig()->get('App.server_url');
            $canonical_host = parse_url((string)$server_url, PHP_URL_HOST);
            $port = parse_url((string)$server_url, PHP_URL_PORT);
            if ($canonical_host && $port) {
                $canonical_host .= ':' . $port;
            }
        }

        if (!$canonical_host || !preg_match('/^[A-Za-z0-9.-]+(?::\d{1,5})?$/', $canonical_host)) {
            throw new InvalidArgumentException('A valid canonical HTTPS host is required');
        }

        $request_uri = $_SERVER['REQUEST_URI'] ?? '/';
        if (!str_starts_with($request_uri, '/') || preg_match('/[\r\n]/', $request_uri)) {
            throw new InvalidArgumentException('Invalid request URI');
        }

        $location = 'https://' . $canonical_host . $request_uri;
        $headers[] = 'Location: ' . $location;

        return $headers;
    }

    public static function redirectToHttps(?string $canonical_host = null): void
    {
        if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === "off") {
            foreach (self::getHttpsHeaders($canonical_host) as $header) {
                header($header);
            }
        }
    }
}
