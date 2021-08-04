<?php
namespace CCR\REDfly\Audit\Command;

// REDfly libraries with namespaces
use CCR\REDfly\Service\Message\CommandInterface;
class UpdatePredictedCrmStates implements CommandInterface
{
    use UpdateStatesCommandTrait;
}
