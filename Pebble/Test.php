<?php

namespace Pebble;

class Test {

    /**
     * @route /test/:param1
     * @verbs POST
     */
    public function index() {

    }

    /**
     * @route /test/hello_world
     * @verbs POST
     */
    public function helloWorld() {
        echo "Hello world";
    }

}