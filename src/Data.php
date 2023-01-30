<?php

declare(strict_types=1);

namespace Pebble;

/**
 * A class to store data on a single script execution
 * E.g. for storing data for display in a template
 * 
 * setData() and getData() is used for storing single values
 * setArrayData() and getArrayData() is used for storing arrays of data
 * 
 */
class Data {

    /**
     * @var array<mixed>
     */
    public array $data = [];

    public function setData (string $key, mixed $value):void {
        $this->data[$key] = $value;
    }

    public function getData(string $key): mixed {
        return $this->data[$key] ?? null;
    }

    public function hasData(string $key): bool {
        return isset($this->data[$key]);
    }

    public function setArrayData(string $key, mixed $value): void {
        $this->data[$key][] = $value;
    }

    /**
     * @return array<mixed>
     */
    public function getArrayData(string $key): array {
        return $this->data[$key] ?? [];
    }

    public function hasArrayData(string $key): bool {
        return isset($this->data[$key]) && is_array($this->data[$key]);
    }
}
