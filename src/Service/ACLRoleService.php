<?php

namespace Pebble\Service;

use Pebble\Service\ConfigService;
use Pebble\Service\DBService;
use Pebble\ACLRole;

class ACLRoleService
{
    public static $acl_role;

    /**
     * @return \Pebble\ACLRole
     */
    public function getACLRole()
    {
        if (self::$acl_role) {
            return self::$acl_role;
        }

        $auth_cookie_settings = (new ConfigService())->getConfig()->getSection('Auth');
        $db = (new DBService())->getDB();

        self::$acl_role = new ACLRole($db, $auth_cookie_settings);
        return self::$acl_role;
    }
}
