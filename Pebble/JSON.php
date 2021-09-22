<?php declare (strict_types = 1);

namespace Pebble;

use Exception;
use Pebble\Config;

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

    /**
     * json_encode wrapper which add content-type header
     * If the App.env is 'dev' then the response also adds POST and GET vars
     */
    public static function responseAddRequest(array $value, int $flags = 0, int $depth= 512, $send_header = true ) {

        if (Config::get('App.env') == 'dev') {
            $value['__POST'] = $_POST;
            $value['__GET'] = $_GET;
        }

        return self::response($value, $flags, $depth, $send_header);
        
    }
}