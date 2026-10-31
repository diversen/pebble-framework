<?php

declare(strict_types=1);

use Pebble\App\CommonUtils;
use Pebble\CSRF;
use Pebble\JSON;
use PHPUnit\Framework\TestCase;
use function Safe\get_include_path;

final class CommonUtilsTest extends TestCase
{
    protected function tearDown(): void
    {
        JSON::$debug = false;
        CSRF::$disabled = false;
    }

    public function testDebugModeDoesNotDisableCsrf(): void
    {
        JSON::$debug = false;
        CSRF::$disabled = false;

        (new CommonUtils())->setDebug();

        $this->assertTrue(JSON::$debug);
        $this->assertFalse(CSRF::$disabled);
    }

    public function testAppBase(): void
    {
        $main_utils = new CommonUtils();
        $include_path_original = get_include_path();

        $main_utils->addIncludePath('/tmp');

        $include_path = get_include_path();
        $this->assertStringContainsString('/tmp', $include_path);
        set_include_path($include_path_original);

        // This will only work if the repo is checked out as pebble-framework
        $main_utils->addBaseToIncudePath();
        $include_path = get_include_path();
        $this->assertStringContainsString('pebble-framework', $include_path);
        set_include_path($include_path_original);

        $res = $main_utils->setErrorHandler();
        if ($res) {
            // Old error handler PHPUnit\Util\ErrorHandler
            $this->assertIsCallable($res);
        }

        // Restore back to PHPUnit\Util\ErrorHandler
        restore_error_handler();
    }
}
