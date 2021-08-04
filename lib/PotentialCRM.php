<?php
// ======================================================================
// The potential CRM is used when examining several RCs at a locus
// (same gene, same coordinates, and same anatomical expression
// identifiers) to determine the CRM.
// This includes the capability to manually override a CRM and set
// alerts to alert the curator to calculated RCs that are enclosed
// within an overriden RC.
// ======================================================================
class PotentialCRM extends RC
{
    private $alerts = array();
    // TRUE if this PotentialCRM has been manually overriden to be a CRM
    private $manualOverride = false;
    // TRUE if this PotentialCRM has been calculated to be a CRM.
    // This is initially set to TRUE and changed during minimization if this
    // PotentialCRM encloses another PotentialCRM.
    // If $this->manualOverride && $this->isCalculatedCrm then this means
    // that another PotentialCRM would change the designation of this
    // manually overriden CRM.
    private $isCalculatedCrm = true;
    // TRUE if this PotentialCRM is enclosed by another RC
    private $isEnclosed = false;
    public function __construct(
        $id,
        $name,
        $geneIdentifier,
        $chromosomeId,
        $chromosomeName,
        $currentStart,
        $currentEnd,
        $override = false,
        $anatomicalExpressionIdentifiers
    ) {
        parent::__construct(
            $id,
            $name,
            $geneIdentifier,
            $chromosomeId,
            $chromosomeName,
            $currentStart,
            $currentEnd,
            $anatomicalExpressionIdentifiers
        );
        $this->manualOverride = $override;
    }
    // ----------------------------------------------------------------------
    // Add a manual override alert.
    // Manual alerts are RCs that are enclosed within an overriden RC.
    // @param $alert The RC that is enclosed within this RC.
    // ----------------------------------------------------------------------
    public function addManualOverrideAlert(PotentialCRM $alert)
    {
        $this->alerts[] = $alert;
    }
    // ----------------------------------------------------------------------
    // @returns TRUE if this RC has manual override alerts set
    // ----------------------------------------------------------------------
    public function hasManualOverrideAlert()
    {
        return ( count($this->alerts) > 0 );
    }
    // ----------------------------------------------------------------------
    // @returns The array of RUE if this RC has manual override alerts set
    // ----------------------------------------------------------------------
    public function getManualOverrideAlerts()
    {
        return $this->alerts;
    }
    public function isCrm()
    {
        return ( $this->manualOverride || $this->isCalculatedCrm );
    }
    // ----------------------------------------------------------------------
    // @returns TRUE if this RC has been manually overriden to be a CRM
    //   and it encloses another RC.
    // ----------------------------------------------------------------------
    public function alert()
    {
        return ( $this->manualOverride && $this->enclosesRc );
    }
    // ----------------------------------------------------------------------
    // @returns TRUE if this RC has been manually overridden to be a CRM.
    // ----------------------------------------------------------------------
    public function isManuallyOverriden()
    {
        return $this->manualOverride;
    }
    // ----------------------------------------------------------------------
    // Set the manual override flag.
    // @returns TRUE
    // ----------------------------------------------------------------------
    public function setManualOverride()
    {
        return $this->manualOverride = true;
    }
    // ----------------------------------------------------------------------
    // Clear the manual override flag.
    // @returns FALSE
    // ----------------------------------------------------------------------
    public function clearManualOverride()
    {
        return $this->manualOverride = false;
    }
    // ----------------------------------------------------------------------
    // @returns TRUE if this RC has been manually overridden to be a CRM.
    // ----------------------------------------------------------------------
    public function isEnclosed()
    {
        return $this->isEnclosed;
    }
    // ----------------------------------------------------------------------
    // Set the manual override flag.
    // @returns TRUE
    // ----------------------------------------------------------------------
    public function setEnclosed()
    {
        return $this->isEnclosed = true;
    }
    // ----------------------------------------------------------------------
    // Clear the manual override flag.
    // @returns FALSE
    // ----------------------------------------------------------------------
    public function clearEnclosed()
    {
        return $this->isEnclosed = false;
    }
}
