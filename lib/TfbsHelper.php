<?php
// ================================================================================
// Binding Site helper class.
// The public methods should not allow the application to enter an inconsistent
// state.
// These methods should be used to manage the TFBS lifecycle, they roughly
// correspond to the actions from the curator interface:
// No longer used
// - getData()
//   Used to get the data from an TFBS.
// No longer used
// - create()
//   Used to create a new TFBS in the "editing" or "approval" state.
// No longer used
// - update()
//   Used to modify a TFBS in the "editing" or "approval" state.
// No longer used
// - approve()
//   Used to approve (change to "current") a TFBS in the "approval"
//   state.
// No longer used
// - createEdit()
//   Used to create a new "editing" or "approval" state TFBS from a TFBS
//   in the "current" state.
// No longer used
// - createNewVersion()
//   Used to create a new "current" state TFBS from a TFBS in the
//   "current" state. This method is useful for external scripts that
//   need to change properties of "current" TFBSs that require a new
//   version to be created.
// Functions:
// - getGeneName()
// - validateAndFormatSequenceWithFlank()
// - setFlankSize()
// - updateAssociatedRc()
// ================================================================================
class TfbsHelper extends aEntityHelper
{
    // BindingSite table columns and mysqli type.
    // Excludes the primary key "tfbs_id" since it should never be set directly.
    private static $tfbsColumnTypeList = array(
        "archive_date"                              => "s",
        "archived_ends"                             => "s",
        "archived_genome_assembly_release_versions" => "s",
        "archived_starts"                           => "s",
        "assayed_in_species_id"                     => "i",
        "auditor_id"                                => "i",
        "chromosome_id"                             => "i",
        "curator_id"                                => "i",
        "current_end"                               => "i",
        "current_genome_assembly_release_version"   => "s",
        "current_start"                             => "i",
        "date_added"                                => "s",
        "entity_id"                                 => "i",
        "evidence_id"                               => "i",
        "figure_labels"                             => "s",
        "gene_id"                                   => "i",
        "has_rc"                                    => "i",
        "last_audit"                                => "s",
        "last_update"                               => "s",
        "name"                                      => "s",
        "notes"                                     => "s",
        "num_flank_bp"                              => "i",
        "pubmed_id"                                 => "s",
        "sequence"                                  => "s",
        "sequence_from_species_id"                  => "i",
        "sequence_with_flank"                       => "s",
        "size"                                      => "i",
        "state"                                     => "s",
        "tf_id"                                     => "i",
        "version"                                   => "i"
    );
    // --------------------------------------------------------------------------------
    // Factory method design pattern.
    // --------------------------------------------------------------------------------
    public static function factory()
    {
        return new TfbsHelper();
    }
    // --------------------------------------------------------------------------------
    // Constructor.
    // --------------------------------------------------------------------------------
    protected function __construct()
    {
        parent::__construct();
        $this->tableName = "BindingSite";
        $this->pkColumn = "tfbs_id";
        $this->abbrev = "BS";
        $this->columnTypeList = self::$tfbsColumnTypeList;
    }
    // --------------------------------------------------------------------------------
    // Get the raw data for an TFBS
    // @param int $tfbsId The BindingSite tfbs_id
    // @returns array An array of TFBS data
    // --------------------------------------------------------------------------------
    public function getData($tfbsId)
    {
        try {
            $sql = "SELECT * 
                    FROM BindingSite
                    WHERE tfbs_id = " . $tfbsId;
            $result = $this->db->query($sql);
            if ( ($tfbs = $result->fetch_assoc()) === null ) {
                throw new Exception("No TFBS found for tfbs_id = " . $tfbsId);
            }
        } catch ( Exception $e ) {
            throw new Exception("Error fetching TFBS: " . $e->getMessage());
        }

        return $tfbs;
    }
    // --------------------------------------------------------------------------------
    // Get the gene name for a gene identifier
    // @param int $geneId The Gene gene_id
    // @returns string The gene name
    // --------------------------------------------------------------------------------
    public function getGeneName($geneId)
    {
        try {
            $sql = "SELECT name 
                    FROM Gene
                    WHERE gene_id = " . $geneId;
            $result = $this->db->query($sql);
            if ( ($geneRow = $result->fetch_assoc()) === null ) {
                throw new Exception(sprintf(
                    "No gene name found for id %d",
                    $geneId
                ));
            }
        } catch ( Exception $e ) {
            throw new Exception(sprintf(
                "Error fetching gene name for id %d: %s",
                $geneId,
                $e->getMessage()
            ));
        }

        return $geneRow["name"];
    }
    // --------------------------------------------------------------------------------
    // This method is no longer used but still kept to follow the abstract class used
    // by this class.
    // Create a new TFBS.
    // @param array $data An array of data used to create the TFBS.
    // @returns in The tfbs_id for the new TFBS.
    // --------------------------------------------------------------------------------
    public function create(array $data)
    {
    }
    // --------------------------------------------------------------------------------
    // This method is no longer used but still kept to follow the abstract class used
    // by this class.
    // Update a TFBS without creating a new version
    // @param int $tfbsId The BindingSite tfbs_id
    // @param array $data An array of data used to update the TFBS
    // @returns array The TFBS data
    // --------------------------------------------------------------------------------
    public function update(
        $tfbsId,
        array $data
    ) {
    }
    // --------------------------------------------------------------------------------
    // This method is no longer used but still kept to follow the abstract class used
    // by this class.
    // Approve an TFBS.
    // @param int $tfbsId The BindingSite tfbs_id
    // @param array $data (optional) An array of data used to update the TFBS
    // @returns array The TFBS data
    // --------------------------------------------------------------------------------
    public function approve(
        $tfbsId,
        array $data = array()
    ) {
    }
    // --------------------------------------------------------------------------------
    // This method is no longer used but still kept to follow the abstract class used
    // by this class.
    // Create an "editing" or "approval" version of a "current" TFBS.
    // @param int $tfbsId The BindingSite tfbs_id
    // @param array $data (optional) An array of data used to update the TFBS
    // @returns array The TFBS data
    // --------------------------------------------------------------------------------
    public function createEdit(
        $tfbsId,
        array $data = array()
    ) {
    }
    // --------------------------------------------------------------------------------
    // This method is no longer used but still kept to follow the abstract class used
    // by this class.
    // Create a new "current" version of a "current" TFBS.
    // The changes to the "current" TFBS will also be applied to any
    // non-current versions (i.e. "editing" and "approval" versions of
    // the TFBS).
    // @param int $tfbsId The BindingSite tfbs_id
    // @param array $data (optional) An array of data used to update the TFBS
    // @returns int The tfbs_id for the new TFBS version
    // --------------------------------------------------------------------------------
    public function createNewVersion(
        $tfbsId,
        array $data = array()
    ) {
    }
    // --------------------------------------------------------------------------------
    // Verify that the sequence with flank passes the following tests and also format it
    // so that the sequence is capitalized and the flank is lowercase. Note that the
    // input parameter will be modified with the formatted sequences.
    // 1. The sequence with flank must conform to the regex /^[ACGTURYKMSWBDHVN]{1,}$/i
    //    generated using:
    //    http://en.wikipedia.org/wiki/Nucleotide#Abbreviation_codes_for_degenerate_bases
    // 2. The length of the left and right flanks must be the same
    // 3. The sequence must be present in the sequence with flank
    // @param array $data Reference to an array containing data with minimally the following
    //   keys which will be modified during formatting: sequence, sequence_with_flank
    // @throws Exception If the required keys are not defined in $data
    // @throws Exception If the sequence with flank contains invalid characters
    // @throws Exception If the flanks are of differing lengths
    // @throws Exception If the sequence is not found at the center of the sequence with flank
    // --------------------------------------------------------------------------------
    public function validateAndFormatSequenceWithFlank(array &$data)
    {
        if ( (! isset($data["sequence"])) ||
            empty($data["sequence"]) ||
            (! isset($data["sequence_with_flank"])) ||
            empty($data["sequence_with_flank"]) ) {
            throw new Exception("Data missing sequence or sequence with flank");
        }
        // Strip off any whitespace so we have contiguous strings
        $sequence = preg_replace(
            "/[\s]+/",
            "",
            $data["sequence"]
        );
        $sequenceLength = strlen($sequence);
        $sequenceWithFlank = preg_replace(
            "/[\s]+/",
            "",
            $data["sequence_with_flank"]
        );
        $sequenceWithFlankLength = strlen($sequenceWithFlank);
        // 1. The sequence with flank must conform to the regex /^[ACGTURYKMSWBDHVN]{1,}$/i
        if ( ! preg_match("/^[ACGTURYKMSWBDHVN]{1,}$/i", $sequenceWithFlank) ) {
            throw new Exception(
                sprintf(
                    "Sequence with flank \"%s\" contains characters not in /ACGTURYKMSWBDHVN/",
                    $sequenceWithFlank
                )
            );
        }
        // 2. The length of the left and right flanks must be the same
        if ( (($sequenceWithFlankLength - $sequenceLength) % 2) !== 0 ) {
            throw new Exception("Left and right flanks are of different lengths");
        }
        // 3. The sequence must be present in the sequence with flank
        $centerSequence = substr(
            strtoupper($sequenceWithFlank),
            ($sequenceWithFlankLength - $sequenceLength) / 2,
            $sequenceLength
        );
        if ( strtoupper($sequence) !== $centerSequence ) {
            throw new Exception(
                sprintf(
                    "Sequence \"%s\" not found at center of the sequence with flank \"%s\", found \"%s\"",
                    $sequence,
                    $sequenceWithFlank,
                    $centerSequence
                )
            );
        }
        // Format the sequences so the sequence is in capitals and the flank is lowercase for easy reading.
        $flankLength = ($sequenceWithFlankLength - $sequenceLength) / 2;
        $data["sequence"] = strtoupper($sequence);
        $data["sequence_with_flank"] = strtolower(substr($sequenceWithFlank, 0, $flankLength)) .
            $data["sequence"] .
            strtolower(substr($sequenceWithFlank, $flankLength + $sequenceLength));

        return true;
    }
    // --------------------------------------------------------------------------------
    // Set the flank size if the sequence and sequence with flank are supplied.
    // Throws an error if only one of the sequence and sequence with flank
    // are supplied.
    // @param array $data An array of entity data
    // --------------------------------------------------------------------------------
    protected function setFlankSize(&$data)
    {
        if ( isset($data["sequence"]) &&
            isset($data["sequence_with_flank"]) ) {
            $data["num_flank_bp"] = ( strlen($data["sequence_with_flank"]) - strlen($data["sequence"]) ) / 2;
        } elseif ( isset($data["sequence"]) &&
            (! isset($data["sequence_with_flank"])) ) {
            throw new Exception("Sequence supplied without sequence with flank");
        } elseif ( (! isset($data["sequence"])) &&
            isset($data["sequence_with_flank"]) ) {
            throw new Exception("Sequence with flank supplied without sequence");
        }
    }
    // --------------------------------------------------------------------------------
    // Update the associated RC for a given TFBS
    // This should only be used with a TFBS with "current" state.
    // @param integer $tfbsId ID of the TFBS to update
    // @param integer $chromosomeId Sequence chromosome ID
    // @param integer $start Sequence start coordinate
    // @param integer $end Sequence end coordinate
    // --------------------------------------------------------------------------------
    public function updateAssociatedRc(
        $tfbsId,
        $chromosomeId,
        $currentStart,
        $currentEnd
    ) {
        if ( ($previousTfbsId = $this->getPreviousPk($tfbsId)) !== null ) {
            $this->updateAssociatedRcHasBs($previousTfbsId);
        }
        $sql = "DELETE FROM RC_associated_BS
                WHERE tfbs_id = " . $tfbsId;
        $this->db->query($sql);
        $sql = "INSERT INTO RC_associated_BS (rc_id, tfbs_id)
                SELECT rc_id, $tfbsId
                FROM ReporterConstruct
                WHERE $currentStart BETWEEN current_start AND current_end AND
                    $currentEnd BETWEEN current_start AND current_end AND
                    chromosome_id = $chromosomeId AND
                    state = 'current'";
        $this->db->query($sql);
        $hasRc = $this->db->getHandle()->affected_rows > 0
            ? 1
            : 0;
        $sql = "UPDATE BindingSite
                SET has_rc = " . $hasRc . "
                WHERE tfbs_id = " . $tfbsId;
        $this->db->query($sql);
        $sql = "UPDATE ReporterConstruct
                SET has_tfbs = 1
                WHERE " . $currentStart . " BETWEEN current_start AND current_end AND
                    " . $currentEnd . " BETWEEN current_start AND current_end AND
                    chromosome_id = " . $chromosomeId . " AND
                    state = 'current'";
        $this->db->query($sql);
    }
    // --------------------------------------------------------------------------------
    // Update the "has_tfbs" field of RC associated with this TFBS
    // This should be called with the old tfbs_id of an TFBS whenever a
    // new "current" version of the TFBS is created with different
    // coordinates.
    // @param int $tfbsId The TFBS tfbs_id
    // --------------------------------------------------------------------------------
    private function updateAssociatedRcHasBs($tfbsId)
    {
        // Set has_tfbs = 0 for all RC that are associated with this TFBS and
        // only this TFBS.
        // Find all RC associated with this version of the TFBS
        $sql = "CREATE TEMPORARY TABLE temp_rc_assoc_bs AS
                SELECT DISTINCT rc_id
                FROM RC_associated_BS
                JOIN BindingSite USING (tfbs_id)
                WHERE tfbs_id = " . $tfbsId;
        $this->db->query($sql);
        // Of those tfbs, which are associated with no RC (excluding the
        // current one)
        $sql = "CREATE TEMPORARY TABLE temp_rc_with_no_bs AS
                SELECT DISTINCT temp_rc_assoc_bs.rc_id
                FROM temp_rc_assoc_bs
                JOIN RC_associated_BS assoc USING (rc_id)
                LEFT JOIN BindingSite tfbs ON assoc.tfbs_id = tfbs.tfbs_id AND
                    tfbs.state = 'current' AND
                    tfbs.tfbs_id != " . $tfbsId . "
                GROUP BY rc_id
                HAVING COUNT(DISTINCT tfbs.tfbs_id) = 0";
        $this->db->query($sql);
        $sql = "UPDATE ReporterConstruct rc
                JOIN temp_rc_with_no_bs USING (rc_id)
                SET rc.has_tfbs = 0";
        $this->db->query($sql);
        $sql = "DROP TEMPORARY TABLE temp_rc_assoc_bs";
        $this->db->query($sql);
        $sql = "DROP TEMPORARY TABLE temp_rc_with_no_bs";
        $this->db->query($sql);
    }
}
