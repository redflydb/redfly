<?php
namespace CCR\REDfly\Dynamic\Query;

// Third-party libraries
use Doctrine\DBAL\Connection;
// REDfly libraries with namespaces
use CCR\REDfly\Service\Message\QueryResult;
class CuratorListHandler
{
    private $connection;
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }
    public function __invoke(CuratorList $query): QueryResult
    {
        $builder = $this->connection->createQueryBuilder()
            ->select(
                "user_id AS id",
                "first_name",
                "last_name",
                "CONCAT(first_name, ' ', last_name) AS full_name"
            )->from("Users")
            ->where("LOWER(first_name) LIKE LOWER(:query)")
            ->orWhere("LOWER(last_name) LIKE LOWER(:query)")
            ->setFirstResult($query->getStart())
            ->setParameter("query", $query->getQuery() . "%");
        foreach ( $query->getSort() as $sort ) {
            $builder->addOrderBy(
                $sort["property"],
                $sort["direction"]
            );
        }

        return QueryResult::fromQueryBuilder($builder);
    }
}
