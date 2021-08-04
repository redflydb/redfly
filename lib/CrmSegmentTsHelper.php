<?php
// ================================================================================
// Triple Store helper class.
// The public methods should not allow the application to enter an inconsistent
// state.
// These methods should be used to manage the triple store lifecycle, they
// roughly correspond to the actions from the curator interface:
// - create()
//   Used to create a new triple store of an existing CRM segment.
// - update()
//   Used to modify an existing triple store of an existing CRM segment.
// - delete()
//   Used to delete all the triple stores associated to the CRM segment id
//   and the anatomical expression identifier given.
// - deleteAll()
//   Used to delete all the triple stores associated to all the anatomical
//   expression identifiers associated to an CRM segment id given.
// ================================================================================
class CrmSegmentTsHelper
{
    // DbService instance.
    private $db = null;
    // Triple store table name.
    private $tableName;
    // Triple store table primary key column.
    private $pkColumn;
    // triplestore_crm_segment table columns and mysqli type. Excludes the
    // primary key "ts_id" since it should never be set directly.
    private $columnTypeList;
    // --------------------------------------------------------------------------------
    // Factory method design pattern.
    // --------------------------------------------------------------------------------
    public static function factory()
    {
        return new CrmSegmentTsHelper();
    }
    // --------------------------------------------------------------------------------
    // Constructor.
    // --------------------------------------------------------------------------------
    public function __construct()
    {
        $this->db = DbService::factory();
        $this->tableName = "triplestore_crm_segment";
        $this->pkColumn = "ts_id";
        $this->columnTypeList =  array(
            "crm_segment_id"     => "i",
            "expression"         => "s",
            "pubmed_id"          => "s",
            "stage_on"           => "s",
            "stage_off"          => "s",
            "biological_process" => "s",
            "sex"                => "s",
            "ectopic"            => "i",
            "silencer"           => "s"
        );
    }
    // --------------------------------------------------------------------------------
    // Get the raw triple store data of an anatomical expression term belonging to an
    // CRM segment.
    // @param int $crmsId The CRM segment id
    // @param string $anatomicalExpressionIdentifier The anatomical expression
    //   identifier
    // @returns array An array of triple store data having a common anatomical
    //   expression identifier
    // --------------------------------------------------------------------------------
    public function getData(
        int $crmsId,
        string $anatomicalExpressionIdentifier
    ) {
        try {
            $sql = <<<SQL
            SELECT crmsts.ts_id,
                crmsts.crm_segment_id,
                crmsts.expression AS anatomical_expression_identifier,
                et.term AS anatomical_expression_term,
                crmsts.pubmed_id,
                crmsts.stage_on AS stage_on_identifier,
                ds_on.term AS stage_on_term,
                crmsts.stage_off AS stage_off_identifier,
                ds_off.term AS stage_off_term,
                IF(ISNULL(bp.go_id), '', crmsts.biological_process) AS biological_process_identifier,
                IF(ISNULL(bp.go_id), '', bp.term) AS biological_process_term,
                crmsts.sex AS sex_id,
                crmsts.ectopic AS ectopic_id,
                crmsts.silencer AS enhancer_or_silencer_attribute_id
            FROM $this->tableName crmsts
            JOIN CRMSegment crms ON crmsts.crm_segment_id = crms.crm_segment_id
            JOIN ExpressionTerm et ON crmsts.expression = et.identifier
            JOIN DevelopmentalStage ds_on ON crmsts.stage_on = ds_on.identifier
            JOIN DevelopmentalStage ds_off ON crmsts.stage_off = ds_off.identifier
            LEFT OUTER JOIN BiologicalProcess bp ON crmsts.biological_process = bp.go_id
            WHERE crmsts.crm_segment_id = $crmsId AND
                crmsts.expression = '$anatomicalExpressionIdentifier'
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
    // @param int $crmsId The CRM segment id
    // @returns array An array of triple store data having all the expression
    //   identifiers belonging to an CRM segment
    // --------------------------------------------------------------------------------
    public function getAllData(int $crmsId)
    {
        try {
            $sql = <<<SQL
            SELECT crmsts.ts_id,
                crmsts.crm_segment_id,
                crmsts.expression AS anatomical_expression_identifier,
                et.term AS anatomical_expression_term,
                crmsts.pubmed_id,
                ds_on.stage_id AS stage_on_id,
                crmsts.stage_on AS stage_on_identifier,
                ds_on.term AS stage_on_term,
                ds_off.stage_id AS stage_off_id,
                crmsts.stage_off AS stage_off_identifier,
                ds_off.term AS stage_off_term,
                IF(ISNULL(bp.go_id), 0, bp.process_id) AS biological_process_id,
                IF(ISNULL(bp.go_id), '', crmsts.biological_process) AS biological_process_identifier,
                IF(ISNULL(bp.go_id), '', bp.term) AS biological_process_term,
                crmsts.sex AS sex_id,
                crmsts.ectopic AS ectopic_id,
                crmsts.silencer AS enhancer_or_silencer_attribute_id
            FROM $this->tableName crmsts
            JOIN CRMSegment crms ON crmsts.crm_segment_id = crms.crm_segment_id
            JOIN ExpressionTerm et ON crmsts.expression = et.identifier
            JOIN DevelopmentalStage ds_on ON crmsts.stage_on = ds_on.identifier
            JOIN DevelopmentalStage ds_off ON crmsts.stage_off = ds_off.identifier
            LEFT OUTER JOIN BiologicalProcess bp ON crmsts.biological_process = bp.go_id
            WHERE crmsts.crm_segment_id = $crmsId
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
    // @param int $crmsId The CRM segment id
    // @param string $anatomicalExpressionIdentifier The anatomical expression
    //   identifier
    // @returns int The number of rows having both common CRM segment id and
    //   anatomical expression identifier
    // --------------------------------------------------------------------------------
    public function getRowsNumber(
        int $crmsId,
        string $anatomicalExpressionIdentifier
    ) {
        try {
            $sql = <<<SQL
            SELECT expression
            FROM $this->tableName crmsts
            WHERE crmsts.crm_segment_id = $crmsId AND
                crmsts.expression = '$anatomicalExpressionIdentifier';
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
        $crmsId = $data["crm_segment_id"];
        $anatomicalExpressionIdentifier = $data["anatomical_expression_identifier"];
        try {
            $sql = <<<SQL
            SELECT term_id
            FROM ExpressionTerm
            WHERE identifier = '$anatomicalExpressionIdentifier';
SQL;
            $queryResults = $this->db->query($sql);
            $anatomicalExpressionTermId = intval(mysqli_fetch_array(
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
            FROM CRMSegment_has_Expression_Term
            WHERE crm_segment_id = $crmsId AND
                term_id = $anatomicalExpressionTermId;
SQL;
            $queryResults = $this->db->query($sql);
            $associatedAnatomicalExpressionTermsNumber = intval(mysqli_fetch_array(
                $queryResults,
                MYSQLI_ASSOC
            )["number"]);
        } catch ( Exception $e ) {
            throw new Exception("Error fetching the CRM segment id and the anatomical " .
                "expression identifier: " . $e->getMessage());
        }
        if ( $associatedAnatomicalExpressionTermsNumber === 0 ) {
            try {
                $this->db->startTransaction();
                $sql = <<<SQL
                INSERT INTO CRMSegment_has_Expression_Term (crm_segment_id, term_id) 
                VALUES (?, ?);
SQL;
                $parameterTypes = "ii";
                $parametersList = array();
                $parametersList[] = $crmsId;
                $parametersList[] = $anatomicalExpressionTermId;
                if ( ($statement = $this->db->getHandle()->prepare($sql)) === false ) {
                    throw new Exception("Error preparing the statement: " .
                        $this->db->getError());
                }
                $statement->bind_param($parameterTypes, ...$parametersList);
                if ( $statement->execute() === false ) {
                    throw new Exception("Failed to insert CRMSegment_has_Expression_Term row: " .
                        $statement->error);
                }
                $this->db->commit();
            } catch ( Exception $e ) {
                $this->db->rollback();
                throw new Exception("Error creating CRMSegment_has_Expression_Term row: " .
                    $e->getMessage());
            }
        }
        try {
            $this->db->startTransaction();
            $sql = <<<SQL
            INSERT INTO triplestore_crm_segment (crm_segment_id, expression, pubmed_id, stage_on, stage_off, biological_process, sex, ectopic, silencer)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?);
SQL;
            $parameterTypes = "isissssis";
            $parametersList = array();
            $parametersList[] = $crmsId;
            $parametersList[] = $anatomicalExpressionIdentifier;
            $parametersList[] = $data["pubmed_id"];
            $parametersList[] = $data["stage_on_identifier"];
            $parametersList[] = $data["stage_off_identifier"];
            $parametersList[] = $data["biological_process_identifier"];
            $parametersList[] = $data["sex_id"];
            $parametersList[] = $data["ectopic_id"];
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
                ectopic = ?,
                silencer = ?
            WHERE ts_id = ?;
SQL;
            if ( ($statement = $this->db->getHandle()->prepare($sql)) === false ) {
                throw new Exception("Error preparing the statement: " .
                    $this->db->getError());
            }
            $parameterTypes = "issssisi";
            $parametersList = array();
            $parametersList[] = $data["pubmed_id"];
            $parametersList[] = $data["stage_on_identifier"];
            $parametersList[] = $data["stage_off_identifier"];
            $parametersList[] = $data["biological_process_identifier"];
            $parametersList[] = $data["sex_id"];
            $parametersList[] = $data["ectopic_id"];
            $parametersList[] = $data["enhancer_or_silencer_attribute_id"];
            $parametersList[] = $data["ts_id"];
            $statement->bind_param($parameterTypes, ...$parametersList);
            if ( $statement->execute() === false ) {
                throw new Exception("Error updating the table triplestore_crm_segment: " .
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
    // Delete all the triple stores associated to both CRM segment id and anatomical
    // expression identifier given.
    // @param int $crmsId The CRM segment id
    // @param string $anatomicalExpressionIdentifier The anatomical expression identifier
    // --------------------------------------------------------------------------------
    public function delete(
        int $crmsId,
        string $anatomicalExpressionIdentifier
    ) {
        try {
            $this->db->startTransaction();
            $sql = <<<SQL
            DELETE
            FROM $this->tableName
            WHERE crm_segment_id = ? AND
                expression = ?;
SQL;
            if ( ($statement = $this->db->getHandle()->prepare($sql)) === false ) {
                throw new Exception("Error preparing the statement: " .
                    $this->db->getError());
            }
            $parameterTypes = "is";
            $parametersList = array();
            $parametersList[] = $crmsId;
            $parametersList[] = $anatomicalExpressionIdentifier;
            $statement->bind_param($parameterTypes, ...$parametersList);
            if ( $statement->execute() === false ) {
                throw new Exception("Error deleting from the table triplestore_crm_segment: " .
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
    // identifiers associated to an CRM segment id given.
    // @param int $crmsId The CRM segment identifier
    // --------------------------------------------------------------------------------
    public function deleteAll(int $crmsId)
    {
        try {
            $this->db->startTransaction();
            $sql = <<<SQL
            DELETE
            FROM $this->tableName
            WHERE crm_segment_id = ?;
SQL;
            if ( ($statement = $this->db->getHandle()->prepare($sql)) === false ) {
                throw new Exception("Error preparing the statement: " .
                    $this->db->getError());
            }
            $parameterTypes = "i";
            $parametersList = array();
            $parametersList[] = $crmsId;
            $statement->bind_param($parameterTypes, ...$parametersList);
            if ( $statement->execute() === false ) {
                throw new Exception("Error deleting from the table triplestore_crm_segment: " .
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
