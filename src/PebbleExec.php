<?php

declare(strict_types=1);

namespace Pebble;

use Pebble\Exception\ForbiddenException;
use Pebble\Exception\NotFoundException;
use Pebble\Exception\TemplateException;
use Pebble\Service\LogService;
use Pebble\Pebble\StdErrorController;

use Throwable;
use Exception;

/**
 * Runs an application. If any exception is thrown it will be caught here
 * and the error controller will handle it.
 */
class PebbleExec
{

    public function __construct()
    {
        $this->log = (new LogService())->getLog();
    }

    /**
     * Set error controller
     */
    private $errorController = null;
    public function setErrorController(string $class_name)
    {
        $this->errorController = new $class_name();
    }

    /**
     * Set App
     */
    public function setApp(string $class_name)
    {
        $this->app = new $class_name();
    }

    /**
     * Run app
     */
    public function run()
    {
        if (!$this->app) {
            throw new Exception('No app added to PebbleExec');
        }

        if (!$this->errorController) {
            $this->errorController = new StdErrorController();
        }

        try {
            $this->app->run();
        } catch (TemplateException $e) {
            $this->errorController->templateException($e);
        } catch (NotFoundException $e) {
            $this->errorController->notFoundException($e);
        } catch (ForbiddenException $e) {
            $this->errorController->forbiddenException($e);
        } catch (Throwable $e) {
            // This will catch almost anything
            $this->errorController->internalException($e);
        }
    }
}
