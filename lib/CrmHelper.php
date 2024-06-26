<?php
// ================================================================================
// Helper class for CRM identification
// Note that there could be reporter constructs not having any expression term
// that will not be identified here
// ================================================================================
class CrmHelper
{
    private $db = null;
    private $report = array();
    private $potentialCRMSet = null;
    private $rcHelper = null;
    private $errorMargin = 0;
    // --------------------------------------------------------------------------------
    // Factory design pattern
    // --------------------------------------------------------------------------------
    public static function factory()
    {
        return new CrmHelper();
    }
    private function __construct()
    {
        $this->db = DbService::factory();
        $this->potentialCRMSet = new PotentialCRMSet();
        $this->rcHelper = RcHelper::factory();
        $this->errorMargin = $GLOBALS["options"]->rc->error_margin;
    }
    public function reset()
    {
        $this->report = array();
        $this->potentialCRMSet->reset();
    }
    // --------------------------------------------------------------------------------
    // Calculate CRMS.
    // @param $createNewVersions (optional) Defaults to TRUE and create
    //   new RC versions.
    //   Use FALSE to update RCs without creating new versions.
    // @returns An array of report data
    // NOTE: It is important to have e.identifier in the order by clause
    // or else the string generated by GROUP_CONCAT(e.identifier) as
    // anatomical_expression_identifiers might return the same set of identifiers
    // but in pseudo-random order!
    // --------------------------------------------------------------------------------
    public function findAllCRMs($createNewVersions = true)
    {
        $this->reset();
        // 1) The first SQL consult will refer to the current reporter constructs
        // having any gene, any chromosome, and any anatomical expression identifier.
        // 2) The second SQL consult will refer to the current reporter constructs
        // having any gene and any chromosome. But they may or may not have any
        // anatomical expression identifier. They are newly reassigned as negative
        // expression. So, they must reset their is_crm attribute to FALSE (0).
        $sql = <<<SQL
        SELECT rc.rc_id,
            rc.name,
            g.identifier AS gene_identifier,
            g.name AS gene,
            c.chromosome_id,
            c.name AS chromosome,
            rc.current_start,
            rc.current_end,
            rc.is_override,
            GROUP_CONCAT(e.identifier ORDER BY e.identifier ASC separator ',') AS anatomical_expression_identifiers
        FROM ReporterConstruct rc
        INNER JOIN Gene g ON rc.gene_id = g.gene_id
        INNER JOIN Chromosome c ON rc.chromosome_id = c.chromosome_id
        INNER JOIN RC_has_ExprTerm map ON rc.rc_id = map.rc_id
        INNER JOIN ExpressionTerm e ON map.term_id = e.term_id
        WHERE rc.state = 'current'
        GROUP BY rc.rc_id
        UNION
        SELECT rc.rc_id,
            rc.name,
            g.identifier AS gene_identifier,
            g.name AS gene,
            c.chromosome_id,
            c.name AS chromosome,
            rc.current_start,
            rc.current_end,
            rc.is_override,
            IFNULL(GROUP_CONCAT(e.identifier ORDER BY e.identifier ASC separator ','), '') AS anatomical_expression_identifiers
        FROM ReporterConstruct rc
        INNER JOIN Gene g ON rc.gene_id = g.gene_id
        INNER JOIN Chromosome c ON rc.chromosome_id = c.chromosome_id
        LEFT OUTER JOIN RC_has_ExprTerm map ON rc.rc_id = map.rc_id
        LEFT OUTER JOIN ExpressionTerm e ON map.term_id = e.term_id
        WHERE rc.state = 'current' AND
            rc.is_crm = 1 AND
            rc.is_negative = 1
        GROUP BY rc.rc_id
        ORDER BY gene,
            chromosome,
            anatomical_expression_identifiers
SQL;
        try {
            $result = $this->db->query($sql);
        } catch ( Exception $e ) {
            throw new Exception("Error querying RCs: " . $e->getMessage());
        }
        $previousGene = null;
        $previousChromosome = null;
        $previousAnatomicalExpressionIdentifiers = null;
        while ( $row = $result->fetch_assoc() ) {
            // If the gene, chromosome, or anatomical expression identifiers have changed
            // then this is a new set of potential CRMs
            if ( ($previousGene !== $row["gene"]) ||
               ($previousChromosome !== $row["chromosome"]) ||
               ($previousAnatomicalExpressionIdentifiers !== $row["anatomical_expression_identifiers"]) ) {
                if ( ($previousGene !== null) &&
                    ($this->potentialCRMSet->count() !== 0) ) {
                    // Process the data in the current set
                    $this->processPotentialCRMSet(
                        $this->potentialCRMSet,
                        $createNewVersions
                    );
                }
                $this->potentialCRMSet->reset();
            }
            // Add the potential CRM to the set.
            $this->potentialCRMSet->addRc(new PotentialCRM(
                $row["rc_id"],
                $row["name"],
                $row["gene_identifier"],
                $row["chromosome_id"],
                $row["chromosome"],
                $row["current_start"],
                $row["current_end"],
                $row["is_override"],
                explode(",", $row["anatomical_expression_identifiers"])
            ));
            $previousGene = $row["gene"];
            $previousChromosome = $row["chromosome"];
            $previousAnatomicalExpressionIdentifiers = $row["anatomical_expression_identifiers"];
        }
        // Process the final set
        if ( $this->potentialCRMSet->count() !== 0 ) {
            // Process the data in the current set
            $this->processPotentialCRMSet(
                $this->potentialCRMSet,
                $createNewVersions
            );
        }

        return $this->report;
    }
    // --------------------------------------------------------------------------------
    // Find CRMs for the given data
    // @param array $data Reporter construct data array:
    //   gene_identifier                   Gene identifier
    //   chromosome_id                     Chromosome ID
    //   start                             Coordinate start
    //   end                               Coordinate end
    //   anatomical_expression_identifiers Array of anatomical expression identifiers
    // @param $createNewVersions (optional) Defaults to TRUE and create
    //   new RC versions.
    //   Use FALSE to update RCs without creating new versions.
    // NOTE: It is important to have e.identifier in the order by clause
    // or else the string generated by GROUP_CONCAT(e.identifier) as
    // anatomical_expression_identifiers might return the same set of identifiers
    // but in pseudo-random order!
    // --------------------------------------------------------------------------------
    public function findCRMs(
        $data,
        $createNewVersions = true
    ) {
        $geneIdentifier = $data["gene_identifier"];
        $chromosomeId = $data["chromosome_id"];
        $start = $data["start"];
        $end = $data["end"];
        $anatomicalExpressionIdentifiers = array();
        if ( is_array($data["expr_terms"]) ) {
            foreach ( $data["expr_terms"] as $anatomicalExpressions ) {
                $anatomicalExpressionIdentifiers[] = $anatomicalExpressions["identifier"];
            }
        }
        // Sort the anatomical expression identifiers to match the database consult
        //(ascending, case-insensitive)
        usort(
            $anatomicalExpressionIdentifiers,
            "strcasecmp"
        );
        // Create a set of RCs that have all the same gene name, chromosome identifier,
        // and anatomical expression identifiers as the specified RC, and also overlap
        // the RC in any way
        $sql = <<<SQL
        SELECT t.rc_id,
            t.name,
            t.chromosome,
            t.current_start,
            t.current_end,
            t.is_override
        FROM (SELECT rc.rc_id,
                  rc.name,
                  c.name AS chromosome,
                  rc.current_start,
                  rc.current_end,
                  rc.is_override,
                  IFNULL(GROUP_CONCAT(e.identifier ORDER BY e.identifier ASC separator ','), '') AS anatomical_expression_identifiers
              FROM ReporterConstruct rc
              INNER JOIN Gene g ON rc.gene_id = g.gene_id
              INNER JOIN Chromosome c ON rc.chromosome_id = c.chromosome_id
              LEFT OUTER JOIN RC_has_ExprTerm map ON rc.rc_id = map.rc_id
              LEFT OUTER JOIN ExpressionTerm e ON map.term_id = e.term_id
              WHERE rc.state = 'current' AND
                  g.identifier = ? AND
                  c.chromosome_id = ? AND
                  rc.current_start <= ? AND
                  rc.current_end >= ?
              GROUP BY rc.rc_id) t
        WHERE t.anatomical_expression_identifiers = ?
SQL;
        if ( ($statement = $this->db->getHandle()->prepare($sql)) === false ) {
            throw new Exception("Error preparing statement: " . $this->db->getError());
        }
        $statement->bind_param(
            "sisii",
            $geneIdentifier,
            $chromosomeId,
            $start - $this->errorMargin,
            $end   + $this->errorMargin,
            implode(
                ",",
                $anatomicalExpressionIdentifiers
            )
        );
        if ( $statement->execute() === false ) {
            throw new Exception("Error querying RCs: " . $statement->error);
        }
        $rcId = $name = $chromosomeName = $currentStart = $currentEnd = $isOverride = null;
        $statement->bind_result(
            $rcId,
            $name,
            $chromosomeName,
            $currentStart,
            $currentEnd,
            $isOverride
        );
        $this->potentialCRMSet->reset();
        while ( $statement->fetch() ) {
            $potentialCRM = new PotentialCRM(
                $rcId,
                $name,
                $geneIdentifier,
                $chromosomeId,
                $chromosomeName,
                $currentStart,
                $currentEnd,
                $isOverride,
                $anatomicalExpressionIdentifiers
            );
            $this->potentialCRMSet->addRc($potentialCRM);
        }
        $this->processPotentialCRMSet(
            $this->potentialCRMSet,
            $createNewVersions
        );

        return $this->report;
    }
    // --------------------------------------------------------------------------------
    // Process a PotentialCRMSet
    // @param PotentialCRMSet $potentialCRMSet The set of potential CRMs to process
    // @param $createNewVersions (optional) Defaults to TRUE and creates
    //   new RC versions.
    //   Use FALSE to update RCs without creating new versions.
    // --------------------------------------------------------------------------------
    private function processPotentialCRMSet(
        PotentialCRMSet $potentialCRMSet,
        $createNewVersions = true
    ) {
        $potentialCRMSet->findCRMs();
        foreach ( $potentialCRMSet as $potentialCRMSetObject ) {
            $rcChanges = array();
            $reportMessageList = array();
            $overrideAlert = false;
            $rcId = $potentialCRMSetObject->getId();
            $sql = <<<SQL
            SELECT entity_id,
                is_crm,
                is_negative
            FROM ReporterConstruct
            WHERE rc_id = $rcId
SQL;
            $queryResult = $this->db->query($sql);
            if ( ($currentRc = $queryResult->fetch_assoc()) === null ) {
                throw new Exception("Error fetching rc_id = " . $rcId);
            }
            $entityId = $currentRc["entity_id"];
            $currentVersionIsNegative = $currentRc["is_negative"];
            $sql = <<<SQL
            SELECT is_negative
            FROM ReporterConstruct
            WHERE entity_id = $entityId AND
                version = ((SELECT version
                            FROM ReporterConstruct
                            WHERE entity_id = $entityId AND
                                state = 'current') - 1)
SQL;
            $queryResult = $this->db->query($sql);
            if ( ($previousVersionRc = $queryResult->fetch_assoc()) === null ) {
                $previousVersionIsNegative = "";
            } else {
                $previousVersionIsNegative = $previousVersionRc["is_negative"];
            }
            if ( ($previousVersionIsNegative === "0") &&
                ($currentVersionIsNegative === "1") ) {
                // Has the IsNegative attribute changed from FALSE (0) to TRUE (1)?
                try {
                    $this->db->startTransaction();
                    $updateSql = <<<SQL
                    UPDATE ReporterConstruct
                    SET is_crm = 0
                    WHERE rc_id = $rcId
SQL;
                    $this->db->query($updateSql);
                    $this->db->commit();
                } catch ( Exception $e ) {
                    $this->db->rollback();
                    throw new Exception("Error updating the new reporter construct: " . $e->getMessage());
                }
                $reportMessageList[] = "CRM status of " .
                    $potentialCRMSetObject->getName() . " changed to FALSE " .
                    "due to having the is_negative attribute as TRUE";
            } else {
                // Has the CRM designation of the RC changed?
                // Be sure to check for manual overrides.
                $isCrm = ! $potentialCRMSetObject->enclosesRc();
                if ( $currentRc["is_crm"] === "1" ) {
                    $isCrmDb = true;
                } else {
                    $isCrmDb = false;
                }
                if ( $isCrm !== $isCrmDb ) {
                    if ( (! $isCrm) &&
                        $potentialCRMSetObject->isManuallyOverriden() ) {
                        $isCrm = true;
                    }
                    // Double-check to make sure the status actually changed
                    if ( $isCrm !== $isCrmDb ) {
                        $reportMessageList[] = "CRM status of " .
                            $potentialCRMSetObject->getName() . " changed to " . ($isCrm ? "TRUE"
                                                                                         : "FALSE");
                        $rcChanges["is_crm"] = $isCrm;
                    }
                }
                // Display manual override alerts.
                // Each alert object is an RC that is enclosed by a manually overriden CRM.
                if ( $potentialCRMSetObject->hasManualOverrideAlert() ) {
                    $overrideAlert = true;
                    $alertNameList = array();
                    foreach ( $potentialCRMSetObject->getManualOverrideAlerts() as $alertObj ) {
                        $alertNameList[] = $alertObj->getName();
                    }
                    $reportMessageList[] = "Alert! manually overriden CRM " .
                        $potentialCRMSetObject->getName() . " encloses " .
                        implode(", ", $alertNameList);
                }
                if ( count($rcChanges) !== 0 ) {
                    // Only create a new RC version if requested and the CRM status
                    // has changed.
                    if ( $createNewVersions &&
                        isset($rcChanges["is_crm"]) ) {
                        $this->rcHelper->createNewVersion(
                            $rcId,
                            $rcChanges
                        );
                    } else {
                        $this->rcHelper->update(
                            $rcId,
                            $rcChanges
                        );
                    }
                }
            }
            if ( count($reportMessageList) !== 0 ) {
                $this->report[] = array(
                    "name"           => $potentialCRMSetObject->getName(),
                    "override_alert" => $overrideAlert,
                    "rc_id"          => $rcId,
                    "coord"          => $potentialCRMSetObject->getCoordinates(),
                    "messages"       => $reportMessageList);
            }
        }
    }
}
