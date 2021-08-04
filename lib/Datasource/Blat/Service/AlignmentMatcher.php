<?php
namespace CCR\REDfly\Datasource\Blat\Service;

// Standard PHP Libraries (SPL)
use RuntimeException;
// REDfly libraries with namespaces
use CCR\REDfly\Service\External\BlatDataSource;
class AlignmentMatcher
{
    private $blatDataSource;
    public function __construct(BlatDataSource $blatDataSource)
    {
        $this->blatDataSource = $blatDataSource;
    }
    /**
     * Checks the sequence against the genome database chosen in the BLAT server
     * with a 95% identity mandatory and returns an array of Alignment objects.
     */
    public function get(
        string $speciesShortName,
        string $sequence
    ): Array {
        $alignmentsMap = [];
        $alignments = $this->blatDataSource->query(
            $speciesShortName,
            $sequence
        );
        foreach ( $alignments as $coordinate => $alignment ) {
            if ( (isset($alignmentsMap[$coordinate]) === false) ||
                ($alignmentsMap[$coordinate]->score < $alignment->score) ) {
                $alignmentsMap[$coordinate] = $alignment;
            }
        }

        return $alignmentsMap;
    }
}
