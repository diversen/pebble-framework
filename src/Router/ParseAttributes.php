<?php

declare(strict_types=1);

namespace Pebble\Router;

use Pebble\Router\Utils;
use Pebble\Attributes\Route;
use Pebble\AttributeParser;

class ParseAttributes
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
     * @return array<mixed>
     */
    public function getRoutes(string $class)
    {
        $method_routes = [];
        $attr = AttributeParser::parseAttributes($class);
        foreach ($attr as $attribute) {
            $attr_name = $attribute['attribute_name'];
            if ($attr_name !== Route::class) {
                continue;
            }

            $args = $attribute['arguments'];
            if (!isset($args['path'])) {
                continue;
            }

            if (!isset($args['verbs'])) {
                $args['verbs'] = ['GET'];
            }

            $routes = $this->getRouteDefinitions($class, $attribute['method_name'], $args);
            foreach ($routes as $route) {
                $method_routes[] = $route;
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
