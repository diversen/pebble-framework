<?php declare (strict_types = 1);

namespace Pebble;

use Exception;

class JSON {

    /**
     * json_encode wrapper which just add content-type header
     */
    public static function response(array $value, int $flags = 0, int $depth= 512, $send_header = true ) {
        
        if ($send_header) {
            header('Content-Type: application/json');
        }

        $res = json_encode($value, $flags, $depth);
        if ($res === false){
            throw new Exception('JSON could not be encoded');
        }

        return $res;
    }

    public static $debug = false;

    /**
     * json_encode wrapper which add content-type header
     * If self::$debug is 'true' then the response also adds POST and GET vars
     */
    public static function responseAddRequest(array $value, int $flags = 0, int $depth= 512, $send_header = true ) {

        if (self::$debug) {
            $value['__POST'] = $_POST;
            $value['__GET'] = $_GET;
        }

        return self::response($value, $flags, $depth, $send_header);
        
    }
}