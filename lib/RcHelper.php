<?php
// ================================================================================
// Reporter Construct helper class.
// The public methods should not allow the application to enter an
// inconsistent state.
// These methods should be used to manage the reporter construct lifecycle, they
// roughly correspond to the actions from the curator interface:
// Functions implementing the abstract functions of the Abstract entity helper
// class, aEntityHelper:
// - getData()
//   Used to get the data from an reporter construct.
// - create()
//   Used to create a new reporter construct in the "editing" or "approval" state.
// - update()
//   Used to modify an reporter construct in the "editing" or "approval" state.
// - approve()
//   Used to approve an reporter construct in the "approval" state and change its
//   state to   "approved".
// - createEdit()
//   Used to create a new "editing" or "approval" state reporter construct from an
//   reporter construct in the "current" state.
// - createNewVersion()
//   Used to create a new "current" state reporter construct from an reporter
//   construct in the "current" state. This method is useful for changing CRM and
//   minimization status. It is also appropriate to use it in external scripts that
//   need to change properties of "current" reporter constructs that require a new
//   version to be created.
// Functions:
// - updateAssociatedBs()
// - isMinimized()
// - minimizeAllRcs()
// ================================================================================
class RcHelper extends aEntityHelper
{
    // ReporterConstruct table columns and mysqli type.
    private static $rcColumnTypeList = array(
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
        "fbal"                                      => "s",
        "fbtp"                                      => "s",
        "figure_labels"                             => "s",
        "gene_id"                                   => "i",
        "has_flyexpress_images"                     => "i",
        "has_tfbs"                                  => "i",
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
        return new RcHelper();
    }
    // --------------------------------------------------------------------------------
    // Constructor.
    // --------------------------------------------------------------------------------
    public function __construct()
    {
        parent::__construct();
        $this->tableName = "ReporterConstruct";
        $this->pkColumn = "rc_id";
        $this->abbrev = "RC";
        $this->columnTypeList = self::$rcColumnTypeList;
    }
    // --------------------------------------------------------------------------------
    // Get the raw data for a reporter construct.
    // @param int $rcId The ReporterConstruct rc_id
    // @returns array An array of reporter construct data
    // --------------------------------------------------------------------------------
    public function getData($rcId)
    {
        try {
            $sql = <<<SQL
            SELECT *
            FROM ReporterConstruct
            WHERE rc_id = $rcId
SQL;
            $result = $this->db->query($sql);
            if ( ($rc = $result->fetch_assoc()) === null ) {
                throw new Exception("No reporter construct found for rc_id = " . $rcId);
            }
            $sql = <<<SQL
            SELECT term_id
            FROM RC_has_ExprTerm
            WHERE rc_id = $rcId
SQL;
            $result = $this->db->query($sql);
            $anatomicalExpressionTermList = array();
            while ( $anatomicalExpressionTermRow = $result->fetch_assoc() ) {
                $anatomicalExpressionTermList[] = $anatomicalExpressionTermRow["term_id"];
            }
            $rc["anatomical_expression_term_ids"] = $anatomicalExpressionTermList;
        } catch ( Exception $e ) {
            throw new Exception("Error fetching the reporter construct: " . $e->getMessage());
        }

        return $rc;
    }
    // --------------------------------------------------------------------------------
    // Create a new reporter construct.
    // @param array $data An array of data used to create the reporter construct
    // @returns int The rc_id for the new reporter construct
    // --------------------------------------------------------------------------------
    public function create(array $data)
    {
        try {
            $this->db->startTransaction();
            // List of keys in the $data array that may be used to create the new
            // reporter construct.
            $keyList = array(
                "anatomical_expression_term_ids",
                "assayed_in_species_id",
                "cell_culture_only",
                "chromosome_id",
                "curator_id",
                "current_genome_assembly_release_version",
                "end",
                "evidence_id",
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
                      throw new Exception("Can not set RC \"$key\"");
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
                throw new Exception("Failed to insert the new reporter construct: " . $statement->error);
            }
            $rcId = $this->db->lastInsertId();
            if ( isset($labelList) ) {
                $this->updateFigureLabels(
                    $rcId,
                    $labelList
                );
            }
            if ( isset($data["anatomical_expression_term_ids"]) ) {
                $this->updateAnatomicalExpressionTerms(
                    $rcId,
                    $data["anatomical_expression_term_ids"]
                );
            }
            if ( isset($data["staging_data_ui"]) ) {
                $this->updateTripleStores(
                    $rcId,
                    $data["staging_data_ui"]
                );
            }
            $this->db->commit();
        } catch ( Exception $e ) {
            $this->db->rollback();
            throw new Exception("Error creating the new reporter construct: " . $e->getMessage());
        }

        return $rcId;
    }
    // --------------------------------------------------------------------------------
    // Update a reporter construct without creating a new version
    // @param int $rcId The ReporterConstruct rc_id
    // @param array $data An array of data used to update the reporter construct
    // @returns array The reporter construct data
    // --------------------------------------------------------------------------------
    public function update(
        $rcId,
        array $data
    ) {
        try {
            $this->db->startTransaction();
            $rc = $this->getData($rcId);
            // List of keys in the $data array that may be used to update the existing
            // reporter construct.
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
                    ($data[$key] !== $rc[$key]) &&
                    (! in_array($key, $keyList)) ) {
                    throw new Exception("Can not modify RC \"$key\"");
                }
            }
            // Make sure that the entity being updated is the newest version.
            if ( isset($rc["entity_id"]) ) {
                $maxVersion = $this->getMaxVersionNumber($rc["entity_id"]);
                if ( $rc["version"] !== $maxVersion ) {
                    throw new Exception(
                        sprintf(
                            "Attempt to update the older version of the reporter construct with id %s (entity id %d). " .
                                "Editing version %d, current version %d",
                            $rcId,
                            $rc["entity_id"],
                            $rc["version"],
                            $maxVersion
                        )
                    );
                }
            }
            if ( ! isset($rc["state"]) ) {
                throw new Exception("Error about no state from the reporter construct to be updated");
            }
            if ( isset($data["state"]) &&
                ($rc["state"] !== $data["state"]) ) {
                // Check if an unacceptable change of state has been requested.
                $stateTransitions = array(
                    // Only users with the admin role can make the following state transition:
                    // 1) Approve an entity
                    array(self::STATE_approval, self::STATE_approved),
                    // 2) Edit an entity that is in the approval queue and save for later
                    array(self::STATE_approval, self::STATE_editing),
                    // 3) Edit an approved entity and submit back for approval
                    array(self::STATE_approved, self::STATE_approval),
                    // 4) Edit an approved entity and save for later
                    array(self::STATE_approved, self::STATE_editing),
                    // 5) Submit an entity for approval
                    array(self::STATE_editing, self::STATE_approval)
                );
                $allowed = false;
                foreach ( $stateTransitions as $transition ) {
                    if ( ($transition[0] === $rc["state"]) &&
                        ($transition[1] === $data["state"]) ) {
                        $allowed = true;
                        break;
                    }
                }
                if ( ! $allowed ) {
                    throw new Exception(sprintf(
                        "Illegal state transition: %s => %s",
                        $rc["state"],
                        $data["state"]
                    ));
                }
            }
            // Only being used by the minimization of all the reporter constructs
            $stayingCurrent = ($rc["state"] === self::STATE_current) &&
                isset($data["state"]) &&
                ($data["state"] === self::STATE_current);
            $coordinatesChanged = ( isset($data["chromosome_id"]) && ($rc["chromosome_id"] !== $data["chromosome_id"]) ) ||
                ( isset($data["current_start"]) && ($rc["current_start"] !== $data["current_start"]) ) ||
                ( isset($data["current_end"]) && ($rc["current_end"] !== $data["current_end"]) );
            if ( ($stayingCurrent && $coordinatesChanged) ) {
                $updateTfbs = true;
            }
            $sqlList = array();
            if ( ($rc["state"] === self::STATE_approval) &&
                isset($data["state"]) &&
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
                    $rcId,
                    $labelList
                );
            }
            if ( isset($data["start"]) ) {
                $data["current_start"] = $data["start"];
                unset($data["start"]);
            }
            if ( isset($data["end"]) ) {
                $data["current_end"] = $data["end"];
                unset($data["end"]);
            }
            $this->setSequenceSize($data);
            list(
                $sql,
                $types,
                $paramList
            ) = $this->constructUpdateStatement(
                $rcId,
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
                throw new Exception("Error updating the reporter construct: " . $statement->error);
            }
            if ( isset($data["anatomical_expression_term_ids"]) ) {
                $this->updateAnatomicalExpressionTerms(
                    $rcId,
                    $data["anatomical_expression_term_ids"]
                );
            }
            if ( isset($data["staging_data_ui"]) ) {
                $this->updateTripleStores(
                    $rcId,
                    $data["staging_data_ui"]
                );
            }
            // Copy the data that was not changed into the supplied array.
            foreach ( $rc as $key => $value ) {
                if ( ! isset($data[$key]) ) {
                    $data[$key] = $rc[$key];
                }
            }
            if ( isset($updateTfbs) &&
                $updateTfbs ) {
                $this->updateAssociatedBs(
                    $rcId,
                    $data["chromosome_id"],
                    $data["current_start"],
                    $data["current_end"]
                );
            }
            $this->db->commit();
        } catch ( Exception $e ) {
            $this->db->rollback();
            throw new Exception("Error updating the reporter construct: " . $e->getMessage());
        }

        return $data;
    }
    // --------------------------------------------------------------------------------
    // Approve a reporter construct and make it "current".
    // @see update()
    // @param int $rcId The ReporterConstruct rc_id
    // @param array $data (optional) An array of data used to update the reporter construct
    // @returns array The reporter construct data
    // --------------------------------------------------------------------------------
    public function approve(
        $rcId,
        array $data = array()
    ) {
        try {
            $this->db->startTransaction();
            $data["state"] = self::STATE_approved;
            $data = $this->update(
                $rcId,
                $data
            );
            $this->db->commit();
        } catch ( Exception $e ) {
            $this->db->rollback();
            throw new Exception("Error approving the reporter construct: " . $e->getMessage());
        }

        return $data;
    }
    // --------------------------------------------------------------------------------
    // Create an "editing" or "approval" version of a "current" reporter construct.
    // @param int $rcId The ReporterConstruct rc_id
    // @param array $data (optional) An array of data used to update the reporter
    //   construct
    // @returns array The reporter construct data
    // --------------------------------------------------------------------------------
    public function createEdit(
        $rcId,
        array $data = array()
    ) {
        try {
            $this->db->startTransaction();
            $rc = $this->getData($rcId);
            if ( $rc["entity_id"] === null ) {
                throw new Exception("Attempt to create edit of non-entity");
            }
            if ( $rc["state"] !== self::STATE_current ) {
                throw new Exception("Attempt to create edit of non-current the reporter construct");
            }
            // List of keys in the $data array that may be used to create the new
            // version of the existing reporter construct.
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
                "evidence_id",
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
                    ($data[$key] !== $rc[$key]) &&
                    (! in_array($key, $keyList)) ) {
                      throw new Exception("Can not modify RC \"$key\"");
                }
                // Copy data to preserve $data array.
                $rc[$key] = $value;
            }
            # Typically both start and end variables come from the front end
            # where no "current" word is still used
            if ( isset($data["start"]) ) {
                $rc["current_start"] = $data["start"];
            }
            if ( isset($data["end"]) ) {
                $rc["current_end"] = $data["end"];
            }
            // Make sure an unacceptable state has been supplied.
            $stateList = array(
                self::STATE_approval,
                self::STATE_deleted,
                self::STATE_editing
            );
            if ( isset($data["state"]) ) {
                if ( ! in_array($data["state"], $stateList) ) {
                    throw new Exception("Unacceptable state '" . $data["state"] . "'");
                }
            } else {
                $data["state"] = "editing";
            }
            $data = $this->createNew($rc);
            $this->db->commit();
        } catch ( Exception $e ) {
            $this->db->rollback();
            throw new Exception("Error the creating edit of the reporter construct: " . $e->getMessage());
        }

        return $data;
    }
    // --------------------------------------------------------------------------------
    // Create a new "current" version of a "current" reporter construct.
    // The changes to the "current" reporter construct will also be applied to any
    // non-current versions (i.e. "editing" and "approval" versions of
    // the reporter construct).
    // @param int $rcId The ReporterConstruct rc_id
    // @param array $data (optional) An array of data used to update the reporter construct
    // @returns int The rc_id for the new version of the reporter construct
    // --------------------------------------------------------------------------------
    public function createNewVersion(
        $rcId,
        array $data = array()
    ) {
        try {
            $this->db->startTransaction();
            $rc = $this->getData($rcId);
            if ( $rc["entity_id"] === null ) {
                throw new Exception("Attempt to create new version of non-entity");
            }
            if ( $rc["state"] !== self::STATE_current ) {
                throw new Exception("Attempt to create new version of non-current reporter construct");
            }
            if ( isset($data["state"]) &&
                ($data["state"] !== self::STATE_current) ) {
                throw new Exception("Can not create a non-current version of a current reporter construct");
            }
            // These are the keys of items that can be changed when creating a
            // new version of the reporter construct.
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
                "evidence_id",
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
                "start"
            );
            foreach ( $data as $key => $value ) {
                if ( array_key_exists($key, $this->columnTypeList) &&
                    ($data[$key] !== $rc[$key]) &&
                    (! in_array($key, $keyList)) ) {
                      throw new Exception("Can not modify RC \"$key\"");
                }
                // Copy data to preserve $data array.
                $rc[$key] = $value;
            }
            $rc = $this->createNew($rc);
            $this->archivePreviousVersions(
                $rc["entity_id"],
                $rc["version"]
            );
            $this->fixVersionNumbers($rc["entity_id"]);
            $this->updateAssociatedBs(
                $rc["rc_id"],
                $rc["chromosome_id"],
                $rc["current_start"],
                $rc["current_end"]
            );
            // Update any non-current versions of the reporter construct.
            $sql = "
            SELECT rc_id
            FROM ReporterConstruct
            WHERE entity_id = " . $rc["entity_id"] . " AND
                state IN ('" . self::STATE_approval . "', '" .
                self::STATE_editing . "', '" .
                self::STATE_rejected . "')";
            $result = $this->db->query($sql);
            while ( $row = $result->fetch_assoc() ) {
                $this->update($row["rc_id"], $data);
            }
            $this->db->commit();
        } catch ( Exception $e ) {
            $this->db->rollback();
            throw new Exception("Error updating the reporter construct: " . $e->getMessage());
        }

        return $rc;
    }
    // --------------------------------------------------------------------------------
    // Helper function for the other functions that create new versions.
    // @param array $data An array of reporter construct data.
    //   It must contain keys for every column (with the exception of those that are
    //   calculated from other columns) and related table
    //   (e.g. anatomical_expression_term_ids).
    // @returns array The reporter construct data.
    // --------------------------------------------------------------------------------
    private function createNew(array $data)
    {
        $data["version"] = $this->getNextVersionNumber($data["entity_id"]);
        $this->setSequenceSize($data);
        $labelList = $this->normalizeFigureLabels($data["figure_labels"]);
        $data["figure_labels"] = implode("^", $labelList);
        $sqlList = array(
            "curator_id"  => Auth::getUser()->userId(),
            "last_update" => "NOW()"
        );
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
            throw new Exception("Error updating the reporter construct: " . $statement->error);
        }
        $oldId = $data["rc_id"];
        $newId = $this->db->lastInsertId();
        $data["rc_id"] = $newId;
        $this->updateFigureLabels(
            $newId,
            $labelList
        );
        $this->updateAnatomicalExpressionTermsWithTripleStores(
            $oldId,
            $newId,
            $data["anatomical_expression_term_ids"]
        );

        return $data;
    }
    // --------------------------------------------------------------------------------
    // Update the anatomical expression terms of the reporter construct
    // @param int $rcId The primary key of the reporter construct
    // @param array $labelList A list of term_id values
    // --------------------------------------------------------------------------------
    private function updateAnatomicalExpressionTerms(
        $rcId,
        array $anatomicalExpressionTermList
    ) {
        try {
            $this->db->startTransaction();
            $deleteSql = <<<SQL
            DELETE
            FROM RC_has_ExprTerm
            WHERE rc_id = $rcId
SQL;
            $this->db->query($deleteSql);
            if ( count($anatomicalExpressionTermList) > 0 ) {
                $insertSql = "INSERT INTO RC_has_ExprTerm (rc_id, term_id) VALUES ";
                $valueList = array();
                foreach ( $anatomicalExpressionTermList as $anatomicalExpressionTermId ) {
                    $valueList[] = "(" . $rcId . ", ".  $anatomicalExpressionTermId . ")";
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
    // Update the triple stores of the reporter construct
    // @param int $rcId The primary key of the reporter construct
    // @param array $tripleStoreList A list of triple store primary keys
    // --------------------------------------------------------------------------------
    private function updateTripleStores(
        $rcId,
        array $stagingDataList
    ) {
        try {
            $selectSql = <<<SQL
            SELECT ts_id
            FROM triplestore_rc
            WHERE rc_id = $rcId
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
                    INSERT INTO triplestore_rc (rc_id, expression, pubmed_id, stage_on, stage_off, biological_process, sex, ectopic, silencer)
                    VALUES ($rcId, '$anatomicalExpressionIdentifier', $pubmedId, '$stageOnIdentifier', '$stageOffIdentifier', '$biologicalProcessIdentifier', '$sexId', $ectopicId, '$enhancerOrSilencerAttributeId')
SQL;
                    $this->db->query($insertSql);
                } else {
                    if ( in_array($tsId, $existingStagingData) ) {
                        $updateSql = <<<SQL
                        UPDATE triplestore_rc
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
                FROM triplestore_rc
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
    // @param int $oldRcId The old primary key of the reporter construct
    // @param int $newRcId The new primary key of the reporter construct
    // @param array $anatomicalExpressionTermList A list of term_id values
    // --------------------------------------------------------------------------------
    private function updateAnatomicalExpressionTermsWithTripleStores(
        $oldRcId,
        $newRcId,
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
                FROM triplestore_rc
                WHERE rc_id = $oldRcId AND
                    expression = '$anatomicalExpressionIdentifier'
SQL;
                $result = $this->db->query($selectSql);
                while ( $row = $result->fetch_assoc() ) {
                    $insertSql = "INSERT INTO triplestore_rc (rc_id, expression, pubmed_id, stage_on, stage_off, biological_process, sex, ectopic, silencer) VALUES (" .
                        $newRcId . ", '". $anatomicalExpressionIdentifier . "', " . $row["pubmed_id"] . ", '" .
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
            $newRcId,
            $anatomicalExpressionTermList
        );
    }
    // --------------------------------------------------------------------------------
    // Update the associated transcription factor binding sites for a given reporter
    // construct
    // This should only be used with a reporter construct with "current" state.
    // @param int $rcId ID of the reporter construct to update
    // @param int $chromosomeId Sequence chromosome ID
    // @param int $currentStart Sequence start coordinate
    // @param int $currentEnd Sequence end coordinate
    // --------------------------------------------------------------------------------
    public function updateAssociatedBs(
        $rcId,
        $chromosomeId,
        $currentStart,
        $currentEnd
    ) {
        if ( ($previousRcId = $this->getPreviousPk($rcId)) !== null ) {
            $this->updateAssociatedBsHasRc($previousRcId);
        }
        // Delete current associations for the reporter construct
        $sql = <<<SQL
        DELETE
        FROM RC_associated_BS
        WHERE rc_id = $rcId
SQL;
        $this->db->query($sql);
        // Create new associations
        $sql = <<<SQL
        INSERT INTO RC_associated_BS
            SELECT $rcId,
                tfbs_id
            FROM BindingSite
            WHERE current_start BETWEEN $currentStart AND $currentEnd AND
                current_end BETWEEN $currentStart AND $currentEnd AND
                chromosome_id = $chromosomeId AND
                state = 'current'
SQL;
        $this->db->query($sql);
        $hasTfbs = $this->db->getHandle()->affected_rows > 0
            ? 1
            : 0;
        // Update has_tfbs accordingly
        $sql = <<<SQL
        UPDATE ReporterConstruct
        SET has_tfbs = $hasTfbs
        WHERE rc_id = $rcId
SQL;
        $this->db->query($sql);
        // Update has_rc for associated TFBS
        $sql = <<<SQL
        UPDATE BindingSite
        SET has_rc = 1
        WHERE current_start BETWEEN $currentStart AND $currentEnd AND
            current_end BETWEEN $currentStart AND $currentEnd AND
            chromosome_id = $chromosomeId AND
            state = 'current'
SQL;
        $this->db->query($sql);
    }
    // --------------------------------------------------------------------------------
    // Update the "has_rc" field of transcription factor binding sites associated with
    // the version of a reporter construct that is no longer current.
    // This should be called with the old rc_id of a reporter construct whenever a new
    // "current" version of the reporter construct is created.
    // @param int $rcId The reporter construct rc_id
    // --------------------------------------------------------------------------------
    private function updateAssociatedBsHasRc($rcId)
    {
        // Set has_rc = 0 for all TFBS that are associated with this RC and
        // only this RC.
        // Find all TFBS associated with this version of the RC
        $sql = <<<SQL
        CREATE TEMPORARY TABLE temp_bs_assoc_rc AS
            SELECT DISTINCT tfbs_id
            FROM RC_associated_BS
            JOIN ReporterConstruct USING (rc_id)
            WHERE rc_id = $rcId
SQL;
        $this->db->query($sql);
        // Of those TFBS, which are associated with no RC (excluding the
        // current one)
        $sql = <<<SQL
        CREATE TEMPORARY TABLE temp_bs_with_no_rc AS
            SELECT DISTINCT temp_bs_assoc_rc.tfbs_id
            FROM temp_bs_assoc_rc
            JOIN RC_associated_BS assoc USING (tfbs_id)
            LEFT JOIN ReporterConstruct rc ON assoc.rc_id = rc.rc_id AND
                rc.state = 'current' AND
                rc.rc_id != $rcId
            GROUP BY tfbs_id
            HAVING COUNT(DISTINCT rc.rc_id) = 0
SQL;
        $this->db->query($sql);
        $sql = <<<SQL
        UPDATE BindingSite bs
        JOIN temp_bs_with_no_rc USING (tfbs_id)
        SET bs.has_rc = 0
SQL;
        $this->db->query($sql);
        // Remove temporary tables
        $sql = <<<SQL
        DROP TEMPORARY TABLE temp_bs_assoc_rc
SQL;
        $this->db->query($sql);
        $sql = <<<SQL
        DROP TEMPORARY TABLE temp_bs_with_no_rc
SQL;
        $this->db->query($sql);
    }
    // --------------------------------------------------------------------------------
    // Check if a reporter construct is minimized.
    // A reporter construct is considered minimized if it is  either fully enclosed by
    // or fully encloses another reporter construct.
    // See examples below.
    // RC:                |---------------|
    // Fully Enclosed:       |------|
    // Not Enclosed:   |-------|
    // Not Enclosed:                   |------|
    // Fully Encloses: |-----------------------|
    // @param RC $rc
    // @returns bool
    // --------------------------------------------------------------------------------
    public function isMinimized(RC $rc)
    {
        $rcId = $rc->getId();
        $chromosomeId = $rc->getChromosomeId();
        $currentStart = $rc->getCurrentStart();
        $currentEnd = $rc->getCurrentEnd();
        $startMin = $currentStart - $this->rcErrorMargin;
        $startMax = $currentStart + $this->rcErrorMargin;
        $endMin = $currentEnd - $this->rcErrorMargin;
        $endMax = $currentEnd + $this->rcErrorMargin;
        // Find reporter construct(s) that are fully enclosed by the current reporter
        // construct plus/minus the error margin (typically, 5bp) or  other reporter
        // construct(s) that fully enclose this reporter construct plus/minus the same
        // error margin
        $sql = <<<SQL
        SELECT COUNT(*)
        FROM ReporterConstruct
        WHERE rc_id != $rcId AND
            state = 'current' AND
            chromosome_id = $chromosomeId AND
            ( (current_start > $startMin AND current_end < $endMax) OR
              (current_start < $startMax AND current_end > $endMin) )
SQL;
        $result = $this->db->query($sql);
        if ( ($row = $result->fetch_row()) === null ) {
            throw new Exception("Error checking for enclosed reporter constructs");
        }
        if ( $row[0] !== "0" ) {
            return true;
        }

        return false;
    }
    // --------------------------------------------------------------------------------
    // Update minimization status of all the reporter constructs.
    // @param $createNewVersions (optional) Defaults to TRUE and create
    //   new versions of the reporter constructs.
    //   Use FALSE to update reporter constructs without creating new versions.
    // @returns An array of report data
    // --------------------------------------------------------------------------------
    public function minimizeAllRcs($createNewVersions = true)
    {
        $report = array();
        $sql = <<<SQL
        SELECT rc.rc_id,
            rc.name,
            rc.gene_id,
            c.name AS chromosome,
            rc.chromosome_id,
            rc.current_start,
            rc.current_end,
            rc.is_negative,
            rc.is_minimalized
        FROM ReporterConstruct rc
        JOIN Chromosome c ON rc.chromosome_id = c.chromosome_id
        WHERE state = 'current'
        ORDER BY name
SQL;
        $result = $this->db->query($sql);
        while ( $row = $result->fetch_assoc() ) {
            $rcId = $row["rc_id"];
            // NOTE: Anatomical expression terms are currently not used here
            // since they are not used in the minimization process.
            $rc = new RC(
                $rcId,
                $row["name"],
                $row["gene_id"],
                $row["chromosome"],
                $row["chromosome_id"],
                $row["current_start"],
                $row["current_end"],
                $row["is_negative"]
            );
            if ( $row["is_minimalized"] === "1" ) {
                $minimizedDb = true;
            } else {
                $minimizedDb = false;
            }
            $minimizedCalc = $this->isMinimized($rc);
            if ( $minimizedDb !== $minimizedCalc ) {
                if ( $createNewVersions ) {
                    $this->createNewVersion(
                        $rcId,
                        array("is_minimalized" => $minimizedCalc)
                    );
                } else {
                    $this->update(
                        $rcId,
                        array("is_minimalized" => $minimizedCalc)
                    );
                }
                $reportMessageList = array("Minimization status changed to " .
                    ( $minimizedCalc
                        ? "TRUE"
                        : "FALSE" ));
                $report[] = array(
                    "name"     => $rc->getName(),
                    "rc_id"    => $rcId,
                    "coord"    => $rc->getCoordinates(),
                    "messages" => $reportMessageList
                );
            }
        }

        return $report;
    }
}
