<?php

declare(strict_types=1);

namespace Pebble\App;

use ErrorException;

use Pebble\Path;
use Pebble\Session;
use Pebble\Headers;
use Pebble\JSON;
use Pebble\Service\ACLRoleService;
use Pebble\Service\ACLService;
use Pebble\Service\AuthService;
use Pebble\Service\ConfigService;
use Pebble\Service\DBService;
use Pebble\Service\LogService;
use Pebble\Service\MigrationService;
use Pebble\Flash;
use Pebble\Template;
use Pebble\HTTP\AcceptLanguage;


/**
 * an app base with helpful methods
 */
class AppBase
{

    /**
     * Add base path to php include path. Then we always know how to include files
     */
    public function addIncludePath(string $path_path)
    {
        set_include_path(get_include_path() . PATH_SEPARATOR . $path_path);
    }

    /**
     * Add base path `../vendor` to include_path
     */
    public function addBaseToIncudePath() {
        $this->addIncludePath(Path::getBasePath());
    }

    /**
     * Add src path `../vendor/src` to include_path
     */
    public function addSrcToIncludePath() {
        $this->addIncludePath(Path::getBasePath() . '/src');
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
     * Start session with configuraton fra Session config
     */
    public function sessionStart()
    {
        Session::setConfigSettings($this->getConfig()->getSection('Session'));
        session_start();
    }

    /**
     * Force SSL
     */
    public function sendSSLHeaders()
    {
        $config = $this->getConfig();
        if ($config->get('App.force_ssl')) {
            Headers::redirectToHttps();
        }
    }


    public function getRequestLanguage()
    {
        $default = $this->getConfig()->get('Language.default');
        $supported = $this->getConfig()->get('Language.enabled');

        return AcceptLanguage::getLanguage($supported, $default);
    }

    /**
     * Set some debug
     */
    public function setDebug()
    {
        if ($this->getConfig()->get('App.env') === 'dev') {
            JSON::$debug = true;
        }
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

    /**
     * @return \Pebble\Migration
     */
    public function getMigration()
    {
        $migrate = new MigrationService();
        return $migrate->getMigration();
    }

    /**
     * @return \Pebble\Flash
     */
    public function getFlash() {
        $flash = new Flash();
        return $flash;
    }

    /**
     * @return \Pebble\Template
     */
    public function getTemplate() {
        $template = new Template();
        return $template;
    }

    /**
     * @return \Pebble\JSON
     */
    public function getJSON() {
        $json = new JSON();
        return $json;
    }
}
