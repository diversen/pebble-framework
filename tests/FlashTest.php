<?php

use Pebble\Flash;
use PHPUnit\Framework\TestCase;

final class FlashTest extends TestCase
{
    public function test_setMessage(): void
    {
        $_SESSION = [];
        $flash = new Flash();
        
        $flash->setMessage('Error test', 'error', ['alert_option' => true]);

        $expect = [
            'message' => 'Error test',
            'type' => 'error',
            'options' => [
                'alert_option' => true,
            ]
        ];
        $this->assertEquals($expect, $_SESSION['flash'][0]);

    }

    public function test_getMessage(): void
    {
        $_SESSION = [];
        $flash = new Flash();
        $flash->setMessage('Error test', 'error', ['alert_option' => true]);

        $expect = [
            'message' => 'Error test',
            'type' => 'error',
            'options' => [
                'alert_option' => true,
            ]
        ];
        $messages = $flash->getMessages();
        $this->assertEquals($expect, $messages[0]);
    }
}
