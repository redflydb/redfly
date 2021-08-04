<?php
class CoordinateHandler
{
    private $helper = null;
    private $db = null;
    // --------------------------------------------------------------------------------
    // Factory design pattern
    // --------------------------------------------------------------------------------
    public static function factory()
    {
        return new CoordinateHandler;
    }
    private function __construct()
    {
        $this->db = DbService::factory();
        $this->helper = RestHandlerHelper::factory();
    }
    // --------------------------------------------------------------------------------
    // Return help for the "list" action
    // --------------------------------------------------------------------------------
    public function searchHelp()
    {
        $description = "Return the coordinates for the specified entity." .
            " NOTE: Only the current coordinate release version is supported.";
        $options = array("redfly_id" => "The REDfly identifier (or an array of them)" .
            " for the entity (e.g., RFRC:0000312.001)");
        
        return RestResponse::factory(
            true,
            $description,
            $options
        );
    }
    // --------------------------------------------------------------------------------
    // List the coordinates
    // --------------------------------------------------------------------------------
    public function searchAction(
        array $arguments,
        array $postData = null
    ) {
        $sqlCriteria = array();
        $sqlOrderBy = array();
        $limit = "";
        $response = null;
        $results = array();
        $helper = RestHandlerHelper::factory();
        $redflyIdList = array(
            CrmsegmentHandler::EntityCode                     => array(),
            PredictedcrmHandler::EntityCode                   => array(),
            ReporterconstructHandler::EntityCode              => array(),
            TranscriptionfactorbindingsiteHandler::EntityCode => array()
        );
        foreach ( $arguments as $arg => $value ) {
            if ( ($value !== false) &&
                (($value === null) || ($value === "")) ) {
                continue;
            }
             // Extract any optional operators from the value
            $sqlOperator = "=";
            $helper->extractOperator(
                $value,
                $sqlOperator
            );
            // If a wildcard was found in the value change the operator to "LIKE"
            /* We are not supporting wildcards on redfly ids
            if ( $helper->convertWildcards($value) ) {
                $sqlOperator = "LIKE";
            }
            */
            switch ( $arg ) {
                case "limit":
                    $limit = $helper->constructLimitStr($arguments);
                    break;
                case "redfly_id":
                    // Normalize to an array
                    $value = ( ! is_array($value)
                        ? array($value)
                        : $value );
                    foreach ( $value as $id ) {
                        $type = $entityId = $version = $dbId = null;
                        try {
                            $this->helper->parseEntityId(
                                $id,
                                $type,
                                $entityId,
                                $version,
                                $dbId
                            );
                        } catch ( Exception $e ) {
                            print $e->getMessage();
                            // Skip invalid REDfly identifiers
                            continue;
                        }
                        $redflyIdList[$type][] = $entityId;
                    }
                    break;
                case "sort":
                    $sortInformation = $helper->extractSortInformation($value);
                    foreach ( $sortInformation as $sortColumn => $direction ) {
                        switch ( $sortColumn ) {
                            case "name":
                                $sqlOrderBy[] = "name " . $direction;
                                break;
                            default:
                                break;
                        }
                    }
                    break;
                case "species_short_name":
                    $sqlCriteria[] = "s.short_name " . $sqlOperator . " " . $this->db->escape($value, true);
                    break;
                default:
                    break;
            }
        }
        // Query CRMSs
        if ( count($redflyIdList[CrmsegmentHandler::EntityCode]) !== 0 ) {
            $entityIdList = implode(",", $redflyIdList[CrmsegmentHandler::EntityCode]);
            if ( $entityIdList !== "" ) {
                $sql = <<<SQL
                SELECT s.short_name AS species_short_name,
                    crms.crm_segment_id AS id,
                    crms.name,
                    crms.entity_id,
                    crms.version,
                    chr.name AS chromosome,
                    crms.current_start,
                    crms.current_end
                FROM CRMSegment crms
                INNER JOIN Species s ON crms.sequence_from_species_id = s.species_id
                INNER JOIN Chromosome chr ON crms.chromosome_id = chr.chromosome_id
                WHERE crms.state = 'current' AND 
                    crms.entity_id IN ($entityIdList)
SQL;
                if ( count($sqlCriteria) !== 0 ) {
                    $sql .= implode(" AND ", $sqlCriteria);
                }
                if ( count($sqlOrderBy) !== 0 ) {
                    $sql .= " ORDER BY " . implode(",", $sqlOrderBy);
                }
                $sql .= " " . $limit;
                $queryResult = $this->db->query($sql);
                while ( $row = $queryResult->fetch_assoc() ) {
                    $tmpRow = $row;
                    $tmpRow["redfly_id"] = $this->helper->entityId(
                        CrmsegmentHandler::EntityCode,
                        $row["entity_id"],
                        $row["version"],
                        $row["id"]
                    );
                    $tmpRow["coordinates"] = $this->helper->formatCoordinates(
                        $row["chromosome"],
                        $row["current_start"],
                        $row["current_end"]
                    );
                    unset($tmpRow["entity_id"]);
                    unset($tmpRow["version"]);
                    $results[] = $tmpRow;
                }
            }
        }
        // Query predicted CRMs
        if ( count($redflyIdList[PredictedcrmHandler::EntityCode]) !== 0 ) {
            $entityIdList = implode(",", $redflyIdList[PredictedcrmHandler::EntityCode]);
            if ( $entityIdList !== "" ) {
                $sql = <<<SQL
                SELECT s.short_name AS species_short_name,
                    pcrm.predicted_crm_id AS id,
                    pcrm.name,
                    pcrm.entity_id,
                    pcrm.version,
                    chr.name AS chromosome,
                    pcrm.current_start,
                    pcrm.current_end
                FROM PredictedCRM pcrm
                INNER JOIN Species s ON pcrm.sequence_from_species_id = s.species_id
                INNER JOIN Chromosome chr ON pcrm.chromosome_id = chr.chromosome_id
                WHERE pcrm.state = 'current' AND 
                    pcrm.entity_id IN ($entityIdList)
SQL;
                if ( count($sqlCriteria) !== 0 ) {
                    $sql .= implode(" AND ", $sqlCriteria);
                }
                if ( count($sqlOrderBy) !== 0 ) {
                    $sql .= " ORDER BY " . implode(",", $sqlOrderBy);
                }
                $sql .= " " . $limit;
                $queryResult = $this->db->query($sql);
                while ( $row = $queryResult->fetch_assoc() ) {
                    $tmpRow = $row;
                    $tmpRow["redfly_id"] = $this->helper->entityId(
                        PredictedcrmHandler::EntityCode,
                        $row["entity_id"],
                        $row["version"],
                        $row["id"]
                    );
                    $tmpRow["coordinates"] = $this->helper->formatCoordinates(
                        $row["chromosome"],
                        $row["current_start"],
                        $row["current_end"]
                    );
                    unset($tmpRow["entity_id"]);
                    unset($tmpRow["version"]);
                    $results[] = $tmpRow;
                }
            }
        }
        // Query RCs
        if ( count($redflyIdList[ReporterconstructHandler::EntityCode]) !== 0 ) {
            $entityIdList = implode(",", $redflyIdList[ReporterconstructHandler::EntityCode]);
            if ( $entityIdList !== "" ) {
                $sql = <<<SQL
                SELECT s.short_name AS species_short_name,
                    rc.rc_id AS id,
                    rc.name,
                    rc.entity_id,
                    rc.version,
                    chr.name AS chromosome,
                    rc.current_start,
                    rc.current_end
                FROM ReporterConstruct rc
                INNER JOIN Species s ON rc.sequence_from_species_id = s.species_id
                INNER JOIN Chromosome chr ON rc.chromosome_id = chr.chromosome_id
                WHERE rc.state = 'current' AND 
                    rc.entity_id IN ($entityIdList)
SQL;
                if ( count($sqlCriteria) !== 0 ) {
                    $sql .= implode(" AND ", $sqlCriteria);
                }
                if ( count($sqlOrderBy) !== 0 ) {
                    $sql .= " ORDER BY " . implode(",", $sqlOrderBy);
                }
                $sql .= " " . $limit;
                $queryResult = $this->db->query($sql);
                while ( $row = $queryResult->fetch_assoc() ) {
                    $tmpRow = $row;
                    $tmpRow["redfly_id"] = $this->helper->entityId(
                        ReporterconstructHandler::EntityCode,
                        $row["entity_id"],
                        $row["version"],
                        $row["id"]
                    );
                    $tmpRow["coordinates"] = $this->helper->formatCoordinates(
                        $row["chromosome"],
                        $row["current_start"],
                        $row["current_end"]
                    );
                    unset($tmpRow["entity_id"]);
                    unset($tmpRow["version"]);
                    $results[] = $tmpRow;
                }
            }
        }
        // Query TFBSs
        if ( count($redflyIdList[TranscriptionfactorbindingsiteHandler::EntityCode]) !== 0 ) {
            $entityIdList = implode(",", $redflyIdList[TranscriptionfactorbindingsiteHandler::EntityCode]);
            if ( $entityIdList !== "" ) {
                $sql = <<<SQL
                SELECT s.short_name AS species_short_name,
                    tfbs.tfbs_id AS id,
                    tfbs.name,
                    tfbs.entity_id,
                    tfbs.version,
                    chr.name AS chromosome,
                    tfbs.current_start,
                    tfbs.current_end
                FROM BindingSite tfbs
                INNER JOIN Species s ON tfbs.sequence_from_species_id = s.species_id
                INNER JOIN Chromosome chr ON tfbs.chromosome_id = chr.chromosome_id
                WHERE tfbs.state = 'current' AND
                    tfbs.entity_id IN ($entityIdList)
SQL;
                if ( count($sqlCriteria) !== 0 ) {
                    $sql .= implode(" AND ", $sqlCriteria);
                }
                if ( count($sqlOrderBy) !== 0 ) {
                    $sql .= " ORDER BY " . implode(",", $sqlOrderBy);
                }
                $sql .= " " . $limit;
                $queryResult = $this->db->query($sql);
                while ( $row = $queryResult->fetch_assoc() ) {
                    $tmpRow = $row;
                    $tmpRow["redfly_id"] = $this->helper->entityId(
                        TranscriptionfactorbindingsiteHandler::EntityCode,
                        $row["entity_id"],
                        $row["version"],
                        $row["id"]
                    );
                    $tmpRow["coordinates"] = $this->helper->formatCoordinates(
                        $row["chromosome"],
                        $row["current_start"],
                        $row["current_end"]
                    );
                    unset($tmpRow["entity_id"]);
                    unset($tmpRow["version"]);
                    $results[] = $tmpRow;
                }
            }
        }
        $response = RestResponse::factory(
            true,
            null,
            $results
        );

        return $response;
    }
}
