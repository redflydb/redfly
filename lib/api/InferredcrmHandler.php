<?php
class InferredcrmHandler implements iEditable
{
    // The entity code of the iCRMs is not defined, yet.
    const EntityCode = "";
    // Return formats used by the search action to present different views of the
    // data:
    // Summary list for searching
    const VIEW_DEFAULT = "default";
    // Full list for displaying individual inferred CRMs
    // (Not applied at the moment)
    const VIEW_FULL = "full";
    // Curator list for searching in the curation tool
    // (Not applied at the moment)
    const VIEW_CURATOR = "curator";
    private $helper = null;
    private $db = null;
    // --------------------------------------------------------------------------------
    // Factory design pattern
    // --------------------------------------------------------------------------------
    public static function factory()
    {
        return new InferredcrmHandler;
    }
    private function __construct()
    {
        $this->db = DbService::factory();
        $this->helper = RestHandlerHelper::factory();
    }
    // --------------------------------------------------------------------------------
    // Query the database for a summary list of inferred CRM(s) based on the criteria
    // provided. It is for the public view.
    // @param $additionalJoins Array containing additional join info
    // @param $sqlCriteria Array containing SQL where clauses (these will be
    //   and-ed together)
    // @param $sqlGroupBy Array containing SQL group by clauses (these will be
    //   and-ed together)
    // @param $sqlOrderBy Array containing SQL order by clauses (these will be
    //   and-ed together)
    // @param $limitStr Optional limit string for paging results
    // @param $options An array containing additional options
    // @returns A RestResponse object containing the search results
    // --------------------------------------------------------------------------------
    private function querySummaryList(
        array $additionalJoins,
        array $sqlCriteria,
        array $sqlGroupBy,
        array $sqlOrderBy,
        $limitStr,
        array $options
    ) {
        $sql = <<<SQL
        SELECT SQL_CALC_FOUND_ROWS icrm.id,
            sfs.scientific_name AS sequence_from_species_scientific_name,
            ais.scientific_name AS assayed_in_species_scientific_name,
            icrm.gene,
            icrm.coordinates,
            icrm.expressions AS anatomical_expressions,
            icrm.expression_identifiers AS anatomical_expression_identifiers
        FROM inferred_crm_read_model icrm
        INNER JOIN Species sfs ON icrm.sequence_from_species_id = sfs.species_id
        INNER JOIN Species ais ON icrm.assayed_in_species_id = ais.species_id
        INNER JOIN Chromosome c ON icrm.chromosome_id = c.chromosome_id
SQL;
        $sqlGroupBy[] = "icrm.id";
        $this->helper->constructQuery(
            $sql,
            $additionalJoins,
            $sqlCriteria,
            $sqlGroupBy,
            $sqlOrderBy,
            $limitStr
        );
        try {
            $queryResult = $this->db->query($sql);
            $results = array();
            while ( $row = $queryResult->fetch_assoc() ) {
                $results[] = $row;
            }
            $rowsNumber = $this->db->query("SELECT FOUND_ROWS()")->fetch_assoc()["FOUND_ROWS()"];
            $response = RestResponse::factory(
                true,
                null,
                $results,
                [],
                200,
                $rowsNumber
            );
        } catch ( Exception $e ) {
            $response = RestResponse::factory(
                false,
                $e->getMessage()
            );
        }

        return $response;
    }
    // --------------------------------------------------------------------------------
    // Return help for the "search" action
    // --------------------------------------------------------------------------------
    public function searchHelp()
    {
        $description = "Return a list of inferred CRM(s) matching the specified criteria.";
        $options = array(
            "anatomical_expression_term"       => "List only entities containing this anatomical expression term",
            "assayed_in_species_id"            => "List only entities that have an \"Assayed In\" species",
            "chr_end"                          => "List only entities having the coordinate end with its predefined error margin " .
                                                  "(most recent coordinate release)",
            "chr_id"                           => "List only entities with this chromosome (internal id)",
            "chr_start"                        => "List only entities having the coordinate start with its predefined error margin " .
                                                  "(most recent coordinate release)",
            "exact_anatomical_expression_term" => "If TRUE list only entities containing the exact expression term, " .
                                                  "if FALSE list entities containing the term and any descendants " .
                                                  "according to the ontology",
            "gene_locus"                       => "List only entities by the gene locus",
            "limit"                            => "Maximum number of entities to return",
            "maximum_sequence_size"            => "List only entities with a sequence of this size or less",
            "sequence_from_species_id"         => "List only entities that have a \"Sequence From\" species",
            "sort"                             => "Sort field. Valid options are: gene and chr",
            "state"                            => "List only entities having such a state",
            "view"                             => "Set the view to use for the returned results. " .
                                                  "Valid values are \"default\", \"full\", and \"curator\""
        );

        return RestResponse::factory(
            true,
            $description,
            $options
        );
    }
    // --------------------------------------------------------------------------------
    // Search the entities
    // --------------------------------------------------------------------------------
    public function searchAction(
        array $arguments,
        array $postData = null
    ) {
        $additionalJoins = array();
        $sqlCriteria = array();
        $sqlGroupBy = array();
        $sqlOrderBy = array();
        $limitStr = "";
        $queryOptions = array();
        $coordinateStart = 0;
        $coordinateEnd = 0;
        // The anatomical expression identifier provided
        $anatomicalExpressionIdentifier = "";
        // Match the exact expression identifier, if provided
        $exactAnatomicalExpressionIdentifier = false;
        $returnFormat = self::VIEW_DEFAULT;
        $response = null;
        foreach ( $arguments as $argument => $value ) {
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
            if ( (! is_array($value)) &&
                $this->helper->convertWildcards($value) ) {
                $sqlOperator = "LIKE";
            }
            switch ( $argument ) {
                case "anatomical_expression_identifier":
                    $anatomicalExpressionIdentifier = $value;
                    break;
                case "assayed_in_species_id":
                    $sqlCriteria[] = "icrm.assayed_in_species_id = " . $value;
                    break;
                case "auditor_id":
                    // Not applied here since such inferred CRMs lack any auditor ID.
                    $sqlCriteria[] = "0";
                    break;
                case "biological_process_identifier":
                    // Not applied here since such inferred CRMs lack any biological process.
                    $sqlCriteria[] = "0";
                    break;
                case "chr_end":
                    if ( is_numeric($value) ) {
                        $coordinateEnd = (int)$value;
                    }
                    break;
                case "chr_id":
                    if ( is_numeric($value) ) {
                        $sqlCriteria[] = "icrm.chromosome_id = " . $value;
                    }
                    break;
                case "chr_start":
                    if ( is_numeric($value) ) {
                        $coordinateStart = (int)$value;
                    }
                    break;
                case "components":
                    if ( $value !== "%" ) {
                        $sqlCriteria[] = str_replace(
                            "_",
                            "\_",
                            "LOWER(icrm.components) LIKE LOWER(" . $this->db->escape($value, true) . ")"
                        );
                    }
                    break;
                case "curator_id":
                    // Not applied here since such inferred CRMs lack any curator ID.
                    $sqlCriteria[] = "0";
                    break;
                case "date_added":
                    // Not applied here since such inferred CRMs lack any "date_added" data and
                    // they are always created on the last REDfly release data.
                    break;
                case "developmental_stage_identifier":
                    // Not applied here since such inferred CRMs lack any developmental stage.
                    $sqlCriteria[] = "0";
                    break;
                case "evidence_id":
                    // As any evidence identifier is not applied for inferred CRMs,
                    // a zero is given so that nada is returned from the search consult.
                    $sqlCriteria[] = "0";
                    break;
                case "exact_anatomical_expression_identifier":
                    $exactAnatomicalExpressionIdentifier = $this->helper->convertValueToBool($value);
                    break;
                case "fbtp_identifier":
                    // As any FlyBase transgenic construct identifier is not applied for inferred CRMs,
                    // a zero is given so that nada is returned from the search consult.
                    $sqlCriteria[] = "0";
                    break;
                case "five_prime":
                    // As any 5' to gene is not applied for inferred CRMs,
                    // a zero is given so that nada is returned from the search consult.
                    if ( $this->helper->convertValueToBool($value) ) {
                        $sqlCriteria[] = "0";
                    }
                    break;
                case "gene_identifier":
                    // As any gene identifier is not applied for inferred CRMs,
                    // a zero is given so that nada is returned from the search consult.
                    $sqlCriteria[] = "0";
                    break;
                case "gene_id":
                    // As any gene internal identifier is not applied for inferred CRMs,
                    // a zero is given so that nada is returned from the search consult.
                    $sqlCriteria[] = "0";
                    break;
                case "gene_search":
                    // The first value, "by_locus", is set by default and the other value, "by_name",
                    // does not apply to this entity, inferred CRM
                    if ( $value === "true" ) {
                        if ( $arguments["gene_locus"] === "" ) {
                            $sqlCriteria[] = "0";
                        } else {
                            $sqlCriteria[] = "icrm.gene_locus LIKE '%" .
                                str_replace(
                                    "_",
                                    "\_",
                                    $arguments["gene_locus"]
                                ) . "%'";
                        }
                    }
                    break;
                case "in_exon":
                    // As any in exon is not applied for inferred CRMs,
                    // a zero is given so that nada is returned from the search consult.
                    if ( $this->helper->convertValueToBool($value) ) {
                        $sqlCriteria[] = "0";
                    }
                    break;
                case "in_intron":
                    // As any in intron is not applied for inferred CRMs,
                    // a zero is given so that nada is returned from the search consult.
                    if ( $this->helper->convertValueToBool($value) ) {
                        $sqlCriteria[] = "0";
                    }
                    break;
                case "include_range":
                    // As any gene identifier is not applied for inferred CRMs, a zero is given so that
                    // nothing is returned from the search consult.
                    // It is always true by default from the front side.
                    if ( $value === "true" ) {
                        // The search interval range is 10000 by default from the front side.
                        // So if an interval different from 10000, then it must return nothing
                        if ( $arguments["search_range"] <> "10000" ) {
                            $sqlCriteria[] = "0";
                        }
                    }
                    break;
                case "last_audit":
                    // Not applied here since such inferred CRMs are never audited.
                    $sqlCriteria[] = "0";
                    break;
                case "last_update":
                    // Not applied here since such inferred CRMs are never updated, and
                    // they are always re-created on the last REDfly release data.
                    break;
                case "limit":
                    $limitStr = $this->helper->constructLimitStr($arguments);
                    break;
                case "maximum_sequence_size":
                    if ( is_numeric($value) ) {
                        $sqlCriteria[] = "icrm.size <= " . $value;
                    }
                    break;
                case "name":
                    // As any element name is not applied for inferred CRMs,
                    // a zero is given so that nada is returned from the search consult.
                    $sqlCriteria[] = "0";
                    break;
                case "pubmed_id":
                    // As any Pubmed ID is not applied for inferred CRMs,
                    // a zero is given so that nada is returned from the search consult.
                    $sqlCriteria[] = "0";
                    break;
                case "redfly_id":
                    // Not applied here since such inferred CRMs lack any REDfly identifier.
                    $sqlCriteria[] = "0";
                    break;
                case "sequence_from_species_id":
                    $sqlCriteria[] = "icrm.sequence_from_species_id = " . $value;
                    break;
                case "sort":
                    $sortInformation = $this->helper->extractSortInformation($value);
                    foreach ( $sortInformation as $sortColumn => $direction ) {
                        switch ( $sortColumn ) {
                            case "chr":
                                $sqlOrderBy[] = "icrm.chromosome " . $direction;
                                break;
                            case "coordinates":
                                $sqlOrderBy[] = "icrm.coordinates " . $direction;
                                break;
                            case "gene":
                                $sqlOrderBy[] = "icrm.gene " . $direction;
                                break;
                            default:
                                break;
                        }
                    }
                    break;
                case "state":
                    // Not applied in both public and curator views since such inferred CRMs always have their
                    // state as "current"
                    $sqlCriteria[] = "0";
                    break;
                case "three_prime":
                    // As any 3' to gene is not applied for inferred CRMs,
                    // a zero is given so that nada is returned from the search consult.
                    if ( $this->helper->convertValueToBool($value) ) {
                        $sqlCriteria[] = "0";
                    }
                    break;
                case "view":
                    $returnFormat = trim($value);
                    break;
                default:
                    break;
            }
        }
        // Include the criteria for searching anyone from the coordinate extremes
        $inferredCrmErrorMargin = $GLOBALS["options"]->inferred_crm->error_margin;
        if ( ($coordinateStart !== 0) &&
            ($coordinateEnd !== 0) ) {
            $sqlCriteria[] = ($coordinateStart - $inferredCrmErrorMargin) . " <= icrm.current_start";
            $sqlCriteria[] = "icrm.current_end <= " . ($coordinateEnd + $inferredCrmErrorMargin);
        } else {
            if ( ($coordinateStart !== 0) &&
                ($coordinateEnd === 0) ) {
                $sqlCriteria[] = ($coordinateStart - $inferredCrmErrorMargin) . " <= icrm.current_start";
                if ( ($coordinateStart === 0) &&
                    ($coordinateEnd !== 0) ) {
                    $sqlCriteria[] = "icrm.current_end <= " . ($coordinateEnd + $inferredCrmErrorMargin);
                }
            }
        }
        // Search the anatomical expression identifier provided
        // (only appplied for the Drosophila melanogaster species at the moment)
        if ( $anatomicalExpressionIdentifier !== "" ) {
            if ( $exactAnatomicalExpressionIdentifier ) {
                // Search only the anatomical expression identifier
                $sqlCriteria[] = "icrm.expression_identifiers LIKE '%" .
                    $anatomicalExpressionIdentifier . "%'";
            } else {
                // Search the anatomical expression identifier and its descendant
                // identifiers provided by the anatomy ontology
                $arguments = array("identifier" => $anatomicalExpressionIdentifier);
                $anatomyOntologyHandler = AnatomyontologyHandler::factory();
                $anatomyOntologyResponse = $anatomyOntologyHandler->listAction($arguments);
                $anatomicalExpressionIdentifiersList = array();
                foreach ( $anatomyOntologyResponse->results() as $result ) {
                    $anatomicalExpressionIdentifiersList[] =  "icrm.expression_identifiers LIKE '%" .
                        $result["id"] . "%'";
                }
                $sqlCriteria[] = "(" . implode(" OR ", $anatomicalExpressionIdentifiersList) . ")";
            }
        }
        switch ( $returnFormat ) {
            // Not applied.
            case self::VIEW_CURATOR:
                $response = null;
                break;
            // Not applied.
            // For the public view of an inferred cis-regulatory module chosen
            case self::VIEW_FULL:
                $response = null;
                break;
            // For the public view of a list of inferred cis-regulatory modules
            default:
                $response = $this->querySummaryList(
                    $additionalJoins,
                    $sqlCriteria,
                    $sqlGroupBy,
                    $sqlOrderBy,
                    $limitStr,
                    $queryOptions
                );
                break;
        }

        return $response;
    }
    // --------------------------------------------------------------------------------
    // Not editable at the moment.
    // Just keeping it for the integrity of the "iEditable" interface.
    // --------------------------------------------------------------------------------
    public function saveAction(
        array $arguments,
        array $postData = null
    ) {
    }
}
