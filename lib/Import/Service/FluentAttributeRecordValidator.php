<?php
namespace CCR\REDfly\Import\Service;

/**
 * Provides validation logic for individual fields in a record from a TSV file of attribute data.
 * All public methods are chainable, allowing for fluent-style calls.
 */
class FluentAttributeRecordValidator
{
    private $hasGene;
    private $errors;
    private $entityExistenceDao;
    public function __construct(EntityExistenceDao $entityExistenceDao)
    {
        $this->hasGene = false;
        $this->errors = [];
        $this->entityExistenceDao = $entityExistenceDao;
    }
    // Mandatory for new reporter constructs and
    // new predicted cis-regulatory modules
    public function hasValidSpecies(array $record): self
    {
        if ( $record["sequence_from_species"] === "" ) {
            $this->errors[] = "Invalid \"Sequence From\" species value: \"\". Empty species cell";
            return $this;
        }
        if ( ! $this->entityExistenceDao->hasSpecies($record["sequence_from_species"]) ) {
            $this->errors[] = "Invalid \"Sequence From\" species value: \"" . $record["sequence_from_species"] .
                "\". Not found in our database";
        }

        return $this;
    }
    // Mandatory for new reporter constructs and
    // new predicted cis-regulatory modules
    public function hasValidPmid(array $record): self
    {
        if ( $record["pmid"] === "" ) {
            $this->errors[] = "Invalid Pmid value: \"\". Empty pmid cell";
            return $this;
        }
        if ( filter_var(
            $record["pmid"],
            FILTER_VALIDATE_INT
        ) === false ) {
            $this->errors[] = "Invalid Pmid value: \"" . $record["pmid"] .
                "\". It must have the integer type";
            return $this;
        }
        if ( intval($record["pmid"]) <= 0 ) {
            $this->errors[] = "Invalid Pmid value: \"" . $record["pmid"] .
                "\". It must be positive and greater than zero";
            return $this;
        }
        if ( ! $this->entityExistenceDao->hasPmid($record["pmid"]) ) {
            $this->errors[] = "Invalid Pmid value: \"" . $record["pmid"] .
                "\". No Pmid match in NCBI";
        }

        return $this;
    }
    // Optional for new reporter constructs and
    // new predicted cis-regulatory modules
    public function hasValidAuthorEmail(array $record): self
    {
        if ( $record["author_email"] === "" ) {
            return $this;
        }
        if ( ! filter_var(
            $record["author_email"],
            FILTER_VALIDATE_EMAIL
        ) ) {
            $this->errors[] = "Invalid author email value: \"" . $record["author_email"] .
                "\". Incorrect email format";
        }

        return $this;
    }
    // Mandatory for new reporter constructs
    public function hasValidGene(array $record): self
    {
        if ( $record["gene_name"] === "" ) {
            $this->errors[] = "Invalid gene name value: \"\". Empty gene_name cell";
            return $this;
        }
        if ( $record["sequence_from_species"] !== "" ) {
            $this->hasGene = $this->entityExistenceDao->hasGene(
                $record["sequence_from_species"],
                $record["gene_name"]
            );
            if ( ! $this->hasGene ) {
                $this->errors[] = "Invalid gene name value: \"" . $record["gene_name"] .
                    "\". Not found in our database";
            }
        }

        return $this;
    }
    // Mandatory for new reporter constructs about duplicates by entity name
    public function hasValidRcArbitraryName(array $record): self
    {
        if ( $record["arbitrary_name"] === "" ) {
            $this->errors[] = "Invalid arbitrary name value: \"\". Empty arbitrary_name cell";
            return $this;
        }
        if ( $this->hasGene ) {
            $rcName = $record["gene_name"] . "_" . $record["arbitrary_name"];
            if ( $this->entityExistenceDao->hasRcNameAndState(
                $rcName,
                [
                        "approval",
                        "approved",
                        "current",
                        "deleted",
                        "editing"
                    ]
            ) ) {
                $this->errors[] = "Invalid arbitrary name value: \"" . $record["arbitrary_name"] .
                    "\". Reporter construct name, \"" . $rcName . "\", already exists";
            }
        }
        
        return $this;
    }
    // Mandatory for new predicted cis-regulatory modules about duplicates by entity name
    public function hasValidPredictedCrmName(array $record): self
    {
        if ( $record["name"] === "" ) {
            $this->errors[] = "Invalid name value: \"\". Empty name cell";
            return $this;
        }
        $predictedCrmName = $record["name"];
        if ( $this->entityExistenceDao->hasPredictedCrmNameAndState(
            $predictedCrmName,
            [
                "approval",
                "approved",
                "current",
                "editing"
            ]
        )
            ) {
            $this->errors[] = "Invalid name value: \"" . $predictedCrmName .
                "\". Predicted cis-regulatory module name, \"" . $predictedCrmName .
                "\", already exists";
        }
        
        return $this;
    }
    // Optional for new reporter constructs
    public function hasValidTransgenicConstruct(array $record): self
    {
        if ( $record["transgenic_construct"] === "" ) {
            return $this;
        }
        //if ( preg_match("/^FBtp:[0-9]{6}$/", $record["transgenic_construct"]) !== 1 ) {
        //    $this->errors[] = "Invalid Transgenic Construct value: \"" .
        //    $record["transgenic_construct"] .
        //    "\". It must contain \"FBtp:\" followed by six digits";
        //}
        if ( 64 < strlen($record["transgenic_construct"]) ) {
            $this->errors[] = "Invalid transgenic construct value: \"" .
                $record["transgenic_construct"] .
                "\". It exceeds the number of 64 alphanumeric characters";
        }

        return $this;
    }
    // Mandatory for new reporter constructs and
    // new predicted cis-regulatory modules
    public function hasValidEvidence(array $record): self
    {
        if ( $record["evidence"] === "" ) {
            $this->errors[] = "Invalid evidence value: \"\". Empty evidence cell";
            return $this;
        }
        if ( ! $this->entityExistenceDao->hasEvidenceTerm($record["evidence"]) ) {
            $this->errors[] = "Invalid Evidence value: \"" . $record["evidence"] .
                "\". Not found in our database";
        }

        return $this;
    }
    // Mandatory for new predicted cis-regulatory modules
    public function hasValidEvidenceSubtype(array $record): self
    {
        if ( $record["evidence_subtype"] === "" ) {
            $this->errors[] = "Invalid evidence subtype value: \"\". Empty evidence subtype cell";
            return $this;
        }
        if ( ! $this->entityExistenceDao->hasEvidenceSubtypeTerm($record["evidence_subtype"]) ) {
            $this->errors[] = "Invalid Evidence Subtype value: \"" . $record["evidence_subtype"] .
                "\". Not found in our database";
        }

        return $this;
    }
    // Mandatory for new reporter constructs
    public function hasValidRcSequenceSource(array $record): self
    {
        if ( $record["sequence_source"] === "" ) {
            $this->errors[] = "Invalid sequence source value: \"\". Empty sequence_source cell";
            return $this;
        }

        if ( ! $this->entityExistenceDao->hasSequenceSourceTerm($record["sequence_source"]) ) {
            $this->errors[] = "Invalid sequence source value: \"" . $record["sequence_source"] .
                "\". Not found in our database";
        }

        return $this;
    }
    // Optional for new predicted cis-regulatory modules
    public function hasValidPredictedCrmSequenceSource(array $record): self
    {
        if ( $record["sequence_source"] === "" ) {
            return $this;
        }

        if ( ! $this->entityExistenceDao->hasSequenceSourceTerm($record["sequence_source"]) ) {
            $this->errors[] = "Invalid sequence source value: \"" . $record["sequence_source"] .
                "\". Not found in our database";
        }

        return $this;
    }
    // Mandatory for new reporter constructs.
    // It does not serve for any existent reporter construct if necessary
    public function hasValidRcCoordinates(array $record): self
    {
        if ( $record["coordinates"] === "" ) {
            $this->errors[] = "Invalid coordinates value: \"\". Empty coordinates cell";
            return $this;
        }
        if ( $this->isValidCoordinateString($record["coordinates"]) === false ) {
            $this->errors[] = "Invalid coordinates value: \"" . $record["coordinates"] .
                "\". It must be in the format [string]:[start]..[end]";
            return $this;
        }
        $regex = "/^(X|2R|2L|3R|3L|4|U|Y|UNKN|Y_unplaced|Mt|MT|NC_[0-9]+\.[0-9]+|NW_[0-9]+\.[0-9]+):[0-9]+..[0-9]+/";
        $matches = [];
        preg_match(
            $regex,
            $record["coordinates"],
            $matches
        );
        [
            $chromosomeName,
            $coordinates
        ] = explode(
            ":",
            $matches[0]
        );
        [
            $start,
            $end
        ] = explode(
            "..",
            $coordinates
        );
        if ( $this->entityExistenceDao->hasDuplicateRcCoordinates(
            $record["sequence_from_species"],
            $chromosomeName,
            [
                "approval",
                "approved",
                "current",
                "deleted",
                "editing"
            ],
            $start,
            $end
        ) ) {
            $duplicateCrm = "chromosome: " . $chromosomeName . ", start: " . $start .
                ", end: " . $end;
            $this->errors[] = "Invalid coordinates value: \"" . $record["coordinates"] .
                "\". Possible duplicate reporter construct, \"" . $duplicateCrm .
                "\", in our database";
        }

        return $this;
    }
    // Mandatory for new predicted cis-regulatory modules.
    // It does not serve for any existent predicted cis-regulatory module if necessary
    public function hasValidPredictedCrmCoordinates(array $record): self
    {
        if ( $record["coordinates"] === "" ) {
            $this->errors[] = "Invalid coordinates value: \"\". Empty coordinates cell";
            return $this;
        }
        if ( $this->isValidCoordinateString($record["coordinates"]) === false ) {
            $this->errors[] = "Invalid coordinates value: \"" . $record["coordinates"] .
                "\". It must be in the format [string]:[start]..[end]";
            return $this;
        }
        $regex = "/^(X|2R|2L|3R|3L|4|U|Y|UNKN|Y_unplaced|Mt|MT|NC_[0-9]+\.[0-9]+|NW_[0-9]+\.[0-9]+):[0-9]+..[0-9]+/";
        $matches = [];
        preg_match(
            $regex,
            $record["coordinates"],
            $matches
        );
        [
            $chromosomeName,
            $coordinates
        ] = explode(
            ":",
            $matches[0]
        );
        [
            $start,
            $end
        ] = explode(
            "..",
            $coordinates
        );
        if ( $this->entityExistenceDao->hasDuplicatePredictedCrmCoordinates(
            $record["sequence_from_species"],
            $chromosomeName,
            [
                "approval",
                "approved",
                "current",
                "editing"
            ],
            $start,
            $end
        ) ) {
            $duplicatePredictedCrm = "chromosome: " . $chromosomeName . ", start: " . $start .
                ", end: " . $end . ", PMID: " . $record["pmid"];
            $this->errors[] = "Invalid coordinates value: \"" . $record["coordinates"] .
                "\". Possible duplicate predicted cis-regulatory module, " .
                $duplicatePredictedCrm . ", in our database";
        }

        return $this;
    }
    // Optional for new reporter constructs and
    // new predicted cis-regulatory modules
    public function hasValidNotes(array $record): self
    {
        return $this;
    }
    // Optional for new reporter constructs
    public function hasValidFigureLabel(array $record): self
    {
        return $this;
    }
    // Mandatory for new reporter constructs
    public function hasValidIsNegative(array $record): self
    {
        if ( $record["is_negative"] === "" ) {
            $this->errors[] = "Invalid \"Is Negative\" value: \"\". Empty is_negative cell";
            return $this;
        }
        if ( ! $this->isBoolean($record["is_negative"]) ) {
            $this->errors[] = "Invalid \"Is Negative\" value: \"" . $record["is_negative"] .
                "\". It must be either 1 or 0, (true or false, respectively)";
        }

        return $this;
    }
    public function getErrors(): array
    {
        $this->hasGene = false;
        $result = $this->errors;
        $this->errors = [];

        return $result;
    }
    private function isBoolean(string $value): bool
    {
        return filter_var(
            $value,
            FILTER_VALIDATE_BOOLEAN,
            FILTER_NULL_ON_FAILURE
        ) !== null;
    }
    private function isInteger(string $value): bool
    {
        return is_int(filter_var(
            $value,
            FILTER_VALIDATE_INT
        ));
    }
    private function isValidCoordinateString(string $value): bool
    {
        $regex = "/^(X|2R|2L|3R|3L|4|U|Y|UNKN|Y_unplaced|Mt|MT|NC_[0-9]+\.[0-9]+|NW_[0-9]+\.[0-9]+):[0-9]+..[0-9]+/";
        $matches = [];
        if ( preg_match(
            $regex,
            $value,
            $matches
        ) === 1 ) {
            $exploded = explode(
                "..",
                substr(
                    $matches[0],
                    strpos($matches[0], ":") + 1
                )
            );
            if ( count($exploded) === 2 ) {
                [
                    $start,
                    $end
                ] = $exploded;
                if ( (! $this->isInteger($start)) ||
                    (! $this->isInteger($end)) ) {
                    return false;
                }
                return $start < $end;
            }
        }
        return false;
    }
}
