<?php

namespace WildWolf;

class Psr16MemoryCache implements \Psr\SimpleCache\CacheInterface
{
    private $cache = [];

    private static function validateKey($key)
    {
        static $disallowed = '{}()/\@:';

        if (!is_string($key) || $key === '' || false !== strpbrk($key, $disallowed)) {
            throw new \WildWolf\Cache\InvalidArgumentException();
        }
    }

    private static function validateTtl($ttl)
    {
        if (!is_int($ttl) && null !== $ttl && !($ttl instanceof \DateInterval)) {
            throw new \WildWolf\Cache\InvalidArgumentException();
        }
    }

    private static function validateTraversable($v)
    {
        if (!is_array($v) && !($v instanceof \Traversable)) {
            throw new \WildWolf\Cache\InvalidArgumentException();
        }
    }

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
     * Fetches a value from the cache.
     *
     * @param string $key     The unique key of this item in the cache.
     * @param mixed  $default Default value to return if the key does not exist.
     *
     * @return mixed The value of the item from the cache, or $default in case of cache miss.
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     *   MUST be thrown if the $key string is not a legal value.
     */
    public function get($key, $default = null)
    {
        self::validateKey($key);

        if (isset($this->cache[$key])) {
            list($data, $expires) = $this->cache[$key];

            if (null === $expires || (new \DateTime()) < $expires) {
                return $data;
            }

            unset($this->cache[$key]);
        }

        return $default;
    }

    /**
     * Persists data in the cache, uniquely referenced by a key with an optional expiration TTL time.
     *
     * @param string                $key   The key of the item to store.
     * @param mixed                 $value The value of the item to store, must be serializable.
     * @param null|int|DateInterval $ttl   Optional. The TTL value of this item. If no value is sent and
     *                                     the driver supports TTL then the library may set a default value
     *                                     for it or let the driver take care of that.
     *
     * @return bool True on success and false on failure.
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     *   MUST be thrown if the $key string is not a legal value.
     */
    public function set($key, $value, $ttl = null)
    {
        self::validateKey($key);
        self::validateTtl($ttl);

        if ($ttl instanceof \DateInterval) {
            $expires = new \DateTime();
            $expires->add($ttl);
        } elseif (is_numeric($ttl)) {
            $expires = new \DateTime('now +' . $ttl . ' seconds');
        } else {
            $expires = null;
        }

        if (is_object($value)) {
            $value = clone $value;
        }

        $this->cache[$key] = [$value, $expires];
        return true;
    }

    /**
     * Delete an item from the cache by its unique key.
     *
     * @param string $key The unique cache key of the item to delete.
     *
     * @return bool True if the item was successfully removed. False if there was an error.
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     *   MUST be thrown if the $key string is not a legal value.
     */
    public function delete($key)
    {
        self::validateKey($key);
        unset($this->cache[$key]);
        return true;
    }

    /**
     * Wipes clean the entire cache's keys.
     *
     * @return bool True on success and false on failure.
     */
    public function clear()
    {
        $this->cache = [];
        return true;
    }

    /**
     * Obtains multiple cache items by their unique keys.
     *
     * @param iterable $keys    A list of keys that can obtained in a single operation.
     * @param mixed    $default Default value to return for keys that do not exist.
     *
     * @return iterable A list of key => value pairs. Cache keys that do not exist or are stale will have $default as value.
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     *   MUST be thrown if $keys is neither an array nor a Traversable,
     *   or if any of the $keys are not a legal value.
     */
    public function getMultiple($keys, $default = null)
    {
        self::validateTraversable($keys);

        $result = [];
        foreach ($keys as $key) {
            $result[$key] = $this->get($key, $default);
        }

        return $result;
    }

    /**
     * Persists a set of key => value pairs in the cache, with an optional TTL.
     *
     * @param iterable              $values A list of key => value pairs for a multiple-set operation.
     * @param null|int|DateInterval $ttl    Optional. The TTL value of this item. If no value is sent and
     *                                      the driver supports TTL then the library may set a default value
     *                                      for it or let the driver take care of that.
     *
     * @return bool True on success and false on failure.
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     *   MUST be thrown if $values is neither an array nor a Traversable,
     *   or if any of the $values are not a legal value.
     */
    public function setMultiple($values, $ttl = null)
    {
        self::validateTraversable($values);
        self::validateTtl($ttl);

        $result = true;
        foreach ($values as $key => $value) {
            if (is_int($key)) {
                $key = (string)$key;
            }

            $result = $result && $this->set($key, $value, $ttl);
        }

        return $result;
    }

    /**
     * Deletes multiple cache items in a single operation.
     *
     * @param iterable $keys A list of string-based keys to be deleted.
     *
     * @return bool True if the items were successfully removed. False if there was an error.
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     *   MUST be thrown if $keys is neither an array nor a Traversable,
     *   or if any of the $keys are not a legal value.
     */
    public function deleteMultiple($keys)
    {
        self::validateTraversable($keys);

        $result = true;
        foreach ($keys as $key) {
            $result = $result && $this->delete($key);
        }

        return $result;
    }

    /**
     * Determines whether an item is present in the cache.
     *
     * NOTE: It is recommended that has() is only to be used for cache warming type purposes
     * and not to be used within your live applications operations for get/set, as this method
     * is subject to a race condition where your has() will return true and immediately after,
     * another script can remove it making the state of your app out of date.
     *
     * @param string $key The cache item key.
     *
     * @return bool
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     *   MUST be thrown if the $key string is not a legal value.
     */
    public function has($key)
    {
        self::validateKey($key);

        if (isset($this->cache[$key])) {
            list($data, $expires) = $this->cache[$key];

            if (null === $expires || (new \DateTime()) < $expires) {
                return true;
            }

            unset($this->cache[$key]);
        }

        return false;
    }
}
