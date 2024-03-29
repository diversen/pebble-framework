<?php

declare(strict_types=1);

namespace Pebble;

use Pebble\Exception\NotFoundException;

use Pebble\Router\Utils;
use InvalidArgumentException;
use Pebble\Router\ParseAttributes;
use Pebble\Router\Request;

class Router
{
    /**
     * Holding routes
     * @var array<mixed>
     */
    private array $routes = [];

    /**
     * Array of middleware callables
     * @var array<callable>
     */
    private array $middleware = [];

    /**
     * Request URI
     */
    private string $request_uri;

    /**
     * Request method
     */
    private string $request_method;

    /**
     * Route parser
     */
    private ParseAttributes $route_parser;

    /**
     * Base controller
     */
    private string $base_controller = '';

    /**
     * faster router based on URL segment
     * e.g. /settings/test/:id
     * If faster router is enabled then there need to be a controller named App/Settings/*
     * Anything atempt to loader a controller is skipped if there is no match inf faster_route mode
     */
    private bool $faster_router = false;

    public function __construct()
    {
        $this->request_method = $_SERVER['REQUEST_METHOD'];
        $this->request_uri = $_SERVER['REQUEST_URI'];
        $this->route_parser = new ParseAttributes();
    }

    /**
     * Check if a string starts with a neddle, ':param'
     */
    private function startsWith(string $haystack, string $needle): bool
    {
        return substr_compare($haystack, $needle, 0, strlen($needle)) === 0;
    }

    /**
     * Check if a string is in the format ':param', ':username' or not
     */
    private function isParam(string $str): bool
    {
        if ($this->startsWith($str, ':')) {
            return true;
        }
        return false;
    }


    /**
     * Check if a route with REQUEST_METHOD is set
     */
    private function filterRouteByRequestMethod(): void
    {
        if (!isset($this->routes[$this->request_method])) {
            $this->routes[$this->request_method] = [];
        }
    }

    /**
     * Filter out routes that does not have the correct length
     * /test/:hello/:world (3 in length)
     */
    private function filterRoutesByPartsLength(): void
    {
        $length = count(Utils::getUrlParts($this->request_uri));

        $valid_routes = [];
        foreach ($this->routes[$this->request_method] as $key => $route) {
            $route_parts_length = count($route['parts']);

            if ($route_parts_length === $length) {

                // Add params array
                $route['params'] = [];
                $valid_routes[] = $route;
            }
        }

        $this->routes[$this->request_method] = $valid_routes;
    }

    /**
     * Compare each route part with each REQUEST_URI part and filter
     * remove routes that does not match each and every URL part
     */
    private function filterRoutesByIndexPart(int $index, string $part): void
    {
        $valid_routes = [];
        foreach ($this->routes[$this->request_method] as $route) {
            $route_parts = $route['parts'];

            if ($this->isParam($route_parts[$index])) {

                // Extract value of param
                $param = ltrim($route_parts[$index], ':');
                $route['params'][$param] = $part;
                $valid_routes[] = $route;
            }

            if ($route_parts[$index] == $part) {
                $valid_routes[] = $route;
            }
        }

        $this->routes[$this->request_method] = $valid_routes;
    }

    /**
     * Filter routes part by part
     */
    private function filterRoutesByParts(): void
    {
        $current_url_parts = Utils::getUrlParts($this->request_uri);
        foreach ($current_url_parts as $index => $part) {
            $this->filterRoutesByIndexPart($index, $part);
        }
    }

    /**
     * @return array<mixed>
     */
    public function getValidRoutes(): array
    {
        $this->filterRouteByRequestMethod();
        $this->filterRoutesByPartsLength();
        $this->filterRoutesByParts();

        $routes = $this->routes[$this->request_method];
        if (empty($routes)) {
            throw new NotFoundException('The page does not exist');
        }

        foreach ($routes as $route) {
            if (!method_exists($route['class'], $route['method'])) {
                throw new NotFoundException("The page does not exist. No such method: {$route['class']}::{$route['method']} ");
            }
        }

        return $routes;
    }

    /**
     * @return array<mixed>
     */
    public function getFirstRoute(): array
    {
        return $this->getValidRoutes()[0];
    }

    /**
     * Add a single route
     * `$router->add('GET', '/some/route/with/:param', \Some\Namespace::class, 'classMethod')`
     * @param array<mixed> $route
     */
    public function add(array $route): void
    {
        $method = $route['verb'];
        $this->routes[$method][] = $route;
    }

    /**
     * Set faster router mode
     */
    public function setFasterRouter(string $base_controller = 'home'): void
    {
        $this->base_controller = $base_controller;
        $this->faster_router = true;
    }

    /**
     * Check if a class should be skipped if routing is based on first URL segment
     * @return bool
     */
    private function skipFasterRouterClass(string $class): bool
    {
        $first_segment = Utils::getUrlSegment($this->request_uri, 0);
        if (!$first_segment) {
            $first_segment = $this->base_controller;
        }

        $class_lower = strtolower($class);
        $first_segment_lower = strtolower($first_segment);
        $class_search = "app\\" . $first_segment_lower;
        if (!strstr($class_lower, $class_search)) {
            return true;
        }
        return false;
    }

    /**
     * Add class routes found in a doc block. In order for a method to be added to the router
     * it needs a @route and a @verbs tag. E.g. like in the following:
     *
     * @param string $class
     *
     * @route /api/posts/:id
     * @verbs GET,POST
     *
     */
    public function addClass(string $class): void
    {
        if (!class_exists($class)) {
            throw new InvalidArgumentException("Class $class does not exist");
        }

        if ($this->faster_router && $this->skipFasterRouterClass($class)) {
            return;
        }

        $routes = $this->route_parser->getRoutes($class);
        foreach ($routes as $route) {
            $this->add($route);
        }
    }

    /**
     * Sets a middleware callable
     */

    public function use(callable $callable): void
    {
        $this->middleware[] = $callable;
    }

    /**
     * When all routes are loaded then the first route found will be executed
     */
    public function run(): void
    {
        $route = $this->getFirstRoute();

        $params = $this->route_parser->getParams($route);

        $request = new Request($params);
        $request->setCurrentRoute($route['route']);

        foreach ($this->middleware as $middleware) {
            $middleware($request);
        }

        $class_method = $route['method'];
        $class = $route['class'];
        $object = new $class();

        $object->$class_method($request);
    }
}
