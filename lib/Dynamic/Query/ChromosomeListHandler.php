<?php
namespace CCR\REDfly\Dynamic\Query;

// Third-party libraries
use Doctrine\DBAL\Connection;
// REDfly libraries with namespaces
use CCR\REDfly\Service\Message\QueryResult;
class ChromosomeListHandler
{
    private $connection;
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }
    public function __invoke(ChromosomeList $query): QueryResult
    {
        $builder = $this->connection->createQueryBuilder()
            ->select(
                "c.chromosome_id AS id",
                "c.name",
                "s.short_name AS species_short_name",
                "s.scientific_name AS species_scientific_name",
                "CONCAT(c.name, ' (', s.short_name, ')') AS display",
                "CONCAT(s.short_name, c.name) AS display_sort"
            )->from("Species s, GenomeAssembly ga, Chromosome c")
            ->where("s.species_id = ga.species_id")
            ->andWhere("ga.is_deprecated = 0")
            ->andWhere("ga.genome_assembly_id = c.genome_assembly_id")
            ->andWhere("LOWER(c.name) LIKE LOWER(:query)")
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
