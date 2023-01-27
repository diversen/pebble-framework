<?php

use Pebble\Trait\MainUtils;
use PHPUnit\Framework\TestCase;
use function Safe\get_include_path;

final class MainUtilsTest extends TestCase
{
    public function test_AppBase(): void
    {

        $main_utls = new class {
            use MainUtils;
        };
        $include_path_original = get_include_path();
        // $app_base = new AppBase();
        $main_utls->addIncludePath('/tmp');

        $include_path = get_include_path();
        $this->assertStringContainsString('/tmp', $include_path);
        set_include_path($include_path_original);

        // This will only work if the repo is checked out as pebble-framework
        $main_utls->addBaseToIncudePath();
        $include_path = get_include_path();
        $this->assertStringContainsString('pebble-framework', $include_path);
        set_include_path($include_path_original);

        $res = $main_utls->setErrorHandler();
        if ($res) {
            // Old error handler PHPUnit\Util\ErrorHandler
            $this->assertIsCallable($res);
        }

        // Restore back to PHPUnit\Util\ErrorHandler
        restore_error_handler();
    }
}
