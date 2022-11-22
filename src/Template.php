<?php

declare(strict_types=1);

namespace Pebble;

use Exception;
use Pebble\Special;
use Pebble\Exception\TemplateException;

class Template
{
    /**
     * @var string $path
     */
    protected static string $path = '';

    /**
     * @param string $path
     */
    public static function setPath(string $path): void
    {
        self::$path = $path;
    }

    /**
     * Get output from a template
     * @param string $template
     * @param array<mixed> $template_vars
     * @param array<mixed> $options
     */
    public static function getOutput(string $template, array $template_vars = [], array $options = []): ?string
    {
        ob_start();

        self::render($template, $template_vars, $options);

        $content = ob_get_clean();
        if (!$content) {
            return null;
        }

        return $content;
    }

    /**
     * @param string $path
     */
    private static function getTemplatePath(string $path): string
    {
        if (self::$path) {
            $try_path = self::$path . '/' . $path;
            if (file_exists($try_path)) {
                return $try_path;
            }
        }
        return $path;
    }

    public $template_vars;

    public function setTemplateVars(array $template_vars, $options): void
    {
        if (!isset($options['raw'])) {
            $template_vars = Special::encodeAry($template_vars);
        }
        $this->template_vars = $template_vars;
    }

    /**
     * Get output from a template
     * @param string $template
     * @param array<mixed> $template_vars
     * @param array<mixed> $options
     */
    public static function render(string $template, $template_vars = [], array $options = []): void
    {
        try {
            if (!isset($options['raw'])) {
                $template_vars = Special::encodeAry($template_vars);
            }

            extract($template_vars);

            $template = self::getTemplatePath($template);
            require($template);
        } catch (Exception $e) {
            throw new TemplateException("Error in template '$template': " . $e->getMessage());
        }
    }

    /**
     * Get output from a template
     * @param string $template
     * @param array<mixed> $template_vars
     */
    public static function renderRaw(string $template, array $template_vars = []): void
    {
        $options = ['raw' => true];
        self::render($template, $template_vars, $options);
    }

    /**
     * Get output from a template
     * @param string $template
     * @param array<mixed> $template_vars
     */
    public static function getOutputRaw($template, $template_vars): ?string
    {
        $options = ['raw' => true];
        return self::getOutput($template, $template_vars, $options);
    }
}
