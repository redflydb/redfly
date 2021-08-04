<?php
namespace CCR\REDfly\Service\Message;

// Standard PHP Libraries (SPL)
use JsonSerializable;
/**
 * Provides a generic success result. Particularly useful as a response for
 * commands which do not return any data.
 */
class SuccessResult implements JsonSerializable
{
    public function jsonSerialize(): array
    {
        return [
            "success" => true
        ];
    }
}
