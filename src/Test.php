<?php

declare(strict_types=1);

namespace Pebble;

use Attribute;
use Pebble\Attributes\Route;

class Test
{
    /**
     * @route /test/:param1
     * @verbs POST
     */
    #[Route(path: '/test/:param1', verbs: ['POST'])]
    public function index(): void
    {
    }

    /**
     * @route /test/hello_world
     * @verbs POST
     */
    #[Route(path: '/test/hello_world', verbs: ['POST'])]
    public function helloWorld(): void
    {
        echo "Hello world";
    }

    #[Route(path: '/cast/test/:id', cast: ['id' => 'int'])]
    public function testcast(array $params): void
    {
        echo "Param: " . $params['id'];
    }

    /**
     * @param array<mixed> $params
     */
	#[Route(path: '/attributes/test/:id', verbs: ['GET', 'POST'], cast: ['id' => 'int'])]
    public function test_attributes(array $params): void
    {
        echo "Param: " . $params['id'];
    }
}
