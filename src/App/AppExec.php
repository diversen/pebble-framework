<?php

declare(strict_types=1);

namespace Pebble\App;

use Pebble\Exception\ForbiddenException;
use Pebble\Exception\NotFoundException;
use Pebble\Exception\TemplateException;
use Pebble\App\StdErrorController;

use Throwable;
use Exception;
use InvalidArgumentException;

/**
 * Runs an application. If any exception is thrown it will be caught here
 * and the error controller will handle it.
 */
class AppExec
{
    /**
     * @var object
     */
    private $app = null;

    /**
     * @var object
     */
    private $errorController = null;

    public function setErrorController(string $class_name): void
    {
        if (!class_exists($class_name)) {
            throw new InvalidArgumentException("Class $class_name not found");
        }

        $this->errorController = new $class_name();
    }

    /**
     * Set App
     */
    public function setApp(string $class_name): void
    {
        if (!class_exists($class_name)) {
            throw new InvalidArgumentException("Class $class_name not found");
        }
        $this->app = new $class_name();
    }

    /**
     * Run app
     */
    public function run(): void
    {
        if (!is_object($this->app)) {
            throw new Exception('No app added to PebbleExec');
        }

        if (!is_object($this->errorController)) {
            $this->errorController = new StdErrorController();
        }

        if (!method_exists($this->app, 'run')) {
            throw new Exception('App does not have a run method');
        }

        if (!method_exists($this->errorController, 'render')) {
            throw new Exception('App does not have a run method');
        }

        try {
            $this->app->run();
        } catch (TemplateException $e) {
            // 510
            $this->errorController->render($e);
        } catch (NotFoundException $e) {
            // 404
            $this->errorController->render($e);
        } catch (ForbiddenException $e) {
            // 403
            $this->errorController->render($e);
        } catch (Throwable $e) {
            // 500 (Any other exception)
            // This should catch anything else
            $this->errorController->render($e);
        }
    }
}
