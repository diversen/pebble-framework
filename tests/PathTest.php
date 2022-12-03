<?php

declare(strict_types=1);

use Pebble\Path;
use PHPUnit\Framework\TestCase;

final class PathTest extends TestCase
{
    public function test_getBasePath(): void
    {
        $path = Path::getBasePath();

        $res = file_exists($path);
        $this->assertEquals(true, $res);

        // Last segment of the path, e.g. /home/user/pebble-framework
        // This will only work if the repo is checked out as pebble-framework
        $last_segment = substr($path, strrpos($path, '/') + 1);
        $this->assertEquals('pebble-framework', $last_segment);
    }
}
