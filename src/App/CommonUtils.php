<?php

declare(strict_types=1);

namespace Pebble\App;

use ErrorException;

use Pebble\Path;
use Pebble\Session;
use Pebble\Headers;
use Pebble\JSON;
use Pebble\CSRF;
use Pebble\Template;
use Pebble\HTTP\AcceptLanguage;
use Pebble\Service\ConfigService;

use function Safe\set_include_path;
use function Safe\get_include_path;

/**
 * Some utilities which 
 * should / may be extended by the main app class
 * 
 * It provides some common  utilities for an app
 */
class CommonUtils
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
    public function setErrorHandler(): ?callable
    {
        // Throw on all kind of errors and notices
        return set_error_handler(function ($errno, $errstr, $errfile, $errline) {
            throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
        });
    }

    /**
     * Start session with configuraton fra Session config
     */
    public function sessionStart(): bool
    {
        $session_config = (new ConfigService())->getConfig()->getSection('Session');
        Session::setConfigSettings($session_config);
        return session_start();
    }

    /**
     * Force SSL
     */
    public function sendSSLHeaders(): void
    {
        $force_ssl = (new ConfigService())->getConfig()->get('App.force_ssl');
        if ($force_ssl) {
            Headers::redirectToHttps();
        }
    }


    public function getRequestLanguage(): ?string
    {
        $config = (new ConfigService())->getConfig();
        $default = $config->get('Language.default');
        $supported = $config->get('Language.enabled');

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
        $config = (new ConfigService())->getConfig();
        if ($config->get('App.env') === 'dev') {
            JSON::$debug = true;
            
            // Easier to run tests
            CSRF::$disabled = true;
        }
    }
}
