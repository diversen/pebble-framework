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
            throw new TemplateException("No content in template");
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

    /**
     * @param array<mixed> $template_vars
     * @param array<mixed> $options
     * @return array<mixed>
     */
    public static function encodeData(array $template_vars, array $options = []): array
    {
        $raw = $options['raw'] ?? false;
        if (!$raw) {
            $template_vars = Special::encodeAry($template_vars);
        }
        return $template_vars;
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
            $template_vars = self::encodeData($template_vars, $options);
            extract($template_vars);
            $template = self::getTemplatePath($template);
            require($template);
        } catch (Exception $e) {
            $error = "Error in template: " . $e->getFile() . ". Line: " .  $e->getLine();
            throw new TemplateException($error);
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
