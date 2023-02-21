<?php

declare(strict_types=1);

namespace Pebble\Router;

use Pebble\Router\Utils;
use ReflectionClass;
use ReflectionMethod;
use Pebble\Interfaces\RouteParser;

class DocBlock implements RouteParser
{
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
     * @param array<mixed> $parsed_doc
     * @return array<mixed>
     */
    private function getRouteDefinitions(string $class, string $method_name, array $parsed_doc): array
    {
        $definitions = [];
        if (isset($parsed_doc['route']) && isset($parsed_doc['verbs'])) {
            $route = $parsed_doc['route'];
            $verbs = explode(',', $parsed_doc['verbs']);
            foreach ($verbs as $verb) {
                $verb = trim($verb);
                $route = [
                    'verb' => $verb,
                    'route' => $route,
                    'class' => $class,
                    'method' => $method_name,
                    'parts' => Utils::getUrlParts($parsed_doc['route']),
                    'parsed_doc' => $parsed_doc,
                ];

                $definitions[] = $route;
            }
        }
        return $definitions;
    }

    /**
     * @param class-string $class
     */
    public function getRoutes(string $class)
    {

        $reflector = new ReflectionClass($class);
        $methods = $reflector->getMethods(ReflectionMethod::IS_PUBLIC);
        $method_routes = [];

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

            $routes = $this->getRouteDefinitions($class, $method_name, $parsed_doc);
            foreach ($routes as $route) {
                $method_routes[] = $route;
            }
        }

        return $method_routes;
    }

    /**
     * cast params, e.g.: @cast int:id,float:price
     * @param string $cast
     * @param array<string> $params
     * @return array<mixed>
     */
    private function castParams(string $cast, array $params): array
    {
        $cast = explode(',', $cast);
        $cast = array_map('trim', $cast);

        foreach ($cast as $cast_item) {
            $cast_item = explode(':', $cast_item);
            $cast_item = array_map('trim', $cast_item);
            $new_params[$cast_item[1]] = $cast_item[0];
        }

        $cast = [
            'int' => 'intval',
            'float' => 'floatval',
            'string' => 'strval',
            'bool' => 'boolval',
        ];

        foreach ($params as $key => $value) {
            if (isset($new_params[$key])) {
                $params[$key] = $cast[$new_params[$key]]($value);
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
        $cast = $route['parsed_doc']['cast'] ?? null;
        if ($cast) {
            $route['params'] = $this->castParams($cast, $route['params']);
        }

        $params = $route['params'];
        return $params;
    }
}
