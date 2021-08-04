<?php
// ================================================================================
// Result of the query to BLAT.
// @see iExtDatasourceResult
// ================================================================================
class Coordinates implements iExtDatasourceResult
{
    public $chromosomeName = null;
    public $currentStart = null;
    public $currentEnd = null;
    public $size = null;
    // Note that UCSC blat coordinates are interbase (0-based) and GFF
    // format requires 1-based coordinates so correct that here.
    // Only add +1 to the start coordinate - BLAT psl coordinates, like most
    // of UCSC data, it is half-open: 0-base for start, 1-base for end.
    // Some file formats such as GFF requires 1-based so a conversion may
    // need to be made on export.
    public function __construct(
        $chromosomeName,
        $currentStart,
        $currentEnd,
        $size
    ) {
        $calculatedSize = $currentEnd - $currentStart + 1;
        if ( $size != $calculatedSize ) {
            throw new Exception("Provided coordinates ".
                "($chromosomeName:$currentStart..$currentEnd) and size ($size) " .
                "are not consistent with a 1-based coordinate representation. " .
                "Calculated size = $calculatedSize");
        }
        $this->chromosomeName = $chromosomeName;
        $this->currentStart = $currentStart;
        $this->currentEnd = $currentEnd;
        $this->size = $size;
    }
    public function format()
    {
        return $this->chromosomeName . ":" . $this->currentStart . ".." . $this->currentEnd;
    }
}
