<?php
namespace CCR\REDfly\Service\Exception;

// Standard PHP Libraries (SPL)
use RuntimeException;
/**
 * Runtime exception for errors arising from authenticating a command or query.
 */
class AuthenticationException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct("Authentication failed; access denied.");
    }
}
