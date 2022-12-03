<?php

declare(strict_types=1);

namespace Pebble\App;

use Pebble\Service\Container;
use Pebble\Service\ACLRoleService;
use Pebble\Service\ACLService;
use Pebble\Service\AuthService;
use Pebble\Service\ConfigService;
use Pebble\Service\DBService;
use Pebble\Service\LogService;
use Pebble\Service\MigrationService;
use Pebble\Flash;
use Pebble\Template;
use Pebble\JSON;

/**
 * A Utils class that returns convenient services which are singletons
 */
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
     * @var \Monolog\Logger
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

    public function getConfig(): \Pebble\Config
    {
        $config = new ConfigService();
        return $config->getConfig();
    }

    public function getDB(): \Pebble\DB
    {
        $config = new DBService();
        return $config->getDB();
    }

    public function getAuth(): \Pebble\Auth
    {
        $auth = new AuthService();
        return $auth->getAuth();
    }

    public function getACL(): \Pebble\ACL
    {
        $acl = new ACLService();
        return $acl->getACL();
    }

    public function getACLRole(): \Pebble\ACLRole
    {
        $acl_role = new ACLRoleService();
        return $acl_role->getACLRole();
    }

    public function getLog(): \Monolog\Logger
    {
        $log = new LogService();
        return $log->getLog();
    }

    public function getMigration(): \Pebble\Migration
    {
        $migrate = new MigrationService();
        return $migrate->getMigration();
    }

    public function getFlash(): \Pebble\Flash
    {
        $container = new Container();
        if (!$container->has('flash')) {
            $container->set('flash', new Flash());
        }
        return $container->get('flash');
    }

    public function getTemplate(): \Pebble\Template
    {
        $container = new Container();
        if (!$container->has('template')) {
            $container->set('template', new Template());
        }
        return $container->get('template');
    }

    public function getJSON(): \Pebble\JSON
    {
        $container = new Container();
        if (!$container->has('json')) {
            $container->set('json', new JSON());
        }
        return $container->get('json');
    }

    /**
     * Properties can only be used in sub classes
     * Convenient if you extend e.g. a controller with the StdUtils class
     * then you can just use, e.g. $this->auth
     */
    public function __construct()
    {
        $this->auth = $this->getAuth();
        $this->log = $this->getLog();
        $this->db = $this->getDB();
        $this->config = $this->getConfig();
        $this->acl = $this->getACL();
        $this->acl_role = $this->getACLRole();
        $this->flash = $this->getFlash();
        $this->template = $this->getTemplate();
        $this->json = $this->getJSON();
    }
}
