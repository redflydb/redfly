<?php
namespace CCR\REDfly\Import\Service;

// Standard PHP Libraries (SPL)
use UnexpectedValueException;
/**
 * Factory that returns the appropriate validator for validating a TSV file of attribute data.
 */
class AttributeTsvFileValidatorFactory
{
    private $fluentAttributeRecordValidator;
    public function __construct(FluentAttributeRecordValidator $fluentAttributeRecordValidator)
    {
        $this->fluentAttributeRecordValidator = $fluentAttributeRecordValidator;
    }
    public function create(): TsvFileValidator
    {
        // It is returned as the callable (renamed as strategy there) by the caller, TsvFileValidator.php
        return new TsvFileValidator(
            function (
                $record,
                $entityType,
                $existentAttributes,
                $updateAnatomicalExpressions
            ) {
                switch ( $entityType ) {
                    case "rc":
                        $this->fluentAttributeRecordValidator
                            ->hasValidSpecies($record)
                            ->hasValidPmid($record)
                            ->hasValidAuthorEmail($record)
                            ->hasValidGene($record)
                            ->hasValidRcArbitraryName($record)
                            ->hasValidTransgenicConstruct($record)
                            ->hasValidEvidence($record)
                            ->hasValidRcSequenceSource($record)
                            ->hasValidRcCoordinates($record)
                            ->hasValidNotes($record)
                            ->hasValidFigureLabel($record)
                            ->hasValidIsNegative($record);
                        break;
                    case "predicted_crm":
                        $this->fluentAttributeRecordValidator
                            ->hasValidSpecies($record)
                            ->hasValidPmid($record)
                            ->hasValidAuthorEmail($record)
                            ->hasValidPredictedCrmName($record)
                            ->hasValidEvidence($record)
                            ->hasValidEvidenceSubtype($record)
                            ->hasValidPredictedCrmSequenceSource($record)
                            ->hasValidPredictedCrmCoordinates($record)
                            ->hasValidNotes($record);
                        break;
                    default:
                        throw new UnexpectedValueException($entityType);
                }

                return $this->fluentAttributeRecordValidator->getErrors();
            }
        );
    }
}
