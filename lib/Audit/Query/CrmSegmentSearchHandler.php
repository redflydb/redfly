<?php
namespace CCR\REDfly\Audit\Query;

// Third-party libraries
use Doctrine\DBAL\Connection;
// REDfly libraries with namespaces
use CCR\REDfly\Service\FilteredQueryHandlerTrait;
use CCR\REDfly\Service\PaginatedQueryHandlerTrait;
use CCR\REDfly\Service\SortedQueryHandlerTrait;
use CCR\REDfly\Service\Message\QueryResult;
class CrmSegmentSearchHandler
{
    use PaginatedQueryHandlerTrait;
    use SortedQueryHandlerTrait;
    use FilteredQueryHandlerTrait;
    private $connection;
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }
    public function __invoke(CrmSegmentSearch $crmSegmentSearch): QueryResult
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
            "evidence_subtype",
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
            ->from("v_cis_regulatory_module_segment_audit");
        $queryBuilder = $this->buildPaginatedQuery(
            $queryBuilder,
            $crmSegmentSearch->getPage(),
            $crmSegmentSearch->getStart(),
            $crmSegmentSearch->getLimit()
        );
        $queryBuilder = $this->buildFilteredQuery(
            $queryBuilder,
            $crmSegmentSearch->getFilters(),
            $fields
        );
        $queryBuilder = $this->buildSortedQuery(
            $queryBuilder,
            $crmSegmentSearch->getSorters(),
            $fields
        );
        
        return QueryResult::fromQueryBuilder($queryBuilder);
    }
}
