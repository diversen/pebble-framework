<?php

namespace Pebble;

class SessionTimed {

    public $sessionKey = 'session_timed';

    /**
     * Set as session value with key, value, and a max time that the session value will exist
     */
    public function setValue($key, $value, $max_time) {
        $_SESSION[$this->sessionKey][$key] = ['time' => time() + $max_time, 'value' => $value];
    }

    /**
     * Get a session value på key
     */
    public function getValue($key) {

        $value = $_SESSION[$this->sessionKey][$key] ?? null;
        if ($value) {
            if(time() < $value['time']) {
                return $value['value'];
            }
            unset($_SESSION[$this->sessionKey][$key]);
        }
    }
}
