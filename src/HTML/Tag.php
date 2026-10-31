<?php

declare(strict_types=1);

namespace Pebble\HTML;

use InvalidArgumentException;

class Tag
{
    private static function validateName(string $name): string
    {
        if (!preg_match('/^[A-Za-z][A-Za-z0-9:-]*$/', $name)) {
            throw new InvalidArgumentException("Invalid HTML name: $name");
        }
        return $name;
    }

    private static function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    /**
     * Returns a html tag from 'tag' 'value', and attributes.
     * `HTML::getTag('button', '2 factor Login', ['title' => 'Click me', 'disabled' => null]);`
     * returns `<button title="Click me" disabled>2 factor login</button>`
     * @param string $tag the html tag
     * @param string $value the html tag value
     * @param array<mixed> $attrs_ary attributes
     */
    public static function getTag(string $tag, string $value, array $attrs_ary): string
    {
        return self::buildTag($tag, self::escape($value), $attrs_ary);
    }

    /**
     * Build a tag with deliberately unescaped inner HTML.
     */
    public static function getRawTag(string $tag, string $html, array $attrs_ary): string
    {
        return self::buildTag($tag, $html, $attrs_ary);
    }

    private static function buildTag(string $tag, string $value, array $attrs_ary): string
    {
        $tag = self::validateName($tag);
        $html = "<$tag ";

        $attrs_ary_parsed = [];
        foreach ($attrs_ary as $attr => $attr_value) {
            $attr = self::validateName((string)$attr);
            if ($attr_value === null || $attr_value === true) {
                $attrs_ary_parsed[] = $attr;
            } elseif ($attr_value === false) {
                continue;
            } else {
                $attrs_ary_parsed[] = $attr . '=' . '"' . self::escape((string)$attr_value) . '"';
            }
        }

        $html .= implode(' ', $attrs_ary_parsed);
        $html .= '>';
        $html .= $value;
        $html .= "</$tag>";
        return $html;
    }
}
