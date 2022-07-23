<?php

require "../../vendor/autoload.php";

use Pebble\Captcha;
use Pebble\Router;

class Controller
{

    /**
     * @route /image
     * @verbs GET
     */
    public function captcha()
    {
        $captcha = new Captcha();
        $captcha->outputImage();
    }

    /**
     * @route /
     * @verbs GET
     */
    public function test() {
        echo '<img src="/image">';
    }
}

$router = new Router();
$router->addClass(Controller::class);
$router->run();