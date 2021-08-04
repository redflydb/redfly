<?php
namespace CCR\REDfly\Import\Service;

// Third-party libraries
use League\Csv\Reader;
/**
 * Checks a TSV file for any extra field.
 */
class FieldChecker
{
    /**
    * Applied for attribute TSV files for RCs.
    */
    public function checkAttributeRcFields(string $attributeTsvFileUri): iterable
    {
        $attributeFields = array(
            "sequence_from_species",
            "pmid",
            "author_email",
            "gene_name",
            "arbitrary_name",
            "transgenic_construct",
            "evidence",
            "sequence_source",
            "coordinates",
            "notes",
            "figure_label",
            "is_negative"
        );
        $attributeFieldsNumber = count($attributeFields);
        $attributesReader = Reader::createFromPath($attributeTsvFileUri)
            ->setDelimiter("\t")
            ->setHeaderOffset(0)
            ->skipEmptyRecords();
        // Such a new array created by the "iterator_to_array" function begins by 1
        $attributeArray = iterator_to_array($attributesReader->getRecords());
        $attributeHeaders = array_keys($attributeArray[1]);
        $attributeHeadersNumber = count($attributeHeaders);
        // "Decontaminating" each array value from non-standard characters
        // and spaces
        for ( $index = 0; $index < $attributeHeadersNumber; $index++ ) {
            $attributeHeaders[$index] = strtolower(trim(
                trim(
                    $attributeHeaders[$index],
                    "\x00..\x20"
                ),
                "\x7F..\xFF"
            ));
        }
        $missingFields = "";
        for ( $index = 0; $index < $attributeFieldsNumber; $index++ ) {
            if ( array_search(
                $attributeFields[$index],
                $attributeHeaders
            ) === false ) {
                if ( $missingFields === "" ) {
                    $missingFields = $attributeFields[$index];
                } else {
                    $missingFields =  $missingFields . ", " . $attributeFields[$index];
                }
            }
        }
        if ( $missingFields !== "" ) {
            yield 0 => "There are missing field(s): " . $missingFields;
        }
        $extraFields = "";
        for ( $index = 0; $index < $attributeHeadersNumber; $index++ ) {
            if ( array_search(
                $attributeHeaders[$index],
                $attributeFields
            ) === false ) {
                if ( $extraFields === "" ) {
                    $extraFields = $attributeHeaders[$index];
                } else {
                    $extraFields =  $extraFields . ", " .  $attributeHeaders[$index];
                }
            }
        }
        if ( $extraFields !== "" ) {
            yield 0 => "There are extra field(s): " . $extraFields;
        }
    }
    /**
    * Applied for attribute TSV files for predicted CRMs.
    */
    public function checkAttributePredictedCrmFields(string $attributeTsvFileUri): iterable
    {
        $attributeFields = array(
            "sequence_from_species",
            "pmid",
            "author_email",
            "name",
            "evidence",
            "evidence_subtype",
            "sequence_source",
            "coordinates",
            "notes"
        );
        $attributeFieldsNumber = count($attributeFields);
        $attributesReader = Reader::createFromPath($attributeTsvFileUri)
            ->setDelimiter("\t")
            ->setHeaderOffset(0)
            ->skipEmptyRecords();
        // Such a new array created by the "iterator_to_array" function begins by 1
        $attributeArray = iterator_to_array($attributesReader->getRecords());
        $attributeHeaders = array_keys($attributeArray[1]);
        $attributeHeadersNumber = count($attributeHeaders);
        // "Decontaminating" each array value from non-standard characters
        // and spaces
        for ( $index = 0; $index < $attributeHeadersNumber; $index++ ) {
            $attributeHeaders[$index] = strtolower(trim(
                trim(
                    $attributeHeaders[$index],
                    "\x00..\x20"
                ),
                "\x7F..\xFF"
            ));
        }
        $missingFields = "";
        for ( $index = 0; $index < $attributeFieldsNumber; $index++ ) {
            if ( array_search(
                $attributeFields[$index],
                $attributeHeaders
            ) === false ) {
                if ( $missingFields === "" ) {
                    $missingFields = $attributeFields[$index];
                } else {
                    $missingFields =  $missingFields . ", " . $attributeFields[$index];
                }
            }
        }
        if ( $missingFields !== "" ) {
            yield 0 => "There are missing field(s): " . $missingFields;
        }
        $extraFields = "";
        for ( $index = 0; $index < $attributeHeadersNumber; $index++ ) {
            if ( array_search(
                $attributeHeaders[$index],
                $attributeFields
            ) === false ) {
                if ( $extraFields === "" ) {
                    $extraFields = $attributeHeaders[$index];
                } else {
                    $extraFields =  $extraFields . ", " . $attributeHeaders[$index];
                }
            }
        }
        if ( $extraFields !== "" ) {
            yield 0 => "There are extra field(s): " . $extraFields;
        }
    }
    /**
    * Applied for anatomical expression TSV files for RCs.
    */
    public function checkAnatomicalExpressionRcFields(string $anatomicalExpressionTsvFileUri): iterable
    {
        $anatomicalExpressionFields = array(
            "gene_name",
            "arbitrary_name",
            "assayed_in_species",
            "expression",
            "pmid",
            "stage_on",
            "stage_off",
            "biological_process",
            "sex",
            "ectopic",
            "enhancer/silencer",
            "notes"
        );
        $anatomicalExpressionFieldsNumber = count($anatomicalExpressionFields);
        $anatomicalExpressionsReader = Reader::createFromPath($anatomicalExpressionTsvFileUri)->setDelimiter("\t");
        $anatomicalExpressionHeaders = $anatomicalExpressionsReader->fetchOne();
        $anatomicalExpressionHeadersNumber = count($anatomicalExpressionHeaders);
        // "Decontaminating" each array value from non-standard characters
        // and spaces
        for ( $index = 0; $index < $anatomicalExpressionHeadersNumber; $index++ ) {
            $anatomicalExpressionHeaders[$index] = strtolower(trim(
                trim(
                    $anatomicalExpressionHeaders[$index],
                    "\x00..\x20"
                ),
                "\x7F..\xFF"
            ));
        }
        $missingFields = "";
        for ( $index = 0; $index < $anatomicalExpressionFieldsNumber; $index++ ) {
            if ( array_search(
                $anatomicalExpressionFields[$index],
                $anatomicalExpressionHeaders
            ) === false ) {
                if ( $missingFields === "" ) {
                    $missingFields = $anatomicalExpressionFields[$index];
                } else {
                    $missingFields =  $missingFields . ", " . $anatomicalExpressionFields[$index];
                }
            }
        }
        if ( $missingFields !== "" ) {
            yield 0 => "There are missing field(s): " . $missingFields;
        }
        $extraFields = "";
        for ( $index = 0; $index < $anatomicalExpressionHeadersNumber; $index++ ) {
            if ( array_search(
                $anatomicalExpressionHeaders[$index],
                $anatomicalExpressionFields
            ) === false ) {
                if ( $extraFields === "" ) {
                    $extraFields = $anatomicalExpressionHeaders[$index];
                } else {
                    $extraFields =  $extraFields . ", " . $anatomicalExpressionHeaders[$index];
                }
            }
        }
        if ( $extraFields !== "" ) {
            yield 0 => "There are extra field(s): " . $extraFields;
        }
    }
    /**
    * Applied for anatomical expression TSV files for predicted CRMs.
    */
    public function checkAnatomicalExpressionPredictedCrmFields(string $anatomicalExpressionTsvFileUri): iterable
    {
        $anatomicalExpressionFields = array(
            "name",
            "sequence_from_species",
            "expression",
            "pmid",
            "stage_on",
            "stage_off",
            "biological_process",
            "sex",
            "enhancer/silencer",
            "notes"
        );
        $anatomicalExpressionFieldsNumber = count($anatomicalExpressionFields);
        $anatomicalExpressionsReader = Reader::createFromPath($anatomicalExpressionTsvFileUri)->setDelimiter("\t");
        $anatomicalExpressionHeaders = $anatomicalExpressionsReader->fetchOne();
        $anatomicalExpressionHeadersNumber = count($anatomicalExpressionHeaders);
        // "Decontaminating" each array value from non-standard characters
        // and spaces
        for ( $index = 0; $index < $anatomicalExpressionHeadersNumber; $index++ ) {
            $anatomicalExpressionHeaders[$index] = strtolower(trim(
                trim(
                    $anatomicalExpressionHeaders[$index],
                    "\x00..\x20"
                ),
                "\x7F..\xFF"
            ));
        }
        $missingFields = "";
        for ( $index = 0; $index < $anatomicalExpressionFieldsNumber; $index++ ) {
            if ( array_search(
                $anatomicalExpressionFields[$index],
                $anatomicalExpressionHeaders
            ) === false ) {
                if ( $missingFields === "" ) {
                    $missingFields = $anatomicalExpressionFields[$index];
                } else {
                    $missingFields =  $missingFields . ", " . $anatomicalExpressionFields[$index];
                }
            }
        }
        if ( $missingFields !== "" ) {
            yield 0 => "There are missing field(s): " . $missingFields;
        }
        $extraFields = "";
        for ( $index = 0; $index < $anatomicalExpressionHeadersNumber; $index++ ) {
            if ( array_search(
                $anatomicalExpressionHeaders[$index],
                $anatomicalExpressionFields
            ) === false ) {
                if ( $extraFields === "" ) {
                    $extraFields = $anatomicalExpressionHeaders[$index];
                } else {
                    $extraFields =  $extraFields . ", " . $anatomicalExpressionHeaders[$index];
                }
            }
        }
        if ( $extraFields !== "" ) {
            yield 0 => "There are extra field(s): " . $extraFields;
        }
    }
}
