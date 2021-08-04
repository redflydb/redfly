<?php
namespace CCR\REDfly\Download\Query;

// Third-party libraries
use Doctrine\DBAL\Connection;
// REDfly libraries with namespaces
use CCR\REDfly\Service\Message\QueryResult;
/**
 * The query handler for the query to initiate a batch download of
 * cis-regulatory module segment data.
 */
class BatchDownloadCRMSegmentsHandler
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
     * Handles the logic for building a file with cis-regulatory module segment
     * data from the persistence layer.
     * @param BatchDownloadCRMSegments $query The query DTO to act on.
     * @return QueryResult The query results.
     */
    public function __invoke(BatchDownloadCRMSegments $query): QueryResult
    {
        $builder = $this->connection->createQueryBuilder();
        $builder
            ->select(
                "chromosome",
                "end",
                "evidence_subtype_term",
                "evidence_term",
                "fbtp",
                "gene_identifier",
                "gene_name",
                "label",
                "name",
                "ontology_term",
                "pubmed_id",
                "crm_segment_id",
                "redfly_id",
                "redfly_id_unversioned",
                "sequence",
                "sequence_from_species_scientific_name",
                "start"
            )
            ->from("v_cis_regulatory_module_segment_file")
            ->where("sequence_from_species_scientific_name = '" . $query->speciesScientificName . "'")
            ->orderBy("name");

        return QueryResult::fromQueryBuilder($builder);
    }
}
