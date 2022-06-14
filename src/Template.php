<?php

declare(strict_types=1);

namespace Pebble;

use Exception;
use Pebble\Special;
use Pebble\Exception\TemplateException;

class Template
{
    /**
     * Get output from a template
     */
    public static function getOutput(string $template, array $vars = [], array $options = []): string
    {
        ob_start();

        self::render($template, $vars, $options);

        $content = ob_get_clean();

        return $content;
    }

    /**
     * Render a template using a template path and some variables
     * Any special entity is encoded on strings and numeric values.
     * Set options['raw'] and no encoding will occur
     */
    public static function render($template_path, $variables = [], array $options = [])
    {
        try {
            if (!isset($options['raw'])) {
                $variables = Special::encodeAry($variables);
            }

            extract($variables);

            require($template_path);
        } catch (Exception $e) {
            throw new TemplateException('No such template path: ' . $template_path);
        }
    }

    /**
     * Shortcut to render a template raw
     */
    public static function renderRaw($template_path, $variables)
    {
        $options = ['raw' => true];
        self::render($template_path, $variables, $options);
    }

    /**
     * Shortcut to get template output raw
     */
    public static function getOutputRaw($template_path, $variables)
    {
        $options = ['raw' => true];
        return self::getOutput($template_path, $variables, $options);
    }
}
