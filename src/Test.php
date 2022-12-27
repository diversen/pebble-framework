<?php

declare(strict_types=1);

namespace Pebble;

class Test
{
    /**
     * @route /test/:param1
     * @verbs POST
     */
    public function index(): void
    {
    }

    /**
     * @route /test/hello_world
     * @verbs POST
     */
    public function helloWorld(): void
    {
        echo "Hello world";
    }

    /**
     * @route /cast/test/:id
     * @cast int:id
     * @verbs GET
     */
     public function testcast(array $params): void {
        echo "Param: " . $params['id'];
     }
}
