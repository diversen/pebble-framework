<?php

declare(strict_types=1);

namespace Pebble;

class Server
{
    public function isSecure(): bool
    {
        if ($_SERVER['SERVER_PORT'] == 443) {
            return true;
        }
        return false;
    }

    /**
     * Get scheme and host $_SERVER variables
     * e.g. http://localhost:8000
     */
    public function getSchemeAndHost(): string
    {
        if (!$this->isSecure()) {
            $scheme = 'http://';
        } else {
            $scheme = 'https://';
        }

        $url = $scheme . $_SERVER['SERVER_NAME'];
        if ($_SERVER['SERVER_PORT'] !== '80' && $_SERVER['SERVER_PORT'] !== '443') {
            $url .= ':' . $_SERVER['SERVER_PORT'];
        }
        return $url;
    }
}
