<?php
namespace CCR\REDfly\Service;

// Third-party libraries
use Doctrine\DBAL\Query\QueryBuilder;
trait FilteredQueryHandlerTrait
{
    private function buildFilteredQuery(
        QueryBuilder $builder,
        array $filters,
        array $whitelist
    ): QueryBuilder {
        foreach ( $filters as $filter ) {
            if ( $this->isFilterSanitized($filter, $whitelist) ) {
                $builder
                    ->andWhere("CAST(" . $filter["property"] . " AS CHAR) LIKE :" . $filter["property"])
                    ->setParameter($filter["property"], $filter["value"] . "%");
            }
        }

        return $builder;
    }
    private function isFilterSanitized(
        array $filter,
        array $whitelist
    ): bool {
        if ( empty($filter["value"]) ) {
            return false;
        }
        if ( in_array($filter["property"], $whitelist, true) ) {
            return true;
        }

        return false;
    }
}
