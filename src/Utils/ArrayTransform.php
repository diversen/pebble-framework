<?php

declare(strict_types=1);

namespace Pebble\Utils;

use Exception;

/**
 * Class that transforms an array
 */
class ArrayTransform
{

    private int $dimensions = 0;
    private array $ary = [];

    public function __construct(array $ary)
    {
        $this->copy = $ary;
        $this->ary = $ary;
        $this->dimensions = $this->getArrayDimensions($ary);
        if ($this->dimensions === 1) {
            $this->ary = [$this->ary];
        }
    }

    /**
     * Get array from $new_columns. Sort according to $new_columns. Update keys to $new_columns.
     * @param array $new_columns
     * @return array
     */
    public function alterColumns(array $new_columns)
    {

        $ary = $this->ary;

        $old_columns = array_keys($ary[0]);

        // Remove keys that are not in the $new_columns array
        $cleaned_columns = array_intersect($old_columns, array_keys($new_columns));


        // Sort keys by ordering in $new_columns
        $sorted_columns = array_intersect(array_keys($new_columns), $cleaned_columns);

        // Change the keys in the array to the new keys
        $ary_ret = [];
        foreach ($ary as $value) {
            $ret = [];
            foreach ($sorted_columns as $column) {
                $ret[$new_columns[$column]] = $value[$column];
            }
            $ary_ret[] = $ret;
        }

        $this->ary = $ary_ret;
    }

    /**
     * Get the number of dimensions in an array
     */
    private function getArrayDimensions($ary)
    {
        $max = 1;
        foreach ($ary as $value) {
            if (is_array($value)) {
                $d = $this->getArrayDimensions($value) + 1;
                if ($d > $max) {
                    $max = $d;
                }
            }
        }
        return $max;
    }

    /**
     * Convert a one dimensional array to a two dimensional array with two columns
     * named $keys[0] and $keys[1]
     */
    public function oneToTwoDimensions(array $keys)
    {
        if ($this->dimensions !== 1) {
            throw new Exception("Array must be one dimensional");
        }

        $ary = $this->ary[0];
        $ary_ret = [];
        foreach ($ary as $key => $value) {
            $ary_ret[] = [$keys[0] => $key, $keys[1] => $value];
        }
        $this->ary = [$ary_ret];
    }

    /** 
     * Method that adds a column (or replace an existing) to a two dimensional array 
     * using a callback function that works on the row
     */
    public function addColumnCallback(string $column, callable $callback)
    {

        $ary = $this->ary;
        $ary_ret = [];
        foreach ($ary as $row) {
            $row[$column] = $callback($row);
            $ary_ret[] = $row;
        }

        $this->ary = $ary_ret;
    }

    /**
     * Method that adds a column callback to multiple columns
      
     */
    public function addColumnCallbackMultiple(array $columns, callable $callback)
    {
        foreach ($columns as $column) {
            $this->addColumnCallback($column, $callback);
        }
    }

    /**
     * Run a callback on the values of the keys in $keys
     */
    public function addCallbackOnValue(array|string $columns, callable $callback)
    {
        $ary = $this->ary;

        if (!is_array($columns)) {
            $columns = [$columns];
        }


        $ary_ret = [];
        foreach ($ary as $row) {
            $ret = [];
            foreach ($row as $key => $value) {
                if (in_array($key, $columns)) {
                    $ret[$key] = $callback($value);
                } else {
                    $ret[$key] = $value;
                }
            }
            $ary_ret[] = $ret;
        }

        $this->ary = $ary_ret;
    }

    /**
     * Return the array
     */
    public function getArray()
    {
        if ($this->dimensions === 1) {
            return $this->ary[0];
        }
        return $this->ary;
    }
}
