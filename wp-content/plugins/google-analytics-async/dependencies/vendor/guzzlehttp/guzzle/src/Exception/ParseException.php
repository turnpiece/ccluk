<?php

namespace Beehive\GuzzleHttp\Exception;

use Beehive\GuzzleHttp\Message\ResponseInterface;
/**
 * Exception when a client is unable to parse the response body as XML or JSON
 */
class ParseException extends \Beehive\GuzzleHttp\Exception\TransferException
{
    /** @var ResponseInterface */
    private $response;
    public function __construct($message = '', \Beehive\GuzzleHttp\Message\ResponseInterface $response = null, \Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);
        $this->response = $response;
    }
    /**
     * Get the associated response
     *
     * @return ResponseInterface|null
     */
    public function getResponse()
    {
        return $this->response;
    }
}