<?php

declare(strict_types=1);

namespace Pebble\App;

use Pebble\Exception\ForbiddenException;
use Pebble\Exception\NotFoundException;
use Pebble\Exception\TemplateException;
use Pebble\Service\LogService;
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
            // 510
            $this->errorController->render($e);
        } catch (NotFoundException $e) {
            // 404
            $this->errorController->render($e);
        } catch (ForbiddenException $e) {
            // 403
            $this->errorController->render($e);
        } catch (Throwable $e) {
            // Any other number
            // This should catch anything else
            $this->errorController->render($e);
        }
    }
}
