<?php

namespace Pebble\Interfaces;

interface RouteParser
{
    /**
     * Get routes from class
     * @return array<mixed>
     */
    public function getRoutes(string $class);

    /**
     * Alter Params from route
     * @param array<mixed> $route
     * @return array<mixed>
     */
    public function getParams(array $route);
}
