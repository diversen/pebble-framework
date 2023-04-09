<?php

declare(strict_types=1);

namespace Pebble;

use ReflectionClass;

class AttributeParser
{
    /**
     * @param class-string $class_name
     * @return array<mixed>
     */
    public static function parseAttributes(string $class_name): array
    {
        $reflection_class = new ReflectionClass($class_name);
        $methods = $reflection_class->getMethods();
        $attributes = [];

        foreach ($methods as $method) {
            $attribute_ary = [];
            $method_name = $method->getName();


            $attr = $method->getAttributes();
            $attribute_ary['method_name'] = $method_name;
            if (empty($attr)) {
                continue;
            }

            foreach ($attr as $attribute) {
                $attr_name = $attribute->getName();
                $attribute_ary['attribute_name'] = $attr_name;

                $args = $attribute->getArguments();
                $attribute_ary['arguments'] = $args;
            }

            $attributes[] = $attribute_ary;
        }

        return $attributes;
    }
}
