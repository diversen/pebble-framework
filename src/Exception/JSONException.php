<?php

declare(strict_types=1);

namespace Pebble\Exception;

use Exception;
use Throwable;

class JSONException extends Exception
{
    public function __construct(string $message = '', int $code = 403, ?Throwable $previous = null)
    {
        http_response_code($code);
        parent::__construct($message, $code, $previous);
    }
}
