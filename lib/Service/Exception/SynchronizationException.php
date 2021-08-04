<?php
namespace CCR\REDfly\Service\Exception;

// Standard PHP Libraries (SPL)
use RuntimeException;
/**
 * Runtime exception for errors arising from executing a synchronized command.
 */
class SynchronizationException extends RuntimeException
{
    public function __construct(string $class)
    {
        parent::__construct(sprintf("%s: Failed to acquire lock; process already running.", $class));
    }
}
