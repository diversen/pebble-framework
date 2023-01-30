<?php

declare(strict_types=1);

use Pebble\Data;
use PHPUnit\Framework\TestCase;

final class DataTest extends TestCase
{
    public function test_data(): void
    {
        
        $data = new Data();

        // Single data

        $this->assertEquals(null, $data->getData('test'));

        $data->setData('test', 'test_value');
        $this->assertEquals('test_value', $data->getData('test'));
        $this->assertEquals(null, $data->getData('test2'));
        $this->assertEquals(true, $data->hasData('test'));
        
        // Array data
        $this->assertEquals([], $data->getArrayData('array_test'));
        $data->setArrayData('array_test', 'test_value');
        $this->assertEquals(['test_value'], $data->getArrayData('array_test'));

        $data->setArrayData('array_test', 'test_value2');
        $this->assertEquals(['test_value', 'test_value2'], $data->getArrayData('array_test'));

        $this->assertEquals(true, $data->hasArrayData('array_test'));
        $this->assertEquals(false, $data->hasArrayData('array_test2'));
        


    }


}
