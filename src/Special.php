<?php

declare(strict_types=1);

namespace Pebble;

class Special
{
    /**
     * Encode html special char on an array
     * It will only encode strings and numeric values
     * Objects will keep value
     * @param array<mixed> $values
     * @return array<mixed> $values
     */
    public static function encodeAry(array $values): array
    {
        foreach ($values as $key => $val) {
            if (is_array($val)) {
                $values[$key] = self::encodeAry($val);
            } else {
                $values[$key] = self::encodeStr($val);
            }
        }

        return $values;
    }

    /**
     * htmlspecialchars.
     * Any other values will just be returned
     * @param mixed $value
     * @return mixed $value
     */
    public static function encodeStr($value)
    {

        // Convert numeric values to strings
        if (is_numeric($value)) {
            $value = strval($value);
        }


        if (is_string($value)) {
            return htmlspecialchars($value, ENT_COMPAT, 'UTF-8');
        }

        return $value;
    }

    /**
     * Decode a string
     */
    public static function decodeStr(string $str): string
    {
        return htmlspecialchars_decode($str, ENT_COMPAT);
    }
}
