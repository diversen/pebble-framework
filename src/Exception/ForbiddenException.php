<?php

declare(strict_types=1);

namespace Pebble\Exception;

use Exception;

class ForbiddenException extends Exception
{
    // Redefine the exception so message isn't optional
    public function __construct(string $message = '', int $code = 403, Exception $previous = null)
    {
        http_response_code(403);
        parent::__construct($message, $code, $previous);
    }
}
