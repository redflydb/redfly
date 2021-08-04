<?php
namespace CCR\REDfly\Service\Exception;

// Standard PHP Libraries (SPL)
use UnexpectedValueException;
/**
 * Runtime exception for errors arising from an invalid command or query.
 */
class InvalidMessageException extends UnexpectedValueException
{
    public function __construct(string $class)
    {
        parent::__construct(sprintf("%s: Invalid message structure; aborting execution.", $class));
    }
}
