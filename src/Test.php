<?php

declare(strict_types=1);

namespace Pebble;

use Pebble\Attributes\Route;
use Pebble\Router\Request;

class Test
{

    #[Route(path: '/test/:param1', verbs: ['POST'])]
    public function index(Request $request): void
    {
    }

    /**
     * @route /test/hello_world
     * @verbs POST
     */
    #[Route(path: '/test/hello_world', verbs: ['POST'])]
    public function helloWorld(Request $request): void
    {
        echo "Hello world";
    }

    #[Route(path: '/cast/test/:id', cast: ['id' => 'int'])]
    public function testcast(Request $request): void
    {
        echo "Param: " . $request->param('id');
    }

    /**
     * @param array<mixed> $params
     */
	#[Route(path: '/attributes/test/:id', verbs: ['GET', 'POST'], cast: ['id' => 'int'])]
    public function test_attributes(Request $request): void
    {
        echo "Param: " . $request->param('id');
    }
}
