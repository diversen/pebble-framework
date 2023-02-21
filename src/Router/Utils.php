<?php

namespace Pebble\Router;

use InvalidArgumentException;

class Utils
{
    /**
     * Split parts of an URL into an array
     * @return array<string>
     */
    public static function getUrlParts(string $route): array
    {
        // Remove query string
        $route = strtok($route, '?');
        if (!$route) {
            return [];
        }

        $url_parts = explode('/', $route);
        $url_parts_filtered = [];
        foreach ($url_parts as $url_part) {
            if ($url_part) {
                $url_parts_filtered[] = $url_part;
            }
        }
        return $url_parts_filtered;
    }

    /**
     * Get URL segment
     */
    public static function getUrlSegment(string $request_uri, int $num_segment): ?string
    {
        $uri_path = parse_url($request_uri, PHP_URL_PATH);
        if (!$uri_path) {
            throw new InvalidArgumentException("PHP_URL_PATH could not be parsed");
        }
        $uri_segments = explode('/', $uri_path);
        unset($uri_segments[0]);
        $uri_segments = array_values($uri_segments);
        if (isset($uri_segments[$num_segment])) {
            return $uri_segments[$num_segment];
        }
        return null;
    }
}
