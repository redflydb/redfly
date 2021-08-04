<?php
namespace CCR\REDfly\Dynamic\Query;

// Third-party libraries
use Doctrine\DBAL\Connection;
// REDfly libraries with namespaces
use CCR\REDfly\Service\Message\QueryResult;
class AnatomicalExpressionListHandler
{
    private $connection;
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }
    public function __invoke(AnatomicalExpressionList $query): QueryResult
    {
        if ( preg_match(
            "/^(FBbt:\d+)|(TGMA:\d+)|(TrOn:\d+)$/",
            $query->getQuery()
        ) ) {
            $parameter = $query->getQuery() . "%";
            $condition = "identifier LIKE :query";
        } else {
            $parameter = "%" . $query->getQuery() . "%";
            $condition = "LOWER(term) LIKE LOWER(:query)";
        }
        $builder = $this->connection->createQueryBuilder()
            ->select(
                "et.term_id AS id",
                "et.term",
                "et.identifier",
                "s.short_name AS species_short_name",
                "s.scientific_name AS species_scientific_name",
                "CONCAT(et.term, ' (', et.identifier, ')') AS display"
            )->from("Species s, ExpressionTerm et")
            ->where("s.species_id = et.species_id")
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
