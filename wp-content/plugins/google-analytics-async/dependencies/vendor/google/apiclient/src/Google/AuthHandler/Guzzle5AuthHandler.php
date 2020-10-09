<?php

namespace Beehive;

use Beehive\Google\Auth\CredentialsLoader;
use Beehive\Google\Auth\HttpHandler\HttpHandlerFactory;
use Beehive\Google\Auth\FetchAuthTokenCache;
use Beehive\Google\Auth\Subscriber\AuthTokenSubscriber;
use Beehive\Google\Auth\Subscriber\ScopedAccessTokenSubscriber;
use Beehive\Google\Auth\Subscriber\SimpleSubscriber;
use Beehive\GuzzleHttp\Client;
use Beehive\GuzzleHttp\ClientInterface;
use Beehive\Psr\Cache\CacheItemPoolInterface;
/**
*
*/
class Google_AuthHandler_Guzzle5AuthHandler
{
    protected $cache;
    protected $cacheConfig;
    public function __construct(\Beehive\Psr\Cache\CacheItemPoolInterface $cache = null, array $cacheConfig = [])
    {
        $this->cache = $cache;
        $this->cacheConfig = $cacheConfig;
    }
    public function attachCredentials(\Beehive\GuzzleHttp\ClientInterface $http, \Beehive\Google\Auth\CredentialsLoader $credentials, callable $tokenCallback = null)
    {
        // use the provided cache
        if ($this->cache) {
            $credentials = new \Beehive\Google\Auth\FetchAuthTokenCache($credentials, $this->cacheConfig, $this->cache);
        }
        // if we end up needing to make an HTTP request to retrieve credentials, we
        // can use our existing one, but we need to throw exceptions so the error
        // bubbles up.
        $authHttp = $this->createAuthHttp($http);
        $authHttpHandler = \Beehive\Google\Auth\HttpHandler\HttpHandlerFactory::build($authHttp);
        $subscriber = new \Beehive\Google\Auth\Subscriber\AuthTokenSubscriber($credentials, $authHttpHandler, $tokenCallback);
        $http->setDefaultOption('auth', 'google_auth');
        $http->getEmitter()->attach($subscriber);
        return $http;
    }
    public function attachToken(\Beehive\GuzzleHttp\ClientInterface $http, array $token, array $scopes)
    {
        $tokenFunc = function ($scopes) use($token) {
            return $token['access_token'];
        };
        $subscriber = new \Beehive\Google\Auth\Subscriber\ScopedAccessTokenSubscriber($tokenFunc, $scopes, $this->cacheConfig, $this->cache);
        $http->setDefaultOption('auth', 'scoped');
        $http->getEmitter()->attach($subscriber);
        return $http;
    }
    public function attachKey(\Beehive\GuzzleHttp\ClientInterface $http, $key)
    {
        $subscriber = new \Beehive\Google\Auth\Subscriber\SimpleSubscriber(['key' => $key]);
        $http->setDefaultOption('auth', 'simple');
        $http->getEmitter()->attach($subscriber);
        return $http;
    }
    private function createAuthHttp(\Beehive\GuzzleHttp\ClientInterface $http)
    {
        return new \Beehive\GuzzleHttp\Client(['base_url' => $http->getBaseUrl(), 'defaults' => ['exceptions' => \true, 'verify' => $http->getDefaultOption('verify'), 'proxy' => $http->getDefaultOption('proxy')]]);
    }
}
/**
*
*/
\class_alias('Beehive\\Google_AuthHandler_Guzzle5AuthHandler', 'Google_AuthHandler_Guzzle5AuthHandler', \false);