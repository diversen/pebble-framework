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
     * @param array<mixed> $vars
     * @param array<mixed> $options
     */
    public static function getOutput(string $template, array $vars = [], array $options = []): ?string
    {
        ob_start();

        self::render($template, $vars, $options);

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

    /**
     * Get output from a template
     * @param string $template
     * @param array<mixed> $vars
     * @param array<mixed> $options
     */
    public static function render(string $template, $vars = [], array $options = []): void
    {
        try {
            if (!isset($options['raw'])) {
                $vars = Special::encodeAry($vars);
            }

            extract($vars);

            $template = self::getTemplatePath($template);
            require($template);
        } catch (Exception $e) {
            throw new TemplateException($e->getMessage());
        }
    }

    /**
     * Get output from a template
     * @param string $template
     * @param array<mixed> $vars
     */
    public static function renderRaw(string $template, array $vars = []): void
    {
        $options = ['raw' => true];
        self::render($template, $vars, $options);
    }

    /**
     * Get output from a template
     * @param string $template
     * @param array<mixed> $vars
     */
    public static function getOutputRaw($template, $vars): ?string
    {
        $options = ['raw' => true];
        return self::getOutput($template, $vars, $options);
    }
}
