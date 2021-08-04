<?php
namespace CCR\REDfly\Import\Query;

// Third-party libraries
use Psr\Http\Message\ServerRequestInterface;
// REDfly libraries with namespaces
use CCR\REDfly\Service\Exception\InvalidMessageException;
use CCR\REDfly\Service\Message\QueryInterface;
/**
 * The command DTO for validating data.
 */
class ValidateImportFiles implements QueryInterface
{
    public static function fromRequest(ServerRequestInterface $serverRequestInterface): self
    {
        $parsedBody = $serverRequestInterface->getParsedBody();
        $uploadedFiles = $serverRequestInterface->getUploadedFiles();
        if ( isset($parsedBody["entityType"]) &&
             isset($uploadedFiles["attributeTsv"]) &&
             isset($uploadedFiles["fasta"]) &&
             isset($uploadedFiles["expressionTsv"]) ) {
            // Such an entity has two predefined values: "rc" and "predicted_crm"
            $entityType = $parsedBody["entityType"];
            $attributeTsvFileName = $uploadedFiles["attributeTsv"]->getClientFilename();
            if ( $attributeTsvFileName !== "" ) {
                $attributeTsvFileUri = $uploadedFiles["attributeTsv"]->getStream()->getMetadata()["uri"];
            } else {
                $attributeTsvFileUri = "";
            }
            $fastaFileName = $uploadedFiles["fasta"]->getClientFilename();
            if ( $fastaFileName !== "" ) {
                $fastaFileUri = $uploadedFiles["fasta"]->getStream()->getMetadata()["uri"];
            } else {
                $fastaFileUri = "";
            }
            $anatomicalExpressionTsvFileName = $uploadedFiles["expressionTsv"]->getClientFilename();
            if ( $anatomicalExpressionTsvFileName !== "" ) {
                $anatomicalExpressionTsvFileUri = $uploadedFiles["expressionTsv"]->getStream()->getMetadata()["uri"];
            } else {
                $anatomicalExpressionTsvFileUri = "";
            }
            if ( $entityType === "rc" ) {
                if ( isset($parsedBody["updateExpressions"]) &&
                    ($parsedBody["updateExpressions"] === "1") ) {
                    $updateAnatomicalExpressions = true;
                } else {
                    $updateAnatomicalExpressions = false;
                }
            } else {
                $updateAnatomicalExpressions = false;
            }

            return new self(
                $entityType,
                $attributeTsvFileName,
                $attributeTsvFileUri,
                $fastaFileName,
                $fastaFileUri,
                $anatomicalExpressionTsvFileName,
                $anatomicalExpressionTsvFileUri,
                $updateAnatomicalExpressions
            );
        }

        throw new InvalidMessageException(self::class);
    }
    private $entityType;
    private $attributeTsvFileName;
    private $attributeTsvFileUri;
    private $fastaFileName;
    private $fastaFileUri;
    private $anatomicalExpressionTsvFileName;
    private $anatomicalExpressionTsvFileUri;
    private $updateAnatomicalExpressions;
    public function __construct(
        string $entityType,
        string $attributeTsvFileName,
        string $attributeTsvFileUri,
        string $fastaFileName,
        string $fastaFileUri,
        string $anatomicalExpressionTsvFileName,
        string $anatomicalExpressionTsvFileUri,
        bool $updateAnatomicalExpressions
    ) {
        $this->entityType = $entityType;
        $this->attributeTsvFileName = $attributeTsvFileName;
        $this->attributeTsvFileUri = $attributeTsvFileUri;
        $this->fastaFileName = $fastaFileName;
        $this->fastaFileUri = $fastaFileUri;
        $this->anatomicalExpressionTsvFileName = $anatomicalExpressionTsvFileName;
        $this->anatomicalExpressionTsvFileUri = $anatomicalExpressionTsvFileUri;
        $this->updateAnatomicalExpressions = $updateAnatomicalExpressions;
    }
    public function getEntityType(): string
    {
        return $this->entityType;
    }
    public function getAttributeTsvFileName(): string
    {
        return $this->attributeTsvFileName;
    }
    public function getAttributeTsvFileUri(): string
    {
        return $this->attributeTsvFileUri;
    }
    public function getFastaFileName(): string
    {
        return $this->fastaFileName;
    }
    public function getFastaFileUri(): string
    {
        return $this->fastaFileUri;
    }
    public function getAnatomicalExpressionTsvFileName(): string
    {
        return $this->anatomicalExpressionTsvFileName;
    }
    public function getAnatomicalExpressionTsvFileUri(): string
    {
        return $this->anatomicalExpressionTsvFileUri;
    }
    public function getUpdateAnatomicalExpressions(): bool
    {
        return $this->updateAnatomicalExpressions;
    }
    public function jsonSerialize(): array
    {
        return [];
    }
}
