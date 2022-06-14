<?php

declare(strict_types=1);

namespace Pebble\Pebble;

use Exception;
use Pebble\ExceptionTrace;

/**
 * Standard error controller
 */
class StdErrorController {

    public function error(Exception $exception) {

        $this->sendHeader($exception->getCode());
        echo "<h3>" . $exception->getCode() . ' ' . $exception->getMessage() . "</h3>";
        echo "<pre>" . ExceptionTrace::get($exception) . "</pre>";
    }

    public function sendHeader($code) {
        if (is_numeric($code)) {
            http_response_code($code);
        }
    }
}