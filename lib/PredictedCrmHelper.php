<?php
// ================================================================================
// Predicted CRM helper class.
// The public methods should not allow the application to enter an inconsistent
// state.
// These methods should be used to manage the predicted CRM lifecycle, they roughly
// correspond to the actions from the curator interface:
// Functions implementing the abstract functions of the Abstract entity helper
// class, aEntityHelper:
// - getData()
//   Used to get the data from a predicted CRM.
// - create()
//   Used to create a new predicted CRM in the "editing" or "approval" state.
// - update()
//   Used to modify a predicted CRM in the "editing" or "approval" state.
// No longer used
// - approve()
//   Used to approve a predicted CRM in the "approval" state and change its state to
//   "approved".
// - createEdit()
//   Used to create a new "editing" or "approval" state predicted CRM from a
//   predicted CRM in the "current" state.
// No longer used
// - createNewVersion()
//   Used to create a new "current" state predicted CRM from a predicted CRM
//   in the "current" state. This method is useful for changing CRM and
//   minimization status. It is also appropriate to use it in external scripts
//   that need to change properties of "current" CRM segments that require a new
//   version to be created.
// ================================================================================
class PredictedCrmHelper extends aEntityHelper
{
    // PredictedCRM table columns and mysqli type
    private static $predictedCrmColumnTypeList = array(
        "archive_date"                              => "s",
        "archived_ends"                             => "s",
        "archived_genome_assembly_release_versions" => "s",
        "archived_starts"                           => "s",
        "auditor_id"                                => "i",
        "chromosome_id"                             => "i",
        "curator_id"                                => "i",
        "current_end"                               => "i",
        "current_genome_assembly_release_version"   => "s",
        "current_start"                             => "i",
        "date_added"                                => "s",
        "entity_id"                                 => "i",
        "evidence_id"                               => "i",
        "evidence_subtype_id"                       => "i",
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
        return new PredictedCrmHelper();
    }
    // --------------------------------------------------------------------------------
    // Constructor.
    // --------------------------------------------------------------------------------
    public function __construct()
    {
        parent::__construct();
        $this->tableName = "PredictedCRM";
        $this->pkColumn = "predicted_crm_id";
        $this->abbrev = "PredictedCRM";
        $this->columnTypeList = self::$predictedCrmColumnTypeList;
    }
    // --------------------------------------------------------------------------------
    // Get the raw data for a predicted CRM.
    // @param int $pcrmId The predicted CRM predicted_crm_id
    // @returns array An array of predicted CRM data
    // --------------------------------------------------------------------------------
    public function getData($pcrmId)
    {
        try {
            $sql = <<<SQL
            SELECT *
            FROM PredictedCRM
            WHERE predicted_crm_id = $pcrmId
SQL;
            $result = $this->db->query($sql);
            if ( ($predictedCrm = $result->fetch_assoc()) === null ) {
                throw new Exception("No predicted CRM found for predicted_crm_id = " . $pcrmId);
            }
            $sql = <<<SQL
            SELECT term_id 
            FROM PredictedCRM_has_Expression_Term
            WHERE predicted_crm_id = $pcrmId
SQL;
            $result = $this->db->query($sql);
            $anatomicalExpressionTermList = array();
            while ( $anatomicalExpressionTermRow = $result->fetch_assoc() ) {
                $anatomicalExpressionTermList[] = $anatomicalExpressionTermRow["term_id"];
            }
            $predictedCrm["anatomical_expression_term_ids"] = $anatomicalExpressionTermList;
        } catch ( Exception $e ) {
            throw new Exception("Error fetching the predicted CRM: " . $e->getMessage());
        }

        return $predictedCrm;
    }
    // --------------------------------------------------------------------------------
    // Create a new predicted CRM.
    // @param array $data An array of data used to create the predicted CRM
    // @returns int The predicted_crm_id for the new predicted CRM version
    // --------------------------------------------------------------------------------
    public function create(array $data)
    {
        try {
            $this->db->startTransaction();
            // List of keys in the $data array that may be used to create
            // the new predicted CRM.
            $keyList = array(
                "anatomical_expression_term_ids",
                "chromosome_id",
                "curator_id",
                "current_end",
                "current_genome_assembly_release_version",
                "current_start",
                "evidence_id",
                "evidence_subtype_id",
                "last_update",
                "name",
                "notes",
                "pubmed_id",
                "sequence",
                "sequence_from_species_id",
                "sequence_source_id",
                "state"
            );
            foreach ( $data as $key => $value ) {
                if ( array_key_exists($key, $this->columnTypeList) &&
                    (! in_array($key, $keyList)) ) {
                      throw new Exception("Can not set predicted CRM \"$key\"");
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
            $this->setSequenceSize($data);
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
                throw new Exception("Failed to insert the new predicted CRM: " . $statement->error);
            }
            $pcrmId = $this->db->lastInsertId();
            if ( isset($data["anatomical_expression_term_ids"]) ) {
                $this->updateAnatomicalExpressionTerms(
                    $pcrmId,
                    $data["anatomical_expression_term_ids"]
                );
            }
            if ( isset($data["staging_data_ui"]) ) {
                $this->updateTripleStores(
                    $pcrmId,
                    $data["staging_data_ui"]
                );
            }
            $this->db->commit();
        } catch ( Exception $e ) {
            $this->db->rollback();
            throw new Exception("Error creating the new predicted CRM: " . $e->getMessage());
        }

        return $pcrmId;
    }
    // --------------------------------------------------------------------------------
    // Update a predicted CRM without creating a new version
    // @param int $pcrmId The PredictedCRM predicted_crm_id
    // @param array $data An array of data used to update the predicted CRM
    // @returns array The predicted CRM data
    // --------------------------------------------------------------------------------
    public function update(
        $pcrmId,
        array $data
    ) {
        try {
            $this->db->startTransaction();
            $pcrm = $this->getData($pcrmId);
            // List of keys in the $data array that may be used to update the predicted CRM.
            $keyList = array(
                "anatomical_expression_term_ids",
                "archived_ends",
                "archived_genome_assembly_release_versions",
                "archived_starts",
                "auditor_id",
                "chromosome_id",
                "curator_id",
                "current_end",
                "current_genome_assembly_release_version",
                "current_start",
                "evidence_id",
                "evidence_subtype_id",
                "last_audit",
                "last_update",
                "name",
                "notes",
                "pubmed_id",
                "sequence",
                "sequence_from_species_id",
                "sequence_source_id",
                "state"
            );
            foreach ( $data as $key => $value ) {
                if ( array_key_exists($key, $this->columnTypeList) &&
                    ($data[$key] !== $pcrm[$key]) &&
                    (! in_array($key, $keyList)) ) {
                    throw new Exception("Can not modify predicted CRM \"$key\"");
                }
            }
            // Make sure that the entity being updated is the newest version.
            if ( $pcrm["entity_id"] !== null ) {
                $maxVersion = $this->getMaxVersionNumber($pcrm["entity_id"]);
                if ( $pcrm["version"] !== $maxVersion ) {
                    throw new Exception(
                        sprintf(
                            "Attempt to update the older version of the predicted CRM with id %s (entity id %d). " .
                                "Editing version %d, current version %d",
                            $pcrmId,
                            $pcrm["entity_id"],
                            $pcrm["version"],
                            $maxVersion
                        )
                    );
                }
            }
            if ( ! isset($pcrm["state"]) ) {
                throw new Exception("Error about no state from the predicted CRM to be updated");
            }
            if ( ! isset($data["state"]) ) {
                throw new Exception("Error about no new state for the predicted CRM");
            }
            if ( $pcrm["state"] !== $data["state"] ) {
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
                    if ( ($transition[0] === $pcrm["state"]) &&
                        ($transition[1] === $data["state"]) ) {
                        $allowed = true;
                        break;
                    }
                }
                if ( ! $allowed ) {
                    throw new Exception(sprintf(
                        "Illegal state transition: %s => %s",
                        $pcrm["state"],
                        $data["state"]
                    ));
                }
            }
            $sqlList = array();
            if ( ($pcrm["state"] === self::STATE_approval) &&
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
            $this->setSequenceSize($data);
            // If the sequence has changed then we will need to update the coordinates.
            // What about the (currently supported) historical coordinates not being set?
            if ( array_key_exists("sequence", $data) &&
                ($pcrm["sequence"] !== $data["sequence"]) ) {
            //    CCR\REDfly\Datasource\Blat\Query
            //    $blatHandler = BlatHandler::factory();
            //    $blatResponse = $blatHandler->searchAction(
            //        array(),
            //        array(
            //            "sequence" => $data["sequence"]
            //        )
            //    );
            //    list($blatResult) = $blatResponse->results();
            //    $data["current_start"] = $blatResult["start"];
            //    $data["current_end"] = $blatResult["end"];
            }
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
                throw new Exception("Error updating the predicted CRM: " . $statement->error);
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
            foreach ( $pcrm as $key => $value ) {
                if ( ! isset($data[$key]) ) {
                    $data[$key] = $pcrm[$key];
                }
            }
            $this->db->commit();
        } catch ( Exception $e ) {
            $this->db->rollback();
            throw new Exception("Error updating the predicted CRM: " . $e->getMessage());
        }

        return $data;
    }
    // --------------------------------------------------------------------------------
    // This method is no longer used but still kept to follow the abstract class used
    // by this class.
    // Approve a predicted CRM
    // @param int $pcrmId The PredictedCRM predicted_crm_id
    // @param array $data (optional) An array of data used to update the predicted CRM
    // @returns array The predicted CRM data
    // --------------------------------------------------------------------------------
    public function approve(
        $pcrmId,
        array $data = array()
    ) {
    }
    // --------------------------------------------------------------------------------
    // Create an "editing" or "approval" version of a "current" predicted CRM.
    // @param int $pcrmId The PredictedCRM predicted_crm_id
    // @param array $data (optional) An array of data used to update the predicted CRM
    // @returns array The predicted CRM data
    // --------------------------------------------------------------------------------
    public function createEdit(
        $pcrmId,
        array $data = array()
    ) {
        try {
            $this->db->startTransaction();
            $pcrm = $this->getData($pcrmId);
            if ( $pcrm["entity_id"] === null ) {
                throw new Exception("Attempt to edit a non-entity predicted CRM");
            }
            if ( $pcrm["state"] !== self::STATE_current ) {
                throw new Exception("Attempt to edit a non-current predicted CRM");
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
            // Acceptable keys in the data array.
            $keyList = array(
                "anatomical_expression_term_ids",
                "archived_ends",
                "archived_genome_assembly_release_versions",
                "archived_starts",
                "chromosome_id",
                "current_end",
                "current_genome_assembly_release_version",
                "current_start",
                "date_added",
                "evidence_id",
                "evidence_subtype_id",
                "name",
                "notes",
                "sequence_source_id",
                "pubmed_id",
                "sequence",
                "sequence_from_species_id",
                "state"
            );
            foreach ( $data as $key => $value ) {
                if ( array_key_exists($key, $this->columnTypeList) &&
                    ($data[$key] !== $pcrm[$key]) &&
                    (! in_array($key, $keyList)) ) {
                      throw new Exception("Can not modify predicted CRM \"$key\"");
                }
                // Copy data to preserve $data array.
                $pcrm[$key] = $value;
            }
            $pcrm["version"] = $this->getNextVersionNumber($pcrm["entity_id"]);
            $this->setSequenceSize($pcrm);
            $sqlList = array(
                "auditor_id"  => "NULL",
                "last_audit"  => "NULL",
                "curator_id"  => Auth::getUser()->userId(),
                "last_update" => "NOW()"
            );
            if ( $pcrm["evidence_subtype_id"] === "" ) {
                $sqlList["evidence_subtype_id"] = "NULL";
            }
            list(
                $sql,
                $types,
                $paramList
            ) = $this->constructInsertStatement(
                $pcrm,
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
                throw new Exception("Error updating the predicted CRM: " . $statement->error);
            }
            $oldId = $pcrm["predicted_crm_id"];
            $newId = $this->db->lastInsertId();
            $pcrm["predicted_crm_id"] = $newId;
            $this->updateAnatomicalExpressionTermsWithTripleStores(
                $oldId,
                $newId,
                $pcrm["anatomical_expression_term_ids"]
            );
            $this->db->commit();
        } catch ( Exception $e ) {
            $this->db->rollback();
            throw new Exception("Error the creating edit of the predicted CRM: " . $e->getMessage());
        }

        return $pcrm;
    }
    // --------------------------------------------------------------------------------
    // This method is no longer used but still kept to follow the abstract class used
    // by this class.
    // Create a new "current" version of a "current" predicted CRM.
    // The changes to the "current" predicted CRM will also be applied to any
    // non-current versions (i.e. "editing" and "approval" versions of the predicted
    // CRM).
    // @param int $pcrmId The predicted CRM identifier, predicted_crm_id
    // @param array $data (optional) An array of data used to update the predicted CRM
    // @returns int The predicted_crm_id for the new version of the predicted CRM
    // --------------------------------------------------------------------------------
    public function createNewVersion(
        $pcrmId,
        array $data = array()
    ) {
    }
    // --------------------------------------------------------------------------------
    // Update predicted CRM anatomical expression terms
    // @param int $pcrmId The predicted CRM primary key
    // @param array $anatomicalExpressionTermList A list of term_id values
    // --------------------------------------------------------------------------------
    private function updateAnatomicalExpressionTerms(
        $pcrmId,
        array $anatomicalExpressionTermList
    ) {
        try {
            $this->db->startTransaction();
            $deleteSql = <<<SQL
            DELETE 
            FROM PredictedCRM_has_Expression_Term 
            WHERE predicted_crm_id = $pcrmId
SQL;
            $this->db->query($deleteSql);
            if ( count($anatomicalExpressionTermList) > 0 ) {
                $insertSql = "INSERT INTO PredictedCRM_has_Expression_Term (predicted_crm_id, term_id) VALUES ";
                $valueList = array();
                foreach ( $anatomicalExpressionTermList as $anatomicalExpressionTermId ) {
                    $valueList[] = "(". $pcrmId . ", " . $anatomicalExpressionTermId . ")";
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
    // Update predicted CRM triple stores
    // @param int $pcrmId The predicted CRM primary key
    // @param array $stagingDataList A list of staging data
    // --------------------------------------------------------------------------------
    private function updateTripleStores(
        $pcrmId,
        array $stagingDataList
    ) {
        try {
            $this->db->startTransaction();
            $selectSql = <<<SQL
            SELECT ts_id
            FROM triplestore_predicted_crm
            WHERE predicted_crm_id = $pcrmId
SQL;
            $result = $this->db->query($selectSql);
            $existingStagingData = array();
            while ( $row = $result->fetch_assoc() ) {
                $existingStagingData[] = $row["ts_id"];
            }
            for ( $index = 0; $index < count($stagingDataList); $index++ ) {
                $stagingData = $stagingDataList[$index];
                $tsId = $stagingData["ts_id"];
                $expressionFlyBaseId = $stagingData["expression_identifier"];
                $pubmedId = $stagingData["pubmed_id"];
                $stageOnFlyBaseId = $stagingData["stage_on_identifier"];
                $stageOffFlyBaseId = $stagingData["stage_off_identifier"];
                $biologicalProcessGoId = $stagingData["biological_process_go_id"];
                $sexId = $stagingData["sex_id"];
                $enhancerOrSilencerAttributeId = $stagingData["enhancer_or_silencer_attribute_id"];
                if ( $tsId === "" ) {
                    $insertSql = <<<SQL
                    INSERT INTO triplestore_predicted_crm (crm_segment_id, expression, pubmed_id, stage_on, stage_off, biological_process, sex, silencer)
                    VALUES ($pcrmId, '$expressionFlyBaseId', $pubmedId, '$stageOnFlyBaseId', '$stageOffFlyBaseId', '$biologicalProcessGoId', '$sexId', '$enhancerOrSilencerAttributeId')
SQL;
                    $this->db->query($insertSql);
                } else {
                    if ( in_array($tsId, $existingStagingData) ) {
                        $updateSql = <<<SQL
                        UPDATE triplestore_predicted_crm
                        SET expression = '$expressionFlyBaseId',
                            pubmed_id = $pubmedId,
                            stage_on = '$stageOnFlyBaseId',
                            stage_off = '$stageOffFlyBaseId',
                            biological_process = '$biologicalProcessGoId',
                            sex = '$sexId',
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
                FROM triplestore_predicted_crm
                WHERE ts_id = $tsId
SQL;
                $this->db->query($deleteSql);
            }
            $this->db->commit();
        } catch ( Exception $e ) {
            $this->db->rollback();
            throw new Exception("Failed to update triple stores: " . $e->getMessage());
        }
    }
    // --------------------------------------------------------------------------------
    // Update the anatomical expression terms and their existing triple stores
    // @param int $oldPcrmId The old predicted CRM primary key
    // @param int $newPcrmId The new predicted CRM primary key
    // @param array $anatomicalExpressionTermList A list of term_id values
    // --------------------------------------------------------------------------------
    private function updateAnatomicalExpressionTermsWithTripleStores(
        $oldPcrmId,
        $newPcrmId,
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
                    silencer
                FROM triplestore_predicted_crm
                WHERE predicted_crm_id = $oldPcrmId AND 
                    expression = '$anatomicalExpressionIdentifier'
SQL;
                $result = $this->db->query($selectSql);
                while ( $row = $result->fetch_assoc() ) {
                    $insertSql = "INSERT INTO triplestore_predicted_crm (predicted_crm_id, expression, pubmed_id, stage_on, stage_off, biological_process, sex, silencer) VALUES (" .
                        $newPcrmId . ", '". $anatomicalExpressionIdentifier . "', " . $row["pubmed_id"] . ", '" .
                        $row["stage_on"] . "', '" . $row["stage_off"] . "', '" .
                        $row["biological_process"] . "', '" . $row["sex"] . ", '" . $row["silencer"] . "');";
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
            $newPcrmId,
            $anatomicalExpressionTermList
        );
    }
}
