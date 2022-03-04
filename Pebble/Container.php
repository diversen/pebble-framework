<?php

namespace Pebble;

class Container {

    private $objects = [];
    public function setObject($name, $object) {
        $objects['name'] = $object;
    }

    public function getObject($name, $object) {
        return $this->objects[$name];
    }
}