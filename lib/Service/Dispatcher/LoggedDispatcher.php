<?php
namespace CCR\REDfly\Service\Dispatcher;

// Standard PHP Libraries (SPL)
use Throwable;
// Third-party libraries
use Psr\Log\LoggerInterface;
// REDfly libraries with namespaces
use CCR\REDfly\Service\Message\{CommandInterface, QueryInterface, QueryResult};
/**
 * Dispatcher decorator that logs the command or query before dispatching to the
 * next dispatcher.
 */
class LoggedDispatcher implements DispatcherInterface
{
    /** @var DispatcherInterface $decorated Decorated dispatcher. */
    private $decorated;
    /** @var LoggerInterface $logger Logger for logging the command or query. */
    private $logger;
    public function __construct(
        DispatcherInterface $decorated,
        LoggerInterface $logger
    ) {
        $this->decorated = $decorated;
        $this->logger = $logger;
    }
    public function send(CommandInterface $command): void
    {
        $this->logger->debug("sending command", ["message" => $command]);
        try {
            $this->decorated->send($command);
        } catch ( Throwable $e ) {
            $this->logger->error("failed sending command", ["exception" => $e]);
            throw $e;
        }
    }
    public function request(QueryInterface $query): QueryResult
    {
        $this->logger->debug("requesting query", ["message" => $query]);
        try {
            return $this->decorated->request($query);
        } catch ( Throwable $e ) {
            $this->logger->error("failed requesting query", ["exception" => $e]);
            throw $e;
        }
    }
}
