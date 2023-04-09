<?php

declare(strict_types=1);

namespace Pebble\Router;

#[\AllowDynamicProperties]
class Request
{
    /**
     * @var array<string>
     */
    private $params = [];

    /**
     * @var string|null
     */
    private $current_route = null;

    /**
     * @param array<string> $params
     */
    public function __construct(array $params)
    {
        $this->params = $params;
    }

    /**
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function param(string $key, $default = null)
    {
        return $this->params[$key] ?? $default;
    }

    /**
     * @return array<mixed>
     */
    public function allParams(): array
    {
        return $this->params;
    }

    public function hasParam(string $key): bool
    {
        return isset($this->params[$key]);
    }

    public function setCurrentRoute(string $route): void
    {
        $this->current_route = $route;
    }

    public function getCurrentRoute(): ?string
    {
        return $this->current_route;
    }
}
