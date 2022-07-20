<?php

declare(strict_types=1);

namespace Pebble\Service;

use Pebble\ACL;
use Pebble\Service\Container;
use Pebble\Service\ConfigService;
use Pebble\Service\DBService;

class ACLService extends Container
{
    /**
     * @return \Pebble\ACL
     */
    public function getACL()
    {
        if (!$this->has('acl')) {
            $auth_cookie_settings = (new ConfigService())->getConfig()->getSection('Auth');
            $db = (new DBService())->getDB();

            $acl = new ACL($db, $auth_cookie_settings);
            $this->set('acl', $acl);
        }

        return $this->get('acl');
    }
}
