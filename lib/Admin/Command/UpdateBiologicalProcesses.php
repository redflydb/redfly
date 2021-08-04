<?php
namespace CCR\REDfly\Admin\Command;

// REDfly libraries with namespaces
use CCR\REDfly\Service\Message\{CommandInterface, EmptyMessageTrait};
/**
 * The command DTO for updating the biolgical processes in the database to
 * synchronize the data with the gene ontology (GO).
 */
class UpdateBiologicalProcesses implements CommandInterface
{
    use EmptyMessageTrait;
}
