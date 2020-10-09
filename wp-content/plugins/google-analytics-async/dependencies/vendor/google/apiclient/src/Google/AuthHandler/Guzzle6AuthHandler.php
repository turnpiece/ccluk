<?php

namespace Beehive;

use Beehive\Google\Auth\CredentialsLoader;
use Beehive\Google\Auth\HttpHandler\HttpHandlerFactory;
use Beehive\Google\Auth\FetchAuthTokenCache;
use Beehive\Google\Auth\Middleware\AuthTokenMiddleware;
use Beehive\Google\Auth\Middleware\ScopedAccessTokenMiddleware;
use Beehive\Google\Auth\Middleware\SimpleMiddleware;
use Beehive\GuzzleHttp\Client;
use Beehive\GuzzleHttp\ClientInterface;
use Beehive\Psr\Cache\CacheItemPoolInterface;
/**
* This supports Guzzle 6
*/
class Google_AuthHandler_Guzzle6AuthHandler
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
        $middleware = new \Beehive\Google\Auth\Middleware\AuthTokenMiddleware($credentials, $authHttpHandler, $tokenCallback);
        $config = $http->getConfig();
        $config['handler']->remove('google_auth');
        $config['handler']->push($middleware, 'google_auth');
        $config['auth'] = 'google_auth';
        $http = new \Beehive\GuzzleHttp\Client($config);
        return $http;
    }
    public function attachToken(\Beehive\GuzzleHttp\ClientInterface $http, array $token, array $scopes)
    {
        $tokenFunc = function ($scopes) use($token) {
            return $token['access_token'];
        };
        $middleware = new \Beehive\Google\Auth\Middleware\ScopedAccessTokenMiddleware($tokenFunc, $scopes, $this->cacheConfig, $this->cache);
        $config = $http->getConfig();
        $config['handler']->remove('google_auth');
        $config['handler']->push($middleware, 'google_auth');
        $config['auth'] = 'scoped';
        $http = new \Beehive\GuzzleHttp\Client($config);
        return $http;
    }
    public function attachKey(\Beehive\GuzzleHttp\ClientInterface $http, $key)
    {
        $middleware = new \Beehive\Google\Auth\Middleware\SimpleMiddleware(['key' => $key]);
        $config = $http->getConfig();
        $config['handler']->remove('google_auth');
        $config['handler']->push($middleware, 'google_auth');
        $config['auth'] = 'simple';
        $http = new \Beehive\GuzzleHttp\Client($config);
        return $http;
    }
    private function createAuthHttp(\Beehive\GuzzleHttp\ClientInterface $http)
    {
        return new \Beehive\GuzzleHttp\Client(['base_uri' => $http->getConfig('base_uri'), 'exceptions' => \true, 'verify' => $http->getConfig('verify'), 'proxy' => $http->getConfig('proxy')]);
    }
}
/**
* This supports Guzzle 6
*/
\class_alias('Beehive\\Google_AuthHandler_Guzzle6AuthHandler', 'Google_AuthHandler_Guzzle6AuthHandler', \false);