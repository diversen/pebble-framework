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

    public function test_getTagEscapesContentAndAttributes(): void
    {
        $html = Tag::getTag('button', '<script>alert(1)</script>', [
            'title' => '" onmouseover="alert(1)',
        ]);

        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringNotContainsString('onmouseover="alert', $html);
        $this->assertStringContainsString('&lt;script&gt;', $html);
        $this->assertStringContainsString('&quot; onmouseover=&quot;', $html);
    }
}
