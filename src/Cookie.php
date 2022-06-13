<?php

declare(strict_types=1);

namespace Pebble;

class Cookie {

    public function __construct(array $settings) {
        $this->settings = $settings;
    }

    /**
     * Set a cookie
     * If time is 0 then the it will be a session cookie
     */
    public function setCookie(string $key, string $value, int $time = 0) {

        $path = $this->settings['cookie_path'];
        $domain = $this->settings['cookie_domain'];
        $secure = $this->settings['cookie_secure'];
        $http_only = $this->settings['cookie_http'];

        if ($time) {
            $time = time() + $time;
        }

        // Little hack for unit testing
        if ($this->isCli()) {
            $_COOKIE[$key] = $value;
            return true;
        }

        return setcookie($key, $value, $time, $path, $domain, $secure, $http_only);
    
    }

    private function isCli()
    {
        if (php_sapi_name() === 'cli') {
            return true;
        }
        return false;
    }

}