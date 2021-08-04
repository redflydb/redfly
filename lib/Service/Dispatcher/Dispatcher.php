<?php
namespace CCR\REDfly\Service\Dispatcher;

// REDfly libraries with namespaces
use CCR\REDfly\Service\Message\{CommandInterface, QueryInterface, QueryResult};
/**
 * A dispatcher that resolves and dispatches a command or query to its handler.
 * The dispatcher is intended to be wrapped within one or more middlewares
 * implementing DispatcherInterface according to the decorator design pattern.
 */
class Dispatcher implements DispatcherInterface
{
    /** @var CallableResolverInterface $resolver Callable resolver. */
    private $resolver;
    public function __construct(CallableResolverInterface $resolver)
    {
        $this->resolver = $resolver;
    }
    public function send(CommandInterface $command): void
    {
        ($this->resolver->resolve($command))($command);
    }
    public function request(QueryInterface $query): QueryResult
    {
        return ($this->resolver->resolve($query))($query);
    }
}
