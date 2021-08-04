<?php
namespace CCR\REDfly\Service\External\Model;

/**
 * Model representing an alignment returned from a BLAT query.
 */
class Alignment
{
    /**
     * @var string $strand Strand ("+" or "-").
     */
    public $strand;
    /**
     * @var string $chromosomeName Chromosome name.
     */
    public $chromosomeName;
    /**
     * @var int $startCoordinate Start coordinate.
     */
    public $startCoordinate;
    /**
     * @var int $endCoordinate End coordinate.
     */
    public $endCoordinate;
    /**
     * @var string $sequence Sequence.
     */
    public $sequence;
    /**
     * @var int $score Accuracy score for this alignment.
     */
    public $score;
    public function __construct(
        string $strand,
        string $chromosomeName,
        int $startCoordinate,
        int $endCoordinate,
        string $sequence,
        int $matchesNumber,
        int $repeatMatchesNumber,
        int $mismatchesNumber,
        int $queryInsertsNumber,
        int $targetInsertsNumber
    ) {
        $this->strand = $strand;
        $this->chromosomeName = $chromosomeName;
        /**
         * Note that UCSC blat coordinates are interbase (0-based) and GFF
         * format requires 1-based coordinates so correct that here.
         * Only add +1 to the start coordinate - BLAT psl coordinates,
         * like most of UCSC data, is half-open: 0-base for start, 1-base for end.
         * Some file formats such as GFF requires 1-based so a conversion may
         * need to be made on export.
         */
        $this->startCoordinate = $startCoordinate + 1;
        $this->endCoordinate = $endCoordinate;
        $this->sequence = $sequence;
        $this->score = $this->calculateScore(
            $matchesNumber,
            $repeatMatchesNumber,
            $mismatchesNumber,
            $queryInsertsNumber,
            $targetInsertsNumber
        );
    }
    /**
     * Calculates the accuracy score of the alignment.
     * The calculated score is based on a formula discussed on page 16 of the paper
     * 'Using BLAT to Find Sequence Similarity in Closely Related Genomes':
     * https://www.ncbi.nlm.nih.gov/pmc/articles/PMC4101998/pdf/nihms572788.pdf
     * @param int $matchesNumber Number of matches.
     * @param int $repeatMatchesNumber Number of multiple matches.
     * @param int $mismatchesNumber Number of mismatches.
     * @param int $queryInsertsNumber Query gap count.
     * @param int $targetInsertsNumber Homologous genome region gap count.
     * @return int The calculated score.
     */
    private function calculateScore(
        int $matchesNumber,
        int $repeatMatchesNumber,
        int $mismatchesNumber,
        int $queryInsertsNumber,
        int $targetInsertsNumber
    ) {
        return ($matchesNumber + $repeatMatchesNumber) - $mismatchesNumber - $queryInsertsNumber - $targetInsertsNumber;
    }
}
