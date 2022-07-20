<?php

declare(strict_types=1);

use Pebble\Service\Container;
use Pebble\Service\DBCacheService;
use PHPUnit\Framework\TestCase;

final class DBCacheTest extends TestCase
{
    private function __setup()
    {
        $container = new Container();
        $container->unsetAll();

    }

    public function test_can_get_instance() {

        $container = new Container();
        $container->unsetAll();
        
        $db_cache = (new DBCacheService())->getDBCache();
        $this->assertInstanceOf(Pebble\DBCache::class, $db_cache);
    }


    public function test_set()
    {
        $this->__setup();
        $cache = (new DBCacheService())->getDBCache();

        $to_cache = ['this is a test'];
        $res = $cache->set('some_key', $to_cache);

        $this->assertEquals(null, $res);
    }

    public function test_get()
    {
        $this->__setup();
        $cache = (new DBCacheService())->getDBCache();

        $to_cache = ['this is a test'];
        $cache->set('some_key', $to_cache);

        // Try to get a result that has expired
        $from_cache = $cache->get('some_key', -1);
        $this->assertEquals(null, $from_cache);

        // No expire
        $from_cache = $cache->get('some_key');
        $this->assertEquals($to_cache, $from_cache);
    }

    public function test_delete()
    {
        $this->__setup();
        $cache = (new DBCacheService())->getDBCache();

        $to_cache = ['this is a test'];
        $cache->set('some_key', $to_cache);

        $cache->delete('some_key');
        $from_cache = $cache->get('some_key');
        $this->assertEquals(null, $from_cache);
    }
}
