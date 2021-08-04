<?php
class GeneHandler
{
    private $helper = null;
    private $db = null;
    // --------------------------------------------------------------------------------
    // Factory design pattern
    // --------------------------------------------------------------------------------
    public static function factory()
    {
        return new GeneHandler;
    }
    private function __construct()
    {
        $this->db = DbService::factory();
        $this->helper = RestHandlerHelper::factory();
    }
    // --------------------------------------------------------------------------------
    // Return help for the "list" action
    // --------------------------------------------------------------------------------
    public function listHelp()
    {
        $description = "List the genes. The results will be an array where " .
            "the key is the gene identifier and the individual records will consist " .
            "of (id, species_id, name, identifier, display).";
        $options = array(
            "id"                  => "Return the gene matching the internal id",
            "identifier"          => "Return only the gene(s) matching the identifier",
            "include_coordinates" => "Include the gene start and stop coordinates",
            "limit"               => "The maximum number of genes to return",
            "limitoffset"         => "The offset of the first gene to return. " .
                                     "(It requires \"limit\")",
            "name"                => "Return only the gene(s) matching the name",
            "sort"                => "The sort field. " .
                                     "The valid options are: identifier and name",
            "species_id"          => "Restrict the list to the species id"
        );

        return RestResponse::factory(
            true,
            $description,
            $options
        );
    }
    // --------------------------------------------------------------------------------
    // List the genes
    // --------------------------------------------------------------------------------
    public function listAction(
        array $arguments,
        array $postData = null
    ) {
        $sqlCriteria = array();
        $sqlOrderBy = array();
        $limit = "";
        $includeCoordinates = false;
        $response = null;
        foreach ( $arguments as $arg => $value ) {
            if ( ($value !== false) &&
                (($value === null) || ($value === "")) ) {
                continue;
            }
            // Extract any optional operators from the value
            $sqlOperator = "=";
            $this->helper->extractOperator(
                $value,
                $sqlOperator
            );
            // If a wildcard was found in the value change the operator to "LIKE"
            if ( $this->helper->convertWildcards($value) ) {
                $sqlOperator = "LIKE";
            }
            switch ( $arg ) {
                case "id":
                    $sqlCriteria[] = "gene_id = " . $this->db->escape($value);
                    break;
                case "identifier":
                    if ( $value !== "" ) {
                        $sqlCriteria[] = "LOWER(identifier) " . $sqlOperator . " LOWER(" . $this->db->escape($value, true) . ")";
                    }
                    break;
                case "include_coordinates":
                    $includeCoordinates = $this->helper->convertValueToBool($value);
                    break;
                case "limit":
                    $limit = $this->helper->constructLimitStr($arguments);
                    break;
                case "name":
                    if ( $value !== "%" ) {
                        $sqlCriteria[] = str_replace(
                            "_",
                            "\_",
                            "LOWER(name) " . $sqlOperator . " LOWER(" . $this->db->escape($value, true) . ")"
                        );
                    }
                    break;
                case "sort":
                    $sortInformation = $this->helper->extractSortInformation($value);
                    foreach ( $sortInformation as $sortColumn => $direction ) {
                        switch ( $sortColumn ) {
                            case "identifier":
                                $sqlOrderBy[] = "identifier " . $direction;
                                break;
                            case "name":
                                $sqlOrderBy[] = "name " . $direction;
                                break;
                            default:
                                $sqlOrderBy[] = "display " . $direction;
                                break;
                        }
                    }
                    break;
                case "species_id":
                    $sqlCriteria[] = "species_id = " . $this->db->escape($value);
                    break;
                default:
                    break;
            }
        }
        $sql = "
        SELECT gene_id AS id,
            species_id,
            name,
            identifier,
            CONCAT(name, ' (', identifier, ')') AS display" .
            ( $includeCoordinates ? ", start, stop " : " " ) .
            "FROM Gene";
        if ( count($sqlCriteria) !== 0 ) {
            $sql .= " WHERE " . implode(" AND ", $sqlCriteria);
        }
        if ( count($sqlOrderBy) !== 0 ) {
            $sql .= " ORDER BY " . implode(",", $sqlOrderBy);
        }
        $sql .= " " . $limit;
        $response = $this->helper->query(
            $this->db,
            $sql
        );

        return $response;
    }
}
