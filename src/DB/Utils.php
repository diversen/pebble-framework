<?php

namespace Pebble\DB;

use function Safe\parse_url;
use InvalidArgumentException;

class Utils
{
    /**
     * @return array<string>
     */
    public static function parsePDOString(string $pdo_str): array
    {
        $ary = [];

        $parsed_url = parse_url($pdo_str);
        if (!is_array($parsed_url)) {
            throw new InvalidArgumentException("Invalid PDO string: " . $pdo_str);
        }

        if (isset($parsed_url['scheme'])) {
            $ary['database'] = $parsed_url['scheme'];
        }

        if (isset($parsed_url['path'])) {
            $path_parts = explode(';', $parsed_url['path']);
            foreach ($path_parts as $part) {
                list($key, $value) = explode('=', $part);
                $ary[$key] = $value;
            }
        }

        return $ary;
    }
}
