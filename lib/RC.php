<?php
// ======================================================================
// Reporter Construct class. RC information such as database id,
// name, coordinates, and anatomical expression terms are defined as
// well as functionality for determining if the one RC encloses another
// or if an RC sequence overlaps another.
// ======================================================================
class RC
{
    public $size = 0;
    protected $id = null;
    protected $name = null;
    protected $geneId = null;
    protected $chromosomeId = null;
    protected $chromosomeName = null;
    protected $currentStart = null;
    protected $currentEnd = null;
    protected $isNegative = false;
    protected $anatomicalExpressionTerms = array();
    // TRUE if this RC encloses another RC
    protected $enclosesRc = false;
    // Wiggle room used for determining whether or not one reporter
    // construct encloses another
    protected $errorMargin = 0;
    public function __construct(
        $id,
        $name,
        $geneId,
        $chromosomeName,
        $chromosomeId,
        $currentStart,
        $currentEnd,
        $isNegative = false,
        $anatomicalExpressionTerms = null
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->geneId = $geneId;
        $this->chromosomeId = $chromosomeId;
        $this->chromosomeName = $chromosomeName;
        $this->currentStart = $currentStart;
        $this->currentEnd = $currentEnd;
        $this->isNegative = $isNegative;
        $this->anatomicalExpressionTerms = ( null !== $anatomicalExpressionTerms ? $anatomicalExpressionTerms : array() );
        $this->size = $currentEnd - $currentStart + 1;
        $this->errorMargin = $GLOBALS["options"]->rc->error_margin;
    }
    // ----------------------------------------------------------------------
    // Determine if this RC encloses the target based on (current_start,
    // current_end) coordinates. Take into account the error margin when
    // making the calculation allowing the error both before and after the
    // current start and end coordinates.
    // @param $target The target RC
    // @returns TRUE if this RC encloses the target RC (i.e., the target
    //   RC is nested within this one).
    // ----------------------------------------------------------------------
    public function encloses(RC $target)
    {
        return ( ($this->currentStart <= ($target->currentStart + $this->errorMargin)) &&
            ($this->currentEnd >= ($target->currentEnd - $this->errorMargin)) );
    }
    // ----------------------------------------------------------------------
    // Determine if this RC overlaps the target and calculate the
    // overlapping region.
    // @param $target Target RC
    // @param $overlappingRegion Reference to the calculated overlapping
    //   region.
    // @returns TRUE if the sequence of the target RC overlaps this RC.
    //   If either RC completely encloses the other this is considered
    //   overlap.
    // ----------------------------------------------------------------------
    public function overlaps(
        RC $target,
        &$overlappingRegion
    ) {
        if ( $this->chromosomeName != $target->getChromosomeName() ) {
            return false;
        }
        $currentStart = max(
            $target->currentStart,
            $this->currentStart
        );
        $currentEnd   = min(
            $target->currentEnd,
            $this->currentEnd
        );
        $overlaps = $currentStart <= $currentEnd;
        if ( $overlaps ) {
            $overlappingRegion = array(
              $currentStart,
              $currentEnd
            );
        }

        return $overlaps;
    }
    // ----------------------------------------------------------------------
    // @returns TRUE if this RC encloses at least one other RC.
    // ----------------------------------------------------------------------
    public function enclosesRc()
    {
        return $this->enclosesRc;
    }
    public function setEnclosesRc()
    {
        $this->enclosesRc = true;
    }
    public function clearEnclosesRc()
    {
        $this->enclosesRc = false;
    }
    public function addAnatomicalExpressionTerm(
        $term,
        $termId
    ) {
        if ( empty($term) || empty($termId) ) {
            throw new Exception("Invalid anatomical expression term: \"$term\" ($termId)");
        }
        if ( ! array_key_exists($termId, $this->anatomicalExpressionTerms) ) {
            $this->anatomicalExpressionTerms[$termId] = $term;
        }
    }
    public function getName()
    {
        return $this->name;
    }
    public function getId()
    {
        return $this->id;
    }
    public function getChromosomeName()
    {
        return $this->chromosomeName;
    }
    public function getChromosomeId()
    {
        return $this->chromosomeId;
    }
    public function getGeneId()
    {
        return $this->geneId;
    }
    public function getCurrentStart()
    {
        return $this->currentStart;
    }
    public function getCurrentEnd()
    {
        return $this->currentEnd;
    }
    public function getSize()
    {
        return $this->size;
    }
    public function isNegative()
    {
        return $this->isNegative;
    }
    public function getAnatomicalExpressionTerms()
    {
        return $this->anatomicalAnatomicalExpressionTerms;
    }
    public function getCoordinates()
    {
        return $this->chromosomeName . ":" . $this->currentStart . ".." . $this->currentEnd;
    }
    public function __toString()
    {
        return "({$this->name}; {$this->getCoordinates()}; " .
            implode(
                ",",
                $this->anatomicalExpressionTerms
            ) .
            "; " .
            ( $this->enclosesRc ? "RC" : "CRM" ) .
            ")\n";
    }
}
