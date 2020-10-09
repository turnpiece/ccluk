<?php

namespace Beehive\GuzzleHttp\Event;

use Beehive\GuzzleHttp\Transaction;
use Beehive\GuzzleHttp\ClientInterface;
use Beehive\GuzzleHttp\Message\RequestInterface;
/**
 * Base class for request events, providing a request and client getter.
 */
abstract class AbstractRequestEvent extends \Beehive\GuzzleHttp\Event\AbstractEvent
{
    /** @var Transaction */
    protected $transaction;
    /**
     * @param Transaction $transaction
     */
    public function __construct(\Beehive\GuzzleHttp\Transaction $transaction)
    {
        $this->transaction = $transaction;
    }
    /**
     * Get the HTTP client associated with the event.
     *
     * @return ClientInterface
     */
    public function getClient()
    {
        return $this->transaction->client;
    }
    /**
     * Get the request object
     *
     * @return RequestInterface
     */
    public function getRequest()
    {
        return $this->transaction->request;
    }
    /**
     * Get the number of transaction retries.
     *
     * @return int
     */
    public function getRetryCount()
    {
        return $this->transaction->retries;
    }
    /**
     * @return Transaction
     */
    public function getTransaction()
    {
        return $this->transaction;
    }
}