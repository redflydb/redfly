<?php
namespace CCR\REDfly\Import\Service;

// Standard PHP Libraries (SPL)
use UnexpectedValueException;
/**
 * Provides validation logic for individual fields in a record from a TSV file of
 * anatomical expression data.
 * All public methods are chainable, allowing for fluent-style calls.
 */
class FluentAnatomicalExpressionRecordValidator
{
    private $entityExistenceDao;
    private $entityIdDao;
    private $entityInformationDao;
    private $hasGene;
    private $hasRcName;
    private $hasPredictedCrmName;
    private $hasAnatomicalExpressionIdentifier;
    private $hasAnatomicalExpressionTerm;
    private $hasStageOnIdentifier;
    private $hasStageOffIdentifier;
    private $hasBiologicalProcessIdentifier;
    private $errors;
    public function __construct(
        EntityExistenceDao $entityExistenceDao,
        EntityIdDao $entityIdDao,
        EntityInformationDao $entityInformationDao
    ) {
        $this->entityExistenceDao = $entityExistenceDao;
        $this->entityIdDao = $entityIdDao;
        $this->entityInformationDao = $entityInformationDao;
        $this->hasGene = false;
        $this->hasRcName = false;
        $this->hasPredictedCrmName = false;
        $this->hasAnatomicalExpressionIdentifier = false;
        $this->hasAnatomicalExpressionTerm = false;
        $this->hasStageOnIdentifier = false;
        $this->hasStageOffIdentifier = false;
        $this->hasBiologicalProcessIdentifier = false;
        $this->errors = [];
    }
    // Mandatory for new and existing reporter constructs
    public function hasValidGene(array $record): self
    {
        if ( $record["gene_name"] === "" ) {
            $this->errors[] = "Invalid gene name value: \"\". Empty gene_name cell";
            return $this;
        }
        if ( $record["assayed_in_species"] !== "" ) {
            $this->hasGene = $this->entityExistenceDao->hasGene(
                $record["assayed_in_species"],
                $record["gene_name"]
            );
            if ( ! $this->hasGene ) {
                $this->errors[] = "Invalid gene name value: \"" . $record["gene_name"] .
                    "\". Not found in our database";
            }
        }

        return $this;
    }
    // Mandatory for new reporter constructs
    public function hasValidNewRcArbitraryName(array $record): self
    {
        if ( $record["arbitrary_name"] === "" ) {
            $this->errors[] = "Invalid arbitrary name value: \"\". Empty arbitrary_name cell";
            return $this;
        }
        if ( $this->hasGene ) {
            $rcName = $record["gene_name"] . "_" . $record["arbitrary_name"];
            $this->hasRcName = $this->entityExistenceDao->hasRcNameAndState(
                $rcName,
                [
                    "approval",
                    "approved",
                    "current",
                    "deleted",
                    "editing"
                ]
            );
            if ( $this->hasRcName ) {
                $this->errors[] = "Invalid arbitrary name value: \"" . $record["arbitrary_name"] .
                    "\". The reporter construct name, \"" . $rcName . "\", already exists";
            }
        }

        return $this;
    }
    // Mandatory for new predicted cis-regulatory modules
    public function hasValidNewPredictedCrmName(array $record): self
    {
        if ( $record["name"] === "" ) {
            $this->errors[] = "Invalid name value: \"\". Empty name cell";
            return $this;
        }
        $predictedCrmName = $record["name"];
        $this->hasPredictedCrmName = $this->entityExistenceDao->hasPredictedCrmNameAndState(
            $predictedCrmName,
            [
                "approval",
                "approved",
                "current",
                "editing"
            ]
        );
        if ( $this->hasPredictedCrmName ) {
            $this->errors[] = "Invalid name value: \"" . $record["name"] .
                "\". Predicted cis-regulatory module name, \"" . $predictedCrmName .
                "\", already exists";
        }

        return $this;
    }
    // Mandatory for existing reporter constructs
    public function hasValidEditingRcArbitraryName(array $record): self
    {
        if ( $record["arbitrary_name"] === "" ) {
            $this->errors[] = "Invalid arbitrary name value: \"\". Empty arbitrary_name cell";
            return $this;
        }
        if ( $this->hasGene ) {
            $rcName = $record["gene_name"] . "_" . $record["arbitrary_name"];
            $this->hasRcName = $this->entityExistenceDao->hasRcNameAndState(
                $rcName,
                ["editing"]
            );
            if ( ! $this->hasRcName ) {
                $this->errors[] = "Invalid arbitrary name value: \"" . $record["arbitrary_name"] .
                    "\". The \"editing\" state of the reporter construct name, \"" . $rcName .
                    "\", does not exist ";
            }
        }

        return $this;
    }
    // Mandatory for existing predicted cis-regulatory modules
    public function hasValidEditingPredictedCrmName(array $record): self
    {
        if ( $record["name"] === "" ) {
            $this->errors[] = "Invalid name value: \"\". Empty name cell";
            return $this;
        }
        $predictedCrmName = $record["name"];
        $this->hasPredictedCrmName = $this->entityExistenceDao->hasPredictedCrmNameAndState(
            $predictedCrmName,
            ["editing"]
        );
        if ( ! $this->hasPredictedCrmName ) {
            $this->errors[] = "Invalid name value: \"" . $predictedCrmName .
                "\". The \"editing\" state of the predicted cis-regulatory module name, \"" .
                $predictedCrmName . "\", does not exist";
        }
           
        return $this;
    }
    // Mandatory for existing reporter constructs
    public function hasValidExistingRcArbitraryName(array $record): self
    {
        if ( $record["arbitrary_name"] === "" ) {
            $this->errors[] = "Invalid arbitrary name value: \"\". Empty arbitrary_name cell";
            return $this;
        }
        if ( $this->hasGene ) {
            $rcName = $record["gene_name"] . "_" . $record["arbitrary_name"];
            $this->hasRcName = $this->entityExistenceDao->hasRcNameAndState(
                $rcName,
                [
                    "approval",
                    "approved",
                    "current",
                    "deleted",
                    "editing"
                ]
            );
            if ( ! $this->hasRcName ) {
                $this->errors[] = "Invalid arbitrary name value: \"" . $record["arbitrary_name"] .
                    "\". The reporter construct name, \"" . $rcName . "\", does not exist ";
            }
        }

        return $this;
    }
    // Mandatory for existing predicted cis-regulatory modules
    public function hasValidExistingPredictedCrmName(array $record): self
    {
        if ( $record["name"] === "" ) {
            $this->errors[] = "Invalid name value: \"\". Empty name cell";
            return $this;
        }
        $predictedCrmName = $record["name"];
        $this->hasPredictedCrmName = $this->entityExistenceDao->hasPredictedCrmNameAndState(
            $predictedCrmName,
            [
                "approval",
                "approved",
                "current",
                "editing"
            ]
        );
        if ( ! $this->hasPredictedCrmName ) {
            $this->errors[] = "Invalid name value: \"" . $predictedCrmName .
                "\". The predicted cis-regulatory module name, \"" . $predictedCrmName .
                "\", does not exist";
        }
           
        return $this;
    }
    // Mandatory for new and existing reporter constructs and
    // new and existing predicted cis-regulatory modules
    public function hasValidSpecies(array $record): self
    {
        $speciesScientificName = $this->getRightSpeciesScientificName($record);
        if ( $speciesScientificName === "" ) {
            $this->errors[] = "Invalid species value: \"\". Empty species cell";
            return $this;
        }
        if ( ! $this->entityExistenceDao->hasSpecies($speciesScientificName) ) {
            $this->errors[] = "Invalid species value: \"" . $speciesScientificName .
                "\". Not found in our database";
        }

        return $this;
    }
    // Mandatory for new and existing reporter constructs and
    // new and existing predicted cis-regulatory modules
    public function hasValidAnatomicalExpression(array $record): self
    {
        if ( $record["expression"] === "" ) {
            $this->errors[] = "Invalid anatomical expression value: \"\". Empty expression cell";
            return $this;
        }
        // The first capturing group, FBbt:[0-9]{8}, refers to the Drosophila melanogaster species.
        // The second capturing group, TGMA:[0-9]{7}, refers to both Aedes aegypti and Anopheles gambiae species.
        // The third capturing group, TrOn:[0-9]{7}, refers to the Tribolium castaneum species.
        // If there is no match following the anatomical expression identifier pattern,
        // then an anatomical expression term is expected instead
        if ( preg_match(
            "/^(FBbt:[0-9]{8})|(TGMA:[0-9]{7})|(TrOn:[0-9]{7})$/",
            $record["expression"]
        ) !== 1 ) {
            $this->hasAnatomicalExpressionTerm = $this->entityExistenceDao->hasAnatomicalExpressionTerm(
                "",
                $record["expression"]
            );
            if ( ! $this->hasAnatomicalExpressionTerm ) {
                $this->errors[] = "Invalid anatomical expression term: \"" . $record["expression"] .
                    "\". Not found in our database";
                return $this;
            } else {
                $this->hasAnatomicalExpressionIdentifier = true;
            }
        } else {
            $this->hasAnatomicalExpressionIdentifier = $this->entityExistenceDao->hasAnatomicalExpressionIdentifier(
                "",
                $record["expression"]
            );
            if ( ! $this->hasAnatomicalExpressionIdentifier ) {
                $this->errors[] = "Invalid anatomical expression identifier: \"" . $record["expression"]
                    . "\". Not found in our database";
                return $this;
            } else {
                $this->hasAnatomicalExpressionTerm = true;
            }
        }

        return $this;
    }
    // Optional for new and existing reporter constructs and
    // new and existing predicted cis-regulatory modules
    public function hasValidPmid(array $record): self
    {
        if ( $record["pmid"] === "" ) {
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
    // Mandatory for new and existing reporter constructs and
    // new and existing predicted cis-regulatory modules
    public function hasValidStageOnIdentifier(array $record): self
    {
        if ( $record["stage_on"] === "" ) {
            $this->errors[] = "Invalid stage on value: \"\". Empty stage_on cell";
            return $this;
        }
        if ( ($record["stage_on"] === "aaeg:none") ||
            ($record["stage_on"] === "agam:none") ||
            ($record["stage_on"] === "dmel:none") ||
            ($record["stage_on"] === "tcas:none") ) {
            $this->hasStageOnIdentifier = true;
            return $this;
        }
        // The capturing group, FBdv:[0-9]{8}, refers to the Drosophila melanogaster species
        if ( preg_match(
            "/^FBdv:[0-9]{8}$/",
            $record["stage_on"]
        ) !== 1 ) {
            $this->errors[] = "Invalid stage on value: \"" . $record["stage_on"] .
                "\". It must contain \"FBdv:\" followed by eight digits";
            return $this;
        }
        $this->hasStageOnIdentifier = $this->entityExistenceDao->hasDevelopmentalStageIdentifier(
            "",
            $record["stage_on"]
        );
        if ( ! $this->hasStageOnIdentifier ) {
            $this->errors[] = "Invalid stage on value: \"" . $record["stage_on"] .
                "\". Not found in our database";
        }

        return $this;
    }
    // Mandatory for new and existing reporter constructs and
    // new and existing predicted cis-regulatory modules
    public function hasValidStageOffIdentifier(array $record): self
    {
        if ( $record["stage_off"] === "" ) {
            $this->errors[] = "Invalid stage off value: \"\". Empty stage_off cell";
            return $this;
        }
        if ( ($record["stage_off"] === "aaeg:none") ||
            ($record["stage_off"] === "agam:none") ||
            ($record["stage_off"] === "dmel:none") ||
            ($record["stage_off"] === "tcas:none") ) {
            $this->hasStageOffIdentifier = true;
            return $this;
        }
        // The capturing group, FBdv:[0-9]{8}, refers to the Drosophila melanogaster species
        if ( preg_match(
            "/^FBdv:[0-9]{8}$/",
            $record["stage_off"]
        ) !== 1 ) {
            $this->errors[] = "Invalid stage off value: \"" . $record["stage_off"] .
                "\". It must contain \"FBdv:\" followed by eight digits";
            return $this;
        }
        $this->hasStageOffIdentifier = $this->entityExistenceDao->hasDevelopmentalStageIdentifier(
            "",
            $record["stage_off"]
        );
        if ( ! $this->hasStageOffIdentifier ) {
            $this->errors[] = "Invalid stage off value: \"" . $record["stage_off"] .
                "\". Not found in our database";
        }
        
        return $this;
    }
    // Optional for new and existing reporter constructs and
    // new and existing predicted cis-regulatory modules
    public function hasValidBiologicalProcessIdentifier(array $record): self
    {
        if ( $record["biological_process"] === "" ) {
            $this->hasBiologicalProcessIdentifier = true;
            return $this;
        }
        // The capturing group, GO:[0-9]{7}, refers to all the terms from the GO ontology
        if ( preg_match(
            "/^GO:[0-9]{7}$/",
            $record["biological_process"]
        ) !== 1 ) {
            $this->errors[] = "Invalid biological process value: \"" . $record["biological_process"] .
                "\". It must contain \"GO:\" followed by seven digits";
            return $this;
        }
        $this->hasBiologicalProcessIdentifier = $this->entityExistenceDao->hasBiologicalProcessIdentifier($record["biological_process"]);
        if ( ! $this->hasBiologicalProcessIdentifier ) {
            $this->errors[] = "Invalid biological process value: \"" . $record["biological_process"] .
                "\". Not found in our database";
        }

        return $this;
    }
    // Mandatory for new reporter constructs
    public function hasValidRcTripleStore(array $record): self
    {
        if ( ($this->hasGene === true) &&
            ($this->hasRcName === true) &&
            ($this->hasValidSpecies == true) &&
            ($this->hasAnatomicalExpressionIdentifier === true) &&
            ($this->hasAnatomicalExpressionTerm === true) &&
            ($this->hasStageOnIdentifier === true) &&
            ($this->hasStageOffIdentifier === true) &&
            ($this->hasBiologicalProcessIdentifier === true) ) {
            $rcName = $record["gene_name"] . "_" . $record["arbitrary_name"];
            $rcId = $this->entityIdDao->getRcIdByNameAndStates(
                $rcName,
                [
                    "approval",
                    "approved",
                    "current",
                    "deleted",
                    "editing"
                ]
            );
            // The first capturing group, FBbt:[0-9]{8}, refers to the Drosophila melanogaster species.
            // The second capturing group, TGMA:[0-9]{7}, refers to both Aedes aegypti and Anopheles gambiae species.
            // The third capturing group, TrOn:[0-9]{7}, refers to the Tribolium castaneum species.
            if ( preg_match(
                "/^(FBbt:[0-9]{8})|(TGMA:[0-9]{7})|(TrOn:[0-9]{7})$/",
                $record["expression"]
            ) !== 1 ) {
                $anatomicalExpression = $this->entityInformationDao->getAnatomicalExpressionIdentifierByAnatomicalExpressionTerm(
                    "",
                    $record["expression"]
                );
            } else {
                $anatomicalExpression = $record["expression"];
            }
            if ( $this->entityExistenceDao->hasRcTripleStore(
                $rcId,
                $anatomicalExpression,
                $record["stage_on"],
                $record["stage_off"],
                $record["biological_process"]
            ) ) {
                $this->errors[] = "Invalid staging data: \"" .
                    $record["gene_name"] . "\", \"" .
                    $record["arbitrary_name"] . "\", \"" .
                    $record["expression"] . "\", \"" .
                    $record["stage_on"] . "\", \"" .
                    $record["stage_off"] . "\" and \"" .
                    $record["biological_process"] .
                    "\". These staging data already exist";
            }
        }
    
        return $this;
    }
    // Mandatory for new predicted cis-regulatory modules
    public function hasValidPredictedCrmTripleStore(array $record): self
    {
        if ( ($this->hasPredictedCrmName === true) &&
            ($this->hasAnatomicalExpressionIdentifier === true) &&
            ($this->hasAnatomicalExpressionTerm === true) &&
            ($this->hasStageOnIdentifier === true) &&
            ($this->hasStageOffIdentifier === true) &&
            ($this->hasBiologicalProcessIdentifier === true) ) {
            $predictedCrmName = $record["name"];
            $predictedCrmId = $this->entityIdDao->getPredictedCrmIdByNameAndStates(
                $predictedCrmName,
                [
                    "approval",
                    "approved",
                    "current",
                    "editing"
                ]
            );
            // The first capturing group, FBbt:[0-9]{8}, refers to the Drosophila melanogaster species.
            // The second capturing group, TGMA:[0-9]{7}, refers to both Aedes aegypti and Anopheles gambiae species.
            // The third capturing group, TrOn:[0-9]{7}, refers to the Tribolium castaneum species.
            if ( preg_match(
                "/^(FBbt:[0-9]{8})|(TGMA:[0-9]{7})|(TrOn:[0-9]{7})$/",
                $record["expression"]
            ) !== 1 ) {
                $anatomicalExpression = $this->entityInformationDao->getAnatomicalExpressionIdentifierByAnatomicalExpressionTerm(
                    "",
                    $record["expression"]
                );
            } else {
                $anatomicalExpression = $record["expression"];
            }
            if ( $this->entityExistenceDao->hasPredictedCrmTripleStore(
                $predictedCrmId,
                $anatomicalExpression,
                $record["stage_on"],
                $record["stage_off"],
                $record["biological_process"]
            ) ) {
                $this->errors[] = "Invalid staging data: \"" .
                    $record["gene_name"] . "\", \"" .
                    $record["arbitrary_name"] . "\", \"" .
                    $record["expression"] . "\", \"" .
                    $record["stage_on"] . "\", \"" .
                    $record["stage_off"] . "\" and \"" .
                    $record["biological_process"] .
                    "\". These staging data already exist";
            }
        }
    
        return $this;
    }
    // Optional for new and existing reporter constructs and
    // new and existing predicted cis-regulatory modules
    public function hasValidSex(array $record): self
    {
        if ( $record["sex"] === "" ) {
            return $this;
        }
        if ( ! is_string($record["sex"]) ) {
            $this->errors[] = "Invalid sex value: \"" . $record["sex"] .
                "\". It must have the string type";
            return $this;
        }
        if ( ($record["sex"] !== "m") &&
            ($record["sex"] !== "f") &&
            ($record["sex"] !== "both") ) {
            $this->errors[] = "Invalid sex value: \"" . $record["sex"] .
                "\". It must be \"m\" or \"f\" or \"both\"";
        }

        return $this;
    }
    // Optional for new and existing reporter constructs
    public function hasValidEctopic(array $record): self
    {
        if ( $record["ectopic"] === "" ) {
            return $this;
        }
        if ( ! $this->isBoolean($record["ectopic"]) ) {
            $this->errors[] = "Invalid ectopic value: \"" . $record["ectopic"] .
                "\". It must be either 1 or 0, (true or false, respectively)";
        }

        return $this;
    }
    // Optional for new and existing reporter constructs and
    // new and existing predicted cis-regulatory modules
    public function hasValidEnhancerOrSilencer(array $record): self
    {
        if ( $record["enhancer/silencer"] === "" ) {
            return $this;
        }
        if ( (strtolower($record["enhancer/silencer"]) !== "enhancer") &&
            (strtolower($record["enhancer/silencer"]) !== "silencer") ) {
            $this->errors[] = "Invalid \"enhancer/silencer\" value: \"" . $record["enhancer/silencer"] .
                "\". It must be \"enhancer\" or \"silencer\"";
        }

        return $this;
    }
    // Optional for new and existing reporter constructs and
    // new and existing predicted cis-regulatory modules
    public function hasValidNotes(array $record): self
    {
        if ( $record["notes"] === "" ) {
            return $this;
        }

        return $this;
    }
    public function getErrors(): array
    {
        $this->hasGene = false;
        $this->hasRcName = false;
        $this->hasPredictedCrmName = false;
        $this->hasAnatomicalExpressionIdentifier = false;
        $this->hasAnatomicalExpressionTerm = false;
        $this->hasStageOnIdentifier = false;
        $this->hasStageOffIdentifier = false;
        $this->hasBiologicalProcessIdentifier = false;
        $result = $this->errors;
        $this->errors = [];

        return $result;
    }
    private function getRightSpeciesScientificName(array $record): string
    {
        if ( isset($record["sequence_from_species"]) &&
            (! isset($record["assayed_in_species"])) ) {
            return $record["sequence_from_species"];
        } else {
            if ( (! isset($record["sequence_from_species"])) &&
                isset($record["assayed_in_species"]) ) {
                return $record["assayed_in_species"];
            } else {
                throw new UnexpectedValueException("Species scientific name not found");
            }
        }
    }
    private function isBoolean(string $value): bool
    {
        return filter_var(
            $value,
            FILTER_VALIDATE_BOOLEAN,
            FILTER_NULL_ON_FAILURE
        ) !== null;
    }
}
