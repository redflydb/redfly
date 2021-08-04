<?php
namespace CCR\REDfly\Audit\Query;

// Third-party libraries
use Doctrine\DBAL\Connection;
// REDfly libraries with namespaces
use CCR\REDfly\Service\FilteredQueryHandlerTrait;
use CCR\REDfly\Service\PaginatedQueryHandlerTrait;
use CCR\REDfly\Service\SortedQueryHandlerTrait;
use CCR\REDfly\Service\Message\QueryResult;
class CrmSegmentNoTsSearchHandler
{
    use PaginatedQueryHandlerTrait;
    use SortedQueryHandlerTrait;
    use FilteredQueryHandlerTrait;
    private $connection;
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }
    public function __invoke(CrmSegmentNoTsSearch $crmSegmentNoTsSearch): QueryResult
    {
        // The fields alphabetically sorted only
        // for a better checking here
        $fields = [
            "anatomical_expression_display",
            "assayed_in_species_scientific_name",
            "chromosome_display",
            "curator_full_name",
            "end",
            "gene_display",
            "name",
            "pubmed_id",
            "sequence_from_species_scientific_name",
            "start",
            "state"
        ];
        $queryBuilder = $this->connection->createQueryBuilder()
            ->select(...$fields)
            ->from("v_cis_regulatory_module_segment_no_ts_audit");
        $queryBuilder = $this->buildPaginatedQuery(
            $queryBuilder,
            $crmSegmentNoTsSearch->getPage(),
            $crmSegmentNoTsSearch->getStart(),
            $crmSegmentNoTsSearch->getLimit()
        );
        $queryBuilder = $this->buildFilteredQuery(
            $queryBuilder,
            $crmSegmentNoTsSearch->getFilters(),
            $fields
        );
        $queryBuilder = $this->buildSortedQuery(
            $queryBuilder,
            $crmSegmentNoTsSearch->getSorters(),
            $fields
        );
        
        return QueryResult::fromQueryBuilder($queryBuilder);
    }
}
