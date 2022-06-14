<?php

declare(strict_types=1);

namespace Pebble\Exception;

use Exception;

class NotFoundException extends Exception
{
    // Redefine the exception so message isn't optional
    public function __construct($message = '', $code = 404, Exception $previous = null)
    {

        // make sure everything is assigned properly
        parent::__construct($message, $code, $previous);
    }

    // custom string representation of object
    public function __toString()
    {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }

    public function customFunction()
    {
        echo "A custom function for this type of exception\n";
    }
}
