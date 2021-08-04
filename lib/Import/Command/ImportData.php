<?php
namespace CCR\REDfly\Import\Command;

// Third-party libraries
use Psr\Http\Message\ServerRequestInterface;
// REDfly libraries with namespaces
use CCR\REDfly\Service\Exception\InvalidMessageException;
use CCR\REDfly\Service\Message\CommandInterface;
/**
 * The command DTO for importing data.
 */
class ImportData implements CommandInterface
{
    public static function fromRequest(ServerRequestInterface $serverRequestInterface): self
    {
        $parsedBody = $serverRequestInterface->getParsedBody();
        $uploadedFiles = $serverRequestInterface->getUploadedFiles();
        if ( isset($parsedBody["entityType"]) &&
            isset($uploadedFiles["attributeTsv"]) &&
            isset($uploadedFiles["fasta"]) &&
            isset($uploadedFiles["expressionTsv"]) ) {
            // Such an entity type has two predefined values: "rc" and "predicted_crm"
            $entityType = $parsedBody["entityType"];
            if ( $uploadedFiles["attributeTsv"]->getClientFilename() !== "" ) {
                $attributeTsvFileUri = $uploadedFiles["attributeTsv"]->getStream()->getMetadata()["uri"];
            } else {
                $attributeTsvFileUri = "";
            }
            if ( $uploadedFiles["fasta"]->getClientFilename() !== "" ) {
                $fastaFileUri = $uploadedFiles["fasta"]->getStream()->getMetadata()["uri"];
            } else {
                $fastaFileUri = "";
            }
            if ( $uploadedFiles["expressionTsv"]->getClientFilename() !== "" ) {
                $anatomicalExpressionTsvFileUri = $uploadedFiles["expressionTsv"]->getStream()->getMetadata()["uri"];
            } else {
                $anatomicalExpressionTsvFileUri = "";
            }
            if ( isset($parsedBody["updateExpressions"]) &&
                 ($parsedBody["updateExpressions"] === "1") ) {
                $updateAnatomicalExpressions = true;
            } else {
                $updateAnatomicalExpressions = false;
            }

            return new self(
                $serverRequestInterface->getAttribute("username"),
                $serverRequestInterface->getAttribute("password"),
                $entityType,
                $attributeTsvFileUri,
                $fastaFileUri,
                $anatomicalExpressionTsvFileUri,
                $updateAnatomicalExpressions
            );
        }

        throw new InvalidMessageException(self::class);
    }
    private $username;
    private $password;
    private $entityType;
    private $attributeTsvFileUri;
    private $fastaFileUri;
    private $anatomicalExpressionTsvFileUri;
    private $updateAnatomicalExpressions;
    public function __construct(
        string $username,
        string $password,
        string $entityType,
        string $attributeTsvFileUri,
        string $fastaFileUri,
        string $anatomicalExpressionTsvFileUri,
        bool $updateAnatomicalExpressions
    ) {
        $this->username = $username;
        $this->password = $password;
        $this->entityType = $entityType;
        $this->attributeTsvFileUri = $attributeTsvFileUri;
        $this->fastaFileUri = $fastaFileUri;
        $this->anatomicalExpressionTsvFileUri = $anatomicalExpressionTsvFileUri;
        $this->updateAnatomicalExpressions = $updateAnatomicalExpressions;
    }
    public function getUsername(): string
    {
        return $this->username;
    }
    public function getPassword(): string
    {
        return $this->password;
    }
    public function getEntityType(): string
    {
        return $this->entityType;
    }
    public function getAttributeTsvFileUri(): string
    {
        return $this->attributeTsvFileUri;
    }
    public function getFastaFileUri(): string
    {
        return $this->fastaFileUri;
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
