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

    /**
     * @var array<mixed>
     */
    private array $ary = [];

    /**
     * @param array<mixed> $ary
     */
    public function __construct(array $ary)
    {
        $this->ary = $ary;
        $this->dimensions = $this->getArrayDimensions($ary);
        if ($this->dimensions === 1) {
            $this->ary = [$this->ary];
        }
    }

    /**
     * Get array from $new_columns. Sort according to $new_columns. Update keys to $new_columns.
     * @param array<mixed> $new_columns
     */
    public function alterColumns(array $new_columns): void
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
     * @param array<mixed> $ary
     */
    private function getArrayDimensions(array $ary): int
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
     * named $columns[0] and $columns[1]
     * @param array<mixed> $columns
     */
    public function oneToTwoDimensions(array $columns): void
    {
        if ($this->dimensions !== 1) {
            throw new Exception("Array must be one dimensional");
        }

        $ary = $this->ary[0];
        $ary_ret = [];
        foreach ($ary as $key => $value) {
            $ary_ret[] = [$columns[0] => $key, $columns[1] => $value];
        }
        $this->ary = [$ary_ret];
    }

    /**
     * Method that adds a column (or replace an existing) to a two dimensional array
     * using a callback function that works on the row
     */
    public function addColumnCallback(string $column, callable $callback): void
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
     * @param array<mixed> $columns
     */
    public function addColumnCallbackMultiple(array $columns, callable $callback): void
    {
        foreach ($columns as $column) {
            $this->addColumnCallback($column, $callback);
        }
    }

    /**
     * Run a callback on the spcified columns (array) or column (string)
     * @param string|array<mixed> $columns
     */
    public function addCallbackOnValue(array|string $columns, callable $callback): void
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
     * @return array<mixed>
     */
    public function getArray(): array
    {
        if ($this->dimensions === 1) {
            return $this->ary[0];
        }
        return $this->ary;
    }
}
