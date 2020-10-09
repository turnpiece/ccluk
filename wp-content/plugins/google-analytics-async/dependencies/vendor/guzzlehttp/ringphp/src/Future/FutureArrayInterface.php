<?php

namespace Beehive\GuzzleHttp\Ring\Future;

/**
 * Future that provides array-like access.
 */
interface FutureArrayInterface extends \Beehive\GuzzleHttp\Ring\Future\FutureInterface, \ArrayAccess, \Countable, \IteratorAggregate
{
}