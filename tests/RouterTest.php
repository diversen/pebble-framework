<?php

declare(strict_types=1);

use Pebble\Exception\NotFoundException;
use Pebble\Router;
use Pebble\Router\ParseDocBlocks;
use Pebble\Router\ParseAttributes;
use Pebble\Test;
use PHPUnit\Framework\TestCase;

final class RouterTest extends TestCase
{

    public function test_notFound(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['REQUEST_URI'] = '/no/such/route';

        $router = new Router();
        $router->addClass(Test::class);

        $this->expectException(NotFoundException::class);
        $router->getValidRoutes();
    }

    public function test_getValidRoutes(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['REQUEST_URI'] = '/test/hello_world/';

        $router = new Router();

        $router->addClass(Test::class);

        $routes = $router->getValidRoutes();

        // 2 correct matches
        $this->assertEquals(2, count($routes));

        $this->assertEquals($routes[0]['route'], '/test/:param1');
        $this->assertEquals($routes[0]['class'], 'Pebble\Test');
        $this->assertEquals($routes[0]['method'], 'index');
        $this->assertEquals($routes[0]['params']['param1'], 'hello_world');

        $this->assertEquals($routes[1]['route'], '/test/hello_world');
        $this->assertEquals($routes[1]['class'], 'Pebble\Test');
        $this->assertEquals($routes[1]['method'], 'helloWorld');
    }

    /**
     * Same as above we just use annotations for the same routes
     */
    public function test_getValidRoutesFromClass(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['REQUEST_URI'] = '/test/hello_world/';

        $router = new Router();
        $router->addClass(Pebble\Test::class);

        $routes = $router->getValidRoutes();

        // 2 correct matches
        $this->assertEquals(2, count($routes));

        $this->assertEquals($routes[0]['route'], '/test/:param1');
        $this->assertEquals($routes[0]['class'], 'Pebble\Test');
        $this->assertEquals($routes[0]['method'], 'index');
        $this->assertEquals($routes[0]['params']['param1'], 'hello_world');

        $this->assertEquals($routes[1]['route'], '/test/hello_world');
        $this->assertEquals($routes[1]['class'], 'Pebble\Test');
        $this->assertEquals($routes[1]['method'], 'helloWorld');
    }

    public function test_run(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/cast/test/100';

        $router = new Router();

        $router->addClass(Test::class);

        $router->run();

        $this->expectOutputString('Param: 100');
    }

    public function test_castToInt(): void
    {

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/cast/test/10';

        $router = new Router();
        $router->addClass(Test::class);

        $route = $router->getFirstRoute();
        $docblock = new ParseDocBlocks();

        $params = $docblock->getParams($route);

        $this->assertEquals(10, $params['id']);
    }


    public function test_attributes(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/attributes/test/10';

        $route_parser = new ParseAttributes();
        $router = new Router($route_parser);
        $router->addClass(Test::class);

        $route = $router->getFirstRoute();

        $parse_attr = new ParseAttributes();
        $routes = $parse_attr->getRoutes(Test::class);

        $params = $parse_attr->getParams($route);

        $this->assertEquals(10, $params['id']);
    }
}
