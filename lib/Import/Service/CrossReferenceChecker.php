<?php
namespace CCR\REDfly\Import\Service;

// Standard PHP Libraries (SPL)
use RuntimeException;
// Third-party libraries
use League\Csv\Reader;
// REDfly libraries with namespaces
use CCR\REDfly\Service\External\BlatDataSource;
/**
 * Checks a TSV file by cross-referencing the FASTA file to ensure that each
 * record has a corresponding entry.
 */
class CrossReferenceChecker
{
    private $blatDataSource;
    public function __construct(
        EntityInformationDao $entityInformationDao,
        BlatDataSource $blatDataSource
    ) {
        $this->entityInformationDao = $entityInformationDao;
        $this->blatDataSource = $blatDataSource;
    }
    /**
     * Cross-references an attribute TSV file against an FASTA file and returns an
     * iterable that yields all errors found.
     */
    public function checkCoordinates(
        string $attributeTsvFileUri,
        string $fastaFileUri
    ): iterable {
        // Extracts all the coordinates from the FASTA file.
        $handle = fopen(
            $fastaFileUri,
            "r"
        );
        $coordinates = [];
        $headerRegex = "/(^>|loc=)((X|2R|2L|3R|3L|4|U|Y|UNKN|Y_unplaced|Mt|MT|NC_[0-9]+\.[0-9]+|NW_[0-9]+\.[0-9]+):[0-9]+(\.\.|-)+[0-9]+)/";
        $matches = [];
        $lineNumber = 0;
        $headersNumber = 0;
        while ( ($line = fgets($handle)) ) {
            $lineNumber++;
            if ( $line[0] === ">" ) {
                $headersNumber++;
                if ( preg_match(
                    $headerRegex,
                    $line,
                    $matches
                ) === 1 ) {
                    if ( strpos(
                        $line,
                        ".."
                    ) !== false ) {
                        $coordinates[] = $matches[2];
                    } else {
                        $coordinates[] = $matches[2] . "(+)";
                    }
                } else {
                    yield $lineNumber => "Sequence header wrong found in the line #" . $lineNumber .
                        " of the FASTA file";
                }
            }
        }
        fclose($handle);
        // There is a sequence header wrong, at least
        if ( $headersNumber !== count($coordinates) ) {
            return;
        }
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
        $rowNumber = 1;
        foreach ( $attributeArray as $index => $row ) {
            if ( isset($row["coordinates"]) &&
                (in_array(
                    $row["coordinates"],
                    $coordinates
                ) === false) ) {
                yield $rowNumber => "Sequence for " . $row["coordinates"] .
                    " not found in the FASTA file";
            }
            $rowNumber++;
        }
    }
    /**
     * Checks each sequence from an FASTA file against the BLAT server with a
     * 95% identity mandatory and returns an iterable that yields all errors
     * found.
     */
    public function checkAlignments(
        string $attributeTsvFileUri,
        string $fastaFileUri
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
        $alignmentsMap = [];
        $fastaFile = fopen($fastaFileUri, "r");
        if ( $fastaFile === false ) {
            throw new RuntimeException("Cannot open FASTA file.");
        }
        $fixedFastaFile = tmpfile();
        $headerRegex = "/(^>|loc=)((X|2R|2L|3R|3L|4|U|Y|UNKN|Y_unplaced|Mt|MT|NC_[0-9]+\.[0-9]+|NW_[0-9]+\.[0-9]+):[0-9]+(\.\.|-)+[0-9]+)/";
        $matches = [];
        while ( ($line = fgets($fastaFile)) ) {
            if ( ctype_space($line) === false ) {
                if ( $line[0] === ">" ) {
                    // The sequence headers have been already sanitized before
                    preg_match(
                        $headerRegex,
                        $line,
                        $matches
                    );
                    if ( strpos(
                        $line,
                        ".."
                    ) !== false ) {
                        fwrite($fixedFastaFile, ">" . $matches[2] . "\n");
                    } else {
                        fwrite($fixedFastaFile, ">" . $matches[2] . "(+)\n");
                    }
                } else {
                    fwrite($fixedFastaFile, $line);
                }
            }
        }
        fclose($fastaFile);
        $sequenceFromSpeciesScientificName = $attributeArray[1]["sequence_from_species"];
        $sequenceFromSpeciesShortName = $this->entityInformationDao->getSpeciesShortNameByScientificName($sequenceFromSpeciesScientificName);
        $alignments = $this->blatDataSource->batchQuery(
            $sequenceFromSpeciesShortName,
            stream_get_meta_data($fixedFastaFile)["uri"]
        );
        foreach ( $alignments as $coordinates => $alignment ) {
            if ( (isset($alignmentsMap[$coordinates]) === false) ||
                ($alignmentsMap[$coordinates]->score < $alignment->score) ) {
                $alignmentsMap[$coordinates] = $alignment;
            }
        }
        $coordinates = array_keys($alignmentsMap);
        foreach ( $attributeArray as $index => $row ) {
            if ( isset($row["coordinates"]) &&
                (in_array(
                    $row["coordinates"],
                    $coordinates
                ) === false) ) {
                yield $index => "Sequence for " . $row["coordinates"] .
                    " not having 95% identity from the BLAT server";
            }
        }
    }
}
