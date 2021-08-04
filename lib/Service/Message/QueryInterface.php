<?php
namespace CCR\REDfly\Service\Message;

// Standard PHP Libraries (SPL)
use JsonSerializable;
/**
 * Marker interface for an DTO containing all parameters required to execute a
 * query. The interface also extends JsonSerializable, hence requring that all
 * queries can also be serialized to JSON.
 */
interface QueryInterface extends JsonSerializable
{
}
