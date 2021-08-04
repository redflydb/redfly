<?php
namespace CCR\REDfly\Service;

// Third-party libraries
use Doctrine\DBAL\Query\QueryBuilder;
trait PaginatedQueryHandlerTrait
{
    private function buildPaginatedQuery(
        QueryBuilder $builder,
        int $page,
        int $start,
        int $limit
    ): QueryBuilder {
        return $builder
            ->setFirstResult($start)
            ->setMaxResults($limit);
    }
}
