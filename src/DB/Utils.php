<?php

namespace Pebble\DB;

class Utils
{
    /**
     * @return array<string>
     */
    public static function parsePDOString(string $pdo_str): array
    {
        $ary = [];

        $parsed_url = parse_url($pdo_str);
        $ary['database'] = $parsed_url['scheme'];
        $path_parts = explode(';', $parsed_url['path']);

        foreach ($path_parts as $part) {
            list($key, $value) = explode('=', $part);
            $ary[$key] = $value;
        }

        return $ary;
    }
}
