<?php

class Psr6MemoryCacheTest extends \Cache\IntegrationTests\CachePoolTest
{
    public function createCachePool()
    {
        return \WildWolf\Psr6MemoryCache::instance();
    }
}
