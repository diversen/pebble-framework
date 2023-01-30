<?php

declare(strict_types=1);

namespace Pebble\App;

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
     * App object
     * @var object
     */
    private $app = null;

    /**
     * Error object
     * @var object
     */
    private $error = null;

    /**
     * @var string
     */
    private string $app_class = '';

    /**
     * @var string
     */
    private ?string $error_class = null;

    public function __construct()
    {
        $this->error_class = StdErrorController::class;
    }

    public function setErrorController(string $error_class): void
    {
        if (!class_exists($error_class)) {
            throw new InvalidArgumentException("Class $error_class not found");
        }

        $this->error_class = $error_class;
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

            // Init error handler
            $error_class = $this->error_class;
            $this->error = new $error_class();


            if (!method_exists($this->error, 'render')) {
                throw new Exception('Error controller does not have a render method');
            }

            // Init app
            $app_class = $this->app_class;
            $this->app = new $app_class();

            if (!method_exists($this->app, 'run')) {
                throw new Exception('App does not have a run method');
            }

            $this->app->run();
        } catch (Throwable $e) {
            /**
             * phpstan complains about the next line
             * but it does not complain about the above line ($this->app->run)
             * Both objects are created dynamically so I'm ignoring it for now.
             * @phpstan-ignore-next-line
             */
            $this->error->render($e);
        }
    }
}
