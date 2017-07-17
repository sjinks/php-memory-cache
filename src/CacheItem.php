<?php

namespace WildWolf\MemoryCache;

class CacheItem implements \Psr\Cache\CacheItemInterface
{
    /**
     * @var string
     */
    private $key;

    /**
     * @var mixed
     */
    private $value;

    /**
     * @var bool
     */
    private $hit;

    /**
     * @var \DateTimeInterface
     */
    private $expires;

    /**
     * @param string $key
     */
    public function __construct($key)
    {
        $this->key     = $key;
        $this->value   = null;
        $this->hit     = false;
        $this->expires = null;
    }

    /**
     * @internal
     * @return \DateTimeInterface
     */
    public function expires()
    {
        return $this->expires;
    }

    /**
     * @internal
     * @param bool $v
     * @return \WildWolf\MemoryCache\CacheItem
     */
    public function setIsHit($v)
    {
        if ($v !== $this->hit) {
            $this->hit = $v;
            if (!$v) {
                $this->value = null;
            }
        }

        return $this;
    }

    /**
     * Returns the key for the current cache item.
     *
     * The key is loaded by the Implementing Library, but should be available to
     * the higher level callers when needed.
     *
     * @return string
     *   The key string for this cache item.
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * Retrieves the value of the item from the cache associated with this object's key.
     *
     * The value returned must be identical to the value originally stored by set().
     *
     * If isHit() returns false, this method MUST return null. Note that null
     * is a legitimate cached value, so the isHit() method SHOULD be used to
     * differentiate between "null value was found" and "no value was found."
     *
     * @return mixed
     *   The value corresponding to this cache item's key, or null if not found.
     */
    public function get()
    {
        return $this->value;
    }

    /**
     * Confirms if the cache item lookup resulted in a cache hit.
     *
     * Note: This method MUST NOT have a race condition between calling isHit()
     * and calling get().
     *
     * @return bool
     *   True if the request resulted in a cache hit. False otherwise.
     */
    public function isHit()
    {
        return $this->hit;
    }

    /**
     * Sets the value represented by this cache item.
     *
     * The $value argument may be any item that can be serialized by PHP,
     * although the method of serialization is left up to the Implementing
     * Library.
     *
     * @param mixed $value
     *   The serializable value to be stored.
     *
     * @return static
     *   The invoked object.
     */
    public function set($value)
    {
        $this->value = $value;
        return $this;
    }

    /**
     * Sets the expiration time for this cache item.
     *
     * @param \DateTimeInterface|null $expiration
     *   The point in time after which the item MUST be considered expired.
     *   If null is passed explicitly, a default value MAY be used. If none is set,
     *   the value should be stored permanently or for as long as the
     *   implementation allows.
     *
     * @return static
     *   The called object.
     */
    public function expiresAt($expiration)
    {
        $this->expires = ($expiration instanceof \DateTimeInterface) ? $expiration : null;
        return $this;
    }

    /**
     * Sets the expiration time for this cache item.
     *
     * @param int|\DateInterval|null $time
     *   The period of time from the present after which the item MUST be considered
     *   expired. An integer parameter is understood to be the time in seconds until
     *   expiration. If null is passed explicitly, a default value MAY be used.
     *   If none is set, the value should be stored permanently or for as long as the
     *   implementation allows.
     *
     * @return static
     *   The called object.
     */
    public function expiresAfter($time)
    {
        if ($time instanceof \DateInterval) {
            $expires = new \DateTime();
            $expires->add($time);
            $this->expires = $expires;
        } elseif (is_numeric($time)) {
            $expires = new \DateTime('now +' . $time . ' seconds');
            $this->expires = $expires;
        } else {
            $this->expires = null;
        }

        return $this;
    }

    public function __clone()
    {
        if (is_object($this->value)) {
            $this->value = clone $this->value;
        }
    }
}
