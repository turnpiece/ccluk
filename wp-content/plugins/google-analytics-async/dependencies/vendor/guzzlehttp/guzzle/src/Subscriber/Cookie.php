<?php

namespace Beehive\GuzzleHttp\Subscriber;

use Beehive\GuzzleHttp\Cookie\CookieJar;
use Beehive\GuzzleHttp\Cookie\CookieJarInterface;
use Beehive\GuzzleHttp\Event\BeforeEvent;
use Beehive\GuzzleHttp\Event\CompleteEvent;
use Beehive\GuzzleHttp\Event\RequestEvents;
use Beehive\GuzzleHttp\Event\SubscriberInterface;
/**
 * Adds, extracts, and persists cookies between HTTP requests
 */
class Cookie implements \Beehive\GuzzleHttp\Event\SubscriberInterface
{
    /** @var CookieJarInterface */
    private $cookieJar;
    /**
     * @param CookieJarInterface $cookieJar Cookie jar used to hold cookies
     */
    public function __construct(\Beehive\GuzzleHttp\Cookie\CookieJarInterface $cookieJar = null)
    {
        $this->cookieJar = $cookieJar ?: new \Beehive\GuzzleHttp\Cookie\CookieJar();
    }
    public function getEvents()
    {
        // Fire the cookie plugin complete event before redirecting
        return ['before' => ['onBefore'], 'complete' => ['onComplete', \Beehive\GuzzleHttp\Event\RequestEvents::REDIRECT_RESPONSE + 10]];
    }
    /**
     * Get the cookie cookieJar
     *
     * @return CookieJarInterface
     */
    public function getCookieJar()
    {
        return $this->cookieJar;
    }
    public function onBefore(\Beehive\GuzzleHttp\Event\BeforeEvent $event)
    {
        $this->cookieJar->addCookieHeader($event->getRequest());
    }
    public function onComplete(\Beehive\GuzzleHttp\Event\CompleteEvent $event)
    {
        $this->cookieJar->extractCookies($event->getRequest(), $event->getResponse());
    }
}