<?php

use Pebble\Captcha;
use PHPUnit\Framework\TestCase;

final class CaptchaTest extends TestCase
{
    public function test_outputImage(): void
    {
        $captcha = new Captcha();

        $captcha->getBuilder();

        $this->assertIsString($_SESSION['captcha_phrase']);
    }

    public function test_validate(): void
    {
        $captcha = new Captcha();

        $captcha->getBuilder();

        $phrase = $_SESSION['captcha_phrase'];

        $res = $captcha->validate('not correct');

        $this->assertEquals($res, false);

        // A failed attempt consumes the challenge too.
        $this->assertFalse($captcha->validate($phrase));

        $captcha->getBuilder();
        $phrase = $_SESSION['captcha_phrase'];

        $res = $captcha->validate($phrase);

        $this->assertEquals($res, true);
        $this->assertFalse($captcha->validate($phrase));
    }
}
