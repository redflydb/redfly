<?php
namespace CCR\REDfly\Admin\Command;

// REDfly libraries with namespaces
use CCR\REDfly\Service\Message\{CommandInterface, EmptyMessageTrait};
/**
 * The command DTO for updating the genes in the database to synchronize the
 * data with the FlyBase database.
 */
class UpdateGenes implements CommandInterface
{
    use EmptyMessageTrait;
}
