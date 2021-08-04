<?php
namespace CCR\REDfly\Audit\Command;

// Third-party libraries
use Doctrine\DBAL\Connection;
class UpdatePredictedCrmStatesHandler
{
    use UpdateStatesCommandHandlerTrait;
    private $connection;
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }
    public function __invoke(UpdatePredictedCrmStates $command): void
    {
        $builder = $this->connection->createQueryBuilder()
            ->update("PredictedCRM")
            ->where("predicted_crm_id = :id");
        $this->executeUpdates(
            $builder,
            $command->getUpdates()
        );
    }
}
