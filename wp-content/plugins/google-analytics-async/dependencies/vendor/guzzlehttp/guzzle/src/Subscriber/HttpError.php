<?php

namespace Beehive\GuzzleHttp\Subscriber;

use Beehive\GuzzleHttp\Event\CompleteEvent;
use Beehive\GuzzleHttp\Event\RequestEvents;
use Beehive\GuzzleHttp\Event\SubscriberInterface;
use Beehive\GuzzleHttp\Exception\RequestException;
/**
 * Throws exceptions when a 4xx or 5xx response is received
 */
class HttpError implements \Beehive\GuzzleHttp\Event\SubscriberInterface
{
    public function getEvents()
    {
        return ['complete' => ['onComplete', \Beehive\GuzzleHttp\Event\RequestEvents::VERIFY_RESPONSE]];
    }
    /**
     * Throw a RequestException on an HTTP protocol error
     *
     * @param CompleteEvent $event Emitted event
     * @throws RequestException
     */
    public function onComplete(\Beehive\GuzzleHttp\Event\CompleteEvent $event)
    {
        $code = (string) $event->getResponse()->getStatusCode();
        // Throw an exception for an unsuccessful response
        if ($code[0] >= 4) {
            throw \Beehive\GuzzleHttp\Exception\RequestException::create($event->getRequest(), $event->getResponse());
        }
    }
}