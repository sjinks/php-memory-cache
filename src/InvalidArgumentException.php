<?php

namespace WildWolf\Cache;

class InvalidArgumentException extends \Exception implements \Psr\SimpleCache\InvalidArgumentException, \Psr\Cache\InvalidArgumentException
{
}
