<?php

namespace Pebble\Service;

use Pebble\ACL;
use Pebble\Service\ConfigService;
use Pebble\Service\DBService;

class ACLService
{

    public static $acl;

    /**
     * @return \Pebble\ACL
     */
    public function getACL()
    {
        if (self::$acl) return self::$acl;
        
        $auth_cookie_settings = (new ConfigService())->getConfig()->getSection('Auth');
        $db = (new DBService())->getDB();

        self::$acl = new ACL($db, $auth_cookie_settings);
        return self::$acl;
    }
}
