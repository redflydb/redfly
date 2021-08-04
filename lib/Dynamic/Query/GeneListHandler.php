<?php
namespace CCR\REDfly\Dynamic\Query;

// Third-party libraries
use Doctrine\DBAL\Connection;
// REDfly libraries with namespaces
use CCR\REDfly\Service\Message\QueryResult;
class GeneListHandler
{
    private $connection;
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }
    public function __invoke(GeneList $query): QueryResult
    {
        if ( preg_match(
            "/^(AAEL\d+)|(AGAP\d+)|(FBgn\d+)|(TC\d+)$/",
            $query->getQuery()
        ) ) {
            $parameter = $query->getQuery() . "%";
            $condition = "identifier LIKE :query";
        } else {
            $parameter = "%" . $query->getQuery() . "%";
            $condition = "LOWER(name) LIKE LOWER(:query)";
        }
        $builder = $this->connection->createQueryBuilder()
            ->select(
                "g.gene_id AS id",
                "g.name",
                "g.identifier",
                "s.short_name AS species_short_name",
                "s.scientific_name AS species_scientific_name",
                "CONCAT(g.name, ' (', g.identifier, ')') AS display"
            )->from("Species s, Gene g")
            ->where("s.species_id = g.species_id")
            ->andWhere($condition)
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
