<?php
// ================================================================================
// CRM Segment helper class.
// The public methods should not allow the application to enter an inconsistent
// state.
// These methods should be used to manage the CRM segment lifecycle, they roughly
// correspond to the actions from the curator interface:
// Functions implementing the abstract functions of the Abstract entity helper
// class, aEntityHelper:
// - getData()
//   Used to get the data from an CRM segment.
// - create()
//   Used to create a new CRM segment in the "editing" or "approval" state.
// - update()
//   Used to modify an CRM segment in the "editing" or "approval" state.
// No longer used
// - approve()
//   Used to approve an CRM segment in the "approval" state and change its state to
//   "approved".
// - createEdit()
//   Used to create a new "editing" or "approval" state CRM segment from an CRM
//   segment in the "current" state.
// No longer used
// - createNewVersion()
//   Used to create a new "current" state CRM segment from an CRM segment
//   in the "current" state. This method is useful for changing CRM and
//   minimization status. It is also appropriate to use it in external scripts
//   that need to change properties of "current" CRM segments that require a new
//   version to be created.
// ================================================================================
class CrmSegmentHelper extends aEntityHelper
{
    // CRMSegment table columns and mysqli type
    private static $crmSegmentColumnTypeList = array(
        "archive_date"                              => "s",
        "archived_ends"                             => "s",
        "archived_genome_assembly_release_versions" => "s",
        "archived_starts"                           => "s",
        "assayed_in_species_id"                     => "i",
        "auditor_id"                                => "i",
        "cell_culture_only"                         => "i",
        "chromosome_id"                             => "i",
        "curator_id"                                => "i",
        "current_end"                               => "i",
        "current_genome_assembly_release_version"   => "s",
        "current_start"                             => "i",
        "date_added"                                => "s",
        "entity_id"                                 => "i",
        "evidence_id"                               => "i",
        "evidence_subtype_id"                       => "i",
        "fbal"                                      => "s",
        "fbtp"                                      => "s",
        "figure_labels"                             => "s",
        "gene_id"                                   => "i",
        "has_flyexpress_images"                     => "i",
        "is_crm"                                    => "i",
        "is_minimalized"                            => "i",
        "is_negative"                               => "i",
        "is_override"                               => "i",
        "last_audit"                                => "s",
        "last_update"                               => "s",
        "name"                                      => "s",
        "notes"                                     => "s",
        "pubmed_id"                                 => "s",
        "sequence"                                  => "s",
        "sequence_from_species_id"                  => "i",
        "sequence_source_id"                        => "i",
        "size"                                      => "i",
        "state"                                     => "s",
        "version"                                   => "i"
    );
    // --------------------------------------------------------------------------------
    // Factory method design pattern.
    // --------------------------------------------------------------------------------
    public static function factory()
    {
        return new CrmSegmentHelper();
    }
    // --------------------------------------------------------------------------------
    // Constructor.
    // --------------------------------------------------------------------------------
    public function __construct()
    {
        parent::__construct();
        $this->tableName = "CRMSegment";
        $this->pkColumn = "crm_segment_id";
        $this->abbrev = "CRMSegment";
        $this->columnTypeList = self::$crmSegmentColumnTypeList;
    }
    // --------------------------------------------------------------------------------
    // Get the raw data for an CRM segment.
    // @param int $crmsId The CRMSegment crm_segment_id
    // @returns array An array of CRM segment data
    // --------------------------------------------------------------------------------
    public function getData($crmsId)
    {
        try {
            $sql = <<<SQL
            SELECT *
            FROM CRMSegment
            WHERE crm_segment_id = $crmsId
SQL;
            $result = $this->db->query($sql);
            if ( ($crmSegment = $result->fetch_assoc()) === null ) {
                throw new Exception("No CRM segment found for crm_segment_id = " . $crmsId);
            }
            $sql = <<<SQL
            SELECT term_id 
            FROM CRMSegment_has_Expression_Term
            WHERE crm_segment_id = $crmsId
SQL;
            $result = $this->db->query($sql);
            $anatomicalExpressionTermList = array();
            while ( $anatomicalExpressionTermRow = $result->fetch_assoc() ) {
                $anatomicalExpressionTermList[] = $anatomicalExpressionTermRow["term_id"];
            }
            $crmSegment["anatomical_expression_term_ids"] = $anatomicalExpressionTermList;
        } catch ( Exception $e ) {
            throw new Exception("Error fetching the CRM segment: " . $e->getMessage());
        }

        return $crmSegment;
    }
    // --------------------------------------------------------------------------------
    // Create a new CRM segment.
    // @param array $data An array of data used to create the CRM segment
    // @returns int The crm_segment_id for the new CRM segment version
    // --------------------------------------------------------------------------------
    public function create(array $data)
    {
        try {
            $this->db->startTransaction();
            // List of keys in the $data array that may be used to create
            // the new CRM segment.
            $keyList = array(
                "anatomical_expression_term_ids",
                "assayed_in_species_id",
                "cell_culture_only",
                "chromosome_id",
                "curator_id",
                "current_genome_assembly_release_version",
                "end",
                "evidence_id",
                "evidence_subtype_id",
                "fbal",
                "fbtp",
                "figure_labels",
                "gene_id",
                "has_flyexpress_images",
                "is_crm",
                "is_minimalized",
                "is_negative",
                "is_override",
                "last_update",
                "name",
                "notes",
                "pubmed_id",
                "sequence",
                "sequence_from_species_id",
                "sequence_source_id",
                "start",
                "state"
            );
            foreach ( $data as $key => $value ) {
                if ( array_key_exists($key, $this->columnTypeList) &&
                    (! in_array($key, $keyList)) ) {
                      throw new Exception("Can not set CRM segment \"$key\"");
                }
            }
            $stateList = array(self::STATE_editing, self::STATE_approval);
            if ( isset($data["state"]) ) {
                if ( ! in_array($data["state"], $stateList) ) {
                    throw new Exception("Unacceptable state '" . $data["state"] . "'");
                }
            } else {
                throw new Exception("No new state given");
            }
            if ( isset($data["figure_labels"]) ) {
                $labelList = $this->normalizeFigureLabels($data["figure_labels"]);
                $data["figure_labels"] = implode("^", $labelList);
            }
            $this->setSequenceSize($data);
            $data["current_start"] = $data["start"];
            unset($data["start"]);
            $data["current_end"] = $data["end"];
            unset($data["end"]);
            $sqlList = array(
                "date_added"  => "NOW()",
                "last_update" => "NULL"
            );
            if ( $data["evidence_subtype_id"] === "" ) {
                $sqlList["evidence_subtype_id"] = 'NULL';
            }
            list(
                $sql,
                $types,
                $paramList
            ) = $this->constructInsertStatement(
                $data,
                $sqlList
            );
            if ( ($statement = $this->db->getHandle()->prepare($sql)) === false ) {
                throw new Exception("Error preparing the statement: " . $this->db->getError());
            }
            array_unshift(
                $paramList,
                $types
            );
            call_user_func_array(
                array(
                    $statement,
                    "bind_param"
                ),
                $paramList
            );
            if ( $statement->execute() === false ) {
                throw new Exception("Failed to insert the new CRM segment: " . $statement->error);
            }
            $crmsId = $this->db->lastInsertId();
            if ( isset($labelList) ) {
                $this->updateFigureLabels(
                    $crmsId,
                    $labelList
                );
            }
            if ( isset($data["anatomical_expression_term_ids"]) ) {
                $this->updateAnatomicalExpressionTerms(
                    $crmsId,
                    $data["anatomical_expression_term_ids"]
                );
            }
            if ( isset($data["staging_data_ui"]) ) {
                $this->updateTripleStores(
                    $crmsId,
                    $data["staging_data_ui"]
                );
            }
            $this->db->commit();
        } catch ( Exception $e ) {
            $this->db->rollback();
            throw new Exception("Error creating the new CRM segment: " . $e->getMessage());
        }

        return $crmsId;
    }
    // --------------------------------------------------------------------------------
    // Update an CRM segment without creating a new version
    // @param int $crmsId The CRMSegment crm_segment_id
    // @param array $data An array of data used to update the CRM segment
    // @returns array The CRM segment data
    // --------------------------------------------------------------------------------
    public function update(
        $crmsId,
        array $data
    ) {
        try {
            $this->db->startTransaction();
            $crms = $this->getData($crmsId);
            // List of keys in the $data array that may be used to update the existing
            // CRM segment.
            $keyList = array(
                "anatomical_expression_term_ids",
                "archived_ends",
                "archived_genome_assembly_release_versions",
                "archived_starts",
                "assayed_in_species_id",
                "auditor_id",
                "cell_culture_only",
                "chromosome_id",
                "curator_id",
                "current_genome_assembly_release_version",
                "end",
                "evidence_id",
                "evidence_subtype_id",
                "fbal",
                "fbtp",
                "figure_labels",
                "gene_id",
                "has_flyexpress_images",
                "is_crm",
                "is_minimalized",
                "is_negative",
                "is_override",
                "last_audit",
                "last_update",
                "name",
                "notes",
                "pubmed_id",
                "sequence",
                "sequence_from_species_id",
                "sequence_source_id",
                "start",
                "state"
            );
            foreach ( $data as $key => $value ) {
                if ( array_key_exists($key, $this->columnTypeList) &&
                    ($data[$key] !== $crms[$key]) &&
                    (! in_array($key, $keyList)) ) {
                    throw new Exception("Can not modify CRM segment \"$key\"");
                }
            }
            // Make sure that the entity being updated is the newest version.
            if ( $crms["entity_id"] !== null ) {
                $maxVersion = $this->getMaxVersionNumber($crms["entity_id"]);
                if ( $crms["version"] !== $maxVersion ) {
                    throw new Exception(
                        sprintf(
                            "Attempt to update the older version of the CRM segment with id %s (entity id %d). " .
                                "Editing version %d, current version %d",
                            $crmsId,
                            $crms["entity_id"],
                            $crms["version"],
                            $maxVersion
                        )
                    );
                }
            }
            if ( ! isset($crms["state"]) ) {
                throw new Exception("Error about no state from the CRM segment to be updated");
            }
            if ( ! isset($data["state"]) ) {
                throw new Exception("Error about no new state for the CRM segment");
            }
            if ( $crms["state"] !== $data["state"] ) {
                // Check if an unacceptable change of state has been requested.
                $stateTransitions = array(
                    // Only users with the admin role can make the following state transition:
                    // 1) Approve an entity
                    array( self::STATE_approval, self::STATE_approved),
                    // 2) Edit an entity that is in the approval queue and save for later
                    array(self::STATE_approval, self::STATE_editing),
                    // 3) Edit an approved entity and submit back for approval
                    array(self::STATE_approved, self::STATE_approval),
                    // 4) Edit an approved entity and save for later
                    array(self::STATE_approved, self::STATE_editing),
                    // 5) Submit an entity for approval
                    array(self::STATE_editing, self::STATE_approval),
                );
                $allowed = false;
                foreach ( $stateTransitions as $transition ) {
                    if ( ($transition[0] === $crms["state"]) &&
                        ($transition[1] === $data["state"]) ) {
                        $allowed = true;
                        break;
                    }
                }
                if ( ! $allowed ) {
                    throw new Exception(sprintf(
                        "Illegal state transition: %s => %s",
                        $crms["state"],
                        $data["state"]
                    ));
                }
            }
            $sqlList = array();
            if ( ($crms["state"] === self::STATE_approval) &&
                ($data["state"] === self::STATE_approved) ) {
                unset($data["curator_id"]);
                unset($data["last_update"]);
                $sqlList = array(
                    "auditor_id" => Auth::getUser()->userId(),
                    "last_audit" => "NOW()"
                );
            } else {
                unset($data["auditor_id"]);
                unset($data["last_audit"]);
                $sqlList = array(
                    "curator_id"  => Auth::getUser()->userId(),
                    "last_update" => "NOW()"
                );
            }
            if ( isset($data["figure_labels"]) ) {
                $labelList = $this->normalizeFigureLabels($data["figure_labels"]);
                $data["figure_labels"] = implode("^", $labelList);
                $this->updateFigureLabels(
                    $crmsId,
                    $labelList
                );
            }
            $data["current_start"] = $data["start"];
            unset($data["start"]);
            $data["current_end"] = $data["end"];
            unset($data["end"]);
            $this->setSequenceSize($data);
            list(
                $sql,
                $types,
                $paramList
            ) = $this->constructUpdateStatement(
                $crmsId,
                $data,
                $sqlList
            );
            if ( ($statement = $this->db->getHandle()->prepare($sql)) === false ) {
                throw new Exception("Error preparing the statement: " . $this->db->getError());
            }
            array_unshift(
                $paramList,
                $types
            );
            call_user_func_array(
                array(
                    $statement,
                    "bind_param"
                ),
                $paramList
            );
            if ( $statement->execute() === false ) {
                throw new Exception("Error updating the CRM segment: " . $statement->error);
            }
            if ( isset($data["anatomical_expression_term_ids"]) ) {
                $this->updateAnatomicalExpressionTerms(
                    $crmsId,
                    $data["anatomical_expression_term_ids"]
                );
            }
            if ( isset($data["staging_data_ui"]) ) {
                $this->updateTripleStores(
                    $crmsId,
                    $data["staging_data_ui"]
                );
            }
            // Copy the data that was not changed into the supplied array.
            foreach ( $crms as $key => $value ) {
                if ( ! isset($data[$key]) ) {
                    $data[$key] = $crms[$key];
                }
            }
            $this->db->commit();
        } catch ( Exception $e ) {
            $this->db->rollback();
            throw new Exception("Error updating the CRM segment: " . $e->getMessage());
        }

        return $data;
    }
    // --------------------------------------------------------------------------------
    // This method is no longer used but still kept to follow the abstract class used
    // by this class.
    // Approve an CRM segment
    // @param int $crmsId The CRMSegment crm_segment_id
    // @param array $data (optional) An array of data used to update the CRM segment
    // @returns array The CRM segment data
    // --------------------------------------------------------------------------------
    public function approve(
        $crmsId,
        array $data = array()
    ) {
    }
    // --------------------------------------------------------------------------------
    // Create an "editing" or "approval" version of a "current" CRM segment.
    // @param int $crmsId The CRMSegment crm_segment_id
    // @param array $data (optional) An array of data used to update the CRM segment
    // @returns array The CRM segment data
    // --------------------------------------------------------------------------------
    public function createEdit(
        $crmsId,
        array $data = array()
    ) {
        try {
            $this->db->startTransaction();
            $crms = $this->getData($crmsId);
            if ( $crms["entity_id"] === null ) {
                throw new Exception("Attempt to edit a non-entity CRM segment");
            }
            if ( $crms["state"] !== self::STATE_current ) {
                throw new Exception("Attempt to edit a non-current CRM segment");
            }
            // Make sure an acceptable state has been supplied.
            $stateList = array(self::STATE_editing, self::STATE_approval);
            if ( isset($data["state"]) ) {
                if ( ! in_array($data["state"], $stateList) ) {
                    throw new Exception("Unacceptable state '" . $data["state"] . "'");
                }
            } else {
                throw new Exception("No new state given");
            }
            // List of keys in the $data array that may be used to create the new
            // version of the existing CRM segment.
            $keyList = array(
                "anatomical_expression_term_ids",
                "archived_ends",
                "archived_genome_assembly_release_versions",
                "archived_starts",
                "assayed_in_species_id",
                "cell_culture_only",
                "chromosome_id",
                "curator_id",
                "current_genome_assembly_release_version",
                "end",
                "date_added",
                "evidence_id",
                "evidence_subtype_id",
                "fbal",
                "fbtp",
                "figure_labels",
                "gene_id",
                "has_flyexpress_images",
                "is_crm",
                "is_minimalized",
                "is_negative",
                "is_override",
                "name",
                "notes",
                "pubmed_id",
                "sequence",
                "sequence_from_species_id",
                "sequence_source_id",
                "start",
                "state"
            );
            foreach ( $data as $key => $value ) {
                if ( array_key_exists($key, $this->columnTypeList) &&
                    ($data[$key] !== $crms[$key]) &&
                    (! in_array($key, $keyList)) ) {
                      throw new Exception("Can not modify CRM segment \"$key\"");
                }
                // Copy data to preserve $data array.
                $crms[$key] = $value;
            }
            $crms["version"] = $this->getNextVersionNumber($crms["entity_id"]);
            $crms["current_start"] = $crms["start"];
            unset($crms["start"]);
            $data["current_end"] = $crms["end"];
            unset($crms["end"]);
            $this->setSequenceSize($crms);
            $labelList = $this->normalizeFigureLabels($crms["figure_labels"]);
            $crms["figure_labels"] = implode("^", $labelList);
            $sqlList = array(
                "auditor_id"  => "NULL",
                "last_audit"  => "NULL",
                "curator_id"  => Auth::getUser()->userId(),
                "last_update" => "NOW()"
            );
            if ( $crms["evidence_subtype_id"] === "" ) {
                $sqlList["evidence_subtype_id"] = "NULL";
            }
            list(
                $sql,
                $types,
                $paramList
            ) = $this->constructInsertStatement(
                $crms,
                $sqlList
            );
            if ( ($statement = $this->db->getHandle()->prepare($sql)) === false ) {
                throw new Exception("Error preparing the statement: " . $this->db->getError());
            }
            array_unshift(
                $paramList,
                $types
            );
            call_user_func_array(
                array($statement, "bind_param"),
                $paramList
            );
            if ( $statement->execute() === false ) {
                throw new Exception("Error updating the CRM segment: " . $statement->error);
            }
            $oldId = $crms["crm_segment_id"];
            $newId = $this->db->lastInsertId();
            $crms["crm_segment_id"] = $newId;
            $this->updateFigureLabels(
                $newId,
                $labelList
            );
            $this->updateAnatomicalExpressionTermsWithTripleStores(
                $oldId,
                $newId,
                $crms["anatomical_expression_term_ids"]
            );
            $this->db->commit();
        } catch ( Exception $e ) {
            $this->db->rollback();
            throw new Exception("Error the creating edit of the CRM segment: " . $e->getMessage());
        }

        return $crms;
    }
    // --------------------------------------------------------------------------------
    // This method is no longer used but still kept to follow the abstract class used
    // by this class.
    // Create a new "current" version of a "current" CRM segment.
    // The changes to the "current" CRM segment will also be applied to any
    // non-current versions (i.e. "editing" and "approval" versions of the CRM
    // segment).
    // @param int $crmsId The CRM segment identifier, crm_segment_id
    // @param array $data (optional) An array of data used to update the CRM segment
    // @returns int The crm_segment_id for the new version of the CRM segment
    // --------------------------------------------------------------------------------
    public function createNewVersion(
        $crmsId,
        array $data = array()
    ) {
    }
    // --------------------------------------------------------------------------------
    // Update CRM segment anatomical expression terms
    // @param int $crmsId The CRM segment primary key
    // @param array $anatomicalExpressionTermList A list of term_id values
    // --------------------------------------------------------------------------------
    private function updateAnatomicalExpressionTerms(
        $crmsId,
        array $anatomicalExpressionTermList
    ) {
        try {
            $this->db->startTransaction();
            $deleteSql = <<<SQL
            DELETE 
            FROM CRMSegment_has_Expression_Term 
            WHERE crm_segment_id = $crmsId
SQL;
            $this->db->query($deleteSql);
            if ( count($anatomicalExpressionTermList) > 0 ) {
                $insertSql = "INSERT INTO CRMSegment_has_Expression_Term (crm_segment_id, term_id) VALUES ";
                $valueList = array();
                foreach ( $anatomicalExpressionTermList as $anatomicalExpressionTermId ) {
                    $valueList[] = "(" . $crmsId . ", " . $anatomicalExpressionTermId . ")";
                }
                $insertSql .= implode(", ", $valueList);
                $this->db->query($insertSql);
            }
            $this->db->commit();
        } catch ( Exception $e ) {
            $this->db->rollback();
            throw new Exception("Failed to update the anatomical expression terms: " .
                $e->getMessage());
        }
    }
    // --------------------------------------------------------------------------------
    // Update the triple stores of the CRM segment
    // @param int $crmsId The primary key of the CRM segment
    // @param array $stagingDataList A list of triple store primary keys
    // --------------------------------------------------------------------------------
    private function updateTripleStores(
        $crmsId,
        array $stagingDataList
    ) {
        try {
            $this->db->startTransaction();
            $selectSql = <<<SQL
            SELECT ts_id
            FROM triplestore_crm_segment
            WHERE crm_segment_id = $crmsId
SQL;
            $result = $this->db->query($selectSql);
            $existingStagingData = array();
            while ( $row = $result->fetch_assoc() ) {
                $existingStagingData[] = $row["ts_id"];
            }
            for ( $index = 0; $index < count($stagingDataList); $index++ ) {
                $stagingData = $stagingDataList[$index];
                $tsId = $stagingData["ts_id"];
                $anatomicalExpressionIdentifier = $stagingData["anatomical_expression_identifier"];
                $pubmedId = $stagingData["pubmed_id"];
                $stageOnIdentifier = $stagingData["stage_on_identifier"];
                $stageOffIdentifier = $stagingData["stage_off_identifier"];
                $biologicalProcessIdentifier = $stagingData["biological_process_identifier"];
                $sexId = $stagingData["sex_id"];
                $ectopicId = $stagingData["ectopic_id"];
                $enhancerOrSilencerAttributeId = $stagingData["enhancer_or_silencer_attribute_id"];
                if ( $tsId === "" ) {
                    $insertSql = <<<SQL
                    INSERT INTO triplestore_crm_segment (crm_segment_id, expression, pubmed_id, stage_on, stage_off, biological_process, sex, ectopic, silencer)
                    VALUES ($crmsId, '$anatomicalExpressionIdentifier', $pubmedId, '$stageOnIdentifier', '$stageOffIdentifier', '$biologicalProcessIdentifier', '$sexId', $ectopicId, '$enhancerOrSilencerAttributeId')
SQL;
                    $this->db->query($insertSql);
                } else {
                    if ( in_array($tsId, $existingStagingData) ) {
                        $updateSql = <<<SQL
                        UPDATE triplestore_crm_segment
                        SET expression = '$anatomicalExpressionIdentifier',
                            pubmed_id = $pubmedId,
                            stage_on = '$stageOnIdentifier',
                            stage_off = '$stageOffIdentifier',
                            biological_process = '$biologicalProcessIdentifier',
                            sex = '$sexId',
                            ectopic = $ectopicId,
                            silencer = '$enhancerOrSilencerAttributeId'
                        WHERE ts_id = $tsId
SQL;
                        $this->db->query($updateSql);
                        foreach ( $existingStagingData as $key => $value ) {
                            if ( $value === $tsId ) {
                                unset($existingStagingData[$key]);
                            }
                        }
                    }
                }
            }
            foreach ( $existingStagingData as $key => $value ) {
                $tsId = $value;
                $deleteSql = <<<SQL
                DELETE
                FROM triplestore_crm_segment
                WHERE ts_id = $tsId
SQL;
                $this->db->query($deleteSql);
            }
            $this->db->commit();
        } catch ( Exception $e ) {
            $this->db->rollback();
            throw new Exception("Failed to update the triple stores: " .
                $e->getMessage());
        }
    }
    // --------------------------------------------------------------------------------
    // Update the anatomical expression terms and their existing triple stores
    // @param int $oldCrmsId The old CRM segment primary key
    // @param int $newCrmsId The new CRM segment primary key
    // @param array $anatomicalExpressionTermList A list of term_id values
    // --------------------------------------------------------------------------------
    private function updateAnatomicalExpressionTermsWithTripleStores(
        $oldCrmsId,
        $newCrmsId,
        array $anatomicalExpressionTermList
    ) {
        try {
            $this->db->startTransaction();
            foreach ( $anatomicalExpressionTermList as $anatomicalExpressionTermId ) {
                $selectSql = <<<SQL
                SELECT identifier
                FROM ExpressionTerm
                WHERE term_id = $anatomicalExpressionTermId
SQL;
                $result = $this->db->query($selectSql);
                $row = $result->fetch_assoc();
                $anatomicalExpressionIdentifier = $row["identifier"];
                $selectSql = <<<SQL
                SELECT pubmed_id, 
                    stage_on, 
                    stage_off, 
                    biological_process, 
                    sex, 
                    ectopic,
                    silencer
                FROM triplestore_crm_segment
                WHERE crm_segment_id = $oldCrmsId AND 
                    expression = '$anatomicalExpressionIdentifier'
SQL;
                $result = $this->db->query($selectSql);
                while ( $row = $result->fetch_assoc() ) {
                    $insertSql = "INSERT INTO triplestore_crm_segment (crm_segment_id, expression, pubmed_id, stage_on, stage_off, biological_process, sex, ectopic, silencer) VALUES (" .
                        $newCrmsId . ", '". $anatomicalExpressionIdentifier . "', " . $row["pubmed_id"] . ", '" .
                        $row["stage_on"] . "', '" . $row["stage_off"] . "', '" .
                        $row["biological_process"] . "', '" . $row["sex"] . "', " .
                        $row["ectopic"] . ", '" . $row["silencer"] . "');";
                    $this->db->query($insertSql);
                }
            }
            $this->db->commit();
        } catch ( Exception $e ) {
            $this->db->rollback();
            throw new Exception("Failed to update the anatomical expression terms " .
                "with their triple stores: " . $e->getMessage());
        }
        $this->updateAnatomicalExpressionTerms(
            $newCrmsId,
            $anatomicalExpressionTermList
        );
    }
}
