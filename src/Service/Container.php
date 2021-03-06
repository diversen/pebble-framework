<?php

declare(strict_types=1);

namespace Pebble\Service;

use Exception;

/**
 * Class Container - Container for services
 */
class Container
{
    /**
     * @var array<object>
     */
    public static $services = [];

    /**
     * @param string $name
     * @param object $service
     */
    public function set(string $name, $service): void
    {
        self::$services[$name] = $service;
    }

    public function has(string $name): bool
    {
        return isset(self::$services[$name]);
    }

    /**
     * @param string $name
     * @return mixed
     * @throws Exception
     */
    public function get(string $name)
    {
        if (isset(self::$services[$name])) {
            return self::$services[$name];
        }
    }

    public function unsetAll(): void
    {
        self::$services = [];
    }
}
