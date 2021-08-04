<?php
namespace CCR\REDfly\Admin\Command;

// REDfly libraries with namespaces
use CCR\REDfly\Service\Message\{CommandInterface, EmptyMessageTrait};
/**
 * The command DTO for archiving all records marked for deletion.
 */
class ArchiveRecordsMarkedForDeletion implements CommandInterface
{
    use EmptyMessageTrait;
}
