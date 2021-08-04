<?php
namespace CCR\REDfly\Download\Service;

// REDfly libraries with namespaces
use CCR\REDfly\Service\Message\QueryInterface;
/**
 * Encoding strategy for encoding a Traversable to BED.
 * @link https://genome.ucsc.edu/FAQ/FAQformat#format1 The BED file
 *     specification.
 * Note: it is used ONLY for batch downloads of BED data
 * belonging to an unique REDfly entity kind
 */
class BEDEncoder implements Encoder
{
    public function encode(
        iterable $data,
        QueryInterface $queryInterface,
        array $stagingData
    ) {
        $result = "";
        if ( count($data) !== 0 ) {
            if ( $queryInterface->bedFileType === "browser" ) {
                $result .= $this->header($queryInterface) . PHP_EOL;
            }
            foreach ( $data as $line ) {
                $result .= $this->process(
                    $line,
                    $queryInterface
                ) . PHP_EOL;
            }
        }

        return $result;
    }
    private function header(QueryInterface $queryInterface)
    {
        return
            implode(
                " ",
                [
                    "track name=\"" . $queryInterface->bedTrackName . "\"",
                    "description=\"" . $queryInterface->bedTrackDescription . "\"",
                    "visibility=3"
                ]
            ) . PHP_EOL .
            implode(
                "\t",
                [
                    "#chrom",
                    "chromStart",
                    "chromEnd",
                    "name",
                    "score",
                    "strand",
                    "thickStart",
                    "thickEnd"
                ]
            );
    }
    private function process(
        array $line,
        QueryInterface $queryInterface
    ) {
        $processedLine = implode(
            "\t",
            [
                $this->normalizeChromosomeName($line["chromosome"]),
                $line["start"] - 1,
                $line["end"],
                $line["name"]
            ]
        );
        if ( $queryInterface->bedFileType === "browser" ) {
            $processedLine  = implode(
                "\t",
                [
                    $processedLine ,
                    "0",
                    ".",
                    $line["start"] - 1,
                    $line["end"]
                ]
            );
        }

        return $processedLine ;
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
