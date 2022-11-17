<?php

declare(strict_types=1);

namespace Pebble;

class Random
{
    /**
     * Generate a truly random string from a specified length given to random_bytes
     * It returns a hexstring that is `$length * 2` in size
     * @param int<1, max> $length
     */
    public static function generateRandomString(int $length): string
    {
        $random = bin2hex(random_bytes($length));
        return $random;
    }
}
