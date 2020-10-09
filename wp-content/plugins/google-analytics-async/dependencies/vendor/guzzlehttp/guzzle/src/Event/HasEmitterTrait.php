<?php

namespace Beehive\GuzzleHttp\Event;

/**
 * Trait that implements the methods of HasEmitterInterface
 */
trait HasEmitterTrait
{
    /** @var EmitterInterface */
    private $emitter;
    public function getEmitter()
    {
        if (!$this->emitter) {
            $this->emitter = new \Beehive\GuzzleHttp\Event\Emitter();
        }
        return $this->emitter;
    }
}