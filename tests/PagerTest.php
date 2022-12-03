<?php

declare(strict_types=1);

use Pebble\Pager;
use PHPUnit\Framework\TestCase;

final class PagerTest extends TestCase
{
    public function test_getBasePath(): void
    {
        $_GET['page'] = '2';
        $pager = new Pager(101, 10, 'page');

        $this->assertEquals($pager->page, 2);
        $this->assertEquals($pager->limit, 10);
        $this->assertEquals($pager->offset, 10);
        $this->assertEquals($pager->next, 3);
        $this->assertEquals($pager->has_next, true);
        $this->assertEquals($pager->prev, 1);
        $this->assertEquals($pager->has_prev, true);
        $this->assertEquals($pager->num_pages, 11);

    }
}
