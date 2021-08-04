<?php
namespace CCR\REDfly\Audit\Query;

// Third-party libraries
use Doctrine\DBAL\Connection;
// REDfly libraries with namespaces
use CCR\REDfly\Service\FilteredQueryHandlerTrait;
use CCR\REDfly\Service\PaginatedQueryHandlerTrait;
use CCR\REDfly\Service\SortedQueryHandlerTrait;
use CCR\REDfly\Service\Message\QueryResult;
class RcSearchHandler
{
    use PaginatedQueryHandlerTrait;
    use SortedQueryHandlerTrait;
    use FilteredQueryHandlerTrait;
    private $connection;
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }
    public function __invoke(RcSearch $rcSearch): QueryResult
    {
        // The fields alphabetically sorted only
        // for a better checking here
        $fields = [
            "anatomical_expression_displays",
            "assayed_in_species_scientific_name",
            "auditor_full_name",
            "chromosome_display",
            "coordinates",
            "curator_full_name",
            "curator_id",
            "date_added",
            "end",
            "evidence",
            "fbtp",
            "figure_labels",
            "gene_display",
            "id",
            "last_update",
            "name",
            "notes",
            "pubmed_id",
            "sequence_from_species_scientific_name",
            "start",
            "state"
        ];
        $queryBuilder = $this->connection->createQueryBuilder()
            ->select(...$fields)
            ->from("v_reporter_construct_audit");
        $queryBuilder = $this->buildPaginatedQuery(
            $queryBuilder,
            $rcSearch->getPage(),
            $rcSearch->getStart(),
            $rcSearch->getLimit()
        );
        $queryBuilder = $this->buildFilteredQuery(
            $queryBuilder,
            $rcSearch->getFilters(),
            $fields
        );
        $queryBuilder = $this->buildSortedQuery(
            $queryBuilder,
            $rcSearch->getSorters(),
            $fields
        );
        
        return QueryResult::fromQueryBuilder($queryBuilder);
    }
}
