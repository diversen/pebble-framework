<?php

declare(strict_types=1);

namespace Pebble;

/**
 * A class to store data on a single script execution
 * E.g. for storing data for display in a template
 */
class Data {

    public array $data = [];

    public function setData (string $key, mixed $value) {
        $this->data[$key] = $value;
    }

    public function getData(string $key): mixed {
        return $this->data[$key] ?? null;
    }
}
