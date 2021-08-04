<?php
namespace CCR\REDfly\Service\Message;

// Standard PHP Libraries (SPL)
use JsonSerializable, RuntimeException;
// Third-party libraries
use Doctrine\DBAL\Query\QueryBuilder;
/**
 * Provides a wrapper for a query result. This should be returned by a query
 * handler.
 */
class QueryResult implements JsonSerializable
{
    public static function fromArray(array $results): self
    {
        $total = count($results);
        return new self($total, $results);
    }
    public static function fromQueryBuilder(QueryBuilder $builder): self
    {
        $statement = $builder->execute();
        if ( is_int($statement) ) {
            throw new RuntimeException("Failed to fetch the results from the database.");
        }
        $results = $statement->fetchAll();
        $statement = $builder
            ->select("COUNT(*)")
            ->setFirstResult(0)
            ->setMaxResults(PHP_INT_MAX)
            ->execute();
        if ( is_int($statement) ) {
            throw new RuntimeException("Failed to fetch the total count of results.");
        }
        $total = $statement->fetchColumn();

        return new self((int)$total, $results);
    }
    /** @var int $total Total query result count. */
    private $total;
    /** @var array $results Query results. */
    private $results;
    public function __construct(
        int $total = 0,
        array $results = []
    ) {
        $this->total = $total;
        $this->results = $results;
    }
    public function getTotal(): int
    {
        return $this->total;
    }
    public function getResults(): array
    {
        return $this->results;
    }
    public function jsonSerialize(): array
    {
        return [
            "success" => true,
            "total"   => $this->total,
            "results" => $this->results
        ];
    }
}
