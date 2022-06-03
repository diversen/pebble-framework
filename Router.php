<?php

declare(strict_types=1);

namespace Pebble;

use Pebble\Exception\NotFoundException;
use stdClass;
use ReflectionClass;
use ReflectionMethod;

class Router
{
    /**
     * Holding routes
     */
    private $routes = [];

    /**
     * Check if a string starts with a neddle, ':param'
     */
    private function startsWith($haystack, $needle)
    {
        return substr_compare($haystack, $needle, 0, strlen($needle)) === 0;
    }

    /**
     * Check if a string is in the format ':param', ':username' or not
     */
    private function isParam($str)
    {
        if ($this->startsWith($str, ':')) {
            return true;
        }
    }

    /**
     * Split parts of an URL into an array
     */
    private function getUrlParts($route)
    {
        // Remove query string
        $route = strtok($route, '?');
        $url_parts = explode('/', $route);
        $url_parts_filtered = [];
        foreach ($url_parts as $url_part) {
            if ($url_part) {
                $url_parts_filtered[] = $url_part;
            }
        }
        return $url_parts_filtered;
    }

    /**
     * Check if a route with REQUEST_METHOD is set
     */
    private function filterRouteByRequestMethod()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        if (!isset($this->routes[$method])) {
            $this->routes[$method] = [];
        }
    }

    /**
     * Filter out routes that does not have the correct length
     * /test/:hello/:world (3 in length)
     */
    private function filterRoutesByPartsLength()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $length = count($this->getUrlParts($_SERVER['REQUEST_URI']));

        $valid_routes = [];
        foreach ($this->routes[$method] as $key => $route) {
            $route_parts_length = count($route['parts']);

            if ($route_parts_length === $length) {

                // Add params array
                $route['params'] = [];
                $valid_routes[] = $route;
            }
        }

        $this->routes[$method] = $valid_routes;
    }

    /**
     * Compare each route part with each REQUEST_URI part and filter
     * out routes that does not match each and every URL part
     */
    private function filterRoutesByIndexPart($index, $part)
    {
        $method = $_SERVER['REQUEST_METHOD'];

        $valid_routes = [];
        foreach ($this->routes[$method] as $route) {
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

        $this->routes[$method] = $valid_routes;
    }

    /**
     * Filter routes part by part
     */
    private function filterRoutesByParts()
    {
        $current_url_parts = $this->getUrlParts($_SERVER['REQUEST_URI']);
        foreach ($current_url_parts as $index => $part) {
            $this->filterRoutesByIndexPart($index, $part);
        }
    }

    /**
     * Add a single route
     * `$router->add('GET', '/some/route/with/:param', \Some\Namespace::class, 'classMethod')`
     */
    private function add(string $request_method, string $route, string $class, string $class_method)
    {
        $this->request_method = $request_method;
        $this->routes[$request_method][] = [
            'route' => $route,
            'class' => $class,
            'method' => $class_method,
            'parts' => $this->getUrlParts($route),

        ];
    }

    private function getValidRoutes()
    {
        $this->filterRouteByRequestMethod();
        $this->filterRoutesByPartsLength();
        $this->filterRoutesByParts();

        $method = $_SERVER['REQUEST_METHOD'];
        $routes = $this->routes[$method];
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

    private function getFirstRoute()
    {
        return $this->getValidRoutes()[0];
    }

    /**
     * Parse a doc block and return all tags as an array. We are looking for 'route' and 'verbs'
     */
    private function parseDocBlock($doc)
    {
        if (preg_match_all('/@(\w+)\s+(.*)\r?\n/m', $doc, $matches)) {
            $result = array_combine($matches[1], $matches[2]);
            return $result;
        }
    }

    private function addClassMethods(string $class, string $method_name, array $parsed_doc)
    {
        if (isset($parsed_doc['route']) && isset($parsed_doc['verbs'])) {
            $route = $parsed_doc['route'];
            $verbs = explode(',', $parsed_doc['verbs']);
            foreach ($verbs as $verb) {
                $verb = trim($verb);
                $this->add($verb, $route, $class, $method_name);
            }
        }
    }

    /**
     * Add class routes found in a doc block. In order for a method to be added to the router
     * it needs a @route and a @verbs tag. E.g. like in the following:
     *
     * @route /api/posts/:id
     * @verbs GET,POST
     */
    public function addClass(string $class)
    {
        $reflector = new ReflectionClass($class);
        $methods = $reflector->getMethods(ReflectionMethod::IS_PUBLIC);

        foreach ($methods as $method) {
            $method_name = $method->name;
            $doc = $reflector->getMethod($method_name)->getDocComment();

            if (!$doc) {
                continue;
            }

            $parsed_doc = $this->parseDocBlock($doc);

            if (!$parsed_doc) {
                continue;
            }

            $this->addClassMethods($class, $method_name, $parsed_doc);
        }
    }

    private $middleware = [];

    public function use(callable $callable)
    {
        $this->middleware[] = $callable;
    }

    private static $currentRoute = '';

    /**
     * Get current route being run
     */
    public static function getCurrentRoute(): string
    {
        return self::$currentRoute;
    }

    private $middlewareClass = null;

    /**
     * Sets middleware class. If it is not set then `stdClass` will be used 
     */
    public function setMiddlewareClass(string $class)
    {
        $this->middlewareClass = $class;
    }

    /**
     * When all routes are loaded then the first route found will be executed
     */
    public function run()
    {
        if ($this->middlewareClass) {
            $middleware_object = new $this->middleware_object;
        } else {
            $middleware_object = new stdClass();
        }

        $route = $this->getFirstRoute();

        $params = $route['params'];
        self::$currentRoute = $route['route'];

        foreach ($this->middleware as $middleware) {
            $middleware($params, $middleware_object);
        }

        $class_method = $route['method'];
        $class = $route['class'];
        $object = new $class();

        $object->$class_method($params, $middleware_object);
    }

    /**
     * Runs any route found in the router in the order they were added
     */
    public function runAll()
    {
        $std_obj = new stdClass();
        $routes = $this->getValidRoutes();
        foreach ($routes as $route) {
            $params = $route['params'];

            foreach ($this->middleware as $middleware) {
                $middleware($params, $std_obj);
            }

            $class_method = $route['method'];
            $class = $route['class'];
            $object = new $class();

            $object->$class_method($params, $std_obj);
        }
    }
}
