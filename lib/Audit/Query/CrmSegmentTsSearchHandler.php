<?php
namespace CCR\REDfly\Audit\Query;

// Third-party libraries
use Doctrine\DBAL\Connection;
// REDfly libraries with namespaces
use CCR\REDfly\Service\FilteredQueryHandlerTrait;
use CCR\REDfly\Service\PaginatedQueryHandlerTrait;
use CCR\REDfly\Service\SortedQueryHandlerTrait;
use CCR\REDfly\Service\Message\QueryResult;
class CrmSegmentTsSearchHandler
{
    use PaginatedQueryHandlerTrait;
    use SortedQueryHandlerTrait;
    use FilteredQueryHandlerTrait;
    private $connection;
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }
    public function __invoke(CrmSegmentTsSearch $crmSegmentTsSearch): QueryResult
    {
        // The fields alphabetically sorted only
        // for a better checking here
        $fields = [
            "anatomical_expression_display",
            "assayed_in_species_scientific_name",
            "biological_process_display",
            "chromosome_display",
            "curator_full_name",
            "ectopic",
            "end",
            "enhancer_or_silencer",
            "gene_display",
            "name",
            "off_developmental_stage_display",
            "on_developmental_stage_display",
            "pubmed_id",
            "sequence_from_species_scientific_name",
            "sex",
            "start",
            "state"
        ];
        $queryBuilder = $this->connection->createQueryBuilder()
            ->select(...$fields)
            ->from("v_cis_regulatory_module_segment_ts_audit");
        $queryBuilder = $this->buildPaginatedQuery(
            $queryBuilder,
            $crmSegmentTsSearch->getPage(),
            $crmSegmentTsSearch->getStart(),
            $crmSegmentTsSearch->getLimit()
        );
        $queryBuilder = $this->buildFilteredQuery(
            $queryBuilder,
            $crmSegmentTsSearch->getFilters(),
            $fields
        );
        $queryBuilder = $this->buildSortedQuery(
            $queryBuilder,
            $crmSegmentTsSearch->getSorters(),
            $fields
        );
        
        return QueryResult::fromQueryBuilder($queryBuilder);
    }
}
