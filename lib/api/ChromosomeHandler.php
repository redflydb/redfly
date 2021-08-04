<?php
class ChromosomeHandler
{
    // --------------------------------------------------------------------------------
    // Factory design pattern
    // --------------------------------------------------------------------------------
    public static function factory()
    {
        return new ChromosomeHandler;
    }
    // --------------------------------------------------------------------------------
    // Return help for the "list" action
    // --------------------------------------------------------------------------------
    public function listHelp()
    {
        $description = "List the chromosomes. The results will be an array where " .
            "the key is the name and the individual records will consist of " .
            "(id, species_short_name, name, length, display).";
        $options = array(
            "id"                     => "Return the chromosome matching the internal id",
            "limit"                  => "The maximum number of chromosomes to return",
            "name"                   => "Return only the chromosome(s) matching " .
                                        "the name",
            "sort"                   => "The sort field. The valid option is: name",
            "species_id"             => "Restrict the list to the internal id of a " .
                                        "species",
            "species_short_name"     => "Restrict the list to the short name of a " .
                                        "species"
        );

        return RestResponse::factory(
            true,
            $description,
            $options
        );
    }
    // --------------------------------------------------------------------------------
    // List the chromosomes
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
                case "display":
                    $sqlCriteria[] = "CONCAT(c.name, ' (', s.short_name,')') " . $sqlOperator . " " . $db->escape($value, true);
                    break;
                case "id":
                    $sqlCriteria[] = "c.chromosome_id = " . $db->escape($value);
                    break;
                case "limit":
                    $limit = $helper->constructLimitStr($arguments);
                    break;
                case "name":
                    $sqlCriteria[] = "c.name " . $sqlOperator . " " . $db->escape($value, true);
                    break;
                case "sort":
                    $sortInformation = $helper->extractSortInformation($value);
                    foreach ( $sortInformation as $sortColumn => $direction ) {
                        switch ( $sortColumn ) {
                            case "display":
                                $sqlOrderBy[] = "display " . $direction;
                                break;
                            case "name":
                                $sqlOrderBy[] = "c.name " . $direction;
                                break;
                            case "species_short_name":
                                $sqlOrderBy[] = "s.short_name " . $direction;
                                break;
                            default:
                                break;
                        }
                    }
                    break;
                case "species_id":
                    $sqlCriteria[] = "s.species_id = " . $db->escape($value);
                    break;
                case "species_short_name":
                    $sqlCriteria[] = "s.short_name = " . $db->escape($value, true);
                    break;
                default:
                    break;
            }
        }
        $sql = <<<SQL
        SELECT c.chromosome_id AS id,
            s.species_id,
            s.short_name AS species_short_name,
            c.name,
            c.length,
            CONCAT(c.name, ' (', s.short_name,')') AS display 
        FROM Species s, 
            GenomeAssembly ga,
            Chromosome c
        WHERE s.species_id = ga.species_id AND
            ga.is_deprecated = 0 AND
            ga.genome_assembly_id = c.genome_assembly_id
SQL;
        if ( count($sqlCriteria) !== 0 ) {
            $sql .= " AND " . implode(" AND ", $sqlCriteria);
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
