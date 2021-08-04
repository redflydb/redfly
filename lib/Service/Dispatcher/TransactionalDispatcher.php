<?php
namespace CCR\REDfly\Service\Dispatcher;

// Third-party libraries
use ParagonIE\EasyDB\EasyDB;
// REDfly libraries with namespaces
use CCR\REDfly\Service\Message\{CommandInterface, QueryInterface, QueryResult};
/**
 * Dispatcher decorator for wrapping a command handler within a SQL transaction.
 * Any actions against the database taken by the dispatched command will be
 * committed upon success; a thrown exception will cause the transaction to be
 * rolled back.
 * Note that only commands are transactional; queries are punted directly to the
 * decorated dispatcher.
 */
class TransactionalDispatcher implements DispatcherInterface
{
    /** @var DispatcherInterface $decorated Decorated dispatcher. */
    private $decorated;
    /** @var EasyDB $db EasyDB instance for connecting to the database. */
    private $db;
    public function __construct(
        DispatcherInterface $decorated,
        EasyDB $db
    ) {
        $this->decorated = $decorated;
        $this->db = $db;
    }
    public function send(CommandInterface $command): void
    {
        $this->db->tryFlatTransaction(function () use ($command) {
            $this->decorated->send($command);
        });
    }
    public function request(QueryInterface $query): QueryResult
    {
        return $this->decorated->request($query);
    }
}
