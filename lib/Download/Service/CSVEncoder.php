<?php
namespace CCR\REDfly\Download\Service;

// REDfly libraries without any namespace
use DbService;
// REDfly libraries with namespaces
use CCR\REDfly\Service\Message\QueryInterface;
/**
 * Encoding strategy for encoding a Traversable to CSV.
 * Note: it is used ONLY for batch downloads of CSV data
 * belonging to an unique REDfly entity kind
 */
class CSVEncoder implements Encoder
{
    public function encode(
        iterable $data,
        QueryInterface $queryInterface,
        array $stagingData
    ) {
        $result = "";
        if ( count($data) !== 0 ) {
            if ( ! array_key_exists(
                "gene_locus",
                ((array) $data)[0]
            ) ) {
                $result .= $this->header() . PHP_EOL;
                foreach ( $data as $line ) {
                    $result .= $this->process($line) . PHP_EOL;
                }
            } else {
                $result = $this->alternativeHeader() . PHP_EOL;
                foreach ( $data as $line ) {
                    $result .= $this->alternativeProcess($line) . PHP_EOL;
                }
            }
        }

        return $result;
    }
    private function header()
    {
        return implode(
            ",",
            [
                "name",
                "species_scientific_name",
                "gene_name",
                "identifier",
                "coordinates",
                "sequence"
            ]
        );
    }
    private function alternativeHeader()
    {
        return implode(
            ",",
            [
                "name",
                "species_scientific_name",
                "gene_locus",
                "identifier",
                "coordinates",
                "sequence"
            ]
        );
    }
    private function process(array $line)
    {
        return implode(
            ",",
            [
                $line["name"],
                $line["sequence_from_species_scientific_name"],
                $line["gene_name"],
                $line["gene_identifier"],
                $this->normalizeChromosomeName($line["chromosome"]) . ":" . $line["start"] . ".." . $line["end"],
                $line["sequence"]
            ]
        );
    }
    private function alternativeProcess(array $line)
    {
        $db = DbService::factory();

        return implode(
            ",",
            [
                $line["name"],
                $line["sequence_from_species_scientific_name"],
                "\"" . $line["gene_locus"] . "\"",
                "\"" . $line["gene_identifiers"] . "\"",
                $this->normalizeChromosomeName($line["chromosome"]) . ":" . $line["start"] . ".." . $line["end"],
                $line["sequence"]
            ]
        );
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
}
