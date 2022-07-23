<?php

declare(strict_types=1);

use Pebble\Special;
use PHPUnit\Framework\TestCase;

final class SpecialTest extends TestCase
{
    public function test_encodeStr(): void
    {

        // Ints to strings
        $res = Special::encodeStr(9.723458);
        $this->assertEquals("9.723458", $res);

        // Leave booleans
        $res = Special::encodeStr(true);
        $this->assertEquals(true, $res);

        // Ints to strings
        $res = Special::encodeStr(110);
        $this->assertEquals("110", $res);

        // Leave objects
        $obj = new stdClass();
        $res = Special::encodeStr($obj);
        $this->assertInstanceOf(stdClass::class, $obj);

        $res = Special::encodeStr('<p>Test</p>');
        $this->assertEquals('&lt;p&gt;Test&lt;/p&gt;', $res);
    }

    public function test_encodeAry(): void
    {
        $ary = [
            'number' => 9.723458,
            'bool' => false,
            'ary' => ['inner_key' => '<p>test</p>']
        ];

        $res = Special::encodeAry($ary);

        $this->assertEquals("9.723458", $res['number']);
        $this->assertEquals(false, $res['bool']);
        $this->assertEquals('&lt;p&gt;test&lt;/p&gt;', $res['ary']['inner_key']);
    }

    public function test_decodeStr(): void
    {
        $res = Special::decodeStr('&lt;p&gt;Test&lt;/p&gt;');
        $this->assertEquals('<p>Test</p>', $res);
    }
}
