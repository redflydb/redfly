<?php
namespace CCR\REDfly\Download\Query;

// Third-party libraries
use Doctrine\DBAL\Connection;
// REDfly libraries with namespaces
use CCR\REDfly\Service\Message\QueryResult;
/**
 * The query handler for the query to initiate a batch download of
 * binding site data.
 */
class BatchDownloadTranscriptionFactorBindingSitesHandler
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
     * Handles the logic for building a file with binding site data from the
     * persistence layer.
     * @param BatchDownloadTranscriptionFactorBindingSites $query The query DTO to act on.
     * @return QueryResult The query results.
     */
    public function __invoke(BatchDownloadTranscriptionFactorBindingSites $query): QueryResult
    {
        $builder = $this->connection->createQueryBuilder()
            ->select(
                //"associated_rc",
                "chromosome",
                "end",
                "evidence_term",
                "gene_identifier",
                "gene_name",
                "label",
                "name",
                "ontology_term",
                "pubmed_id",
                "sequence",
                "sequence_with_flank",
                "sequence_from_species_scientific_name",
                "tf_identifier",
                "tf_name",
                "redfly_id",
                "redfly_id_unversioned",
                "start"
            )
            ->from("v_transcription_factor_binding_site_file")
            ->where("sequence_from_species_scientific_name = '" . $query->speciesScientificName . "'")
            ->orderBy("name");

        return QueryResult::fromQueryBuilder($builder);
    }
}
