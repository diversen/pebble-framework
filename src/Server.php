<?php

declare(strict_types=1);

namespace Pebble;

use InvalidArgumentException;
use Pebble\Service\ConfigService;

class Server
{
    private string $server_url;

    public function __construct(?string $server_url = null)
    {
        $server_url ??= (string)(new ConfigService())->getConfig()->get('App.server_url');
        $parts = parse_url($server_url);
        if (!is_array($parts) || !isset($parts['scheme'], $parts['host']) || !in_array($parts['scheme'], ['http', 'https'], true)) {
            throw new InvalidArgumentException('App.server_url must be a valid HTTP(S) origin');
        }

        $this->server_url = $parts['scheme'] . '://' . $parts['host'];
        if (isset($parts['port'])) {
            $this->server_url .= ':' . $parts['port'];
        }
    }

    public function isSecure(): bool
    {
        return str_starts_with($this->server_url, 'https://');
    }

    /**
     * Get scheme and host $_SERVER variables
     * e.g. http://localhost:8000
     */
    public function getSchemeAndHost(): string
    {
        return $this->server_url;
    }
}
