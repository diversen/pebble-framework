<?php

declare(strict_types=1);

namespace Pebble;

use Pebble\Special;
use Pebble\Exception\TemplateException;
use Throwable;

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
        $buffer_level = ob_get_level();
        ob_start();

        try {
            self::render($template, $template_vars, $options);
            $content = ob_get_clean();
        } catch (Throwable $e) {
            while (ob_get_level() > $buffer_level) {
                ob_end_clean();
            }
            throw $e;
        }
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
        $root = self::$path ?: Path::getBasePath();
        $root_real = realpath($root);
        if ($root_real === false) {
            throw new TemplateException('Template root does not exist');
        }

        if (!str_starts_with($path, DIRECTORY_SEPARATOR)) {
            $path = rtrim($root_real, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $path;
        }

        $path_real = realpath($path);
        if ($path_real === false || !is_file($path_real)) {
            throw new TemplateException('Template does not exist');
        }

        $root_prefix = rtrim($root_real, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        if ($path_real !== $root_real && !str_starts_with($path_real, $root_prefix)) {
            throw new TemplateException('Template is outside the configured template root');
        }

        return $path_real;
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
            $__pebble_template_path = self::getTemplatePath($template);
            $__pebble_template_vars = self::encodeData($template_vars, $options);

            (static function (
                string $__pebble_template_path,
                array $__pebble_template_vars
            ): void {
                extract($__pebble_template_vars, EXTR_SKIP);
                require $__pebble_template_path;
            })($__pebble_template_path, $__pebble_template_vars);
        } catch (Throwable $e) {
            if ($e instanceof TemplateException) {
                throw $e;
            }
            $error = "Error in template: " . $e->getFile() . ". Line: " .  $e->getLine();
            throw new TemplateException($error, previous: $e);
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
