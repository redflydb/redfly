<?php
// ================================================================================
// Reporter Construct (RC) Anatomical Expression Term (ET) helper class.
// The public methods should not allow the application to enter an inconsistent
// state.
// These methods should be used to manage the RC ET lifecycle, they roughly
// correspond to the actions from the curator interface:
// - delete()
//   Used to delete an anatomical expression term identifier given associated to
//   a reporter construct id given.
// - deleteAll()
//   Used to delete all the anatomical expression term identifiers associated to
//   a reporter construct id given.
// ================================================================================
class RcEtHelper
{
    // DbService instance.
    private $db = null;
    // RC ET table name.
    private $tableName;
    // RC_has_ExprTerm table columns and mysqli type.
    private $columnTypeList;
    // --------------------------------------------------------------------------------
    // Factory method design pattern.
    // --------------------------------------------------------------------------------
    public static function factory()
    {
        return new RcEtHelper();
    }
    // --------------------------------------------------------------------------------
    // Constructor.
    // --------------------------------------------------------------------------------
    public function __construct()
    {
        $this->db = DbService::factory();
        $this->tableName = "RC_has_ExprTerm";
        $this->columnTypeList =  array(
            "rc_id"   => "i",
            "term_id" => "i");
    }
    // --------------------------------------------------------------------------------
    // Delete an anatomical expression term id given associated to a reporter
    // construct id given.
    // @param int $rcId The reporter construct id
    // @param int $etId The anatomical expression term id
    // --------------------------------------------------------------------------------
    public function delete(
        $rcId,
        $etId
    ) {
        try {
            $this->db->startTransaction();
            $sql = "DELETE FROM ". $this->tableName . " 
                    WHERE rc_id = ? AND 
                        term_id = ?;";
            if ( ($statement = $this->db->getHandle()->prepare($sql)) === false ) {
                throw new Exception("Error preparing the statement: " .
                    $this->db->getError());
            }
            $parameterTypes = "ii";
            $parametersList = array();
            $parametersList[] = $rcId;
            $parametersList[] = $etId;
            $statement->bind_param($parameterTypes, ...$parametersList);
            if ( $statement->execute() === false ) {
                  throw new Exception("Error deleting from RC_has_ExprTerm: " .
                    $statement->error);
            }
            $deletedAssociatedAnatomicalExpressionRowsNumber = mysqli_affected_rows($this->db->getHandle());
            $this->db->commit();
        } catch ( Exception $e ) {
            $this->db->rollback();
            throw new Exception("Error deleting an anatomical expression term " .
                "associated to a reporter construct: " . $e->getMessage());
        }

        return $deletedAssociatedAnatomicalExpressionRowsNumber;
    }
    // --------------------------------------------------------------------------------
    // Delete all the anatomical expression term ids associated to a reporter construct
    // id given.
    // @param int $rcId The reporter construct id
    // --------------------------------------------------------------------------------
    public function deleteAll($rcId)
    {
        try {
            $this->db->startTransaction();
            $sql = "DELETE
                    FROM ". $this->tableName . " 
                    WHERE rc_id = ?;";
            if ( ($statement = $this->db->getHandle()->prepare($sql)) === false ) {
                throw new Exception("Error preparing the statement: " .
                    $this->db->getError());
            }
            $parameterTypes = "i";
            $parametersList = array();
            $parametersList[] = $rcId;
            $statement->bind_param($parameterTypes, ...$parametersList);
            if ( $statement->execute() === false ) {
                  throw new Exception("Error deleting from RC_has_ExprTerm: " .
                    $statement->error);
            }
            $deletedAssociatedAnatomicalExpressionRowsNumber = mysqli_affected_rows($this->db->getHandle());
            $this->db->commit();
        } catch ( Exception $e ) {
            $this->db->rollback();
            throw new Exception("Error deleting all the anatomical expression terms " .
                "associated to a reporter construct: " . $e->getMessage());
        }

        return $deletedAssociatedAnatomicalExpressionRowsNumber;
    }
}
