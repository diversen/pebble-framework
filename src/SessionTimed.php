<?php

declare(strict_types=1);

namespace Pebble;

class SessionTimed
{
    public string $sessionKey = 'session_timed';

    /**
     * Set as session value with key, value, and a max time that the session value will exist
     * @param mixed $value
     * @param int $max_time
     */
    public function setValue(string $key, $value, $max_time): void
    {
        $_SESSION[$this->sessionKey][$key] = ['time' => time() + $max_time, 'value' => $value];
    }

    /**
     * Get a session value på key
     * @return mixed 
     * 
     */
    public function getValue(string $key)
    {
        $value = $_SESSION[$this->sessionKey][$key] ?? null;
        if ($value) {
            if (time() < $value['time']) {
                return $value['value'];
            }
            unset($_SESSION[$this->sessionKey][$key]);
        }
    }
}
