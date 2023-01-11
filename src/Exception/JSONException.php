<?php

declare(strict_types=1);

namespace Pebble\Exception;

use Exception;

class JSONException extends Exception
{
    public function __construct(string $message = '', int $code = 403, Exception $previous = null)
    {
        http_response_code($code);
        parent::__construct($message, $code, $previous);
    }
}
