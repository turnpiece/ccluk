<?php

namespace Beehive\GuzzleHttp\Stream;

/**
 * Stream decorator that prevents a stream from being seeked
 */
class NoSeekStream implements \Beehive\GuzzleHttp\Stream\StreamInterface
{
    use StreamDecoratorTrait;
    public function seek($offset, $whence = \SEEK_SET)
    {
        return \false;
    }
    public function isSeekable()
    {
        return \false;
    }
    public function attach($stream)
    {
        $this->stream->attach($stream);
    }
}