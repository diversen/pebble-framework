<?php

namespace Pebble\HTTP;

class AcceptLanguage
{
    /**
     * Get best guess of Request language
     * https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Accept-Language
     * Find language from e.g.: 'fr-CH, fr;q=0.9, en;q=0.8, de;q=0.7, *;q=0.5';
     * @param array<string> $options
     */
    public static function getLanguage(array $options, string $default): string
    {

        // Check if it isset in the request
        $http_accept_language = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? null;
        if (!$http_accept_language) {
            return $default;
        }

        // Remove spaces
        $http_accept_language = str_replace(' ', '', $http_accept_language);

        // Split different languages
        $languages_ary = explode(',', $http_accept_language);

        foreach ($languages_ary as $lang) {

            // Split language and weigt, e.g. 'fr;q=0.9'
            $language_ary = explode(';', $lang);

            // Get language part. 'fr-CH', 'fr' ...
            $language = $language_ary[0] ?? null;

            // Check if language has been found, e.g. 'fr-CH', 'fr'
            if (in_array($language, $options)) {
                return $language;
            }

            // Split language by '-', 'fr-ch'
            $language_ary = explode('-', $language);

            // e.g. 'fr', 'de'
            $language = $language_ary[0] ?? null;

            // We assume the languages are correct sorted in the HTTP_ACCEPT_LANGUAGE return first match
            if (in_array($language, $options)) {
                return $language;
            }
        }

        return $default;
    }
}
