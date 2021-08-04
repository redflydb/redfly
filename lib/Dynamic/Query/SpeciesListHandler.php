<?php
namespace CCR\REDfly\Dynamic\Query;

// Third-party libraries
use Doctrine\DBAL\Connection;
// REDfly libraries with namespaces
use CCR\REDfly\Service\Message\QueryResult;
class SpeciesListHandler
{
    private $connection;
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }
    public function __invoke(SpeciesList $query): QueryResult
    {
        $builder = $this->connection->createQueryBuilder()
            ->select(
                "species_id AS id",
                "scientific_name",
                "short_name",
                "CONCAT(scientific_name, ' (', short_name, ')') AS display"
            )->from("Species")
            ->where("LOWER(scientific_name) LIKE LOWER(:query)")
            ->setFirstResult($query->getStart())
            ->setParameter("query", "%" . $query->getQuery() . "%");
        foreach ( $query->getSort() as $sort ) {
            $builder->addOrderBy(
                $sort["property"],
                $sort["direction"]
            );
        }

        return QueryResult::fromQueryBuilder($builder);
    }
}
