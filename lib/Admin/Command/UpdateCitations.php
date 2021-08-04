<?php
namespace CCR\REDfly\Admin\Command;

// REDfly libraries with namespaces
use CCR\REDfly\Service\Message\{CommandInterface, EmptyMessageTrait};
/**
 * The command DTO for updating the citations to clean up unused entries and
 * update author contacted status of publications cited by current entities.
 */
class UpdateCitations implements CommandInterface
{
    use EmptyMessageTrait;
}
