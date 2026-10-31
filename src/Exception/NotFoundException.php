<?php

declare(strict_types=1);

namespace Pebble\Exception;

use Exception;
use Throwable;

class NotFoundException extends Exception
{
    public function __construct(string $message = '', int $code = 404, ?Throwable $previous = null)
    {
        http_response_code(404);
        parent::__construct($message, $code, $previous);
    }
}
