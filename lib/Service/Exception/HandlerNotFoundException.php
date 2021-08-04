<?php
namespace CCR\REDfly\Service\Exception;

// Standard PHP Libraries (SPL)
use UnexpectedValueException;
/**
 * Runtime exception for errors arising from a failure to locate a handler for
 * a command or query.
 */
class HandlerNotFoundException extends UnexpectedValueException
{
    public function __construct(string $class)
    {
        parent::__construct(sprintf("%s: No handler registered for this message.", $class));
    }
}
