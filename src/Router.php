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
     * @var array<mixed>
     */
    private array $routes = [];

    /**
     * Array middleware, callables
     * @var array<callable>
     */
    private array $middleware = [];

    /**
     * Current route being executed
     */
    private static string $current_route = '';

    /**
     * Class to create middleware transport object from
     */
    private ?string $middleware_class = null;

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
     * Split parts of an URL into an array
     * @return array<string>
     */
    private function getUrlParts(string $route): array
    {
        // Remove query string
        $route = strtok($route, '?');
        if (!$route) return [];
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
    private function filterRouteByRequestMethod(): void
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
    private function filterRoutesByPartsLength(): void
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
     * remove routes that does not match each and every URL part
     */
    private function filterRoutesByIndexPart(int $index, string $part): void
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
    private function filterRoutesByParts(): void
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
    public function add(string $request_method, string $route, string $class, string $class_method): void
    {
        $this->routes[$request_method][] = [
            'route' => $route,
            'class' => $class,
            'method' => $class_method,
            'parts' => $this->getUrlParts($route),

        ];
    }

    /**
     * @return array<mixed> 
     */
    public function getValidRoutes(): array
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

    /**
     * @return array<mixed>
     */
    private function getFirstRoute(): array
    {
        return $this->getValidRoutes()[0];
    }

    /**
     * Parse a doc block and return all tags as an array. We are looking for 'route' and 'verbs'
     * @return array<mixed>
     */
    private function parseDocBlock(string $doc): ?array
    {
        if (preg_match_all('/@(\w+)\s+(.*)\r?\n/m', $doc, $matches)) {
            $result = array_combine($matches[1], $matches[2]);
            return $result;
        }
        return null;
    }

    /**
     * @param array<string> $parsed_doc
     */
    private function addClassMethods(string $class, string $method_name, array $parsed_doc): void
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
    public function addClass(string $class): void
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

    /**
     * Sets a middleware callable
     */

    public function use(callable $callable): void
    {
        $this->middleware[] = $callable;
    }



    /**
     * Get current route being run
     */
    public static function getCurrentRoute(): string
    {
        return self::$current_route;
    }



    /**
     * Sets middleware class. If it is not set then `stdClass` will be used
     */
    public function setMiddlewareClass(string $class): void
    {
        $this->middleware_class = $class;
    }

    /**
     * When all routes are loaded then the first route found will be executed
     */
    public function run(): void
    {
        if ($this->middleware_class) {
            $middleware_object = new $this->middleware_class();
        } else {
            $middleware_object = new stdClass();
        }

        $route = $this->getFirstRoute();

        $params = $route['params'];
        self::$current_route = $route['route'];

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
    public function runAll(): void
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
