<?php
namespace CCR\REDfly\Download\Query;

// Third-party libraries
use Doctrine\DBAL\Connection;
// REDfly libraries with namespaces
use CCR\REDfly\Service\Message\QueryResult;
/**
 * The query handler for the query to initiate a batch download of
 * predicted cis-regulatory module staging data.
 */
class BatchDownloadPredictedCRMStagingDataHandler
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
     * Handles the logic for building a file with predicted cis-regulatory module
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
                "sex"
            )
            ->from("v_predicted_cis_regulatory_module_staging_data_file")
            ->orderBy("name")
            ->addOrderBy("expression_identifier");

        return QueryResult::fromQueryBuilder($builder);
    }
}
