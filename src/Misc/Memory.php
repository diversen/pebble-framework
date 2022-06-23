<?php

declare(strict_types=1);

class Memory
{
    /**
     * Get peak memory usage in KB
     */
    public static function getPeak()
    {
        $mem_peak = memory_get_peak_usage();
        return round($mem_peak / 1024);
    }
}
