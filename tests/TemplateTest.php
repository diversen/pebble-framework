<?php

declare(strict_types=1);

use Pebble\Template;
use Pebble\Exception\TemplateException;
use PHPUnit\Framework\TestCase;

final class TemplateTest extends TestCase
{
    protected function tearDown(): void
    {
        Template::setPath(dirname(__DIR__));
    }

    public function test_render(): void
    {
        $variables = ['escape_this' => '<p>Test</p>'];

        $template = __DIR__ . "/templates/test.tpl.php";
        Template::render($template, $variables);

        $this->expectOutputString('<p>&lt;p&gt;Test&lt;/p&gt;</p>');
    }

    public function test_getOutput(): void
    {
        $variables = ['escape_this' => '<p>Test</p>'];

        $template = __DIR__ . "/templates/test.tpl.php";
        $str = Template::getOutput($template, $variables);

        $this->assertEquals('<p>&lt;p&gt;Test&lt;/p&gt;</p>', $str);
    }

    public function test_getOutput_raw(): void
    {
        $variables = ['escape_this' => '<pre>Test</pre>'];

        $template = __DIR__ . "/templates/test.tpl.php";
        $str = Template::getOutput($template, $variables, ['raw' => true]);

        $this->assertEquals('<p><pre>Test</pre></p>', $str);
    }

    public function testTemplateVariableCannotOverrideTemplatePath(): void
    {
        $template = __DIR__ . '/templates/test.tpl.php';
        $str = Template::getOutput($template, [
            'template' => '/etc/passwd',
            'escape_this' => 'safe',
        ]);

        $this->assertSame('<p>safe</p>', $str);
    }

    public function testTemplateCannotEscapeApplicationRoot(): void
    {
        $this->expectException(TemplateException::class);
        Template::render('/etc/passwd');
    }
}
