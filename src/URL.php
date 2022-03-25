<?php

declare(strict_types=1);

namespace Pebble;

/**
 * Class used for getting path parts of URL From $_SERVER['REQUEST_URI']
 */
class URL
{
    /**
     * Get a 'link' with current URL attached as query param named 'return_to'
     */
    public static function returnTo(string $link, $return_to = null): string
    {
        if (!$return_to) {
            $return_to = $_SERVER['REQUEST_URI'];
        }
        $url = $link . '?return_to=' . urlencode($return_to);
        return $url;
    }

    /**
     * Get a variable from $_GET. If the query part is not set thenthe method returns null
     */
    public static function getQueryPart(string $str)
    {
        if (isset($_GET[$str])) {
            return $_GET[$str];
        }
    }

    /**
     * Get a elem from url path: /some/path
     */
    public static function getUrlPath(int $num_elem): string
    {
        $route = $_SERVER['REQUEST_URI'];

        $route = strtok($route, '?');
        $url_parts = explode('/', $route);
        $url_parts_filtered = [];
        foreach ($url_parts as $url_part) {
            if ($url_part) {
                $url_parts_filtered[] = $url_part;
            }
        }

        $part = $url_parts_filtered[$num_elem] ?? null;
        return $part;
    }
}
