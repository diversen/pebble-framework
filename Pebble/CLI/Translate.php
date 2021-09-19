<?php

namespace Pebble\CLI;

use Pebble\Config;
use Diversen\ParseArgv;
use Diversen\Translate\GoogleTranslate;
use Diversen\Translate\Extractor;
use Exception;

class Translate
{

    // Return main commands help
    public function getCommand()
    {
        return
            array(
                'usage' => 'Extract translation from application',
                'options' => array(
                    '--extract'    => 'Extract translation strings from default language (Language.default) set in configuration',
                    '--gtranslate' => 'Translate into other languages (Language.enabled) using google translate',
                ),
            );
    }

    private function extract(ParseArgv $args)
    {

        $default_lang = Config::get('Language.default');

        // Extract first
        $e = new Extractor();
        $e->defaultLanguage = $default_lang;
        $e->setSingleDir("www/App");
        $e->updateLang();
    }

    private function gtranslate(ParseArgv $args)
    {

        $default_lang = Config::get('Language.default');
        $enabled = Config::get('Language.enabled');

        $translate_to = [];
        foreach($enabled as $lang) {
            if ($lang != $default_lang) {
                $translate_to[] = $lang;
            }
        }
        
        // Extract first
        $e = new Extractor();
        $e->defaultLanguage = $default_lang;
        $e->setSingleDir("www/App");
        $e->updateLang();

        $google_credentials = Config::get('Language.google_application_credentials');

        try {
            putenv("GOOGLE_APPLICATION_CREDENTIALS=$google_credentials");
            // Translate using google
            foreach($translate_to as $lang) {
                $t = new GoogleTranslate();
                $t->source = $default_lang;
                $t->target = $lang;
                $t->setSingleDir("www/App");
                $t->updateLang();
            }
        } catch (Exception $e) {
            echo $e->getMessage() . "\n";
            return 1;
        }

        return 0;
    }


    public function runCommand(ParseArgv $args)
    {
  
        if ($args->getFlag('extract')) {
            return $this->extract($args);
        }

        if ($args->getFlag('gtranslate')) {   
            return $this->gtranslate($args);
        }

        return 0;
    }
}
