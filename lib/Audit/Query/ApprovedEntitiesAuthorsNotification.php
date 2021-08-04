<?php
namespace CCR\REDfly\Audit\Query;

// Third-party libraries
use Psr\Http\Message\ServerRequestInterface;
// REDfly libraries with namespaces
use CCR\REDfly\Service\Message\QueryInterface;
// The DTO for notifying publication authors of newly approved entities
class ApprovedEntitiesAuthorsNotification implements QueryInterface
{
    public static function fromRequest(ServerRequestInterface $request): self
    {
        $parsedBody = $request->getParsedBody();

        return new self($parsedBody["pubmed_ids"]);
    }
    public $pubmedIds;
    public function __construct(array $pubmedIds)
    {
        $this->pubmedIds = $pubmedIds;
    }
    public function jsonSerialize(): array
    {
        return [
            "pubmed_ids" => $this->pubmedIds
        ];
    }
}
