<?php

namespace Beehive\GuzzleHttp\Exception;

use Beehive\GuzzleHttp\Message\ResponseInterface;
/**
 * Exception when a client is unable to parse the response body as XML
 */
class XmlParseException extends \Beehive\GuzzleHttp\Exception\ParseException
{
    /** @var \LibXMLError */
    protected $error;
    public function __construct($message = '', \Beehive\GuzzleHttp\Message\ResponseInterface $response = null, \Exception $previous = null, \LibXMLError $error = null)
    {
        parent::__construct($message, $response, $previous);
        $this->error = $error;
    }
    /**
     * Get the associated error
     *
     * @return \LibXMLError|null
     */
    public function getError()
    {
        return $this->error;
    }
}