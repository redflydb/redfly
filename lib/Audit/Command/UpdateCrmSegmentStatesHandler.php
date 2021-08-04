<?php
namespace CCR\REDfly\Audit\Command;

// Third-party libraries
use Doctrine\DBAL\Connection;
class UpdateCrmSegmentStatesHandler
{
    use UpdateStatesCommandHandlerTrait;
    private $connection;
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }
    public function __invoke(UpdateCrmSegmentStates $command): void
    {
        $builder = $this->connection->createQueryBuilder()
            ->update("CRMSegment")
            ->where("crm_segment_id = :id");
        $this->executeUpdates(
            $builder,
            $command->getUpdates()
        );
    }
}
