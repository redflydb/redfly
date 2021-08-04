<?php
namespace CCR\REDfly\Admin\Command;

// REDfly libraries with namespaces
use CCR\REDfly\Service\Message\{CommandInterface, EmptyMessageTrait};
/**
 * The command DTO for releasing all approved entities so that they are viewable
 * to the public.
 */
class ReleaseApprovedRecords implements CommandInterface
{
    use EmptyMessageTrait;
}
