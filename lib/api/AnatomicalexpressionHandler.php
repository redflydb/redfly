<?php
class AnatomicalexpressionHandler
{
    // --------------------------------------------------------------------------------
    // Factory design pattern
    // --------------------------------------------------------------------------------
    public static function factory()
    {
        return new AnatomicalexpressionHandler;
    }
    // --------------------------------------------------------------------------------
    // Return help for the "list" action
    // --------------------------------------------------------------------------------
    public function listHelp()
    {
        $description = "List the anatomical expressions. The results will be an " .
            "array where the key is the anatomical expression identifier and the " .
            "individual records will consist of (id, species id, term, identifier).";
        $options = array(
            "id"         => "Return the anatomical expression matching the internal id",
            "identifier" => "Return only the anatomical expression(s) matching the " .
                            "identifier",
            "limit"      => "The maximum number of anatomical expressions to return",
            "sort"       => "The sort field. " .
                            "The valid options are: identifier and term",
            "species_id" => "Restrict the list to the species id",
            "term"       => "Return only the anatomical expression(s) matching the " .
                            "term"
        );

        return RestResponse::factory(
            true,
            $description,
            $options
        );
    }
    // --------------------------------------------------------------------------------
    // List the anatomical expressions
    // --------------------------------------------------------------------------------
    public function listAction(
        array $arguments,
        array $postData = null
    ) {
        $sqlCriteria = array();
        $sqlOrderBy = array();
        $limit = "";
        $response = null;
        $db = DbService::factory();
        $helper = RestHandlerHelper::factory();
        foreach ( $arguments as $argument => $value ) {
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
            if ( $helper->convertWildcards($value) ) {
                $sqlOperator = "LIKE";
            }
            switch ( $argument ) {
                case "id":
                    $sqlCriteria[] = "term_id = " . $value;
                    break;
                case "identifier":
                    if ( $value !== "" ) {
                        $sqlCriteria[] = "identifier " . $sqlOperator . " " . $db->escape($value, true);
                    }
                    break;
                case "limit":
                    $limit = $helper->constructLimitStr($arguments);
                    break;
                case "sort":
                    $sortArguments = $helper->extractSortInformation($value);
                    foreach ( $sortArguments as $sortColumn => $direction ) {
                        switch ( $sortColumn ) {
                            case "identifier":
                                $sqlOrderBy[] = "identifier " . $direction;
                                break;
                            case "term":
                                $sqlOrderBy[] = "term " . $direction;
                                break;
                            default:
                                $sqlOrderBy[] = "display " . $direction;
                                break;
                        }
                    }
                    break;
                case "species_id":
                    $sqlCriteria[] = "species_id = " . $value;
                    break;
                case "term":
                    if ( $value !== "" ) {
                        $sqlCriteria[] = "term " . $sqlOperator . " " . $db->escape($value, true);
                    }
                    break;
                default:
                    break;
            }
        }
        $sql = <<<SQL
        SELECT term_id AS id,
            species_id,
            term,
            identifier,
            CONCAT(term, ' (', identifier, ')') AS display
        FROM ExpressionTerm
SQL;
        $where = "";
        if ( count($sqlCriteria) !== 0 ) {
            $where .= " WHERE " . implode(" AND ", $sqlCriteria);
        }
        $orderby = "";
        if ( count($sqlOrderBy) !== 0 ) {
            $orderby .= " ORDER BY " . implode(",", $sqlOrderBy);
        }
        $sql .= <<<SQL
        $where
        $orderby
        $limit;
SQL;
        $response = $helper->query(
            $db,
            $sql
        );

        return $response;
    }
    // --------------------------------------------------------------------------------
    // Return help for the "get" action
    // --------------------------------------------------------------------------------
    public function getHelp()
    {
        $description = "List all the anatomical expression and their staging data " .
            "(if required) associated with a specific entity.";
        $options = array(
            "limit"       => "The maximum number of entities to return",
            "identifier"  => "The identifier (or an array of them) for an entity " .
                "(e.g., RFRC:0000168.001)",
            "sort"        => "The sort field. " .
                "Valid options are: anatomical_expression_identifier, anatomical_expression_term, " .
                "stage_on_identifier, stage_on_term, " .
                "stage_off_identifier, stage_off_term, " .
                "biological_process_identifier, and biological_process_term",
            "triplestore" => "If true, it also lists all the staging data associated " .
                             "to such anatomical expression(s)"
        );

        return RestResponse::factory(
            true,
            $description,
            $options
        );
    }
    // --------------------------------------------------------------------------------
    // List all the anatomical expressions and their staging data (if existing) of
    // an entity
    // --------------------------------------------------------------------------------
    public function getAction(
        array $arguments,
        array $postData = null
    ) {
        $sqlCriteria = array();
        $sqlOrderBy = array();
        $limit = "";
        $redflyIdProvided = false;
        $response = null;
        $type = $entity = $version = $dbId = null;
        $db = DbService::factory();
        $helper = RestHandlerHelper::factory();
        $sortArguments = null;
        $tripleStore = false;
        foreach ( $arguments as $argument => $value ) {
            if ( ($value !== false) &&
                (($value === null) || ($value === "")) ) {
                continue;
            }
            switch ( $argument ) {
                case "limit":
                    $limit = $helper->constructLimitStr($arguments);
                    break;
                case "redfly_id":
                    $helper->parseEntityId(
                        $value,
                        $type,
                        $entity,
                        $version,
                        $dbId
                    );
                    $type = (string) $type;
                    $entity = (int) $entity;
                    $version = (int) $version;
                    switch ( $type ) {
                        case CrmsegmentHandler::EntityCode:
                            $alias = "crms";
                            break;
                        case PredictedcrmHandler::EntityCode:
                            $alias = "pcrm";
                            break;
                        case ReporterconstructHandler::EntityCode:
                            $alias = "rc";
                            break;
                        case TranscriptionfactorbindingsiteHandler::EntityCode:
                            $alias = "tfbs";
                            break;
                        default:
                            return RestResponse::factory(
                                false,
                                "Unknown type: " . $type
                            );
                            break;
                    }
                    $sqlCriteria[] = "(" . $alias . ".entity_id = " . $db->escape($entity) .
                        " AND " . $alias . ".version = " . $db->escape($version) . ")";
                    $redflyIdProvided = true;
                    break;
                case "sort":
                    $sortArguments = $helper->extractSortInformation($value);
                case "triplestore":
                    if ( $value === "true" ) {
                        $tripleStore = true;
                    }
                    break;
                default:
                    break;
            }
        }
        if ( ! $redflyIdProvided ) {
            return RestResponse::factory(
                false,
                "REDfly id not provided"
            );
        }
        if ( $sortArguments !== null ) {
            foreach ( $sortArguments as $sortColumn => $direction ) {
                switch ( $sortColumn ) {
                    case "anatomical_expression_identifier":
                        $sqlOrderBy[] = "identifier " . $direction;
                        break;
                    case "anatomical_expression_term":
                        $sqlOrderBy[] = "term " . $direction;
                        break;
                }
            }
            if ( $tripleStore === true ) {
                foreach ( $sortArguments as $sortColumn => $direction ) {
                    switch ( $sortColumn ) {
                        case "stage_on_identifier":
                            $sqlOrderBy[] = "stage_on_identifier " . $direction;
                            break;
                        case "stage_on_term":
                            $sqlOrderBy[] = "stage_on_term " . $direction;
                            break;
                        case "stage_off_identifier":
                            $sqlOrderBy[] = "stage_off_identifier " . $direction;
                            break;
                        case "stage_off_term":
                            $sqlOrderBy[] = "stage_off_term " . $direction;
                            break;
                        case "biological_process_identifier":
                            $sqlOrderBy[] = "biological_process_identifier " . $direction;
                            break;
                        case "biological_process_term":
                            $sqlOrderBy[] = "biological_process_term " . $direction;
                            break;
                    }
                }
            }
        }
        switch ( $type ) {
            case CrmsegmentHandler::EntityCode:
                if ( $tripleStore === false ) {
                    $sql = <<<SQL
                    SELECT et.term_id AS id,
                        et.identifier,
                        et.term
                    FROM CRMSegment crms
                    INNER JOIN CRMSegment_has_Expression_Term map ON crms.crm_segment_id = map.crm_segment_id
                    INNER JOIN ExpressionTerm et ON map.term_id = et.term_id
SQL;
                    if ( count($sqlCriteria) !== 0 ) {
                        $sql .= " WHERE " . implode(" AND ", $sqlCriteria);
                    }
                } else {
                    $sql = <<<SQL
                    SELECT ts.ts_id AS id,
                        et.term_id,                    
                        et.identifier,
                        et.term,
                        IFNULL(ts.pubmed_id, '') AS pubmed_id,
                        IFNULL(ts.stage_on, '') AS stage_on_identifier,
                        IFNULL(ds_on.term, '') AS stage_on_term,
                        IFNULL(ts.stage_off, '') AS stage_off_identifier,
                        IFNULL(ds_off.term, '') AS stage_off_term,
                        IFNULL(ts.biological_process, '') AS biological_process_identifier,
                        IFNULL(bp.term, '') AS biological_process_term,
                        IFNULL(ts.sex, '') AS sex,
                        IFNULL(ts.ectopic, '') AS ectopic,
                        IFNULL(ts.silencer, '') AS silencer
                    FROM CRMSegment crms
                    INNER JOIN CRMSegment_has_Expression_Term map ON crms.crm_segment_id = map.crm_segment_id
                    INNER JOIN ExpressionTerm et ON map.term_id = et.term_id
                    LEFT OUTER JOIN triplestore_crm_segment ts ON map.crm_segment_id = ts.crm_segment_id AND
                        et.identifier = ts.expression
                    LEFT OUTER JOIN DevelopmentalStage ds_on ON crms.assayed_in_species_id = ds_on.species_id AND
                        ts.stage_on = ds_on.identifier
                    LEFT OUTER JOIN DevelopmentalStage ds_off ON crms.assayed_in_species_id = ds_off.species_id AND
                        ts.stage_off = ds_off.identifier
                    LEFT OUTER JOIN BiologicalProcess bp ON ts.biological_process = bp.go_id
SQL;
                    if ( count($sqlCriteria) !== 0 ) {
                        $sql .= " WHERE " . implode(" AND ", $sqlCriteria);
                    }
                }
                if ( count($sqlOrderBy) !== 0 ) {
                    $sql .= " ORDER BY " . implode(",", $sqlOrderBy);
                }
                break;
            case PredictedcrmHandler::EntityCode:
                if ( $tripleStore === false ) {
                    $sql = <<<SQL
                    SELECT et.term_id AS id,
                        et.identifier,
                        et.term
                    FROM PredictedCRM pcrm
                    INNER JOIN PredictedCRM_has_Expression_Term map ON pcrm.predicted_crm_id = map.predicted_crm_id
                    INNER JOIN ExpressionTerm et ON map.term_id = et.term_id
SQL;
                    if ( count($sqlCriteria) !== 0 ) {
                        $sql .= " WHERE " . implode(" AND ", $sqlCriteria);
                    }
                } else {
                    $sql = <<<SQL
                    SELECT ts.ts_id AS id,
                        et.term_id,
                        et.identifier,
                        et.term,
                        IFNULL(ts.pubmed_id, '') AS pubmed_id,
                        IFNULL(ts.stage_on, '') AS stage_on_identifier,
                        IFNULL(ds_on.term, '') AS stage_on_term,
                        IFNULL(ts.stage_off, '') AS stage_off_identifier,
                        IFNULL(ds_off.term, '') AS stage_off_term,
                        IFNULL(ts.biological_process, '') AS biological_process_identifier,
                        IFNULL(bp.term, '') AS biological_process_term,
                        IFNULL(ts.sex, '') AS sex,
                        IFNULL(ts.silencer, '') AS silencer
                    FROM PredictedCRM pcrm
                    INNER JOIN PredictedCRM_has_Expression_Term map ON pcrm.predicted_crm_id = map.predicted_crm_id
                    INNER JOIN ExpressionTerm et ON map.term_id = et.term_id
                    LEFT OUTER JOIN triplestore_predicted_crm ts ON map.predicted_crm_id = ts.predicted_crm_id AND
                        et.identifier = ts.expression
                    LEFT OUTER JOIN DevelopmentalStage ds_on ON pcrm.sequence_from_species_id = ds_on.species_id AND
                        ts.stage_on = ds_on.identifier
                    LEFT OUTER JOIN DevelopmentalStage ds_off ON pcrm.sequence_from_species_id = ds_off.species_id AND
                        ts.stage_off = ds_off.identifier
                    LEFT OUTER JOIN BiologicalProcess bp ON ts.biological_process = bp.go_id
SQL;
                    if ( count($sqlCriteria) !== 0 ) {
                        $sql .= " WHERE " . implode(" AND ", $sqlCriteria);
                    }
                }
                if ( count($sqlOrderBy) !== 0 ) {
                    $sql .= " ORDER BY " . implode(",", $sqlOrderBy);
                }
                break;
            case ReporterconstructHandler::EntityCode:
                if ( $tripleStore === false ) {
                    $sql = <<<SQL
                    SELECT et.term_id AS id,
                        et.identifier,
                        et.term
                    FROM ReporterConstruct rc
                    INNER JOIN RC_has_ExprTerm map ON rc.rc_id = map.rc_id
                    INNER JOIN ExpressionTerm et ON map.term_id = et.term_id
SQL;
                    if ( count($sqlCriteria) !== 0 ) {
                        $sql .= " WHERE " . implode(" AND ", $sqlCriteria);
                    }
                } else {
                    $sql = <<<SQL
                    SELECT ts.ts_id AS id,
                        et.term_id,
                        et.identifier,
                        et.term,
                        IFNULL(ts.pubmed_id, '') AS pubmed_id,
                        IFNULL(ts.stage_on, '') AS stage_on_identifier,
                        IFNULL(ds_on.term, '') AS stage_on_term,
                        IFNULL(ts.stage_off, '') AS stage_off_identifier,
                        IFNULL(ds_off.term, '') AS stage_off_term,
                        IFNULL(ts.biological_process, '') AS biological_process_identifier,
                        IFNULL(bp.term, '') AS biological_process_term,
                        IFNULL(ts.sex, '') AS sex,
                        IFNULL(ts.ectopic, '') AS ectopic,
                        IFNULL(ts.silencer, '') AS silencer
                    FROM ReporterConstruct rc
                    INNER JOIN RC_has_ExprTerm map ON rc.rc_id = map.rc_id
                    INNER JOIN ExpressionTerm et ON map.term_id = et.term_id
                    LEFT OUTER JOIN triplestore_rc ts ON map.rc_id = ts.rc_id AND
                        et.identifier = ts.expression
                    LEFT OUTER JOIN DevelopmentalStage ds_on ON rc.assayed_in_species_id = ds_on.species_id AND
                        ts.stage_on = ds_on.identifier
                    LEFT OUTER JOIN DevelopmentalStage ds_off ON rc.assayed_in_species_id = ds_off.species_id AND
                        ts.stage_off = ds_off.identifier 
                    LEFT OUTER JOIN BiologicalProcess bp ON ts.biological_process = bp.go_id                
SQL;
                    if ( count($sqlCriteria) !== 0 ) {
                        $sql .= " WHERE " . implode(" AND ", $sqlCriteria);
                    }
                }
                if ( count($sqlOrderBy) !== 0 ) {
                    $sql .= " ORDER BY " . implode(",", $sqlOrderBy);
                }
                break;
            // Transcription factor binding sites inherit the anatomical expressions of
            // any associated reporter constructs.
            // So there can be any data repetition
            case TranscriptionfactorbindingsiteHandler::EntityCode:
                $sql = <<<SQL
                SELECT et.term_id AS id,
                    et.identifier,
                    et.term
                FROM BindingSite tfbs,
	                RC_associated_BS assoc,
	                ReporterConstruct rc,
					RC_has_ExprTerm map,
					ExpressionTerm et
                WHERE tfbs.tfbs_id = assoc.tfbs_id AND
                	assoc.rc_id = rc.rc_id AND
                	rc.rc_id = map.rc_id AND
                	map.term_id = et.term_id
SQL;
                if ( count($sqlCriteria) !== 0 ) {
                    $sql .= " AND " . implode(" AND ", $sqlCriteria);
                }
                if ( count($sqlOrderBy) !== 0 ) {
                    $sql .= " ORDER BY " . implode(",", $sqlOrderBy);
                }
                break;
            default:
                return RestResponse::factory(
                    false,
                    "Unknown type: " . $type
                );
                break;
        }
        $sql .= " " . $limit;
        $response = $helper->query(
            $db,
            $sql
        );

        return $response;
    }
}
