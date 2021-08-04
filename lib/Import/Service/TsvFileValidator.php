<?php
namespace CCR\REDfly\Import\Service;

// Third-party libraries
use League\Csv\Reader;
/**
 * Validates TSV files based on a supplied validation strategy.
 */
class TsvFileValidator
{
    private $strategy;
    public function __construct(callable $strategy)
    {
        $this->strategy = $strategy;
    }
    /**
     * Validates an TSV file and returns an iterable that yields all errors
     * found.
     */
    public function validate(
        string $tsvFileUri,
        string $entityType,
        $existentAttributes,
        bool $updateAnatomicalExpressions
    ): iterable {
        $reader = Reader::createFromPath($tsvFileUri)
            ->setDelimiter("\t")
            ->setHeaderOffset(0)
            ->skipEmptyRecords();
        // Such a new array created by the "iterator_to_array" function begins by 1
        $dataArray = iterator_to_array($reader->getRecords());
        $dataRowsNumber = count($dataArray);
        for ( $rowIndex = 1; $rowIndex <= $dataRowsNumber; $rowIndex++ ) {
            $data = $dataArray[$rowIndex];
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
                if ( $rowKeys[$keyIndex] !== $newKey ) {
                    $data[$newKey] = $data[$rowKeys[$keyIndex]];
                    unset($data[$rowKeys[$keyIndex]]);
                }
            }
            // "Decontaminating" each array value from spaces, non-standard characters,
            // and commas
            $data = array_map(
                function ($item) {
                    return trim(
                        trim(
                            trim(
                                $item,
                                "\x00..\x20"
                            ),
                            "\x7F..\xFF"
                        ),
                        ","
                    );
                },
                $data
            );
            $dataArray[$rowIndex] = $data;
        }
        foreach ( $dataArray as $index => $row ) {
            foreach ( ($this->strategy)(
                $row,
                $entityType,
                $existentAttributes,
                $updateAnatomicalExpressions
            ) as $error ) {
                yield $index => $error;
            }
        }
    }
}
