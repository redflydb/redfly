<?php
// ======================================================================
// Machinery used to determine one or more CRMs from a set of
// ReporterConstructs.  A CRM is currently defined as:
// A CRM is the shortest reporter construct sequence that has no other
// reporter constructs fully contained within it that share exactly
// the identical anatomical expression pattern. More than one CRM can
// be nested within a larger reporter construct.
// ======================================================================
class PotentialCRMSet extends RCSet
{
    // TRUE if the set has beenprocessed to identify CRMs.
    // This is set to FALSE every time a new RC is added.
    private $identifiedCrms = false;
    // The list of CRMs found in this set
    private $crmList = array();
    // --------------------------------------------------------------------------------
    // Reset the internal state.
    // --------------------------------------------------------------------------------
    public function reset()
    {
        parent::reset();
        $this->crmList = array();
        $this->identifiedCrms = false;
    }
    // --------------------------------------------------------------------------------
    // Add a reporter construct to the set.
    // --------------------------------------------------------------------------------
    public function addRc(RC $rc)
    {
        $this->identifiedCrms = false;
        return parent::addRc($rc);
    }
    // --------------------------------------------------------------------------------
    // Determine which RCs in the set are CRMs.  This determines the shortest
    // reporter construct sequence that has no other reporter constructs fully
    // contained within it that share exactly the identical anatomical expression
    // pattern. More than one CRM can be nested within a larger reporter
    // construct.
    // @return The list of CRMs found.  The index of each returned RC
    //   preserved so that graphing the results are possible.
    // --------------------------------------------------------------------------------
    public function findCRMs()
    {
        // For a given RC, all we need to know is whether or not it is
        // nested within another RC.  To do this for a given RC, start
        // with the largest RC and move down making comparisons.  If we
        // start getting a lot of RC to process we may need to something
        // more intelligent such as a binary search.
        $this->crmList = array();
        foreach ( $this->rcList as $index => &$currentRc ) {
            // Shortcut for single RCs.  A single RC at a locus is automatically
            // considered a CRM.
            if ( count($this->rcList) === 1 ) {
                $this->crmList[$index] = $currentRc;
                return $this->crmList;
            }
            foreach ( $this->rcList as $checkRc ) {
                // Do not check against ourselves.
                if ( $checkRc === $currentRc ) {
                    continue;
                }
                // If the current RC encloses another RC, it is not a CRM
                // unless it has been manually overriden.
                if ( $currentRc->encloses($checkRc) ) {
                    // If the current RC encloses another RC but has been manually
                    // overriden to be a CRM set an alert for the curator but do not use
                    // this RC to calculate CRMs.  Otherwise, set the flag indicating that
                    // it encloses an RC and is not a CRM candidate.
                    if ( $currentRc->isManuallyOverriden() ) {
                        $currentRc->addManualOverrideAlert($checkRc);
                    } else {
                        $currentRc->setEnclosesRc();
                    }
                    // Set the enclosed flag on the enclosed RC.  A CRM is
                    // considered minimalized if it is fully enclosed by another
                    // RC.
                    $checkRc->setEnclosed();
                }
            }
            if ( ! $currentRc->enclosesRc() ) {
                $this->crmList[$index] = $currentRc;
            }
        }
        $this->identifiedCrms = true;

        return $this->crmList;
    }
    // --------------------------------------------------------------------------------
    // @returns TRUE if the set has been processed for CRMs
    // --------------------------------------------------------------------------------
    public function crmsIdentified()
    {
        return $this->identifiedCrms;
    }
}
