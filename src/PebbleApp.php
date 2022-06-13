<?php

declare(strict_types=1);

namespace Pebble;

class PebbleApp {

    public $base_path;

    /**
     * Base path is one dir above 'vendor'
     */
    public function __construct() {
        $this->base_path = dirname(dirname(dirname(dirname(__dir__))));
    }
}

