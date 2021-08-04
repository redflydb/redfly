<?php
namespace CCR\REDfly\Service\Message;

// Standard PHP Libraries (SPL)
use JsonSerializable;
/**
 * Provides a generic success result. Particularly useful as a response for if
 * an error or exception occurs.
 */
class ErrorResult implements JsonSerializable
{
    /** @var string $error Error message. */
    private $error;
    public function __construct(string $error)
    {
        $this->error = $error;
    }
    public function jsonSerialize(): array
    {
        return [
            "success" => false,
            "error"   => $this->error
        ];
    }
}
