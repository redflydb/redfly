<?php
namespace CCR\REDfly\Download\Service;

// REDfly libraries without any namespace
use DbService;
// Standard PHP Libraries (SPL)
use DomainException;
// REDfly libraries with namespaces
use CCR\REDfly\Service\Message\QueryInterface;
/**
 * Exports to the FASTA file format.
 * @link http://zhanglab.ccmb.med.umich.edu/FASTA/ The FASTA file specification.
 * Note: it is used ONLY for batch downloads of FASTA data
 * belonging to an unique REDfly entity kind
 */
class FASTAEncoder implements Encoder
{
    public function encode(
        iterable $data,
        QueryInterface $queryInterface,
        array $stagingData
    ) {
        $result = "";
        if ( count($data) !== 0 ) {
            foreach ( $data as $line ) {
                $result .= $this->process(
                    $line,
                    $queryInterface->fastaInclude
                ) . PHP_EOL;
            }
        }

        return $result;
    }
    private function process(
        array $line,
        $include
    ) {
        if ( $include === "seq" ) {
            return $this->sequence($line);
        }
        if ( $include === "flank" ) {
            return $this->withFlank($line);
        }
        if ( $include === "both" ) {
            return $this->both($line);
        }
        throw new DomainException("The sequence inclusion option is not supported.");
    }
    private function sequence(array $line)
    {
        if ( ! array_key_exists(
            "gene_locus",
            $line
        ) ) {
            return ">" . implode(
                "|",
                [
                    $line["redfly_id"],
                    $line["name"],
                    $line["sequence_from_species_scientific_name"],
                    $line["gene_name"],
                    $line["gene_identifier"],
                    $this->normalizeChromosomeName($line["chromosome"]) . ":" . $line["start"] . ".." . $line["end"],
                ]
            ) . PHP_EOL . $this->format($line["sequence"]);
        } else {
            $db = DbService::factory();
            return ">" . implode(
                "|",
                [
                    $line["redfly_id"],
                    $line["name"],
                    $line["sequence_from_species_scientific_name"],
                    $line["gene_locus"],
                    $line["gene_identifiers"],
                    $this->normalizeChromosomeName($line["chromosome"]) . ":" . $line["start"] . ".." . $line["end"],
                ]
            ) . PHP_EOL . $this->format($line["sequence"]);
        }
    }
    private function withFlank(array $line)
    {
        $sequence_with_flank = "";
        if ( isset($line["sequence_with_flank"]) ) {
            $sequence_with_flank = $this->format($line["sequence_with_flank"]);
        }
        if ( ! array_key_exists(
            "gene_locus",
            $line
        ) ) {
            return ">" . implode(
                "|",
                [
                    $line["redfly_id"],
                    $line["name"],
                    $line["sequence_from_species_scientific_name"],
                    $line["gene_name"],
                    $line["gene_identifier"],
                    $this->normalizeChromosomeName($line["chromosome"]) . ":" . $line["start"] . ".." . $line["end"],
                    "with flank"
                ]
            ) . PHP_EOL . $sequence_with_flank;
        } else {
            $db = DbService::factory();
            return ">" . implode(
                "|",
                [
                    $line["redfly_id"],
                    $line["name"],
                    $line["sequence_from_species_scientific_name"],
                    $line["gene_locus"],
                    $line["gene_identifiers"],
                    $this->normalizeChromosomeName($line["chromosome"]) . ":" . $line["start"] . ".." . $line["end"],
                    "with flank"
                ]
            ) . PHP_EOL . $sequence_with_flank;
        }
    }
    private function both(array $line)
    {
        return $this->sequence($line) . PHP_EOL . $this->withFlank($line);
    }
    private function normalizeChromosomeName(string $chromosomeName)
    {
        if ( preg_match(
            "/(NC_\d+)|(NW_\d+)/",
            $chromosomeName
        ) ) {
            return $chromosomeName;
        } else {
            return "chr" . $chromosomeName;
        }
    }
    private function format($sequence)
    {
        return implode(
            PHP_EOL,
            str_split($sequence, 80)
        );
    }
}
