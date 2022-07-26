<?php

declare(strict_types=1);

namespace Pebble\App;

use Pebble\Exception\ForbiddenException;
use Pebble\Exception\NotFoundException;
use Pebble\Exception\TemplateException;
use Pebble\App\StdErrorController;

use Throwable;
use Exception;

/**
 * Runs an application. If any exception is thrown it will be caught here
 * and the error controller will handle it.
 */
class AppExec
{

    /**
     * @var object
     */
    private object $app;

    /**
     * @var object
     */
    private $errorController = null;

    public function setErrorController(string $class_name): void
    {
        $this->errorController = new $class_name();
    }

    /**
     * Set App
     */
    public function setApp(string $class_name): void
    {
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
