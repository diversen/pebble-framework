<?php

declare(strict_types=1);

namespace Pebble\Router;

class Request
{
    private $params = [];
    private $current_route = null;
    public function __construct(array $params)
    {
        $this->params = $params;
    }

    public function param(string $key, $default = null)
    {
        return $this->params[$key] ?? $default;
    }

    public function allParams(): array
    {
        return $this->params;
    }

    public function hasParam(string $key): bool
    {
        return isset($this->params[$key]);
    }

    public function setCurrentRoute(string $route)
    {
        $this->current_route = $route;
    }

    public function getCurrentRoute(): ?string
    {
        return $this->current_route;
    }
}
