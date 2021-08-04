<?php
// ================================================================================
// Triple Store helper class.
// The public methods should not allow the application to enter an inconsistent
// state.
// These methods should be used to manage the triple store lifecycle, they
// roughly correspond to the actions from the curator interface:
// - create()
//   Used to create a new triple store of an existing predicted CRM.
// - update()
//   Used to modify an existing triple store of an existing predicted CRM.
// - delete()
//   Used to delete all the triple store associated to both predicted CRM
//   id and anatomical expression identifier given.
// - deleteAll()
//   Used to delete all the triple store associated to all the anatomical
//   expression identifiers associated to a predicted CRM id given.
// ================================================================================
class PredictedCrmTsHelper
{
    // DbService instance.
    private $db = null;
    // Triple store table name.
    private $tableName;
    // Triple store table primary key column.
    private $pkColumn;
    // triplestore_predicted_crm table columns and mysqli type. Excludes the
    // primary key "ts_id" since it should never be set directly.
    private $columnTypeList;
    // --------------------------------------------------------------------------------
    // Factory method design pattern.
    // --------------------------------------------------------------------------------
    public static function factory()
    {
        return new PredictedCrmTsHelper();
    }
    // --------------------------------------------------------------------------------
    // Constructor.
    // --------------------------------------------------------------------------------
    public function __construct()
    {
        $this->db = DbService::factory();
        $this->tableName = "triplestore_predicted_crm";
        $this->pkColumn = "ts_id";
        $this->columnTypeList =  array(
            "predicted_crm_id"   => "i",
            "expression"         => "s",
            "pubmed_id"          => "s",
            "stage_on"           => "s",
            "stage_off"          => "s",
            "biological_process" => "s",
            "sex"                => "s",
            "silencer"           => "s"
        );
    }
    // --------------------------------------------------------------------------------
    // Get the raw triple store data of an anatomical expression term belonging to a
    // predicted CRM.
    // @param int $pcrmId The predicted CRM id
    // @param string $anatomicalExpressionIdentifier The anatomical expression FlyBase
    //   identifier
    // @returns array An array of triple store data having a common anatomical
    //   expression FlyBase identifier
    // --------------------------------------------------------------------------------
    public function getData(
        int $pcrmId,
        string $anatomicalExpressionIdentifier
    ) {
        try {
            $sql = <<<SQL
            SELECT pcrmts.ts_id,
                pcrmts.predicted_crm_id,
                pcrmts.expression AS anatomical_expression_identifier,
                et.term AS anatomical_expression_term,
                pcrmts.pubmed_id,
                pcrmts.stage_on AS stage_on_identifier,
                ds_on.term AS stage_on_term,
                pcrmts.stage_off AS stage_off_identifier,
                ds_off.term AS stage_off_term,
                IF(ISNULL(bp.go_id), '', pcrmts.biological_process) AS biological_process_identifier,
                IF(ISNULL(bp.go_id), '', bp.term) AS biological_process_term,
                pcrmts.sex AS sex_id,
                pcrmts.silencer AS enhancer_or_silencer_attribute_id
            FROM $this->tableName pcrmts
            LEFT JOIN PredictedCRM pcrm USING (predicted_crm_id)
            LEFT JOIN ExpressionTerm et ON pcrmts.expression = et.identifier AND
                pcrm.sequence_from_species_id = et.species_id
            LEFT JOIN DevelopmentalStage ds_on ON pcrmts.stage_on = ds_on.identifier AND
                pcrm.sequence_from_species_id = ds_on.species_id
            LEFT JOIN DevelopmentalStage ds_off ON pcrmts.stage_off = ds_off.identifier AND
                pcrm.sequence_from_species_id = ds_off.species_id
            LEFT OUTER JOIN BiologicalProcess bp ON pcrmts.biological_process = bp.go_id
            WHERE pcrmts.crm_segment_id = $pcrmId AND
                pcrmts.expression = '$anatomicalExpressionIdentifier'
            ORDER BY stage_on_term, 
                stage_off_term;
SQL;
            $ts = $this->db->query($sql);
        } catch ( Exception $e ) {
            throw new Exception("Error fetching the triple store: " . $e->getMessage() .
                " from the getData() function");
        }

        return mysqli_fetch_array(
            $ts,
            MYSQLI_ASSOC
        );
    }
    // --------------------------------------------------------------------------------
    // Get all the raw triple store data.
    // @param int $pcrmId The predicted CRM id
    // @returns array An array of triple store data having all the anatomical
    //    expression identifiers belonging to a predicted CRM
    // --------------------------------------------------------------------------------
    public function getAllData(int $pcrmId)
    {
        try {
            $sql = <<<SQL
            SELECT pcrmts.ts_id,
                pcrmts.predicted_crm_id,
                pcrmts.expression AS anatomical_expression_identifier,
                et.term AS anatomical_expression_term,
                pcrmts.pubmed_id,
                ds_on.stage_id AS stage_on_id,
                pcrmts.stage_on AS stage_on_identifier,
                ds_on.term AS stage_on_term,
                ds_off.stage_id AS stage_off_id,
                pcrmts.stage_off AS stage_off_identifier,
                ds_off.term AS stage_off_term,
                IF(ISNULL(bp.go_id), 0, bp.process_id) AS biological_process_id,
                IF(ISNULL(bp.go_id), '', pcrmts.biological_process) AS biological_process_go_id,
                IF(ISNULL(bp.go_id), '', bp.term) AS biological_process_term,
                pcrmts.sex AS sex_id,
                pcrmts.silencer AS enhancer_or_silencer_attribute_id
            FROM $this->tableName pcrmts
            LEFT JOIN PredictedCRM pcrm USING (predicted_crm_id)
            LEFT JOIN ExpressionTerm et ON pcrmts.expression = et.identifier AND
                pcrm.sequence_from_species_id = et.species_id
            LEFT JOIN DevelopmentalStage ds_on ON pcrmts.stage_on = ds_on.identifier AND
                pcrm.sequence_from_species_id = ds_on.species_id
            LEFT JOIN DevelopmentalStage ds_off ON pcrmts.stage_off = ds_off.identifier AND
                pcrm.sequence_from_species_id = ds_off.species_id
            LEFT OUTER JOIN BiologicalProcess bp ON (pcrmts.biological_process = bp.go_id)
            WHERE pcrmts.predicted_crm_id = $pcrmId
            ORDER BY anatomical_expression_term, 
                stage_on_term, 
                stage_off_term;
SQL;
            $ts = $this->db->query($sql);
            $results = array();
            while ( $row = $ts->fetch_assoc() ) {
                $results[] = $row;
            }
        } catch ( Exception $e ) {
            throw new Exception("Error fetching the triple store: " . $e->getMessage() .
                " from the getAllData() function");
        }

        return $results;
    }
    // --------------------------------------------------------------------------------
    // Get the number of triple store data rows.
    // @param int $pcrmId The predicted CRM id
    // @param string $anatomicalExpressionIdentifier The anatomical expression
    //   identifier
    // @returns int The number of rows having both common predicted CRM id and
    //    anatomical expression identifier
    // --------------------------------------------------------------------------------
    public function getRowsNumber(
        int $pcrmId,
        string $anatomicalExpressionIdentifier
    ) {
        try {
            $sql = <<<SQL
            SELECT expression
            FROM $this->tableName crmsts
            WHERE pcrmts.predicted_crm_id = $pcrmId AND
                pcrmts.expression = '$anatomicalExpressionIdentifier';
SQL;
            $ts = $this->db->query($sql);
        } catch ( Exception $e ) {
            throw new Exception("Error fetching the triple store: " .
                $e->getMessage());
        }

        return mysqli_num_rows($ts);
    }
    // --------------------------------------------------------------------------------
    // Create a new triple store.
    // @param array $data An array of data used to create the triple store
    // @returns int The triple store id for the new triple store
    // --------------------------------------------------------------------------------
    public function create(array $data)
    {
        $pcrmId = $data["predicted_crm_id"];
        $anatomicalExpressionIdentifier = $data["anatomical_expression_identifier"];
        try {
            $sql = <<<SQL
            SELECT term_id
            FROM ExpressionTerm
            WHERE identifier = '$anatomicalExpressionIdentifier';
SQL;
            $queryResults = $this->db->query($sql);
            $termId = intval(mysqli_fetch_array(
                $queryResults,
                MYSQLI_ASSOC
            )["term_id"]);
        } catch ( Exception $e ) {
            throw new Exception("Error fetching the anatomical expression identifier: " .
                $e->getMessage());
        }
        try {
            $sql = <<<SQL
            SELECT COUNT(*) AS number
            FROM PredictedCRM_has_Expression_Term
            WHERE predicted_crm_id = $pcrmId AND
                term_id = $termId;
SQL;
            $queryResults = $this->db->query($sql);
            $associatedAnatomicalExpressionTermsNumber = intval(mysqli_fetch_array(
                $queryResults,
                MYSQLI_ASSOC
            )["number"]);
        } catch ( Exception $e ) {
            throw new Exception("Error fetching the predicted CRM id and " .
                "the anatomical expression identifiers: " .
                $e->getMessage());
        }
        if ( $associatedAnatomicalExpressionTermsNumber === 0 ) {
            try {
                $this->db->startTransaction();
                $sql = <<<SQL
                INSERT INTO PredictedCRM_has_Expression_Term (predicted_crm_id, term_id)
                VALUES (?, ?);
SQL;
                $parameterTypes = "ii";
                $parametersList = array();
                $parametersList[] = $pcrmId;
                $parametersList[] = $termId;
                if ( ($statement = $this->db->getHandle()->prepare($sql)) === false ) {
                    throw new Exception("Error preparing the statement: " .
                        $this->db->getError());
                }
                $statement->bind_param($parameterTypes, ...$parametersList);
                if ( $statement->execute() === false ) {
                    throw new Exception("Failed to insert PredictedCRM_has_Expression_Term row: " .
                        $statement->error);
                }
                $this->db->commit();
            } catch ( Exception $e ) {
                $this->db->rollback();
                throw new Exception("Error creating PredictedCRM_has_Expression_Term row: " .
                    $e->getMessage());
            }
        }
        try {
            $this->db->startTransaction();
            $sql = <<<SQL
            INSERT INTO triplestore_predicted_crm (predicted_crm_id, expression, pubmed_id, stage_on, stage_off, biological_process, sex, silencer)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?);
SQL;
            $parameterTypes = "isisssss";
            $parametersList = array();
            $parametersList[] = $pcrmId;
            $parametersList[] = $anatomicalExpressionIdentifier;
            $parametersList[] = $data["pubmed_id"];
            $parametersList[] = $data["stage_on_identifier"];
            $parametersList[] = $data["stage_off_identifier"];
            $parametersList[] = $data["biological_process_identifier"];
            $parametersList[] = $data["sex_id"];
            $parametersList[] = $data["enhancer_or_silencer_attribute_id"];
            if ( ($statement = $this->db->getHandle()->prepare($sql)) === false ) {
                throw new Exception("Error preparing the statement: " .
                    $this->db->getError());
            }
            $statement->bind_param($parameterTypes, ...$parametersList);
            if ( $statement->execute() === false ) {
                throw new Exception("Failed to insert the triple store: " .
                    $statement->error);
            }
            $tsId = $this->db->lastInsertId();
            $this->db->commit();
        } catch ( Exception $e ) {
            $this->db->rollback();
            throw new Exception("Error creating the triple store: " .
                $e->getMessage());
        }

