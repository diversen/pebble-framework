<?php

namespace Pebble\Attributes;

#[\Attribute]
class Route {

    /**
     * @param array<string> $verbs
     * @param array<string,string> $cast
     */
    public function __construct(
        string $path,
        array $verbs,
        array $cast = []
    ) {
        $this->path = $path;
        $this->verbs = $verbs;
        $this->cast = $cast;
    }

    
    public string $path;
    
    /**
     * @var array<mixed>
     */
    public array $verbs;

    /**
     * @var array<string,string> $cast
     */
    public array $cast;
}