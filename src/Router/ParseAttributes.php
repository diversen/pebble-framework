<?php

declare(strict_types=1);

namespace Pebble\Router;

use Pebble\Router\Utils;
use ReflectionClass;
use ReflectionMethod;
use Pebble\Interfaces\RouteParser;
use Pebble\Attributes\Route;

class ParseAttributes implements RouteParser
{

    /**
     * @param array<mixed> $args
     * @return array<mixed>
     */
    private function getRouteDefinitions(string $class, string $method_name, array $args): array
    {
        $route_definitions = [];

        $route = $args['path'];
        $verbs = $args['verbs'];
        foreach ($verbs as $verb) {
            $verb = trim($verb);
            $route_definition = [
                'verb' => $verb,
                'route' => $route,
                'class' => $class,
                'method' => $method_name,
                'parts' => Utils::getUrlParts($args['path']),
                'cast' => $args['cast'] ?? null,
            ];

            $route_definitions[] = $route_definition;
        }

        return $route_definitions;
    }

    /**
     * @param class-string $class
     */
    public function getRoutes(string $class)
    {

        $reflection_class = new ReflectionClass($class);
        $methods = $reflection_class->getMethods(ReflectionMethod::IS_PUBLIC);
        $method_routes = [];

        foreach ($methods as $method) {

            $method_name = $method->getName();
            $attr = $method->getAttributes();

            foreach ($attr as $attribute) {
                $attr_name = $attribute->getName();
                if ($attr_name !== Route::class) {
                    continue;
                }

                $args = $attribute->getArguments();
                if (!isset($args['path']) || !isset($args['verbs'])) {
                    continue;
                }

                $routes = $this->getRouteDefinitions($class, $method_name, $args);
                foreach ($routes as $route) {
                    $method_routes[] = $route;
                }
            }
        }

        return $method_routes;
    }

    /**
     * @param array<string> $cast
     * @param array<string> $params
     * @return array<mixed>
     */
    private function castParams(array $cast, array $params): array
    {

        $cast_to_map = [
            'int' => 'intval',
            'float' => 'floatval',
            'string' => 'strval',
            'bool' => 'boolval',
        ];

        foreach ($params as $key => $value) {
            $cast_to = $cast[$key] ?? null;
            if ($cast_to) {
                $params[$key] = $cast_to_map[$cast_to]($value);
            }
        }

        return $params;
    }

    /**
     * @param array<mixed> $route
     * @return array<mixed> $params
     */
    public function getParams(array $route): array
    {
        // Cast params if specified in the doc block
        if (!$route['cast']) {
            return $route['params'];
        }
        
        $route['params'] = $this->castParams($route['cast'], $route['params']);
        
        $params = $route['params'];
        return $params;
    }
}
