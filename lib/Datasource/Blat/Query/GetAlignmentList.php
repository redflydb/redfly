<?php
namespace CCR\REDfly\Datasource\Blat\Query;

// Third-party libraries
use Psr\Http\Message\ServerRequestInterface;
// REDfly libraries with namespaces
use CCR\REDfly\Service\Exception\InvalidMessageException;
use CCR\REDfly\Service\Message\QueryInterface;
class GetAlignmentList implements QueryInterface
{
    public static function fromRequest(ServerRequestInterface $serverRequestInterface): self
    {
        $parsedBody = $serverRequestInterface->getParsedBody();
        if ( isset($parsedBody["speciesShortName"]) &&
            isset($parsedBody["sequence"]) ) {
            $speciesShortName = $parsedBody["speciesShortName"];
            $sequence = $parsedBody["sequence"];

            return new self(
                $speciesShortName,
                $sequence
            );
        }

        throw new InvalidMessageException(self::class);
    }

    private $speciesShortName;
    private $sequence;

    public function __construct(
        string $speciesShortName,
        string $sequence
    ) {
        $this->speciesShortName = $speciesShortName;
        $this->sequence = $sequence;
    }

    public function getSpeciesShortName(): string
    {
        return $this->speciesShortName;
    }

    public function getSequence(): string
    {
        return $this->sequence;
    }

    public function jsonSerialize(): array
    {
        return [];
    }
}
