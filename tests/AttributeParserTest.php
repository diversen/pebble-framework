<?php

use Pebble\Test;
use PHPUnit\Framework\TestCase;
use Pebble\AttributeParser;

final class AttributeParserTest extends TestCase
{
    public function test_parseAttributes(): void
    {
        $attributes = AttributeParser::parseAttributes(Test::class);
        $attribute = $attributes[3];

        $arguments = $attribute['arguments'];

        $this->assertEquals($arguments['path'], '/attributes/test/:id');
        $this->assertEquals($arguments['verbs'], ['GET', 'POST']);
        $this->assertEquals($arguments['cast'], ['id' => 'int']);
    }
}
