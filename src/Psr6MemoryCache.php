<?php

namespace WildWolf;

class Psr6MemoryCache implements \Psr\Cache\CacheItemPoolInterface
{
    private $cache = [];

    public static function instance()
    {
        static $self = null;

        if (!$self) {
            $self = new self();
        }

        return $self;
    }

    private function __construct()
    {
    }

    /**
     * Returns a Cache Item representing the specified key.
     *
     * This method must always return a CacheItemInterface object, even in case of
     * a cache miss. It MUST NOT return null.
     *
     * @param string $key
     *   The key for which to return the corresponding Cache Item.
     *
     * @throws \Psr\Cache\InvalidArgumentException
     *   If the $key string is not a legal value a \Psr\Cache\InvalidArgumentException
     *   MUST be thrown.
     *
     * @return \Psr\Cache\CacheItemInterface
     *   The corresponding Cache Item.
     */
    public function getItem($key)
    {
        \WildWolf\Cache\Validator::validateKey($key);

        if (isset($this->cache[$key])) {
            list($data, $expires) = $this->cache[$key];

            $item = new \WildWolf\MemoryCache\CacheItem($key);
            if (null === $expires || (new \DateTime()) < $expires) {
                $item->setIsHit(true);
                $item->set(is_object($data) ? clone $data : $data);
                $item->expiresAt($expires);
                return $item;
            }

            unset($this->cache[$key]);
        }

        return new \WildWolf\MemoryCache\CacheItem($key);
    }

    /**
     * Returns a traversable set of cache items.
     *
     * @param string[] $keys
     *   An indexed array of keys of items to retrieve.
     *
     * @throws \Psr\Cache\InvalidArgumentException
     *   If any of the keys in $keys are not a legal value a \Psr\Cache\InvalidArgumentException
     *   MUST be thrown.
     *
     * @return array|\Traversable
     *   A traversable collection of Cache Items keyed by the cache keys of
     *   each item. A Cache item will be returned for each key, even if that
     *   key is not found. However, if no keys are specified then an empty
     *   traversable MUST be returned instead.
     */
    public function getItems(array $keys = array())
    {
        $result = [];

        foreach ($keys as $key) {
            $result[$key] = $this->getItem($key);
        }

        return $result;
    }

    /**
     * Confirms if the cache contains specified cache item.
     *
     * Note: This method MAY avoid retrieving the cached value for performance reasons.
     * This could result in a race condition with CacheItemInterface::get(). To avoid
     * such situation use CacheItemInterface::isHit() instead.
     *
     * @param string $key
     *   The key for which to check existence.
     *
     * @throws \Psr\Cache\InvalidArgumentException
     *   If the $key string is not a legal value a \Psr\Cache\InvalidArgumentException
     *   MUST be thrown.
     *
     * @return bool
     *   True if item exists in the cache, false otherwise.
     */
    public function hasItem($key)
    {
        $item = $this->getItem($key);
        return $item->isHit();
    }

    /**
     * Deletes all items in the pool.
     *
     * @return bool
     *   True if the pool was successfully cleared. False if there was an error.
     */
    public function clear()
    {
        $this->cache = [];
        return true;
    }

    /**
     * Removes the item from the pool.
     *
     * @param string $key
     *   The key to delete.
     *
     * @throws InvalidArgumentException
     *   If the $key string is not a legal value a \Psr\Cache\InvalidArgumentException
     *   MUST be thrown.
     *
     * @return bool
     *   True if the item was successfully removed. False if there was an error.
     */
    public function deleteItem($key)
    {
        \WildWolf\Cache\Validator::validateKey($key);
        unset($this->cache[$key]);
        return true;
    }

    /**
     * Removes multiple items from the pool.
     *
     * @param string[] $keys
     *   An array of keys that should be removed from the pool.
     *
     * @throws \Psr\Cache\InvalidArgumentException
     *   If any of the keys in $keys are not a legal value a \Psr\Cache\InvalidArgumentException
     *   MUST be thrown.
     *
     * @return bool
     *   True if the items were successfully removed. False if there was an error.
     */
    public function deleteItems(array $keys)
    {
        foreach ($keys as $key) {
            $this->deleteItem($key);
        }

        return true;
    }

    /**
     * Persists a cache item immediately.
     *
     * @param \Psr\Cache\CacheItemInterface $item
     *   The cache item to save.
     *
     * @return bool
     *   True if the item was successfully persisted. False if there was an error.
     */
    public function save(\Psr\Cache\CacheItemInterface $item)
    {
        $key = $item->getKey();
        $val = $item->get();
        if (is_object($val)) {
            $val = clone $val;
        }

        if (!($item instanceof \WildWolf\MemoryCache\CacheItem)) {
            $expires = null;
        } else {
            $expires = $item->expires();
        }

        $this->cache[$key] = [$val, $expires];
        return true;
    }

    /**
     * Sets a cache item to be persisted later.
     *
     * @param \Psr\Cache\CacheItemInterface $item
     *   The cache item to save.
     *
     * @return bool
     *   False if the item could not be queued or if a commit was attempted and failed. True otherwise.
     */
    public function saveDeferred(\Psr\Cache\CacheItemInterface $item)
    {
        return $this->save($item);
    }

    /**
     * Persists any deferred cache items.
     *
     * @return bool
     *   True if all not-yet-saved items were successfully saved or there were none. False otherwise.
     */
    public function commit()
    {
        return true;
    }
}
