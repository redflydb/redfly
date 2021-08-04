<?php
namespace CCR\REDfly\Audit\Query;

// Third-party libraries
use Psr\Http\Message\ServerRequestInterface;
trait SearchQueryTrait
{
    public static function fromRequest(ServerRequestInterface $serverRequestInterface): self
    {
        $params = $serverRequestInterface->getQueryParams();

        return new self(
            $params["page"] ?? 0,
            $params["start"] ?? 0,
            $params["limit"] ?? 0,
            json_decode($params["sort"] ?? "[]", true),
            json_decode($params["filter"] ?? "[]", true)
        );
    }
    private $page;
    private $start;
    private $limit;
    private $sorters;
    private $filters;
    public function __construct(
        int $page,
        int $start,
        int $limit,
        array $sorters,
        array $filters
    ) {
        $this->page = $page;
        $this->start = $start;
        $this->limit = $limit;
        $this->sorters = $sorters;
        $this->filters = $filters;
    }
    public function getPage(): int
    {
        return $this->page;
    }
    public function getStart(): int
    {
        return $this->start;
    }
    public function getLimit(): int
    {
        return $this->limit;
    }
    public function getSorters(): array
    {
        return $this->sorters;
    }
    public function getFilters(): array
    {
        return $this->filters;
    }
    public function jsonSerialize(): array
    {
        return [];
    }
}
