<?php

declare(strict_types=1);

namespace Pebble\Pebble;

use Exception;
use Throwable;
use Pebble\ExceptionTrace;
use Pebble\Service\ConfigService;
use Pebble\Service\LogService;

/**
 * Standard error controller
 */
class StdErrorController {
    
    private $config;

    public function __construct() {
        $this->log = (new LogService())->getLog();
        $this->config = (new ConfigService())->getConfig();
        $this->env = $this->config->get('App.env');
    }

    private function displayError($e) {
        $error_code = $e->getCode();
        if (!$error_code) $error_code = 500;
        if ($this->env === 'dev') {
            echo "<h3>" . ' '  . $error_code . ' ' . $e->getMessage() . "</h3>";
            echo "<pre>" . ExceptionTrace::get($e) . "</pre>";
        } else {
            echo '<h3>A sever error happened. The incidence has been logged.</h3>';
        }

    }

    public function templateException(Exception $e) {
        $this->log->error('App.template.exception', ['exception' => ExceptionTrace::get($e)]);
        http_response_code(500);
        $this->displayError($e);
    }

    public function notFoundException(Exception $e) {
        $this->log->notice("App.index.not_found ", ['url' => $_SERVER['REQUEST_URI']]);
        http_response_code(404);
        $this->displayError($e);
    }

    public function forbiddenException(Exception $e) {
        $this->log->notice("App.index.forbidden", ['url' => $_SERVER['REQUEST_URI']]);
        http_response_code(403);
        $this->displayError($e);
    }

    public function internalException(Throwable $e) {
        $this->log->notice('App.index.exception', ['exception' => ExceptionTrace::get($e)]);
        http_response_code(500);
        $this->displayError($e);
    }
}