<?php
namespace CCR\REDfly\Download\Query;

use CCR\REDfly\Service\Message\QueryInterface;
abstract class BatchDownloadQuery implements QueryInterface
{
    /**
     * @var string $speciesScientificName The species scientific name.
     */
    public $speciesScientificName;
    /**
     * @var string|null $bedFileType The BED file type.
     *      It must be either 'simple' or 'browser'.
     */
    public $bedFileType;
    /**
     * @var string|null $bedTrackName The track name for the BED file header.
     */
    public $bedTrackName;
    /**
     * @var string|null $bedTrackDescription The track description for the BED file
     *     header.
     */
    public $bedTrackDescription;
    /**
     * @var string|null $fastaInclude The sequence to include in the FASTA file.
     *     It must be 'seq', 'flank' or 'both'.
     */
    public $fastaInclude;
    public function jsonSerialize(): array
    {
        return [];
    }
}
