<?php
namespace CCR\REDfly\Audit\Command;

// REDfly libraries with namespaces
use CCR\REDfly\Service\Message\CommandInterface;
class UpdateRcStates implements CommandInterface
{
    use UpdateStatesCommandTrait;
}
