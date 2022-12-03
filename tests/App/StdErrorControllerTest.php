<?php

declare(strict_types=1);

use Pebble\App\StdErrorController;
use PHPUnit\Framework\TestCase;
use Pebble\Exception\ForbiddenException;
use Pebble\Exception\NotFoundException;
use Pebble\Exception\TemplateException;

final class StdErrorControllerTest extends TestCase
{
    private function catchOutput(callable $func): string
    {
        ob_start();
        $func();
        $output = ob_get_contents();
        ob_end_clean();

        if ($output === false) {
            throw new Exception('ob_get_contents() failed');
        }

        return $output;
    }

    public function test_render(): void
    {
        $_SERVER['REQUEST_URI'] = '/test';
        $std_error_controller = new StdErrorController();

        try {
            throw new ForbiddenException();
        } catch (ForbiddenException $e) {
            $func = function () use ($std_error_controller, $e) {
                $std_error_controller->render($e);
            };

            $output = $this->catchOutput($func);
            $this->assertStringContainsString('403', $output);
        }

        try {
            throw new TemplateException();
        } catch (TemplateException $e) {
            $func = function () use ($std_error_controller, $e) {
                $std_error_controller->render($e);
            };

            $output = $this->catchOutput($func);
            $this->assertStringContainsString('510', $output);
        }

        try {
            throw new NotFoundException();
        } catch (NotFoundException $e) {
            $func = function () use ($std_error_controller, $e) {
                $std_error_controller->render($e);
            };

            $output = $this->catchOutput($func);
            $this->assertStringContainsString('404', $output);
        }

        try {
            throw new Exception();
        } catch (Throwable $e) {
            $func = function () use ($std_error_controller, $e) {
                $std_error_controller->render($e);
            };

            $output = $this->catchOutput($func);
            $this->assertStringContainsString('500', $output);
        }
    }
}
