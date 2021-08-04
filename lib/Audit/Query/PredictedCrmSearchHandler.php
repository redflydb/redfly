<?php
namespace CCR\REDfly\Audit\Query;

// Third-party libraries
use Doctrine\DBAL\Connection;
// REDfly libraries with namespaces
use CCR\REDfly\Service\FilteredQueryHandlerTrait;
use CCR\REDfly\Service\PaginatedQueryHandlerTrait;
use CCR\REDfly\Service\SortedQueryHandlerTrait;
use CCR\REDfly\Service\Message\QueryResult;
class PredictedCrmSearchHandler
{
    use PaginatedQueryHandlerTrait;
    use SortedQueryHandlerTrait;
    use FilteredQueryHandlerTrait;
    private $connection;
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }
    public function __invoke(PredictedCrmSearch $predictedCrmSearch): QueryResult
    {
        // The fields alphabetically sorted only
        // for a better checking here
        $fields = [
            "anatomical_expression_displays",
            "auditor_full_name",
            "chromosome_display",
            "coordinates",
            "curator_full_name",
            "curator_id",
            "date_added",
            "end",
            "evidence",
            "evidence_subtype",
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
            ->from("v_predicted_cis_regulatory_module_audit");
        $queryBuilder = $this->buildPaginatedQuery(
            $queryBuilder,
            $predictedCrmSearch->getPage(),
            $predictedCrmSearch->getStart(),
            $predictedCrmSearch->getLimit()
        );
        $queryBuilder = $this->buildFilteredQuery(
            $queryBuilder,
            $predictedCrmSearch->getFilters(),
            $fields
        );
        $queryBuilder = $this->buildSortedQuery(
            $queryBuilder,
            $predictedCrmSearch->getSorters(),
            $fields
        );
        
        return QueryResult::fromQueryBuilder($queryBuilder);
    }
}
