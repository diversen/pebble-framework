<?php

declare(strict_types=1);

namespace Pebble\Exception;

use Exception;

class TemplateException extends Exception
{
    public function __construct(string $message = '', int $code = 510, Exception $previous = null)
    {
        http_response_code(510);
        parent::__construct($message, $code, $previous);
    }
}
