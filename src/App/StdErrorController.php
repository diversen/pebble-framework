<?php

declare(strict_types=1);

namespace Pebble\App;

use Exception;
use Throwable;
use Pebble\ExceptionTrace;
use Pebble\Service\ConfigService;
use Pebble\Service\LogService;

/**
 * Standard error controller
 */
class StdErrorController
{

    private \Pebble\Config $config;
    private \Monolog\Logger $log;
    private string $env;

    public function __construct()
    {
        $this->log = (new LogService())->getLog();
        $this->config = (new ConfigService())->getConfig();
        $this->env = $this->config->get('App.env');
    }

    private function displayError(Throwable $e): void
    {
        $error_code = $this->getErrorCode($e);
        if ($this->env === 'dev') {
            echo "<h3>" . ' '  . $error_code . ' ' . $e->getMessage() . "</h3>";
            echo "<pre>" . ExceptionTrace::get($e) . "</pre>";
        } else {
            echo '<h3>A sever error happened. The incidence has been logged.</h3>';
        }
    }

    private function getErrorCode(Throwable $e): int
    {
        $error_code = $e->getCode();
        if (!$error_code) {
            $error_code = 500;
        }
        return $error_code;
    }

    public function render(Throwable $e): void
    {
        $error_code = $this->getErrorCode($e);
        http_response_code($error_code);

        if ($error_code === 404) {
            $this->notFoundException($e);
        } elseif ($error_code === 403) {
            $this->forbiddenException($e);
        } elseif ($error_code === 510) {
            $this->templateException($e);
        } else {
            $this->internalException($e);
        }
    }

    private function templateException(Throwable $e): void
    {
        $this->log->error('App.template.exception', ['exception' => ExceptionTrace::get($e)]);
        $this->displayError($e);
    }

    private function notFoundException(Throwable $e): void
    {
        $this->log->notice("App.index.not_found ", ['url' => $_SERVER['REQUEST_URI']]);
        $this->displayError($e);
    }

    private function forbiddenException(Throwable $e): void
    {
        $this->log->notice("App.index.forbidden", ['url' => $_SERVER['REQUEST_URI']]);
        $this->displayError($e);
    }

    private function internalException(Throwable $e): void
    {
        $this->log->notice('App.index.exception', ['exception' => ExceptionTrace::get($e)]);
        $this->displayError($e);
    }
}
