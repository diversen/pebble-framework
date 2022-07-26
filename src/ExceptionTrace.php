<?php

declare(strict_types=1);

namespace Pebble;

use Throwable;

class ExceptionTrace
{
    /**
     * Get information from an exception as a string
     */
    public static function get(Throwable $e): string
    {

        // Log error to file
        $exception_str =
        'Message: ' . $e->getMessage() . "\n" .
        'In: ' . $e->getFile() . ' (' . $e->getLine() . ')' . "\n" .
        'Trace:' ." \n" . $e->getTraceAsString() . "\n";

        return $exception_str;
    }
}
