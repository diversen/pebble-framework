<?php

declare(strict_types=1);


use PHPUnit\Framework\TestCase;
use Pebble\HTML\Tag;

final class TagTest extends TestCase
{
    public function test_getTag(): void
    {
        $html = Tag::getTag('button', '2 factor Login', ['title' => 'Click me', 'disabled' => null, 'class' => 'btn btn-primary']);
        $expect = '<button title="Click me" disabled class="btn btn-primary">2 factor Login</button>';
        $this->assertSame($expect, $html);
    }
}