        return $tsId;
    }
    // --------------------------------------------------------------------------------
    // Update an triple store
    // @param array $data An array of data used to update the triple store
    // --------------------------------------------------------------------------------
    public function update(array $data)
    {
        try {
            $this->db->startTransaction();
            $sql = <<<SQL
            UPDATE $this->tableName
            SET pubmed_id = ?,
                stage_on = ?,
                stage_off = ?,
                biological_process = ?,
                sex = ?,
                silencer = ?
            WHERE ts_id = ?;
SQL;
            if ( ($statement = $this->db->getHandle()->prepare($sql)) === false ) {
                throw new Exception("Error preparing the statement: " .
                    $this->db->getError());
            }
            $parameterTypes = "isssssi";
            $parametersList = array();
            $parametersList[] = $data["pubmed_id"];
            $parametersList[] = $data["stage_on_identifier"];
            $parametersList[] = $data["stage_off_identifier"];
            $parametersList[] = $data["biological_process_identifier"];
            $parametersList[] = $data["sex_id"];
            $parametersList[] = $data["enhancer_or_silencer_attribute_id"];
            $parametersList[] = $data["ts_id"];
            $statement->bind_param($parameterTypes, ...$parametersList);
            if ( $statement->execute() === false ) {
                throw new Exception("Error updating the table triplestore_predicted_crm: " .
                    $statement->error);
            }
            $this->db->commit();
        } catch ( Exception $e ) {
            $this->db->rollback();
            throw new Exception("Error updating the triple store: " .
                $e->getMessage());
        }
    }
    // --------------------------------------------------------------------------------
    // Delete all the triple stores associated to the predicted CRM id and anatomical
    // expression identifier given.
    // @param int $pcrmId The predicted CRM identifier
    // @param string $anatomicalExpressionIdentifier The anatomical expression
    //   identifier
    // --------------------------------------------------------------------------------
    public function delete(
        int $pcrmId,
        string $anatomicalExpressionIdentifier
    ) {
        try {
            $this->db->startTransaction();
            $sql = <<<SQL
            DELETE
            FROM $this->tableName
            WHERE predicted_crm_id = ? AND
                expression = ?;
SQL;
            if ( ($statement = $this->db->getHandle()->prepare($sql)) === false ) {
                throw new Exception("Error preparing the statement: " .
                    $this->db->getError());
            }
            $parameterTypes = "is";
            $parametersList = array();
            $parametersList[] = $pcrmId;
            $parametersList[] = $anatomicalExpressionIdentifier;
            $statement->bind_param($parameterTypes, ...$parametersList);
            if ( $statement->execute() === false ) {
                throw new Exception("Error deleting from the table triplestore_predicted_crm: " .
                    $statement->error);
            }
            $deletedTripleStoreRowsNumber = mysqli_affected_rows($this->db->getHandle());
            $this->db->commit();
        } catch ( Exception $e ) {
            $this->db->rollback();
            throw new Exception("Error deleting the triple store: " .
                $e->getMessage());
        }

        return $deletedTripleStoreRowsNumber;
    }
    // --------------------------------------------------------------------------------
    // Delete all the triple stores associated to all the anatomical expression
    // identifiers associated to a predicted CRM id given.
    // @param int $pcrmId The predicted CRM id
    // --------------------------------------------------------------------------------
    public function deleteAll(int $pcrmId)
    {
        try {
            $this->db->startTransaction();
            $sql = <<<SQL
            DELETE
            FROM $this->tableName
            WHERE predicted_crm_id = ?;
SQL;
            if ( ($statement = $this->db->getHandle()->prepare($sql)) === false ) {
                throw new Exception("Error preparing the statement: " .
                    $this->db->getError());
            }
            $parameterTypes = "i";
            $parametersList = array();
            $parametersList[] = $pcrmId;
            $statement->bind_param($parameterTypes, ...$parametersList);
            if ( $statement->execute() === false ) {
                throw new Exception("Error deleting from the table triplestore_predicted_crm: " .
                    $statement->error);
            }
            $deletedTripleStoreRowsNumber = mysqli_affected_rows($this->db->getHandle());
            $this->db->commit();
        } catch ( Exception $e ) {
            $this->db->rollback();
            throw new Exception("Error deleting the triple store(s): " .
                $e->getMessage());
        }

        return $deletedTripleStoreRowsNumber;
    }
}
