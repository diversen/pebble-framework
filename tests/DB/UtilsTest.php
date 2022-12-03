<?php

use Pebble\DB\Utils;
use PHPUnit\Framework\TestCase;

final class UtilsTest extends TestCase
{
    public function test_parsePDOString(): void
    {
        $db_utils = new Utils();
        $pdo_str = 'mysql:host=localhost;dbname=test;port=3306';
        $ary = $db_utils->parsePDOString($pdo_str);

        $expect = array(
            'database' => 'mysql',
            'host' => 'localhost',
            'dbname' => 'test',
            'port' => '3306',
        );

        $this->assertSame($expect, $ary);
    }
}
