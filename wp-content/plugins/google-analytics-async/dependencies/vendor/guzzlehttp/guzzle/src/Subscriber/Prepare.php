<?php

namespace Beehive\GuzzleHttp\Subscriber;

use Beehive\GuzzleHttp\Event\BeforeEvent;
use Beehive\GuzzleHttp\Event\RequestEvents;
use Beehive\GuzzleHttp\Event\SubscriberInterface;
use Beehive\GuzzleHttp\Message\AppliesHeadersInterface;
use Beehive\GuzzleHttp\Message\RequestInterface;
use Beehive\GuzzleHttp\Mimetypes;
use Beehive\GuzzleHttp\Stream\StreamInterface;
/**
 * Prepares requests with a body before sending
 *
 * **Request Options**
 *
 * - expect: Set to true to enable the "Expect: 100-Continue" header for a
 *   request that send a body. Set to false to disable "Expect: 100-Continue".
 *   Set to a number so that the size of the payload must be greater than the
 *   number in order to send the Expect header. Setting to a number will send
 *   the Expect header for all requests in which the size of the payload cannot
 *   be determined or where the body is not rewindable.
 */
class Prepare implements \Beehive\GuzzleHttp\Event\SubscriberInterface
{
    public function getEvents()
    {
        return ['before' => ['onBefore', \Beehive\GuzzleHttp\Event\RequestEvents::PREPARE_REQUEST]];
    }
    public function onBefore(\Beehive\GuzzleHttp\Event\BeforeEvent $event)
    {
        $request = $event->getRequest();
        // Set the appropriate Content-Type for a request if one is not set and
        // there are form fields
        if (!($body = $request->getBody())) {
            return;
        }
        $this->addContentLength($request, $body);
        if ($body instanceof \Beehive\GuzzleHttp\Message\AppliesHeadersInterface) {
            // Synchronize the body with the request headers
            $body->applyRequestHeaders($request);
        } elseif (!$request->hasHeader('Content-Type')) {
            $this->addContentType($request, $body);
        }
        $this->addExpectHeader($request, $body);
    }
    private function addContentType(\Beehive\GuzzleHttp\Message\RequestInterface $request, \Beehive\GuzzleHttp\Stream\StreamInterface $body)
    {
        if (!($uri = $body->getMetadata('uri'))) {
            return;
        }
        // Guess the content-type based on the stream's "uri" metadata value.
        // The file extension is used to determine the appropriate mime-type.
        if ($contentType = \Beehive\GuzzleHttp\Mimetypes::getInstance()->fromFilename($uri)) {
            $request->setHeader('Content-Type', $contentType);
        }
    }
    private function addContentLength(\Beehive\GuzzleHttp\Message\RequestInterface $request, \Beehive\GuzzleHttp\Stream\StreamInterface $body)
    {
        // Set the Content-Length header if it can be determined, and never
        // send a Transfer-Encoding: chunked and Content-Length header in
        // the same request.
        if ($request->hasHeader('Content-Length')) {
            // Remove transfer-encoding if content-length is set.
            $request->removeHeader('Transfer-Encoding');
            return;
        }
        if ($request->hasHeader('Transfer-Encoding')) {
            return;
        }
        if (null !== ($size = $body->getSize())) {
            $request->setHeader('Content-Length', $size);
            $request->removeHeader('Transfer-Encoding');
        } elseif ('1.1' == $request->getProtocolVersion()) {
            // Use chunked Transfer-Encoding if there is no determinable
            // content-length header and we're using HTTP/1.1.
            $request->setHeader('Transfer-Encoding', 'chunked');
            $request->removeHeader('Content-Length');
        }
    }
    private function addExpectHeader(\Beehive\GuzzleHttp\Message\RequestInterface $request, \Beehive\GuzzleHttp\Stream\StreamInterface $body)
    {
        // Determine if the Expect header should be used
        if ($request->hasHeader('Expect')) {
            return;
        }
        $expect = $request->getConfig()['expect'];
        // Return if disabled or if you're not using HTTP/1.1
        if ($expect === \false || $request->getProtocolVersion() !== '1.1') {
            return;
        }
        // The expect header is unconditionally enabled
        if ($expect === \true) {
            $request->setHeader('Expect', '100-Continue');
            return;
        }
        // By default, send the expect header when the payload is > 1mb
        if ($expect === null) {
            $expect = 1048576;
        }
        // Always add if the body cannot be rewound, the size cannot be
        // determined, or the size is greater than the cutoff threshold
        $size = $body->getSize();
        if ($size === null || $size >= (int) $expect || !$body->isSeekable()) {
            $request->setHeader('Expect', '100-Continue');
        }
    }
}