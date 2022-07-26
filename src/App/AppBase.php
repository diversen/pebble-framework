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
    public function addIncludePath(string $path_path): void
    {
        set_include_path(get_include_path() . PATH_SEPARATOR . $path_path);
    }

    /**
     * Add base path `../vendor` to include_path
     */
    public function addBaseToIncudePath(): void
    {
        $this->addIncludePath(Path::getBasePath());
    }

    /**
     * Add src path `../vendor/src` to include_path
     */
    public function addSrcToIncludePath(): void
    {
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
    public function sessionStart(): void
    {
        Session::setConfigSettings($this->getConfig()->getSection('Session'));
        session_start();
    }

    /**
     * Force SSL
     */
    public function sendSSLHeaders(): void
    {
        $config = $this->getConfig();
        if ($config->get('App.force_ssl')) {
            Headers::redirectToHttps();
            exit();
        }
    }


    public function getRequestLanguage(): string
    {
        $default = $this->getConfig()->get('Language.default');
        $supported = $this->getConfig()->get('Language.enabled');

        return AcceptLanguage::getLanguage($supported, $default);
    }

    public function setTemplateOverridePath(string $path): void
    {
        Template::setPath(Path::getBasePath() . "/src/$path");
    }

    /**
     * Set some debug
     */
    public function setDebug(): void
    {
        if ($this->getConfig()->get('App.env') === 'dev') {
            JSON::$debug = true;
        }
    }

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
        $flash = new Flash();
        return $flash;
    }

    public function getTemplate(): \Pebble\Template
    {
        $template = new Template();
        return $template;
    }

    /**
     * @return \Pebble\JSON
     */
    public function getJSON(): \Pebble\JSON
    {
        $json = new JSON();
        return $json;
    }
}
