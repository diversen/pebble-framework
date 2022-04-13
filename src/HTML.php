<?php

namespace Pebble;

class HTML
{
    /**
     * Returns a html tag from 'tag' 'value', and attributes.
     * `HTML::getTag('button', Lang::translate('Two factor authentication'), ['href' => '/2fa/enable', 'disabled' => null]);`
     * returns <button href="/2fa/enable" disabled>To-faktor login</button>
     * @param string $tag the html tag
     * @param string $value the html tag value
     * @param array $attrs_ary attributes
     */
    public static function getTag(string $tag, string $value, array $attrs_ary): string
    {

        $html = "<$tag ";

        $attrs_ary_parsed = [];
        foreach ($attrs_ary as $attr => $attr_value) {
            if (!$attr_value) {               
                $attrs_ary_parsed[] = $attr;
            } else {
                $attrs_ary_parsed[] = $attr . '=' . '"' . $attr_value . '"';
            }
        }

        $html .= implode(' ', $attrs_ary_parsed);
        $html .= '>';
        $html .= $value;
        $html .= "</$tag>";
        return $html;
    }
}
