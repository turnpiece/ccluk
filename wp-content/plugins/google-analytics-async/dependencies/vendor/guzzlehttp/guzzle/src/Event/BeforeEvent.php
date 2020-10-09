<?php

namespace Beehive\GuzzleHttp\Event;

use Beehive\GuzzleHttp\Message\ResponseInterface;
/**
 * Event object emitted before a request is sent.
 *
 * This event MAY be emitted multiple times (i.e., if a request is retried).
 * You MAY change the Response associated with the request using the
 * intercept() method of the event.
 */
class BeforeEvent extends \Beehive\GuzzleHttp\Event\AbstractRequestEvent
{
    /**
     * Intercept the request and associate a response
     *
     * @param ResponseInterface $response Response to set
     */
    public function intercept(\Beehive\GuzzleHttp\Message\ResponseInterface $response)
    {
        $this->transaction->response = $response;
        $this->transaction->exception = null;
        $this->stopPropagation();
    }
}