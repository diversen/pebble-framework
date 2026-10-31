<?php

declare(strict_types=1);

namespace Pebble;

use Gregwar\Captcha\CaptchaBuilder;

/**
 * Wrapper around Captcha
 */
class Captcha
{
    private const LIFETIME_SECONDS = 600;

    /**
     * @return \Gregwar\Captcha\CaptchaBuilder
     */
    public function getBuilder(): \Gregwar\Captcha\CaptchaBuilder
    {
        $builder = new CaptchaBuilder();
        $builder->build();

        $_SESSION['captcha_phrase'] = $builder->getPhrase();
        $_SESSION['captcha_expires'] = time() + self::LIFETIME_SECONDS;

        return $builder;
    }

    /**
     * Output image
     */
    public function outputImage(): void
    {
        $builder = $this->getBuilder();
        header('Content-type: image/jpeg');

        $builder->output();
    }

    /**
     * Check captcha
     */
    public function validate(string $phrase): bool
    {
        $expected = $_SESSION['captcha_phrase'] ?? null;
        $expires = (int)($_SESSION['captcha_expires'] ?? 0);

        unset($_SESSION['captcha_phrase'], $_SESSION['captcha_expires']);

        if (!is_string($expected) || $expires < time()) {
            return false;
        }

        return hash_equals(mb_strtolower($expected), mb_strtolower($phrase));
    }
}
