<?php

declare(strict_types=1);

namespace Pebble\App;

use Throwable;
use Pebble\ExceptionTrace;
use Pebble\Service\ConfigService;
use Pebble\Service\LogService;
use Pebble\JSON;
use Pebble\Exception\ForbiddenException;
use Pebble\Exception\NotFoundException;
use Pebble\Exception\TemplateException;
use Pebble\Exception\JSONException;

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

        $class = get_class($e);

        if ($class === NotFoundException::class) {

            $this->log->notice("App.not_found ", ['url' => $_SERVER['REQUEST_URI']]);
            $this->displayError($e);

        } elseif ($class === ForbiddenException::class) {

            $this->log->notice("App.forbidden", ['url' => $_SERVER['REQUEST_URI']]);
            $this->displayError($e);

        } elseif ($class === TemplateException::class) {

            $this->log->error('App.template', ['exception' => ExceptionTrace::get($e)]);
            $this->displayError($e);
            
        } elseif ($class === JSONException::class) {

            $this->log->error('App.json', ['exception' => ExceptionTrace::get($e)]);
            
            $response['message'] = $e->getMessage();
            $response['error'] = true;
            $response['code'] = $error_code;

            $json = new JSON();
            $json->render($response);
            
        } else {
            
            $this->log->notice('App.exception', ['exception' => ExceptionTrace::get($e)]);
            $this->displayError($e);

        }
    }
}
