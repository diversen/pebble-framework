<?php

namespace Pebble\Service;

use Pebble\DB;
use Pebble\Auth;
use Pebble\Service\DBService;
use Pebble\Service\ConfigService;

class AuthService
{
    /**
     * @var Pebble\Auth
     */
    public static $auth;

    /**
     * @return \Pebble\Auth
     */

    public function getAuth()
    {
        if (self::$auth) {
            return self::$auth;
        }

        $auth_cookie_settings = (new ConfigService())->getConfig()->getSection('Auth');
        $db = (new DBService())->getDB();

        self::$auth = new Auth($db, $auth_cookie_settings);
        return self::$auth;
    }
}
