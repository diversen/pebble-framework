<?php

use Pebble\App\AppBase;
use PHPUnit\Framework\TestCase;
use function Safe\get_include_path;

final class AppBaseTest extends TestCase
{
    public function test_AppBase(): void
    {
        $include_path_original = get_include_path();
        $app_base = new AppBase();
        $app_base->addIncludePath('/tmp');

        $include_path = get_include_path();
        $this->assertStringContainsString('/tmp', $include_path);
        set_include_path($include_path_original);

        // This will only work if the repo is checked out as pebble-framework
        $app_base->addBaseToIncudePath();
        $include_path = get_include_path();
        $this->assertStringContainsString('pebble-framework', $include_path);
        set_include_path($include_path_original);

        $res = $app_base->setErrorHandler();
        if ($res) {
            // Old error handler PHPUnit\Util\ErrorHandler
            $this->assertIsCallable($res);
        }

        // Restore back to PHPUnit\Util\ErrorHandler
        restore_error_handler();
    }
}
