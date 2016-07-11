<?php

namespace Mobillogix\Launchpad\QueueBundle\Exception;

class UnexpectedHTTPCodeException extends TaskExecutionException
{
    /**
     * UnexpectedHTTPCode constructor.
     * @param int $HTTPCode
     * @param mixed $content
     */
    public function __construct($HTTPCode, $content)
    {
        $message = $HTTPCode . " HTTP code with body: " . json_encode($content);
        parent::__construct($message);
    }
}