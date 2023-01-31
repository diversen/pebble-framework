<?php

declare(strict_types=1);

namespace Pebble\Misc;

class Memory
{
    /**
     * Get peak memory usage in KB
     */
    public static function getPeak(bool $real_usage = false): float
    {
        $mem_peak = memory_get_peak_usage($real_usage);
        return round($mem_peak / 1024);
    }
}
