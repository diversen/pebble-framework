<?php

declare(strict_types=1);

namespace Pebble\Service;

use Pebble\Auth;
use Pebble\Service\Container;
use Pebble\Service\DBService;
use Pebble\Service\ConfigService;

class AuthService extends Container
{
    /**
     * @return \Pebble\Auth
     */
    public function getAuth()
    {
        if (!$this->has('auth')) {
            $auth_cookie_settings = (new ConfigService())->getConfig()->getSection('Auth');
            $db = (new DBService())->getDB();

            $auth = new Auth($db, $auth_cookie_settings);
            $this->set('auth', $auth);
        }

        return $this->get('auth');
    }
}
