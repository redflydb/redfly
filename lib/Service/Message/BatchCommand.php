<?php
namespace CCR\REDfly\Service\Message;

/**
 * A generic command DTO that holds a batch of commands.
 */
class BatchCommand implements CommandInterface
{
    /** @var array $commands Commands in this batch. */
    public $commands;
    public function __construct(CommandInterface ...$commands)
    {
        $this->commands = $commands;
    }
    public function jsonSerialize(): array
    {
        return $this->commands;
    }
}
