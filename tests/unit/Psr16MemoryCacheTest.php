<?php

class Psr16MemoryCacheTest extends \Cache\IntegrationTests\SimpleCacheTest
{
    public function createSimpleCache()
    {
        return \WildWolf\Psr16MemoryCache::instance();
    }
}
