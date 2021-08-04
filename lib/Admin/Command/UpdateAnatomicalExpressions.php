<?php
namespace CCR\REDfly\Admin\Command;

// REDfly libraries with namespaces
use CCR\REDfly\Service\Message\{CommandInterface, EmptyMessageTrait};
/**
 * The command DTO for updating the anatomical expressions in the database
 * to synchronize the data with the latest anatomy ontolog(ies).
 */
class UpdateAnatomicalExpressions implements CommandInterface
{
    use EmptyMessageTrait;
}
