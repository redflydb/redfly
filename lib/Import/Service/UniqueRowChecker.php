<?php
namespace CCR\REDfly\Import\Service;

// Third-party libraries
use League\Csv\Reader;
/**
 * Checks an TSV file to ensure that each row is unique from its other rows.
 */
class UniqueRowChecker
{
    public function __construct(EntityInformationDao $entityInformationDao)
    {
        $this->entityInformationDao = $entityInformationDao;
    }
    /**
     * Checks an attribute TSV file about any repeated attribute row and returns an
     * iterable that yields all errors found.
     */
    public function checkUniqueRcAttributeRows(string $attributeTsvFileUri): iterable
    {
        $attributesReader = Reader::createFromPath($attributeTsvFileUri)
            ->setDelimiter("\t")
            ->setHeaderOffset(0)
            ->skipEmptyRecords();
        // Such a new array created by the "iterator_to_array" function begins by 1
        $attributeArray = iterator_to_array($attributesReader->getRecords());
        $attributeRowsNumber = count($attributeArray);
        for ( $rowIndex = 1; $rowIndex <= $attributeRowsNumber; $rowIndex++ ) {
            $data = $attributeArray[$rowIndex];
            // "Decontaminating" each array key from non-standard characters
            // and spaces
            $rowKeys = array_keys($data);
            $rowKeysNumber = count($rowKeys);
            for ( $keyIndex = 0; $keyIndex < $rowKeysNumber; $keyIndex++ ) {
                $newKey = strtolower(trim(
                    trim(
                        $rowKeys[$keyIndex],
                        "\x00..\x20"
                    ),
                    "\x7F..\xFF"
                ));
                // The key is "contaminated" with non-standard characters
                // and spaces
                if ( $rowKeys[$keyIndex] !== $newKey ) {
                    $data[$newKey] = $data[$rowKeys[$keyIndex]];
                    unset($data[$rowKeys[$keyIndex]]);
                }
            }
            // "Decontaminating" each array value from non-standard characters
            // and spaces
            $data = array_map(
                function ($item) {
                    return trim(
                        trim(
                            $item,
                            "\x00..\x20"
                        ),
                        "\x7F..\xFF"
                    );
                },
                $data
            );
            $attributeArray[$rowIndex] = $data;
        }
        // Such a new new array created by the "array_column" function begin by 0
        $arbitraryNames = array_column(
            $attributeArray,
            "arbitrary_name"
        );
        // Check if the arbitrary name is unique in each row of the attribute TSV file
        for ( $index = 0; $index < $attributeRowsNumber; $index++ ) {
            for ( $index2 = ($index + 1); $index2 < $attributeRowsNumber; $index2++ ) {
                // Any arbitrary name must be different from the other ones,
                // even having different gene names
                if ( $arbitraryNames[$index] === $arbitraryNames[$index2] ) {
                    yield $index => "The arbitrary name, \"" . $arbitraryNames[$index] .
                        "\", must be unique-row, not repeated in the line #" .
                        ($index2 + 2);
                }
            }
        }
    }
    public function checkUniquePredictedCrmAttributeRows(string $attributeTsvFileUri): iterable
    {
        $attributesReader = Reader::createFromPath($attributeTsvFileUri)
            ->setDelimiter("\t")
            ->setHeaderOffset(0)
            ->skipEmptyRecords();
        // Such a new array created by the "iterator_to_array" function begins by 1
        $attributeArray = iterator_to_array($attributesReader->getRecords());
        $attributeRowsNumber = count($attributeArray);
        for ( $rowIndex = 1; $rowIndex <= $attributeRowsNumber; $rowIndex++ ) {
            $data = $attributeArray[$rowIndex];
            // "Decontaminating" each array key from non-standard characters
            // and spaces
            $rowKeys = array_keys($data);
            $rowKeysNumber = count($rowKeys);
            for ( $keyIndex = 0; $keyIndex < $rowKeysNumber; $keyIndex++ ) {
                $newKey = strtolower(trim(
                    trim(
                        $rowKeys[$keyIndex],
                        "\x00..\x20"
                    ),
                    "\x7F..\xFF"
                ));
                // The key is "contaminated" with non-standard characters
                // and spaces
                if ( $rowKeys[$keyIndex] !== $newKey ) {
                    $data[$newKey] = $data[$rowKeys[$keyIndex]];
                    unset($data[$rowKeys[$keyIndex]]);
                }
            }
            // "Decontaminating" each array value from non-standard characters
            // and spaces
            $data = array_map(
                function ($item) {
                    return trim(
                        trim(
                            $item,
                            "\x00..\x20"
                        ),
                        "\x7F..\xFF"
                    );
                },
                $data
            );
            $attributeArray[$rowIndex] = $data;
        }
        // Such a new new array created by the "array_column" function begin by 0
        $arbitraryNames = array_column(
            $attributeArray,
            "name"
        );
        // Check if the name is unique in each row of the attribute TSV file
        for ( $index = 0; $index < $attributeRowsNumber; $index++ ) {
            for ( $index2 = ($index + 1); $index2 < $attributeRowsNumber; $index2++ ) {
                // Any name must be different from the other ones
                if ( $arbitraryNames[$index] === $arbitraryNames[$index2] ) {
                    yield $index => "The name, \"" . $arbitraryNames[$index] .
                        "\", must be unique-row, not repeated in the line #" .
                        ($index2 + 2);
                }
            }
        }
    }
    public function checkUniqueFastaCoordinates(string $fastaFileUri): iterable
    {
        $results = [];
        $lineNumbers = [];
        $handle = fopen($fastaFileUri, "r");
        $lineNumber = 1;
        $regex = "/(^>|loc=)((X|2R|2L|3R|3L|4|U|Y|UNKN|Y_unplaced|Mt|MT|NC_[0-9]+\.[0-9]+|NW_[0-9]+\.[0-9]+):[0-9]+..[0-9]+)(\s|;)/";
        $matches = [];
        while ( ($line = fgets($handle)) ) {
            if ( ($line[0] === ">") &&
                (preg_match(
                    $regex,
                    $line,
                    $matches
                ) === 1) ) {
                if ( in_array($matches[2], $results) === true ) {
                    yield $lineNumber => "The coordinates, \"" . $matches[2] .
                        "\", must be unique, not repeated in the line #" .
                        $lineNumbers[array_search($matches[2], $results)];
                }
                $results[] = $matches[2];
                $lineNumbers[] = $lineNumber;
            }
            $lineNumber++;
        }
        fclose($handle);
    }
    /**
     * Checks an anatomical expression TSV file about any repeated anatomical expression row and
     * returns an iterable that yields all errors found.
     */
    public function checkUniqueRcAnatomicalExpressionRows(string $anatomicalExpressionTsvFileUri): iterable
    {
        $anatomicalExpressionsReader = Reader::createFromPath($anatomicalExpressionTsvFileUri)
            ->setDelimiter("\t")
            ->setHeaderOffset(0)
            ->skipEmptyRecords();
        // Such a new array created by the "iterator_to_array" function begins by 1
        $anatomicalExpressionArray = iterator_to_array($anatomicalExpressionsReader->getRecords());
        $anatomicalExpressionRowsNumber = count($anatomicalExpressionArray);
        for ( $rowIndex = 1; $rowIndex <= $anatomicalExpressionRowsNumber; $rowIndex++ ) {
            $data = $anatomicalExpressionArray[$rowIndex];
            // "Decontaminating" each array key from non-standard characters
            // and spaces
            $rowKeys = array_keys($data);
            $rowKeysNumber = count($rowKeys);
            for ( $keyIndex = 0; $keyIndex < $rowKeysNumber; $keyIndex++ ) {
                $newKey = strtolower(trim(
                    trim(
                        $rowKeys[$keyIndex],
                        "\x00..\x20"
                    ),
                    "\x7F..\xFF"
                ));
                // The key is "contaminated" with non-standard characters
                // and spaces
                if ( $rowKeys[$keyIndex] !== $newKey ) {
                    $data[$newKey] = $data[$rowKeys[$keyIndex]];
                    unset($data[$rowKeys[$keyIndex]]);
                }
            }
            // "Decontaminating" each array value from non-standard characters
            // and spaces
            $data = array_map(
                function ($item) {
                    return trim(
                        trim(
                            $item,
                            "\x00..\x20"
                        ),
                        "\x7F..\xFF"
                    );
                },
                $data
            );
            $anatomicalExpressionArray[$rowIndex] = $data;
        }
        // Such both new arrays created by the "array_column" function begin by 0
        $assayedInSpeciesScientificNames = array_column(
            $anatomicalExpressionArray,
            "assayed_in_species"
        );
        $geneNames = array_column(
            $anatomicalExpressionArray,
            "gene_name"
        );
        $arbitraryNames = array_column(
            $anatomicalExpressionArray,
            "arbitrary_name"
        );
        $anatomicalExpressionIdentifiers = $this->buildAnatomicalExpressionIdentifiers(
            $assayedInSpeciesScientificNames,
            array_column(
                $anatomicalExpressionArray,
                "expression"
            )
        );
        $stageOnIdentifiers = array_column(
            $anatomicalExpressionArray,
            "stage_on"
        );
        $stageOffIdentifiers = array_column(
            $anatomicalExpressionArray,
            "stage_off"
        );
        $biologicalProcessIdentifiers = array_column(
            $anatomicalExpressionArray,
            "biological_process"
        );
        // Check if the gene name, arbitrary name, expression, stage on, stage off, and
        // biological process are unique in each row of the expression TSV file
        for ( $index = 0; $index < $anatomicalExpressionRowsNumber; $index++ ) {
            for ( $index2 = ($index + 1); $index2 < $anatomicalExpressionRowsNumber; $index2++ ) {
                if ( ($assayedInSpeciesScientificNames[$index] === $assayedInSpeciesScientificNames[$index2]) &&
                     ($geneNames[$index] === $geneNames[$index2]) &&
                     ($arbitraryNames[$index] === $arbitraryNames[$index2]) &&
                     ($anatomicalExpressionIdentifiers[$index] === $anatomicalExpressionIdentifiers[$index2]) &&
                     ($stageOnIdentifiers[$index] === $stageOnIdentifiers[$index2]) &&
                     ($stageOffIdentifiers[$index] === $stageOffIdentifiers[$index2]) &&
                     ($biologicalProcessIdentifiers[$index] === $biologicalProcessIdentifiers[$index2]) ) {
                    yield $index => "The species name, gene name, arbitrary name, expression, stage on, " .
                        "stage off, and biological process: \"" .
                        $assayedInSpeciesScientificNames[$index] . "\", \"" .
                        $geneNames[$index] . "\", \"" .
                        $arbitraryNames[$index] . "\", \"" .
                        $anatomicalExpressionIdentifiers[$index] . "\", \"" .
                        $stageOnIdentifiers[$index] . "\", \"" .
                        $stageOffIdentifiers[$index] . "\", and \"" .
                        $biologicalProcessIdentifiers[$index] .
                        "\" must be unique-row, not repeated in the line #" . ($index2 + 2);
                }
            }
        }
    }
    public function checkUniquePredictedCrmAnatomicalExpressionRows(string $anatomicalExpressionTsvFileUri): iterable
    {
        $anatomicalExpressionsReader = Reader::createFromPath($anatomicalExpressionTsvFileUri)
            ->setDelimiter("\t")
            ->setHeaderOffset(0)
            ->skipEmptyRecords();
        // Such a new array created by the "iterator_to_array" function begins by 1
        $anatomicalExpressionArray = iterator_to_array($anatomicalExpressionsReader->getRecords());
        $anatomicalExpressionRowsNumber = count($anatomicalExpressionArray);
        for ( $rowIndex = 1; $rowIndex <= $anatomicalExpressionRowsNumber; $rowIndex++ ) {
            $data = $anatomicalExpressionArray[$rowIndex];
            // "Decontaminating" each array key from non-standard characters
            // and spaces
            $rowKeys = array_keys($data);
            $rowKeysNumber = count($rowKeys);
            for ( $keyIndex = 0; $keyIndex < $rowKeysNumber; $keyIndex++ ) {
                $newKey =  strtolower(trim(
                    trim(
                        $rowKeys[$keyIndex],
                        "\x00..\x20"
                    ),
                    "\x7F..\xFF"
                ));
                // The key is "contaminated" with non-standard characters
                // and spaces
                if ( $rowKeys[$keyIndex] !== $newKey ) {
                    $data[$newKey] = $data[$rowKeys[$keyIndex]];
                    unset($data[$rowKeys[$keyIndex]]);
                }
            }
            // "Decontaminating" each array value from non-standard characters
            // and spaces
            $data = array_map(
                function ($item) {
                    return trim(
                        trim(
                            $item,
                            "\x00..\x20"
                        ),
                        "\x7F..\xFF"
                    );
                },
                $data
            );
            $anatomicalExpressionArray[$rowIndex] = $data;
        }
        // Such both new arrays created by the "array_column" function begin by 0
        $sequenceFromSpeciesScientificNames = array_column(
            $anatomicalExpressionArray,
            "sequence_from_species"
        );
        $names = array_column(
            $anatomicalExpressionArray,
            "name"
        );
        $anatomicalExpressionIdentifiers = $this->buildAnatomicalExpressionIdentifiers(
            $sequenceFromSpeciesScientificNames,
            array_column(
                $anatomicalExpressionArray,
                "expression"
            )
        );
        $stageOnIdentifiers = array_column(
            $anatomicalExpressionArray,
            "stage_on"
        );
        $stageOffIdentifiers = array_column(
            $anatomicalExpressionArray,
            "stage_off"
        );
        $biologicalProcessIdentifiers = array_column(
            $anatomicalExpressionArray,
            "biological_process"
        );
        // Check if the name, expression, stage on, stage off, and biological process are
        // unique in each row of the expression TSV file
        for ( $index = 0; $index < $anatomicalExpressionRowsNumber; $index++ ) {
            for ( $index2 = ($index + 1); $index2 < $anatomicalExpressionRowsNumber; $index2++ ) {
                if ( ($sequenceFromSpeciesScientificNames[$index] === $sequenceFromSpeciesScientificNames[$index2]) &&
                     ($names[$index] === $names[$index2]) &&
                     ($anatomicalExpressionIdentifiers[$index] === $anatomicalExpressionIdentifiers[$index2]) &&
                     ($stageOnIdentifiers[$index] === $stageOnIdentifiers[$index2]) &&
                     ($stageOffIdentifiers[$index] === $stageOffIdentifiers[$index2]) &&
                     ($biologicalProcessIdentifiers[$index] === $biologicalProcessIdentifiers[$index2]) ) {
                    yield $index => "The species name, name, expression, stage on, stage off, and " .
                        "biological process: \"" .
                        $sequenceFromSpeciesScientificNames[$index] . "\", \"" .
                        $names[$index] . "\", \"" .
                        $anatomicalExpressionIdentifiers[$index] . "\", \"" .
                        $stageOnIdentifiers[$index] . "\", \"" .
                        $stageOffIdentifiers[$index] . "\", and \"" .
                        $biologicalProcessIdentifiers[$index] .
                        "\" must be unique-row, not repeated in the line #" . ($index2 + 2);
                }
            }
        }
    }
    private function buildAnatomicalExpressionIdentifiers(
        array $speciesScientificNames,
        array $anatomicalExpressions
    ): array {
        $anatomicalExpressionIdentifiers = array();
        $anatomicalExpressionsNumber = count($anatomicalExpressions);
        for ( $anatomicalExpressionIndex = 0; $anatomicalExpressionIndex < $anatomicalExpressionsNumber; $anatomicalExpressionIndex++ ) {
            // The first capturing group, FBbt:[0-9]{8}, refers to the Drosophila melanogaster species.
            // The second capturing group, TGMA:[0-9]{7}, refers to both Aedes aegypti and Anopheles gambiae species.
            // The third capturing group, TrOn:[0-9]{7}, refers to the Tribolium castaneum species.
            if ( preg_match(
                "/^(FBbt:[0-9]{8})|(TGMA:[0-9]{7})|(TrOn:[0-9]{7})$/",
                $anatomicalExpressions[$anatomicalExpressionIndex]
            ) !== 1 ) {
                $anatomicalExpressionIdentifiers[] = $this->entityInformationDao->getAnatomicalExpressionIdentifierByAnatomicalExpressionTerm(
                    $speciesScientificNames[$anatomicalExpressionIndex],
                    $anatomicalExpressions[$anatomicalExpressionIndex]
                );
            } else {
                $anatomicalExpressionIdentifiers[] = $anatomicalExpressions[$anatomicalExpressionIndex];
            }
        }

        return $anatomicalExpressionIdentifiers;
    }
}
