<?php

declare(strict_types=1);

namespace Pebble;

use ErrorException;

use Pebble\Path;
use Pebble\Service\ACLRoleService;
use Pebble\Service\ACLService;
use Pebble\Service\AuthService;
use Pebble\Service\ConfigService;
use Pebble\Service\DBService;
use Pebble\Service\LogService;
use Pebble\Service\MigrationService;

class PebbleApp
{
    /**
     * The base path of your app should always be one dir above 'vendor/' dir
     */
    public function __construct()
    {
        $this->setIncludePath();
        $this->setErrorHandler();
    }


    /**
     * Add base path to php include path. Then we always know how to include files
     */
    public function setIncludePath()
    {
        $base_path = Path::getBasePath();
        set_include_path(get_include_path() . PATH_SEPARATOR . $base_path);
    }

    /**
     * Set error handler so that any error is an ErrorException
     */
    public function setErrorHandler(): void
    {
        // Throw on all kind of errors and notices
        set_error_handler(function ($errno, $errstr, $errfile, $errline) {
            throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
        });
    }

    /**
     * @return \Pebble\Config
     */
    public function getConfig()
    {
        $config = new ConfigService();
        return $config->getConfig();
    }

    /**
     * @return \Pebble\DB
     */
    public function getDB()
    {
        $config = new DBService();
        return $config->getDB();
    }

    /**
     * @return \Pebble\Auth
     */
    public function getAuth()
    {
        $auth = new AuthService();
        return $auth->getAuth();
    }

    /**
     * @return \Pebble\ACL
     */
    public function getACL()
    {
        $acl = new ACLService();
        return $acl->getACL();
    }

    /**
     * @return \Pebble\ACLRole
     */
    public function getACLRole()
    {
        $acl_role = new ACLRoleService();
        return $acl_role->getACLRole();
    }

    /**
     * @return \Monolog\Logger
     */
    public function getLog()
    {
        $log = new LogService();
        return $log->getLog();
    }

    public function getMigration()
    {
        $migrate = new MigrationService();
        return $migrate->getMigration();
    }
}
