<?php
namespace CCR\REDfly\Service\Dispatcher;

// REDfly libraries with namespaces
use CCR\REDfly\Service\Message\{BatchCommand, CommandInterface, QueryInterface, QueryResult};
/**
 * Dispatcher decorator that handles a batched command. Each command in the
 * batch is sent to the next decorated dispatcher individually.
 * Note that only commands can be batched; queries are punted directly to the
 * decorated dispatcher.
 */
class BatchedDispatcher implements DispatcherInterface
{
    /** @var DispatcherInterface $decorated Decorated dispatcher. */
    private $decorated;
    public function __construct(DispatcherInterface $decorated)
    {
        $this->decorated = $decorated;
    }
    public function send(CommandInterface $command): void
    {
        if ( $command instanceof BatchCommand ) {
            foreach ( $command->commands as $command ) {
                $this->decorated->send($command);
            }
        } else {
            $this->decorated->send($command);
        }
    }
    public function request(QueryInterface $query): QueryResult
    {
        return $this->decorated->request($query);
    }
}
