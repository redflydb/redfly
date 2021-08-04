<?php
namespace CCR\REDfly\Service\Dispatcher;

// Third-party libraries
use ParagonIE\EasyDB\EasyDB;
// REDfly libraries with namespaces
use CCR\REDfly\Service\Exception\SynchronizationException;
use CCR\REDfly\Service\Message\{CommandInterface, QueryInterface, QueryResult};
/**
 * Dispatcher decorator for synchronizing a command handler. Ensures that only
 * one instance of that handler is being processed at a time. The function
 * GET_LOCK() provided by the underlying database is used as the locking
 * mechanism, using the name of the command as the lock identifier. If a lock is
 * acquired, the dispatcher proceeds and dispatches the command; otherwise an
 * exception will be thrown.
 * Note that only commands are synchronized; queries are punted directly to the
 * decorated dispatcher.
 */
class SynchronizedDispatcher implements DispatcherInterface
{
    /** @var DispatcherInterface $decorated Decorated dispatcher. */
    private $decorated;
    /** @var EasyDB $db EasyDB instance for connecting to the database. */
    private $db;
    public function __construct(DispatcherInterface $decorated, EasyDB $db)
    {
        $this->decorated = $decorated;
        $this->db = $db;
    }
    public function send(CommandInterface $command): void
    {
        $class = get_class($command);
        try {
            $lock = $this->db->cell("SELECT GET_LOCK(?, 0);", $class);
            if ( $lock != 1 ) {
                throw new SynchronizationException($class);
            }
            $this->decorated->send($command);
        } finally {
            $this->db->run("SELECT RELEASE_LOCK(?);", $class);
        }
    }
    public function request(QueryInterface $query): QueryResult
    {
        return $this->decorated->request($query);
    }
}
