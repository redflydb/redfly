<?php
class DevelopmentalstageHandler
{
    // --------------------------------------------------------------------------------
    // Factory design pattern
    // --------------------------------------------------------------------------------
    public static function factory()
    {
        return new DevelopmentalstageHandler;
    }
    // --------------------------------------------------------------------------------
    // Return help for the "list" action
    // --------------------------------------------------------------------------------
    public function listHelp()
    {
        $description = "List the developmental stages as long as there is a wildcard. " .
            "The results will be an array where the key is the developmental stage " .
            "identifier and the individual records will consist of (id, species_id, " .
            "identifier, term, display). If there is no wildcard then a search list " .
            "adapted for both shortening and discrimination criteria of the PI is " .
            "shown instead.";
        $options = array(
            "id"         => "Return the developmental stage matching the internal id",
            "identifier" => "Return only the developmental stage(s) matching " .
                            "the identifier",
            "limit"      => "The maximum number of developmental stages to return",
            "sort"       => "The sort field. The valid options are: display, identifier, " .
                            "and term",
            "species_id" => "Restrict the list to the species id",
            "term"       => "Return only the developmental stage(s) matching the term"
        );

        return RestResponse::factory(
            true,
            $description,
            $options
        );
    }
    // --------------------------------------------------------------------------------
    // List the developmental stages
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
        // The restricted search is based on the PI criteria given described below
        // and it is activated by default
        $restrictedSearch = true;
        foreach ( $arguments as $argument => $value ) {
            if ( ($value !== false) &&
                (($value === null) || ($value === "")) ) {
                continue;
            }
            // If a wildcard was found in the value then the operator is changed to "LIKE" and
            // the search restricted by the PI criteria is no longer valid here
            if ( $helper->convertWildcards($value) ) {
                $sqlOperator = "LIKE";
                $restrictedSearch = false;
            } else {
                // Extract any optional operators from the value
                $sqlOperator = "=";
                $helper->extractOperator(
                    $value,
                    $sqlOperator
                );
            }
            switch ( $argument ) {
                case "identifier":
                    if ( $value !== "" ) {
                        $sqlCriteria[] = "identifier " . $sqlOperator . " " . $db->escape($value, true);
                    }
                    break;
                case "id":
                    $sqlCriteria[] = "stage_id = " . $db->escape($value);
                    // If a developmental date identifier needs to be find in the database
                    // then the search restricted by the PI criteria is no longer valid here
                    $restrictedSearch = false;
                    break;
                case "limit":
                    $limit = $helper->constructLimitStr($arguments);
                    break;
                case "sort":
                    $sortInformation = $helper->extractSortInformation($value);
                    foreach ( $sortInformation as $sortColumn => $direction ) {
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
                    $sqlCriteria[] = "species_id = " . $db->escape($value, true);
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
        if ( $restrictedSearch === false ) {
            // All the developmental stages are shown here for the curator(s)
            $sql = <<<SQL
            SELECT stage_id AS id,
                species_id,
                identifier,
                term,
                CONCAT(term, ' (', identifier, ')') AS display
            FROM DevelopmentalStage
            WHERE 
SQL;
        } else {
            // 1) The terms beginning by "embryonic cycle" need to be shortened with "cycle"
            // 2) As there are developmental stages irrelevant according to the criteria of the PI,
            // their terms need to be discriminated by being not shown for the curator(s).
            $sql = <<<SQL
            SELECT stage_id AS id,
                species_id,
                identifier,
                IF (SUBSTRING(term, 1, 15) = 'embryonic cycle',
                    SUBSTRING(term, 11),
                    term) AS term,
                IF (SUBSTRING(term, 1, 15) = 'embryonic cycle',
                    CONCAT(SUBSTRING(term, 11), ' (', identifier, ')'),
                    CONCAT(term, ' (', identifier, ')')) AS display
            FROM DevelopmentalStage
            WHERE term NOT IN (
                'adult age in days',
                'age',
                'biological process',
                'day 0 of adulthood',
                'day 1 of adulthood',
                'day 10 of adulthood',
                'day 11 of adulthood',
                'day 12 of adulthood',
                'day 13 of adulthood',
                'day 14 of adulthood',
                'day 15 of adulthood',
                'day 16 of adulthood',
                'day 17 of adulthood',
                'day 18 of adulthood',
                'day 19 of adulthood',
                'day 2 of adulthood',
                'day 20 of adulthood',
                'day 21 of adulthood',
                'day 22 of adulthood',
                'day 23 of adulthood',
                'day 24 of adulthood',
                'day 25 of adulthood',
                'day 26 of adulthood',
                'day 27 of adulthood',
                'day 28 of adulthood',
                'day 29 of adulthood',
                'day 3 of adulthood',
                'day 30 of adulthood',
                'day 31 of adulthood',
                'day 32 of adulthood',
                'day 33 of adulthood',
                'day 34 of adulthood',
                'day 35 of adulthood',
                'day 36 of adulthood',
                'day 37 of adulthood',
                'day 38 of adulthood',
                'day 39 of adulthood',
                'day 4 of adulthood',
                'day 40 of adulthood',
                'day 41 of adulthood',
                'day 42 of adulthood',
                'day 43 of adulthood',
                'day 44 of adulthood',
                'day 45 of adulthood',
                'day 46 of adulthood',
                'day 47 of adulthood',
                'day 48 of adulthood',
                'day 49 of adulthood',
                'day 5 of adulthood',
                'day 50 of adulthood',
                'day 51 of adulthood',
                'day 52 of adulthood',
                'day 53 of adulthood',
                'day 54 of adulthood',
                'day 55 of adulthood',
                'day 56 of adulthood',
                'day 57 of adulthood',
                'day 58 of adulthood',
                'day 59 of adulthood',
                'day 6 of adulthood',
                'day 60 of adulthood',
                'day 7 of adulthood',
                'day 8 of adulthood',
                'day 9 of adulthood',
                'developmental process',
                'developmental stage',
                'Drosophila life',
                'embryonic cycle',
                'life stage',
                'occurrent') AND 
SQL;
        }
        if ( count($sqlCriteria) === 0 ) {
            $sql .= " 1 = 1 ";
        } else {
            $sql .= implode(" AND ", $sqlCriteria);
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
