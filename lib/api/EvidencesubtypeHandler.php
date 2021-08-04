<?php
class EvidencesubtypeHandler
{
    // --------------------------------------------------------------------------------
    // Factory design pattern
    // --------------------------------------------------------------------------------
    public static function factory()
    {
        return new EvidencesubtypeHandler;
    }
    // --------------------------------------------------------------------------------
    // Return help for the "list" action
    // --------------------------------------------------------------------------------
    public function listHelp()
    {
        $description = "List the evidence subtypes. The results will be an array " .
            "where the key is the term and the individual records will " .
            "consist of (id, term).";
        $options = array(
            "id"          => "Return the evidence subtype matching the internal id",
            "limit"       => "The maximum number of evidence subtypes to return",
            "limitoffset" => "The offset of the first evidence subtype to return ".
                             "(It requires \"limit\")",
            "name"        => "Return only the evidence subtype(s) matching the term",
            "sort"        => "The sort field. The valid option is: name"
        );

        return RestResponse::factory(
            true,
            $description,
            $options
        );
    }
    // --------------------------------------------------------------------------------
    // List the evidence subtypes
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
        foreach ( $arguments as $arg => $value ) {
            if ( ($value !== false)  &&
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
            switch ( $arg ) {
                case "id":
                    $sqlCriteria[] = "evidence_subtype_id " . $sqlOperator . " " . $db->escape($value);
                    break;
                case "name":
                    $sqlCriteria[] = "term " . $sqlOperator . " " . $db->escape($value, true);
                    break;
                case "limit":
                    $limit = $helper->constructLimitStr($arguments);
                    break;
                case "sort":
                    $sortInformation = $helper->extractSortInformation($value);
                    foreach ( $sortInformation as $sortColumn => $direction ) {
                        switch ( $sortColumn ) {
                            case "name":
                                $sqlOrderBy[] = "term " . $direction;
                                break;
                            default:
                                break;
                        }
                    }
                    break;
                default:
                    break;
            }
        }
        $sql = <<<SQL
        SELECT evidence_subtype_id AS id,
            term
        FROM EvidenceSubtypeTerm
SQL;
        if ( count($sqlCriteria) !== 0 ) {
            $sql .= " WHERE " . implode(" AND ", $sqlCriteria);
        }
        if ( count($sqlOrderBy) !== 0 ) {
            $sql .= " ORDER BY " . implode(",", $sqlOrderBy);
        }
        $sql .= " " . $limit;
        $response = $helper->query(
            $db,
            $sql
        );

        return $response;
    }
}
