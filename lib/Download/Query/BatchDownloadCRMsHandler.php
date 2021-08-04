<?php
namespace CCR\REDfly\Download\Query;

// Third-party libraries
use Doctrine\DBAL\Connection;
// REDfly libraries with namespaces
use CCR\REDfly\Service\Message\QueryResult;
/**
 * The query handler for the query to initiate a batch download of
 * cis regulatory module (CRM) data.
 */
class BatchDownloadCRMsHandler
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
     * Handles the logic for building a file with CRM data from the persistence
     * layer.
     * @param BatchDownloadCRMs $query The query DTO to act on.
     * @return QueryResult The query results.
     */
    public function __invoke(BatchDownloadCRMs $query): QueryResult
    {
        $builder = $this->connection->createQueryBuilder();
        $builder
            ->select(
                "associated_tfbs",
                "chromosome",
                "end",
                "evidence_term",
                "fbtp",
                "gene_identifier",
                "gene_name",
                "label",
                "name",
                "ontology_term",
                "pubmed_id",
                "rc_id",
                "redfly_id",
                "redfly_id_unversioned",
                "sequence",
                "sequence_from_species_scientific_name",
                "start"
            )
            ->from("v_reporter_construct_file")
            ->where("is_crm = true")
            ->andWhere("sequence_from_species_scientific_name = '" . $query->speciesScientificName . "'")
            ->orderBy("name");

        return QueryResult::fromQueryBuilder($builder);
    }
}
