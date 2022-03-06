<?php

declare(strict_types=1);

namespace Pebble;

class App
{
    public function isSecure()
    {
        return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443;
    }

    /**
     * Get scheme and host from App config file
     */
    public function getSchemeAndHost(): string
    {

        if(!$this->isSecure()) {
            $scheme = 'http://';
        } else {
            $scheme = 'https://';
        }

        $url = $scheme . $_SERVER['SERVER_NAME'];
        if($_SERVER['SERVER_PORT'] !== '80' && $_SERVER['SERVER_PORT'] !== '443') {
            $url .= ':' . $_SERVER['SERVER_PORT'];
        } 
        return $url;
    }
}
