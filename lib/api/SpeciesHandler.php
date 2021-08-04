<?php
class SpeciesHandler
{
    public static function factory()
    {
        return new SpeciesHandler;
    }
    // --------------------------------------------------------------------------------
    // Return help for the "list" action
    // --------------------------------------------------------------------------------
    public function listHelp()
    {
        $description = "List the species. The results will be an array which ".
            "the records will consist of (id, scientific name, short name, display).";

        return RestResponse::factory(
            true,
            $description
        );
    }
    // --------------------------------------------------------------------------------
    // List the species
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
            switch ( $arg ) {
                case "id":
                    $sqlCriteria[] = "species_id " . $sqlOperator . " " . $db->escape($value);
                    break;
                case "limit":
                    $limit = $helper->constructLimitStr($arguments);
                    break;
                case "scientific_name":
                    $sqlCriteria[] = "scientific_name " . $sqlOperator . " " . $db->escape($value, true);
                    break;
                case "sort":
                    $sortInformation = $helper->extractSortInformation($value);
                    foreach ( $sortInformation as $sortColumn => $direction ) {
                        switch ( $sortColumn ) {
                            case "id":
                                $sqlOrderBy[] = "species_id " . $direction;
                                break;
                            case "scientific_name":
                                $sqlOrderBy[] = "scientific_name " . $direction;
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
        SELECT s.species_id AS id,
            s.scientific_name,
            s.short_name,
            CONCAT(s.scientific_name, ' (', s.short_name, ')') AS display,
            ga.release_version AS current_genome_assembly_release_version
        FROM Species s
        JOIN GenomeAssembly ga USING(species_id)
        WHERE ga.is_deprecated = 0
SQL;
        if ( count($sqlCriteria) !== 0 ) {
            $sql .= " " . implode(" AND ", $sqlCriteria);
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
