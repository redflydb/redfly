<?php
namespace CCR\REDfly\Dynamic\Query;

// Third-party libraries
use Doctrine\DBAL\Connection;
// REDfly libraries with namespaces
use CCR\REDfly\Service\Message\QueryResult;
class BiologicalProcessListHandler
{
    private $connection;
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }
    public function __invoke(BiologicalProcessList $query): QueryResult
    {
        if ( preg_match(
            "/^GO:\d+$/",
            $query->getQuery()
        ) ) {
            $parameter = $query->getQuery() . "%";
            $condition = "go_id LIKE :query";
        } else {
            $parameter = "%" . $query->getQuery() . "%";
            $condition = "LOWER(term) LIKE LOWER(:query)";
        }
        $builder = $this->connection->createQueryBuilder()
            ->select(
                "process_id AS id",
                "term",
                "go_id AS identifier",
                "CONCAT(term, ' (', go_id, ')') AS display"
            )->from("BiologicalProcess")
            ->where($condition)
            ->setParameter("query", $parameter);
        foreach ( $query->getSort() as $sort ) {
            $builder->addOrderBy(
                $sort["property"],
                $sort["direction"]
            );
        }

        return QueryResult::fromQueryBuilder($builder);
    }
}
