<?php

namespace Beehive\GuzzleHttp;

use Beehive\GuzzleHttp\Event\BeforeEvent;
use Beehive\GuzzleHttp\Event\ErrorEvent;
use Beehive\GuzzleHttp\Event\CompleteEvent;
use Beehive\GuzzleHttp\Event\EndEvent;
use Beehive\GuzzleHttp\Exception\StateException;
use Beehive\GuzzleHttp\Exception\RequestException;
use Beehive\GuzzleHttp\Message\FutureResponse;
use Beehive\GuzzleHttp\Message\MessageFactoryInterface;
use Beehive\GuzzleHttp\Ring\Future\FutureInterface;
/**
 * Responsible for transitioning requests through lifecycle events.
 */
class RequestFsm
{
    private $handler;
    private $mf;
    private $maxTransitions;
    public function __construct(callable $handler, \Beehive\GuzzleHttp\Message\MessageFactoryInterface $messageFactory, $maxTransitions = 200)
    {
        $this->mf = $messageFactory;
        $this->maxTransitions = $maxTransitions;
        $this->handler = $handler;
    }
    /**
     * Runs the state machine until a terminal state is entered or the
     * optionally supplied $finalState is entered.
     *
     * @param Transaction $trans      Transaction being transitioned.
     *
     * @throws \Exception if a terminal state throws an exception.
     */
    public function __invoke(\Beehive\GuzzleHttp\Transaction $trans)
    {
        $trans->_transitionCount = 0;
        if (!$trans->state) {
            $trans->state = 'before';
        }
        transition:
        if (++$trans->_transitionCount > $this->maxTransitions) {
            throw new \Beehive\GuzzleHttp\Exception\StateException("Too many state transitions were " . "encountered ({$trans->_transitionCount}). This likely " . "means that a combination of event listeners are in an " . "infinite loop.");
        }
        switch ($trans->state) {
            case 'before':
                goto before;
            case 'complete':
                goto complete;
            case 'error':
                goto error;
            case 'retry':
                goto retry;
            case 'send':
                goto send;
            case 'end':
                goto end;
            default:
                throw new \Beehive\GuzzleHttp\Exception\StateException("Invalid state: {$trans->state}");
        }
        before:
        try {
            $trans->request->getEmitter()->emit('before', new \Beehive\GuzzleHttp\Event\BeforeEvent($trans));
            $trans->state = 'send';
            if ((bool) $trans->response) {
                $trans->state = 'complete';
            }
        } catch (\Exception $e) {
            $trans->state = 'error';
            $trans->exception = $e;
        }
        goto transition;
        complete:
        try {
            if ($trans->response instanceof \Beehive\GuzzleHttp\Ring\Future\FutureInterface) {
                // Futures will have their own end events emitted when
                // dereferenced.
                return;
            }
            $trans->state = 'end';
            $trans->response->setEffectiveUrl($trans->request->getUrl());
            $trans->request->getEmitter()->emit('complete', new \Beehive\GuzzleHttp\Event\CompleteEvent($trans));
        } catch (\Exception $e) {
            $trans->state = 'error';
            $trans->exception = $e;
        }
        goto transition;
        error:
        try {
            // Convert non-request exception to a wrapped exception
            $trans->exception = \Beehive\GuzzleHttp\Exception\RequestException::wrapException($trans->request, $trans->exception);
            $trans->state = 'end';
            $trans->request->getEmitter()->emit('error', new \Beehive\GuzzleHttp\Event\ErrorEvent($trans));
            // An intercepted request (not retried) transitions to complete
            if (!$trans->exception && $trans->state !== 'retry') {
                $trans->state = 'complete';
            }
        } catch (\Exception $e) {
            $trans->state = 'end';
            $trans->exception = $e;
        }
        goto transition;
        retry:
        $trans->retries++;
        $trans->response = null;
        $trans->exception = null;
        $trans->state = 'before';
        goto transition;
        send:
        $fn = $this->handler;
        $trans->response = \Beehive\GuzzleHttp\Message\FutureResponse::proxy($fn(\Beehive\GuzzleHttp\RingBridge::prepareRingRequest($trans)), function ($value) use($trans) {
            \Beehive\GuzzleHttp\RingBridge::completeRingResponse($trans, $value, $this->mf, $this);
            $this($trans);
            return $trans->response;
        });
        return;
        end:
        $trans->request->getEmitter()->emit('end', new \Beehive\GuzzleHttp\Event\EndEvent($trans));
        // Throw exceptions in the terminal event if the exception
        // was not handled by an "end" event listener.
        if ($trans->exception) {
            if (!$trans->exception instanceof \Beehive\GuzzleHttp\Exception\RequestException) {
                $trans->exception = \Beehive\GuzzleHttp\Exception\RequestException::wrapException($trans->request, $trans->exception);
            }
            throw $trans->exception;
        }
    }
}