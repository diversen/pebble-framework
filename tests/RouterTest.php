<?php

declare(strict_types=1);

use Pebble\Exception\NotFoundException;
use Pebble\Router;
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

    public function test_runAll(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';

        // M
        $_SERVER['REQUEST_URI'] = '/test/hello_world';

        $router = new Router();
        
        // Matches two routes
        $router->addClass(Test::class);

        $router->runAll();

        $this->expectOutputString('Hello world');
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

    public function test_castToInt(): void {

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/cast/test/10';

        $router = new Router();
        $router->addClass(Test::class);

        $route = $router->getFirstRoute();
        $params = $router->getParamsFromRoute($route);

        $this->assertEquals(10, $params['id']);

    }
}
