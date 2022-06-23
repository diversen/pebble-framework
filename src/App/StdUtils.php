<?php

declare(strict_types=1);

namespace Pebble\App;

use Pebble\App\AppBase;

class StdUtils
{
    /**
     * @var \Pebble\Auth
     */
    protected $auth;

    /**
     * @var \Pebble\Config
     */
    protected $config;

    /**
     * @var \Pebble\DB
     */
    protected $db;

    /**
     * @var \Pebble\Log
     */
    protected $log;

    /**
     * @var \Pebble\ACL
     */
    protected $acl;

    /**
     * @var \Pebble\ACLRole
     */
    protected $acl_role;

    /**
     * @var \Pebble\Flash
     */
    protected $flash;

    /**
     * @var \Pebble\Template
     */
    protected $template;

    /**
     * @var \Pebble\JSON
     */
    protected $json;

    public function __contruct()
    {
        $this->app_base = new AppBase();
        $this->auth = $this->app_base->getAuth();
        $this->log = $this->app_base->getLog();
        $this->db = $this->app_base->getDB();
        $this->config = $this->app_base->getConfig();
        $this->acl = $this->app_base->getACL();
        $this->acl_role = $this->app_base->getACL();
        $this->flash = $this->app_base->getFlash();
        $this->template = $this->app_base->getTemplate();
        $this->json = $this->app_base->getJSON();
    }
}
