<?php

declare(strict_types=1);

namespace Pebble\CLI;

use Pebble\Service\ConfigService;
use Diversen\ParseArgv;
use Diversen\Translate\GoogleTranslate;
use Diversen\Translate\Extractor;
use Exception;

class Translate
{
    /**
     * @var \Pebble\Config
     */
    private $config;

    /**
     * Pretty print JSON or
     * @var bool
     */
    public $pretty_print_json = true;
    public function __construct()
    {
        $this->config = (new ConfigService())->getConfig();
    }

    /**
     * Return command definition
     * @return array<mixed>
     */
    public function getCommand()
    {
        return
            array(
                'usage' => 'Extract translation from application',
                'options' => array(
                    '--extract'    => 'Extract translation strings from default language (Language.default) set in configuration',
                    '--gtranslate' => 'Translate into other languages (Language.enabled) using google translate',
                    '--js' => 'Export as a JS file'
                ),
            );
    }

    private function extract(ParseArgv $args): void
    {
        $default_lang = $this->config->get('Language.default');
        $translate_dir = $this->config->get('Language.translate_base_dir');

        // Extract first
        $e = new Extractor();
        $e->defaultLanguage = $default_lang;
        $e->setSingleDir($translate_dir);
        $e->updateLang();
    }

    private function gtranslate(ParseArgv $args): int
    {
        $default_lang = $this->config->get('Language.default');
        $enabled = $this->config->get('Language.enabled');
        $translate_dir = $this->config->get('Language.translate_base_dir');

        $translate_to = [];
        foreach ($enabled as $lang) {
            if ($lang != $default_lang) {
                $translate_to[] = $lang;
            }
        }

        // Extract first
        $e = new Extractor();
        $e->defaultLanguage = $default_lang;
        $e->setSingleDir($translate_dir);
        $e->updateLang();

        $google_credentials = $this->config->get('Language.google_application_credentials');

        try {
            putenv("GOOGLE_APPLICATION_CREDENTIALS=$google_credentials");
            // Translate using google
            foreach ($translate_to as $lang) {
                $t = new GoogleTranslate();
                $t->source = $default_lang;
                $t->target = $lang;
                $t->setSingleDir($translate_dir);
                $t->updateLang();
            }
        } catch (Exception $e) {
            echo $e->getMessage() . "\n";
            return 1;
        }

        return 0;
    }

    /**
     * Create js from  $json
     * Including an export
     */
    private function addJsExport(string|bool $json): string
    {
        $js = "const Translation = \n\n";
        $js .= $json . "\n\n";
        $js .= "export {Translation}\n";
        return $js;
    }

    /**
     * Transform php translations to js files
     */
    private function toJS(): void
    {
        $translate_dir = $this->config->get('Language.translate_base_dir');
        $translate_dir_js = $this->config->get('Language.translate_base_dir_js');
        $enabled = $this->config->get('Language.enabled');

        $LANG = [];

        foreach ($enabled as $lang) {
            $lang_base_dir = "$translate_dir/lang/$lang";
            $translation_file = "$lang_base_dir/language.php";

            if (!file_exists($translation_file)) {
                continue;
            }
            include $translation_file;

            $js_lang_path = "$translate_dir_js/$lang";
            if (!file_exists($js_lang_path)) {
                mkdir($js_lang_path, 0777, true);
            }

            $json = json_encode($LANG);
            if ($this->pretty_print_json) {
                $json = json_encode($LANG, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            }
            if ($json) {
                $js = $this->addJsExport($json);
                file_put_contents("$js_lang_path/language.js", $js);
            }
        }
    }

    public function runCommand(ParseArgv $args): int
    {
        $enabled = $this->config->get('Language.enabled');
        if (empty($enabled)) {
            echo "No languages enabled, nothing to do\n";
            return 1;
        }

        if ($args->getOption('extract')) {
            $this->extract($args);
        }

        if ($args->getOption('gtranslate')) {
            $res = $this->gtranslate($args);
            $this->toJS();
            return $res;
        }

        if ($args->getOption('js')) {
            $this->toJS();
        }

        return 0;
    }
}
