<?php
namespace CCR\REDfly\Audit\Query;

// Third-party libraries
use Doctrine\DBAL\Connection;
// REDfly libraries with namespaces
use CCR\REDfly\Service\FilteredQueryHandlerTrait;
use CCR\REDfly\Service\PaginatedQueryHandlerTrait;
use CCR\REDfly\Service\SortedQueryHandlerTrait;
use CCR\REDfly\Service\Message\QueryResult;
class TfbsSearchHandler
{
    use PaginatedQueryHandlerTrait;
    use SortedQueryHandlerTrait;
    use FilteredQueryHandlerTrait;
    private $connection;
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }
    public function __invoke(TfbsSearch $tfbsSearch): QueryResult
    {
        // The fields alphabetically sorted only
        // for a better checking here
        $fields = [
            "assayed_in_species_scientific_name",
            "chromosome_display",
            "coordinates",
            "curator_full_name",
            "curator_id",
            "date_added",
            "end",
            "gene_display",
            "id",
            "last_update",
            "name",
            "pubmed_id",
            "sequence_from_species_scientific_name",
            "start",
            "state",
            "transcription_factor_display"
        ];
        $queryBuilder = $this->connection->createQueryBuilder()
            ->select(...$fields)
            ->from("v_transcription_factor_binding_site_audit");
        $queryBuilder = $this->buildPaginatedQuery(
            $queryBuilder,
            $tfbsSearch->getPage(),
            $tfbsSearch->getStart(),
            $tfbsSearch->getLimit()
        );
        $queryBuilder = $this->buildFilteredQuery(
            $queryBuilder,
            $tfbsSearch->getFilters(),
            $fields
        );
        $queryBuilder = $this->buildSortedQuery(
            $queryBuilder,
            $tfbsSearch->getSorters(),
            $fields
        );

        return QueryResult::fromQueryBuilder($queryBuilder);
    }
}
