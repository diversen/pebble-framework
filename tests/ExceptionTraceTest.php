<?php

use Pebble\ExceptionTrace;
use PHPUnit\Framework\TestCase;

final class ExceptionTraceTest extends TestCase
{
    public function test_get_from_exception(): void
    {
        try {
            throw new Exception('An error');
        } catch (Throwable $e) {
            $trace = ExceptionTrace::get($e);
            $this->assertStringContainsString('Message: An error', $trace);
            $this->assertStringContainsString('tests/ExceptionTraceTest.php', $trace);
            $this->assertStringContainsString('Trace:', $trace);
        }
    }

    public function test_get_from_error(): void
    {
        try {
            throw new Error('An error');
        } catch (Throwable $e) {
            $trace = ExceptionTrace::get($e);
            $this->assertStringContainsString('Message: An error', $trace);
            $this->assertStringContainsString('tests/ExceptionTraceTest.php', $trace);
            $this->assertStringContainsString('Trace:', $trace);
        }
    }
}
