<?php
class BiologicalprocessHandler
{
    // --------------------------------------------------------------------------------
    // Factory design pattern
    // --------------------------------------------------------------------------------
    public static function factory()
    {
        return new BiologicalprocessHandler;
    }
    // --------------------------------------------------------------------------------
    // Return help for the "list" action
    // --------------------------------------------------------------------------------
    public function listHelp()
    {
        $description = "List the biological processes. The results will be an " .
            "array where the key is the biological process identifier and the " .
            "individual records will consist of (id, identifier, term, display).";
        $options = array(
            "id"         => "Return the biological process matching the internal id",
            "identifier" => "Return only the biological process(es) matching " .
                            "the identifier",
            "limit"      => "The maximum number of biological process to return",
            "sort"       => "The sort field. The valid options are: go_id and term",
            "term"       => "Return only the biological process(es) matching " .
                            "the term"
        );

        return RestResponse::factory(
            true,
            $description,
            $options
        );
    }
    // --------------------------------------------------------------------------------
    // List the biological processes
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
                    $sqlCriteria[] = "process_id = " . $db->escape($value);
                    break;
                case "identifier":
                    if ( $value !== "" ) {
                        $sqlCriteria[] = "go_id " . $sqlOperator . " " . $db->escape($value, true);
                    }
                    break;
                case "limit":
                    $limit = $helper->constructLimitStr($arguments);
                    break;
                case "sort":
                    $sortInformation = $helper->extractSortInformation($value);
                    foreach ( $sortInformation as $sortColumn => $direction ) {
                        switch ( $sortColumn ) {
                            case "identifier":
                                $sqlOrderBy[] = "go_id " . $direction;
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
        SELECT process_id AS id,
               go_id AS identifier,
               term,
               CONCAT(term, ' (', go_id, ')') AS display
        FROM BiologicalProcess
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
}
