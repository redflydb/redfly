<?php
namespace CCR\REDfly\Import\Service;

// Standard PHP Libraries (SPL)
use UnexpectedValueException;
/**
 * Factory that returns the appropriate validator for validating a TSV file of anatomical expression data.
 */
class AnatomicalExpressionTsvFileValidatorFactory
{
    private $fluentAnatomicalExpressionRecordValidator;
    public function __construct(FluentAnatomicalExpressionRecordValidator $fluentAnatomicalExpressionRecordValidator)
    {
        $this->fluentAnatomicalExpressionRecordValidator = $fluentAnatomicalExpressionRecordValidator;
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
                if ( ($entityType !== "rc") &&
                    ($entityType !== "predicted_crm") ) {
                    throw new UnexpectedValueException("Unknown entity type: " . $entityType);
                }
                if ( $entityType === "rc" ) {
                    $this->fluentAnatomicalExpressionRecordValidator
                        ->hasValidGene($record);
                }
                // Saving new attributes data
                if ( ! $existentAttributes ) {
                    if ( $entityType === "rc" ) {
                        $this->fluentAnatomicalExpressionRecordValidator
                            ->hasValidNewRcArbitraryName($record);
                    } else {
                        $this->fluentAnatomicalExpressionRecordValidator
                            ->hasValidNewPredictedCrmName($record);
                    }
                } // Having existing attributes data already
                else {
                    if ( ! $updateAnatomicalExpressions ) {
                        if ( $entityType === "rc" ) {
                            $this->fluentAnatomicalExpressionRecordValidator
                                ->hasValidEditingRcArbitraryName($record);
                        }
                    } else {
                        if ( $entityType === "rc" ) {
                            $this->fluentAnatomicalExpressionRecordValidator
                                ->hasValidExistingRcArbitraryName($record);
                        }
                    }
                }
                $this->fluentAnatomicalExpressionRecordValidator
                    ->hasValidSpecies($record)
                    ->hasValidAnatomicalExpression($record)
                    ->hasValidPmid($record)
                    ->hasValidStageOnIdentifier($record)
                    ->hasValidStageOffIdentifier($record)
                    ->hasValidBiologicalProcessIdentifier($record);
                if ( ($entityType === "rc") &&
                    $existentAttributes &&
                    (! $updateAnatomicalExpressions) ) {
                    $this->fluentAnatomicalExpressionRecordValidator
                        ->hasValidRcTripleStore($record);
                }
                $this->fluentAnatomicalExpressionRecordValidator
                    ->hasValidSex($record);
                if ( $entityType === "rc" ) {
                    $this->fluentAnatomicalExpressionRecordValidator
                        ->hasValidEctopic($record);
                }
                $this->fluentAnatomicalExpressionRecordValidator
                    ->hasValidEnhancerOrSilencer($record)
                    ->hasValidNotes($record);
            
                return $this->fluentAnatomicalExpressionRecordValidator->getErrors();
            }
        );
    }
}
