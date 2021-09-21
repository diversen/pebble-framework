<?php

namespace Pebble;

class Autoloader {

    public function setPath($path) {

        spl_autoload_register(function($class_name) use ($path)
        {

            $class_path = $path . '/' . str_replace("\\", '/', $class_name) . '.php';

            if (file_exists($class_path)) {
                require_once $class_path;
            } else {
                
                echo "The autoloader could not load ($class_name). Path: $class_path";
                exit(255);
            }
        });
    }
}