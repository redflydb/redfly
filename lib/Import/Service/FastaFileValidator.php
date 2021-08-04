<?php
namespace CCR\REDfly\Import\Service;

/**
 * Validates FASTA files.
 */
class FastaFileValidator
{
    /**
     * Validates an FASTA file and returns an iterable that yields all errors
     * found.
     */
    private function isInvalidSequence(string $sequence): bool
    {
        return preg_match(
            "/[^ATGCN]/i",
            trim($sequence)
        ) !== 0;
    }
    public function validate(string $fastaFileUri): iterable
    {
        $handle = fopen($fastaFileUri, "r");
        $new = false;
        $index = 0;
        $size = 0;
        while ( ($line = fgets($handle)) ) {
            if ( ctype_space($line) === false ) {
                if ( $line[0] === ">" ) {
                    if ( $new ) {
                        yield $index => "An entry must contain a sequence on its own line(s)";
                    }
                    $new = true;
                    $size = 0;
                } else {
                    if ( $this->isInvalidSequence($line) ) {
                        yield $index => "Invalid nucleic acid notation; may only contain A, T, G, C and/or N";
                    }
                    $new = false;
                    $size += strlen($line);
                    if ( $size > 20000 ) {
                        yield $index => "Sequence too large; sequences exceeding 20,000 characters long is not supported";
                    }
                }
            }
            $index++;
        }
        fclose($handle);
    }
}
