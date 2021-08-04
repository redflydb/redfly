<?php
namespace CCR\REDfly\Audit\Command;

// Third-party libraries
use Doctrine\DBAL\Connection;
class UpdateTfbsStatesHandler
{
    use UpdateStatesCommandHandlerTrait;
    private $connection;
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }
    public function __invoke(UpdateTfbsStates $command): void
    {
        $builder = $this->connection->createQueryBuilder()
            ->update("BindingSite")
            ->where("tfbs_id = :id");
        $this->executeUpdates(
            $builder,
            $command->getUpdates()
        );
    }
}
