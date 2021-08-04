<?php
namespace CCR\REDfly\Dynamic\Query;

// Third-party libraries
use Psr\Http\Message\ServerRequestInterface;
trait DynamicQueryTrait
{
    public static function fromRequest(ServerRequestInterface $request): self
    {
        $params = $request->getQueryParams();

        return new self(
            $params["query"] ?? "",
            $params["page"] ?? 0,
            $params["start"] ?? 0,
            $params["limit"] ?? 0,
            json_decode($params["sort"] ?? "[]", true)
        );
    }
    private $page;
    private $start;
    private $limit;
    private $sort;
    private $query;
    public function __construct(
        string $query,
        int $page,
        int $start,
        int $limit,
        array $sort
    ) {
        $this->query = $query;
        $this->page = $page;
        $this->start = $start;
        $this->limit = $limit;
        $this->sort = $sort;
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
    public function getSort(): array
    {
        return $this->sort;
    }
    public function getQuery(): string
    {
        return $this->query;
    }
    public function jsonSerialize(): array
    {
        return [
            "page"  => $this->getPage(),
            "start" => $this->getStart(),
            "limit" => $this->getLimit(),
            "sort"  => $this->getSort(),
            "query" => $this->getQuery()
        ];
    }
}
