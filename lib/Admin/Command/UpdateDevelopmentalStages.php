<?php
namespace CCR\REDfly\Admin\Command;

// REDfly libraries with namespaces
use CCR\REDfly\Service\Message\{CommandInterface, EmptyMessageTrait};
/**
 * The command DTO for updating the developmental stages in the database to
 * synchronize the data with the FlyBase developmental ontology.
 */
class UpdateDevelopmentalStages implements CommandInterface
{
    use EmptyMessageTrait;
}
