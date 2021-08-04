<?php
namespace CCR\REDfly\Import\Service;

// Third-party libraries
use League\Csv\Reader;
/**
 * Checks an attribute TSV file by cross-naming the anatomical expression TSV file to ensure that
 * the gene name and name are matched between both files.
 */
class CrossNameChecker
{
    /**
    * Checks an anatomical expression TSV file about any row mismatch with the attribute TSV file and
    * returns an iterable that yields all errors found. Applied for reporter constructs.
     */
    public function checkMatchedRcRows(
        string $attributeTsvFileUri,
        string $anatomicalExpressionTsvFileUri
    ): iterable {
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
        // Such both new arrays created by the "array_column" function begin by 0
        $attributeGeneNames = array_column(
            $attributeArray,
            "gene_name"
        );
        $attributeArbitraryNames = array_column(
            $attributeArray,
            "arbitrary_name"
        )
        ;
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
        for ( $index = 1; $index <= $anatomicalExpressionRowsNumber; $index++ ) {
            // Check if the gene name from the anatomical expression TSV file is included in the attribute TSV file
            $anatomicalExpressionGeneName = $anatomicalExpressionArray[$index]["gene_name"];
            // Only an integer key or false is returned from the array_search function
            $attributeGeneNameIndex = array_search(
                $anatomicalExpressionGeneName,
                $attributeGeneNames
            );
            if ( $attributeGeneNameIndex === false ) {
                yield $index => "Gene name: " . $anatomicalExpressionGeneName .
                    " is not matched by the attributes TSV file";
            }
            // Check if the arbitrary name from the anatomical expression TSV file is included in the attribute TSV file
            $anatomicalExpressionArbitraryName = $anatomicalExpressionArray[$index]["arbitrary_name"];
            // Only an integer key or false is returned from the array_search function
            $attributeArbitraryNameIndex = array_search(
                $anatomicalExpressionArbitraryName,
                $attributeArbitraryNames
            );
            if ( $attributeArbitraryNameIndex === false ) {
                yield $index => "Arbitrary name: " . $anatomicalExpressionArbitraryName .
                    " is not matched by the attributes TSV file";
            }
        }
    }
    /**
    * Checks an anatomical expression TSV file about any row mismatch with the attribute TSV file and
    * returns an iterable that yields all errors found. Applied for predicted CRMs.
     */
    public function checkMatchedPredictedCrmRows(
        string $attributeTsvFileUri,
        string $anatomicalExpressionTsvFileUri
    ): iterable {
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
        // Such both new arrays created by the "array_column" function begin by 0
        $attributeNames = array_column(
            $attributeArray,
            "name"
        );
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
        for ( $index = 1; $index <= $anatomicalExpressionRowsNumber; $index++ ) {
            // Check if the name from the anatomical expression TSV file is included in the attribute TSV file
            $anatomicalExpressionName = $anatomicalExpressionArray[$index]["name"];
            // Only an integer key or false is returned from the array_search function
            $attributeNameIndex = array_search(
                $anatomicalExpressionName,
                $attributeNames
            );
            if ( $attributeNameIndex === false ) {
                yield $index => "Name: " . $anatomicalExpressionName .
                    " is not matched by the attributes TSV file";
            }
        }
    }
}
