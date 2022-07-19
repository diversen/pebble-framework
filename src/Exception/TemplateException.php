<?php

declare(strict_types=1);

namespace Pebble\Exception;

use Exception;

class TemplateException extends Exception
{
    public function __construct($message = '', $code = 510, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
