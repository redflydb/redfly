<?php
namespace CCR\REDfly\Service\Message;

// Standard PHP Libraries (SPL)
use JsonSerializable;
/**
 * Marker interface for an DTO containing all parameters required to execute a
 * command. The interface also extends JsonSerializable, hence requring that all
 * commands can also be serialized to JSON.
 */
interface CommandInterface extends JsonSerializable
{
}
