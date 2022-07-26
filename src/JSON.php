<?php

declare(strict_types=1);

namespace Pebble;

use Exception;

class JSON
{
    public static bool $debug = false;

    /**
     * json_encode wrapper which just add content-type header
     * @param mixed $value
     * @return mixed
     */
    public static function response($value, int $flags = 0, int $depth= 512, bool $send_header = true)
    {
        if ($send_header) {
            header('Content-Type: application/json; charset=utf-8');
        }

        if (self::$debug) {
            $value['__POST'] = $_POST;
            $value['__GET'] = $_GET;
        }

        $res = json_encode($value, $flags, $depth);
        if ($res === false) {
            throw new Exception('JSON could not be encoded');
        }

        return $res;
    }

    /**
     * Render JSON
     * @param mixed $value
     */
    public function render($value, int $flags = 0, int $depth= 512, bool $send_header = true): void
    {
        echo self::response($value, $flags, $depth, $send_header);
    }
}
