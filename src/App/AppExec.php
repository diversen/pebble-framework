<?php

declare(strict_types=1);

namespace Pebble\App;

use Pebble\Exception\ForbiddenException;
use Pebble\Exception\NotFoundException;
use Pebble\Exception\TemplateException;
use Pebble\Exception\JSONException;
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
    private $error_controller = null;

    /**
     * @var string
     */
    private string $app_class = '';

    /**
     * @var object
     */
    private $error_class = null;

    public function setErrorController(string $class_name): void
    {
        if (!class_exists($class_name)) {
            throw new InvalidArgumentException("Class $class_name not found");
        }

        $this->error_class = $class_name;
    }

    /**
     * Set App
     */
    public function setApp(string $class_name): void
    {
        if (!class_exists($class_name)) {
            throw new InvalidArgumentException("Class $class_name not found");
        }
        $this->app_class = $class_name;
    }

    /**
     * Run app
     */
    public function run(): void
    {
        try {

            // Init error controller.
            $error_class = $this->error_class;
            $this->error_controller = new $error_class();

            if (!is_object($this->error_controller)) {
                $this->error_controller = new StdErrorController();
            }

            if (!method_exists($this->error_controller, 'render')) {
                throw new Exception('Error controller does not have a render method');
            }

            // Init app
            $app_class = $this->app_class;
            $this->app = new $app_class();
    
            if (!method_exists($this->app, 'run')) {
                throw new Exception('App does not have a run method');
            }

            $this->app->run();
            

        } catch (TemplateException $e) {
            // 510
            $this->error_controller->render($e);
        } catch (NotFoundException $e) {
            // 404
            $this->error_controller->render($e);
        } catch (ForbiddenException $e) {
            // 403
            $this->error_controller->render($e);
        } catch (JSONException $e) {
            // 403
            $this->error_controller->render($e);
        } catch (Throwable $e) {
            // 500 (Any other exception)
            // This should catch anything else
            $this->error_controller->render($e);
        }
    }
}
