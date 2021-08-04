<?php
// ================================================================================
// Abstract entity helper class.
// Contains functions that are useful for entities. Also defines an interface
// for creating and modifying entites.
// These functions should be used to manage the entity lifecycle, they
// roughly correspond to the actions from the curator interface:
// Abstract functions:
// Note: any class using this abstract entity helper class must implement the
// following abstract functions despite all.
// - getData()
//   Used to get the data from an entity
// - create()
//   Used to create a new entity in the "editing" or "approval" state.
// - update()
//   Used to modify an entity in the "editing" or "approval" state.
// - approve()
//   Used to approve an entity in the "approval" and change its state
//   to "approved".
// - createEdit()
//   Used to create a new "editing" or "approval" state entity from an
//   entity in the "current" state.
// - createNewVersion()
//   Used to create a new "current" state entity from an entity in the
//   "current" state.
// Functions:
// - deleteVersion()
//   Delete a specific version (in the "editing" or "approval" state) of
//   an entity.
// ================================================================================
abstract class aEntityHelper
{
    // --------------------------------------------------------------------------------
    // Entity states
    // --------------------------------------------------------------------------------
    // The entity is awaiting approval
    const STATE_approval = "approval";
    // The entity is approved and will become the current version at the release
    const STATE_approved = "approved";
    // The entity has been archived
    const STATE_archived = "archived";
    // The entity is the current version
    const STATE_current = "current";
    // The entity has been deleted
    const STATE_deleted = "deleted";
    // The entity is an edited version (this can be a new version, a modification of
    // a previous version, or a modification of an existing version)
    const STATE_editing = "editing";
    // The entity has been rejected
    const STATE_rejected = "rejected";
    // --------------------------------------------------------------------------------
    // Actions we can take on an entity
    // --------------------------------------------------------------------------------
    // Approval by the administrator
    const ACTION_approve = "approve";
    // Delete the entity
    const ACTION_delete = "delete";
    // Rejection by the administrator
    const ACTION_reject = "reject";
    // Save the entity (either a new entity or a modification to an existing one)
    const ACTION_save = "save";
    // Save the entity and move on to create a new blank entity. This is
    // equivalent to saving for the REST API, the UI handles moving on to create a
    // new one.
    const ACTION_saveNew = "save_new";
    // Save the entity and move on to create a new entity based on this one. This
    // is equivalent to saving for the REST API, the UI handles moving on to
    // create a new one.
    const ACTION_saveNewBasedOn = "save_new_based_on";
    // Submit the entity for administrator approval
    const ACTION_submitForApproval = "submit_for_approval";
    // DbService instance.
    protected $db = null;
    // Error margin used in overlap calculations when searching minimized RCs.
    protected $rcErrorMargin = 0;
    // --------------------------------------------------------------------------------
    // These properties must be set in subclasses.
    // --------------------------------------------------------------------------------
    // Entity table name.
    protected $tableName = null;
    // Entity table primary key column.
    protected $pkColumn = null;
    // Entity abbreviation.  This must be the abbreviation used in tables
    // that join the entity table (e.g. RC is the reporter construct
    // abbreviation and BS is the binding site abbreviation used in
    // RC_has_FigureLabel and BS_has_FigureLabel).
    protected $abbrev = null;
    // Contains the mysqli type string for each column in the entity table
    // (e.g. array("species_id" => "i", "name" => "s", ... )). Used to
    // construct SQL queries. It should not contain the primary key.
    protected $columnTypeList = array();
    // --------------------------------------------------------------------------------
    // Constructor.
    // --------------------------------------------------------------------------------
    protected function __construct()
    {
        $this->db = DbService::factory();
        $this->rcErrorMargin = $GLOBALS["options"]->rc->error_margin;
    }
    // --------------------------------------------------------------------------------
    // Get the raw data for an entity.
    // @param int $primaryKey The entity primary key.
    // @returns array An array of entity data
    // --------------------------------------------------------------------------------
    abstract public function getData($primaryKey);
    // --------------------------------------------------------------------------------
    // Create a new entity.
    // @param array $data An array of data used to create the entity
    // @returns int The primary key for the new entity version
    // --------------------------------------------------------------------------------
    abstract public function create(array $data);
    // --------------------------------------------------------------------------------
    // Update an entity without creating a new version
    // @param int $primaryKey The entity primary key
    // @param array $data An array of data used to update the entity
    // @returns array The entity data
    // --------------------------------------------------------------------------------
    abstract public function update(
        $primaryKey,
        array $data
    );
    // --------------------------------------------------------------------------------
    // Approve an entity and make it "current".
    // @see update()
    // @param int $primaryKey The entity primary key
    // @param array $data (optional) An array of data used to update the entity
    // @returns array The entity data
    // --------------------------------------------------------------------------------
    abstract public function approve(
        $primaryKey,
        array $data = array()
    );
    // --------------------------------------------------------------------------------
    // Create an "editing" or "approval" version of a "current" entity.
    // @param int $primaryKey The entity primary key
    // @param array $data (optional) An array of data used to update the entity
    // @returns array The entity data
    // --------------------------------------------------------------------------------
    abstract public function createEdit(
        $primaryKey,
        array $data = array()
    );
    // --------------------------------------------------------------------------------
    // Create a new "current" version of a "current" entity.
    // The changes to the "current" entity will also be applied to any
    // non-current versions (i.e. "editing" and "approval" versions of
    // the entity).
    // @param int $primaryKey The entity primary key
    // @param array $data (optional) An array of data used to update the entity
    // @returns int The primary key for the new entity version
    // --------------------------------------------------------------------------------
    abstract public function createNewVersion(
        $primaryKey,
        array $data = array()
    );
    // --------------------------------------------------------------------------------
    // Find the primary key for the current version of an entity.
    // @param int $entityId The entity entity_id.
    // @returns int The entity primary key, or NULL if no current version is found.
    // --------------------------------------------------------------------------------
    public function getPk($entityId)
    {
        $sql = "SELECT {$this->pkColumn}
                FROM {$this->tableName}
                WHERE entity_id = $entityId AND
                    state = 'current';";
        $result = $this->db->query($sql);
        if ( null === ($row = $result->fetch_assoc()) ) {
            return null;
        }

        return $row[$this->pkColumn];
    }
    // --------------------------------------------------------------------------------
    // Given the primary key of a "current" entity, return the primary key
    // of the previous version or NULL.
    // @param int $primaryKey The entity primary key
    // @returns int The primary key of the previous version or NULL if there is no
    //   previous version of the entity
    // --------------------------------------------------------------------------------
    protected function getPreviousPk($primaryKey)
    {
        $sql = "SELECT archived_entity.{$this->pkColumn}
                FROM {$this->tableName} archived_entity
                JOIN {$this->tableName} current_entity ON archived_entity.entity_id = current_entity.entity_id
                WHERE current_entity.{$this->pkColumn} = $primaryKey AND
                    archived_entity.state = 'archived'
                ORDER BY archived_entity.version DESC
                LIMIT 1;";
        $result = $this->db->query($sql);
        if ( null === ($row = $result->fetch_assoc()) ) {
            return null;
        }

        return $row[$this->pkColumn];
    }
    // --------------------------------------------------------------------------------
    // Returns the next available entity_id.
    // @returns int
    // --------------------------------------------------------------------------------
    protected function getNextEntityId()
    {
        $sql = "SELECT MAX(entity_id) + 1 AS next_entity_id
                FROM {$this->tableName};";
        $result = $this->db->query($sql);
        if ( null === ($row = $result->fetch_assoc()) ) {
            throw new Exception("Error fetching the max entity_id");
        }

        return $row["next_entity_id"];
    }
    // --------------------------------------------------------------------------------
    // Returns the next version number to use for an entity.
    // @param int $entityId The entity_id.
    // @returns int
    // --------------------------------------------------------------------------------
    protected function getNextVersionNumber($entityId)
    {
        $sql = "SELECT MAX(version) + 1 AS next_version
                FROM {$this->tableName}
                WHERE entity_id = $entityId;";
        $result = $this->db->query($sql);
        if ( null === ($row = $result->fetch_assoc()) ) {
            throw new Exception("Unable to determine the next version number");
        }

        return $row["next_version"];
    }
    // --------------------------------------------------------------------------------
    // Returns the maximum version number of an entity.
    // @param int $entityId The entity_id.
    // @returns int
    // --------------------------------------------------------------------------------
    protected function getMaxVersionNumber($entityId)
    {
        $sql = "SELECT MAX(version) AS max_version
                FROM {$this->tableName}
                WHERE entity_id = $entityId;";
        $result = $this->db->query($sql);
        if ( null === ($row = $result->fetch_assoc()) ) {
            throw new Exception("Unable to determine the maximum version number");
        }

        return $row["max_version"];
    }
    // --------------------------------------------------------------------------------
    // Delete a single version of an entity
    // This should only be used with entity versions which state from the ones:
    // "approval", "approved", and "editing".
    // @param int $primaryKey The entity primary key
    // --------------------------------------------------------------------------------
    public function deleteVersion($primaryKey)
    {
        try {
            $this->db->startTransaction();
            $sql = "DELETE FROM {$this->tableName}
                    WHERE {$this->pkColumn} = $primaryKey AND
                        state IN ('editing', 'approval', 'approved');";
            $this->db->query($sql);
            $this->db->commit();
        } catch ( Exception $e ) {
            $this->db->rollback();
            throw new Exception("Failed to delete {$this->abbrev}: " . $e->getMessage());
        }
    }
    // --------------------------------------------------------------------------------
    // Fix out of order version numbers
    // @param int $entityId The entity primary key
    // --------------------------------------------------------------------------------
    protected function fixVersionNumbers($entityId)
    {
        $sql = "SELECT {$this->pkColumn}, state, version
                FROM {$this->tableName}
                WHERE entity_id = $entityId AND
                    state IN ('editing', 'approval', 'current')
                ORDER BY version ASC;";
        $result = $this->db->query($sql);
        $entityList = array();
        while ( $row = $result->fetch_assoc() ) {
            if ( "current" === $row["state"] ) {
                $entityList["current"] = $row;
            } else {
                $entityList["not_current"] = $row;
            }
        }
        if ( 1 === count($entityList) ) {
            return;
        }
        // Swap version numbers if the current is greater than the
        // non-current
        if ( $entityList["current"]["version"] >
        $entityList["not_current"]["version"] ) {
            list($entityList["current"]["version"],
            $entityList["not_current"]["version"] ) =
              array($entityList["not_current"]["version"],
              $entityList["current"]["version"]);
        } else {
            return;
        }
        foreach ( $entityList as $entity ) {
            $sql = "UPDATE {$this->tableName}
                    SET version = " . $entity["version"] . "
                    WHERE {$this->pkColumn} = {$entity[$this->pkColumn]};";
            $this->db->query($sql);
        }
    }
    // --------------------------------------------------------------------------------
    // Update entity figure labels.
    // @param int $primaryKey The entity primary key.
    // @param array $labelList A list of normalized labels.
    // --------------------------------------------------------------------------------
    protected function updateFigureLabels(
        $primaryKey,
        array $figureLabelList = array()
    ) {
        $sql = "DELETE FROM {$this->abbrev}_has_FigureLabel
                WHERE {$this->pkColumn} = $primaryKey;";
        $this->db->query($sql);
        if ( count($figureLabelList) > 0 ) {
            $sql = "INSERT INTO {$this->abbrev}_has_FigureLabel ({$this->pkColumn}, label)
                    VALUES ";
            $normalizedFigureLabelList = array();
            foreach ( $figureLabelList as $figureLabel ) {
                $normalizedFigureLabel = trim($figureLabel);
                if ( in_array(
                    $normalizedFigureLabel,
                    $normalizedFigureLabelList
                ) ) {
                    throw new \Exception("Figure label repeated: " . $normalizedFigureLabel);
                }
                $valueList[] = "(" . $primaryKey . ", " . $this->db->escape($normalizedFigureLabel, true) . ")";
            }
            $sql .= implode(", ", $valueList);
            $this->db->query($sql);
        }
    }
    // --------------------------------------------------------------------------------
    // Normalize figure labels by sanitizing.
    // @param string $labels A string containing a "^" delimited list of labels
    // @returns array An array of normalized figure labels
    // --------------------------------------------------------------------------------
    protected function normalizeFigureLabels($labels)
    {
        $normalizedFigureLabelList = array();
        foreach ( explode("^", $labels) as $label ) {
            $normalizedFigureLabelList[] = trim($label);
        }

        return $normalizedFigureLabelList;
    }
    // --------------------------------------------------------------------------------
    // Set the sequence size if start and end coordinates are supplied.
    // Throws an error if only one of the start and end coordinates are
    // supplied.
    // @param array $data An array of entity data
    // --------------------------------------------------------------------------------
    protected function setSequenceSize(array &$data)
    {
        if ( isset($data["start"]) && isset($data["end"]) ) {
            $data["size"] = $data["end"] - $data["start"] + 1;
        } elseif ( isset($data["start"]) && ! isset($data["end"]) ) {
            throw new Exception("Coordinate \"start\" provided without \"end\"");
        } elseif ( ! isset($data["start"]) && isset($data["end"]) ) {
            throw new Exception("Coordinate \"end\" provided without \"start\"");
        }
    }
    // --------------------------------------------------------------------------------
    // Archive any previous versions of an entity.
    // Archiving sets the "state" to "archived" and the "archive_date" to
    // the current date and time.
    // @param int $entityId The entity primary key
    // @param int $version The current entity version
    // --------------------------------------------------------------------------------
    protected function archivePreviousVersions(
        $entityId,
        $version
    ) {
        $sql = "UPDATE {$this->tableName}
                SET state = 'archived',
                    archive_date = NOW()
                WHERE entity_id = $entityId AND
                    state = 'current' AND
                    version < $version;";
        $this->db->query($sql);
    }
    // --------------------------------------------------------------------------------
    // Create mysqli prepared statement to insert into the entity table.
    // Literal SQL expressions take precedence over data if both contain
    // the same key.
    // @param array $dataList An array of data
    // @param array $literalSqlList An array of SQL expressions
    // @return array An array containing a SQL string possibly containing
    //   mysqli parameter markers, a type string for use with the mysqli
    //   bind_param function and an array of parameters
    // --------------------------------------------------------------------------------
    protected function constructInsertStatement(
        array &$dataList = array(),
        array $literalSqlList = array()
    ) {
        $columnList = array();
        $valueList = array();
        $paramList = array();
        $typeList = array();
        $paramCount = 0;
        foreach ( $this->columnTypeList as $column => $type ) {
            if ( isset($literalSqlList[$column]) ) {
                $columnList[] = $column;
                $valueList[] = $literalSqlList[$column];
            } elseif ( isset($dataList[$column]) ) {
                $columnList[] = $column;
                $valueList[] = "?";
                $typeList[] = $type;
                $paramList[$paramCount] =& $dataList[$column];
                $paramCount++;
            }
        }
        $sql = "INSERT INTO " . $this->tableName . " (" .
           implode(", ", $columnList) . " ) VALUES (" .
           implode(", ", $valueList) . ")";
        $bindTypes = implode("", $typeList);

        return array(
            $sql,
            $bindTypes,
            $paramList
        );
    }
    // --------------------------------------------------------------------------------
    // Create mysqli prepared statement to update a single row in the
    // enttiy table.
    // Literal SQL expressions take precedence over data if both contain
    // the same key.
    // @param int $primaryKey The entity primary key
    // @param array $dataList An array of data
    // @param array $literalSqlList An array of SQL expressions
    // @return array An array containing a SQL string possibly containing
    //   mysqli parameter markers, a type string for use with the mysqli
    //   bind_param function and an array of parameters
    // --------------------------------------------------------------------------------
    protected function constructUpdateStatement(
        &$primaryKey,
        array &$dataList = array(),
        array $literalSqlList = array()
    ) {
        $columnList = array();
        $paramList = array();
        $typeList = array();
        $paramCount = 0;
        foreach ( $this->columnTypeList as $column => $type ) {
            if ( isset($literalSqlList[$column]) ) {
                $columnList[] = $column . " = " . $literalSqlList[$column];
            } elseif ( isset($dataList[$column]) ) {
                $columnList[] = $column . " = ?";
                $paramList[$paramCount] =& $dataList[$column];
                $paramCount++;
                $typeList[] = $type;
            }
        }
        if ( 0 === count($columnList) ) {
            throw new \Exception("No data supplied");
        }
        $paramList[$paramCount] =& $primaryKey;
        $typeList[] = "i";
        $sql = "UPDATE " . $this->tableName .
           " SET " . implode(", ", $columnList) .
           " WHERE " . $this->pkColumn . " = ?";
        $bindTypes = implode("", $typeList);

        return array(
            $sql,
            $bindTypes,
            $paramList
        );
    }
}
