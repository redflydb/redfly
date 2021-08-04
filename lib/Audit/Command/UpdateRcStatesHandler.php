<?php
namespace CCR\REDfly\Audit\Command;

// Third-party libraries
use Doctrine\DBAL\Connection;
class UpdateRcStatesHandler
{
    use UpdateStatesCommandHandlerTrait;
    private $connection;
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }
    public function __invoke(UpdateRcStates $command): void
    {
        $builder = $this->connection->createQueryBuilder()
            ->update("ReporterConstruct")
            ->where("rc_id = :id");
        $this->executeUpdates(
            $builder,
            $command->getUpdates()
        );
    }
}
