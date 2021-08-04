<?php
namespace CCR\REDfly\Service;

// Third-party libraries
use Doctrine\DBAL\Query\QueryBuilder;
trait SortedQueryHandlerTrait
{
    private function buildSortedQuery(
        QueryBuilder $builder,
        array $sorters,
        array $whitelist
    ): QueryBuilder {
        foreach ( $sorters as $sorter ) {
            if ( $this->isSorterSanitized($sorter, $whitelist) ) {
                $builder->addOrderBy($sorter["property"], $sorter["direction"]);
            }
        }

        return $builder;
    }
    private function isSorterSanitized(
        array $sorter,
        array $whitelist
    ): bool {
        if ( $sorter["direction"] === "ASC" || $sorter["direction"] === "DESC" ) {
            if ( in_array($sorter["property"], $whitelist, true) ) {
                return true;
            }
        }

        return false;
    }
}
