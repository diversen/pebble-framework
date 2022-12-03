<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Pebble\HTTP\AcceptLanguage;

final class AcceptLanguageTest extends TestCase
{

    public function test_getLanguage(): void
    {

        $options = ['fr', 'en', 'de'];

        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'fr-CH, fr;q=0.9, en;q=0.8, de;q=0.7, *;q=0.5';
        $language = AcceptLanguage::getLanguage($options, 'en');
        $this->assertSame('fr', $language);

        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'en-GB, en;q=0.9, fr;q=0.8, de;q=0.7, *;q=0.5';
        $language = AcceptLanguage::getLanguage($options, 'en');
        $this->assertSame('en', $language);

        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'de-CH';
        $language = AcceptLanguage::getLanguage($options, 'en');
        $this->assertSame('de', $language);

        // Allow for more locale variant  en-GB
        $options = ['en-GB', 'fr', 'en', 'de'];
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'en-GB, en;q=0.9, fr;q=0.8, de;q=0.7, *;q=0.5';
        $language = AcceptLanguage::getLanguage($options, 'en');
        $this->assertSame('en-GB', $language);

        // Not set
        unset($_SERVER['HTTP_ACCEPT_LANGUAGE']);
        $language = AcceptLanguage::getLanguage($options, 'en');
        $this->assertSame('en', $language);

        // Gibberish
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'gibberish';
        $language = AcceptLanguage::getLanguage($options, 'en');
        $this->assertSame('en', $language);
    }
}
