<?php

declare(strict_types=1);

namespace Pebble\Service;

use Pebble\Service\Container;
use Pebble\Service\ConfigService;
use Pebble\Service\DBService;
use Pebble\ACLRole;

class ACLRoleService extends Container
{
    /**
     * @return \Pebble\ACLRole
     */
    public function getACLRole()
    {
        if ($this->get('acl_role')) {
            $auth_cookie_settings = (new ConfigService())->getConfig()->getSection('Auth');
            $db = (new DBService())->getDB();

            $acl_role = new ACLRole($db, $auth_cookie_settings);
            $this->set('acl_role', $acl_role);
        }

        return $this->get('acl_role');
    }
}
