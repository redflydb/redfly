<?php
namespace CCR\REDfly\Import\Query;

// Standard PHP Libraries (SPL)
use Exception;
// REDfly libraries with namespaces
use CCR\REDfly\Import\Service\{AttributeTsvFileValidatorFactory,
    CrossNameChecker, CrossReferenceChecker, AnatomicalExpressionTsvFileValidatorFactory,
    FastaFileValidator, FieldChecker, UniqueRowChecker};
use CCR\REDfly\Service\Message\QueryResult;
/**
 * The command handler for validating data.
 */
class ValidateImportFilesHandler
{
    private $fieldChecker;
    private $attributeTsvFileValidatorFactory;
    private $fastaFileValidator;
    private $anatomicalExpressionTsvFileValidatorFactory;
    private $uniqueRowChecker;
    private $crossNameChecker;
    private $crossReferenceChecker;
    public function __construct(
        FieldChecker $fieldChecker,
        AttributeTsvFileValidatorFactory $attributeTsvFileValidatorFactory,
        FastaFileValidator $fastaFileValidator,
        AnatomicalExpressionTsvFileValidatorFactory $anatomicalExpressionTsvFileValidatorFactory,
        UniqueRowChecker $uniqueRowChecker,
        CrossNameChecker $crossNameChecker,
        CrossReferenceChecker $crossReferenceChecker
    ) {
        $this->fieldChecker = $fieldChecker;
        $this->attributeTsvFileValidatorFactory = $attributeTsvFileValidatorFactory;
        $this->fastaFileValidator = $fastaFileValidator;
        $this->anatomicalExpressionTsvFileValidatorFactory = $anatomicalExpressionTsvFileValidatorFactory;
        $this->uniqueRowChecker = $uniqueRowChecker;
        $this->crossNameChecker = $crossNameChecker;
        $this->crossReferenceChecker = $crossReferenceChecker;
    }
    public function __invoke(ValidateImportFiles $validateImportFiles): QueryResult
    {
        $errors = [];
        //
        // First checking all the uploaded files as non-empty
        //
        if ( ($validateImportFiles->getAttributeTsvFileUri() !== "")  &&
             (filesize($validateImportFiles->getAttributeTsvFileUri()) === 0) ) {
            $errors[] = [
                "filename" => $validateImportFiles->getAttributeTsvFileName(),
                "line"     => 1,
                "error"    => "Empty input file"
            ];
        }
        if ( ($validateImportFiles->getFastaFileUri() !== "") &&
             (filesize($validateImportFiles->getFastaFileUri()) === 0) ) {
            $errors[] = [
                "filename" => $validateImportFiles->getFastaFileName(),
                "line"     => 1,
                "error"    => "Empty input file"
            ];
        }
        if ( ($validateImportFiles->getAnatomicalExpressionTsvFileUri() !== "" ) &&
            (filesize($validateImportFiles->getAnatomicalExpressionTsvFileUri()) === 0) ) {
            $errors[] = [
                "filename" => $validateImportFiles->getAnatomicalExpressionTsvFileName(),
                "line"     => 1,
                "error"    => "Empty input file"
            ];
        }
        if ( count($errors) !== 0 ) {
            return QueryResult::fromArray($errors);
        }
        //
        // Second checking all the mandatory and optional columns of each TSV file as existent
        //
        if ( $validateImportFiles->getAttributeTsvFileUri() !== "" ) {
            switch ( $validateImportFiles->getEntityType() ) {
                case "rc":
                    foreach ( $this->fieldChecker->checkAttributeRcFields($validateImportFiles->getAttributeTsvFileUri()) as $index => $error ) {
                        $errors[] = [
                            "filename" => $validateImportFiles->getAttributeTsvFileName(),
                            "line"     => 1,
                            "error"    => $error
                        ];
                    }
                    break;
                case "predicted_crm":
                    foreach ( $this->fieldChecker->checkAttributePredictedCrmFields($validateImportFiles->getAttributeTsvFileUri()) as $index => $error ) {
                        $errors[] = [
                            "filename" => $validateImportFiles->getAttributeTsvFileName(),
                            "line"     => 1,
                            "error"    => $error
                        ];
                    }
                    break;
                default:
                    throw new Exception("Unknown entity type: " . $validateImportFiles->getEntityType() .
                        " when trying to validate the attributes file");
            }
        }
        if ( $validateImportFiles->getAnatomicalExpressionTsvFileUri() !== "" ) {
            switch ( $validateImportFiles->getEntityType() ) {
                case "rc":
                    foreach ( $this->fieldChecker->checkAnatomicalExpressionRcFields($validateImportFiles->getAnatomicalExpressionTsvFileUri()) as $index => $error ) {
                        $errors[] = [
                            "filename" => $validateImportFiles->getAnatomicalExpressionTsvFileName(),
                            "line"     => 1,
                            "error"    => $error
                        ];
                    }
                    break;
                case "predicted_crm":
                    foreach ( $this->fieldChecker->checkAnatomicalExpressionPredictedCrmFields($validateImportFiles->getAnatomicalExpressionTsvFileUri()) as $index => $error ) {
                        $errors[] = [
                            "filename" => $validateImportFiles->getAnatomicalExpressionTsvFileName(),
                            "line"     => 1,
                            "error"    => $error
                        ];
                    }
                    break;
                default:
                    throw new Exception("Unknown entity type: " . $validateImportFiles->getEntityType() .
                        " when trying to validate the anatomical expressions file");
            }
        }
        if ( count($errors) !== 0 ) {
            return QueryResult::fromArray($errors);
        }
        //
        // Third checking each one of the three input files to have unique rows/coordinates
        //
        if ( $validateImportFiles->getAttributeTsvFileUri() !== "" ) {
            switch ( $validateImportFiles->getEntityType() ) {
                case "rc":
                    foreach ( $this->uniqueRowChecker->checkUniqueRcAttributeRows($validateImportFiles->getAttributeTsvFileUri()) as $index => $error ) {
                        $errors[] = [
                            "filename" => $validateImportFiles->getAttributeTsvFileName(),
                            "line"     => ($index + 2),
                            "error"    => $error
                        ];
                    }
                    break;
                case "predicted_crm":
                    foreach ( $this->uniqueRowChecker->checkUniquePredictedCrmAttributeRows($validateImportFiles->getAttributeTsvFileUri()) as $index => $error ) {
                        $errors[] = [
                            "filename" => $validateImportFiles->getAttributeTsvFileName(),
                            "line"     => ($index + 2),
                            "error"    => $error
                        ];
                    }
                    break;
                default:
                    throw new Exception("Unknown entity type: " . $validateImportFiles->getEntityType() .
                        " when trying to validate the attributes file");
            }
        }
        if ( $validateImportFiles->getFastaFileUri() !== "" ) {
            foreach ( $this->uniqueRowChecker->checkUniqueFastaCoordinates($validateImportFiles->getFastaFileUri()) as $index => $error ) {
                $errors[] = [
                    "filename" => $validateImportFiles->getFastaFileName(),
                    "line"     => $index,
                    "error"    => $error
                ];
            }
        }
        if ( $validateImportFiles->getAnatomicalExpressionTsvFileUri() !== "" ) {
            switch ( $validateImportFiles->getEntityType() ) {
                case "rc":
                    foreach ( $this->uniqueRowChecker->checkUniqueRcAnatomicalExpressionRows($validateImportFiles->getAnatomicalExpressionTsvFileUri()) as $index => $error ) {
                        $errors[] = [
                            "filename" => $validateImportFiles->getAnatomicalExpressionTsvFileName(),
                            "line"     => ($index + 2),
                            "error"    => $error
                        ];
                    }
                    break;
                case "predicted_crm":
                    foreach ( $this->uniqueRowChecker->checkUniquePredictedCrmAnatomicalExpressionRows($validateImportFiles->getAnatomicalExpressionTsvFileUri()) as $index => $error ) {
                        $errors[] = [
                            "filename" => $validateImportFiles->getAnatomicalExpressionTsvFileName(),
                            "line"     => ($index + 2),
                            "error"    => $error
                        ];
                    }
                    break;
                default:
                    throw new Exception("Unknown entity type: " . $validateImportFiles->getEntityType() .
                        " when trying to validate the anatomical expressions file");
            }
        }
        if ( count($errors) !== 0 ) {
            return QueryResult::fromArray($errors);
        }
        //
        // Fourth checking each one of the three input files for any data error
        //
        if ( $validateImportFiles->getAttributeTsvFileUri() !== "" ) {
            $attributeTsvFileValidator = $this->attributeTsvFileValidatorFactory->create();
            foreach ( $attributeTsvFileValidator->validate(
                $validateImportFiles->getAttributeTsvFileUri(),
                $validateImportFiles->getEntityType(),
                false,
                false
            ) as $index => $error ) {
                $errors[] = [
                    "filename" => $validateImportFiles->getAttributeTsvFileName(),
                    "line"     => ($index + 1),
                    "error"    => $error
                ];
            }
        }
        if ( $validateImportFiles->getFastaFileUri() !== "" ) {
            foreach ( $this->fastaFileValidator->validate($validateImportFiles->getFastaFileUri()) as $index => $error ) {
                $errors[] = [
                    "filename" => $validateImportFiles->getFastaFileName(),
                    "line"     => ($index + 1),
                    "error"    => $error
                ];
            }
        }
        if ( $validateImportFiles->getAnatomicalExpressionTsvFileUri() !== "" ) {
            $anatomicalExpressionTsvFileValidator = $this->anatomicalExpressionTsvFileValidatorFactory->create();
            foreach ( $anatomicalExpressionTsvFileValidator->validate(
                $validateImportFiles->getAnatomicalExpressionTsvFileUri(),
                $validateImportFiles->getEntityType(),
                // If there is no attributes file uploaded, then there is existent
                // attributes data in the REDfly database
                ($validateImportFiles->getAttributeTsvFileUri() === ""),
                $validateImportFiles->getUpdateAnatomicalExpressions()
            ) as $index => $error ) {
                $errors[] = [
                    "filename" => $validateImportFiles->getAnatomicalExpressionTsvFileName(),
                    "line"     => ($index + 1),
                    "error"    => $error
                ];
            }
        }
        if ( count($errors) !== 0 ) {
            return QueryResult::fromArray($errors);
        }
        //
        // Fifth checking each one of the attribute rows to have its coordinates assigned from the FASTA sequences file
        //
        if ( ($validateImportFiles->getAttributeTsvFileUri() !== "") &&
             ($validateImportFiles->getFastaFileUri() !== "") ) {
            foreach ( $this->crossReferenceChecker->checkCoordinates(
                $validateImportFiles->getAttributeTsvFileUri(),
                $validateImportFiles->getFastaFileUri()
            ) as $index => $error ) {
                $errors[] = [
                    "filename" => $validateImportFiles->getAttributeTsvFileName(),
                    "line"     => $index,
                    "error"    => $error
                ];
            }
        }
        if ( count($errors) !== 0 ) {
            return QueryResult::fromArray($errors);
        }
        //
        // Sixth checking each one of the attribute rows to have its assigned sequence getting 100% identify from the BLAT server
        //
        if ( ($validateImportFiles->getAttributeTsvFileUri() !== "") &&
            ($validateImportFiles->getFastaFileUri() !== "") ) {
            foreach ( $this->crossReferenceChecker->checkAlignments(
                $validateImportFiles->getAttributeTsvFileUri(),
                $validateImportFiles->getFastaFileUri()
            ) as $index => $error ) {
                $errors[] = [
                    "filename" => $validateImportFiles->getAttributeTsvFileName(),
                    "line"     => ($index + 1),
                    "error"    => $error
                ];
            }
        }
        if ( count($errors) !== 0 ) {
            return QueryResult::fromArray($errors);
        }
        //
        // Seventh checking both attributes and anatomical expressions files to be row-matched
        //
        if ( ($validateImportFiles->getAttributeTsvFileUri() !== "") &&
             ($validateImportFiles->getAnatomicalExpressionTsvFileUri() !== "") ) {
            switch ( $validateImportFiles->getEntityType() ) {
                case "rc":
                    foreach ( $this->crossNameChecker->checkMatchedRcRows(
                        $validateImportFiles->getAttributeTsvFileUri(),
                        $validateImportFiles->getAnatomicalExpressionTsvFileUri()
                    ) as $index => $error ) {
                            $errors[] = [
                                "filename" => $validateImportFiles->getAnatomicalExpressionTsvFileName(),
                                "line"     => ($index + 1),
                                "error"    => $error
                            ];
                    }
                    break;
                case "predicted_crm":
                    foreach ( $this->crossNameChecker->checkMatchedPredictedCrmRows(
                        $validateImportFiles->getAttributeTsvFileUri(),
                        $validateImportFiles->getAnatomicalExpressionTsvFileUri()
                    ) as $index => $error ) {
                            $errors[] = [
                                "filename" => $validateImportFiles->getAnatomicalExpressionTsvFileName(),
                                "line"     => ($index + 1),
                                "error"    => $error
                            ];
                    }
                    break;
                default:
                    throw new Exception("Unknown entity type: " . $validateImportFiles->getEntityType() .
                        " when trying to validate the anatomical expressions file");
            }
        }

        return QueryResult::fromArray($errors);
    }
}
