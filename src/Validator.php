<?php

namespace WildWolf\Cache;

abstract class Validator
{
    public static function validateKey($key)
    {
        static $disallowed = '{}()/\@:';

        if (!is_string($key) || $key === '' || false !== strpbrk($key, $disallowed)) {
            throw new \WildWolf\Cache\InvalidArgumentException();
        }
    }

    public static function validateTtl($ttl)
    {
        if (!is_int($ttl) && null !== $ttl && !($ttl instanceof \DateInterval)) {
            throw new \WildWolf\Cache\InvalidArgumentException();
        }
    }

    public static function validateTraversable($v)
    {
        if (!is_array($v) && !($v instanceof \Traversable)) {
            throw new \WildWolf\Cache\InvalidArgumentException();
        }
    }
}
