<?php
namespace CCR\REDfly\Download\Query;

// Third-party libraries
use Doctrine\DBAL\Connection;
// REDfly libraries with namespaces
use CCR\REDfly\Service\Message\QueryResult;
/**
 * The query handler for the query to initiate a batch download of
 * reporter construct staging data.
 */
class BatchDownloadReporterConstructStagingDataHandler
{
    /**
     * @var Connection $connection The connection to the database.
     */
    private $connection;
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }
    /**
     * Handles the logic for building a file with reporter construct
     * staging data from the persistence layer.
     * @return QueryResult The query results.
     */
    public function __invoke(): QueryResult
    {
        $builder = $this->connection->createQueryBuilder();
        $builder
            ->select(
                "entity_type",
                "parent_id",
                "parent_pubmed_id",
                "name",
                "pubmed_id",
                "expression_identifier",
                "stage_on_identifier",
                "stage_off_identifier",
                "biological_process_identifier",
                "sex",
                "ectopic"
            )
            ->from("v_reporter_construct_staging_data_file")
            ->orderBy("name")
            ->addOrderBy("expression_identifier");

        return QueryResult::fromQueryBuilder($builder);
    }
}
