<?php
class PredictedcrmHandler implements iEditable
{
    // Code to be used when generating a REDfly ID for this entity
    // (e.g. PCRM:00000000.000)
    const EntityCode = "PCRM";
    // Return formats used by the search action to present different views of the
    // data:
    // Summary list for searching
    const VIEW_DEFAULT = "default";
    // Full list for displaying individual predicted CRMs
    const VIEW_FULL = "full";
    // Curator list for searching in the curation tool
    const VIEW_CURATOR = "curator";
    private $helper = null;
    private $db = null;
    // --------------------------------------------------------------------------------
    // Factory design pattern
    // --------------------------------------------------------------------------------
    public static function factory()
    {
        return new PredictedcrmHandler;
    }
    private function __construct()
    {
        $this->db = DbService::factory();
        $this->helper = RestHandlerHelper::factory();
    }
    // --------------------------------------------------------------------------------
    // Helper to construct the REDfly identifier from the entity, the version, and the
    // database identifier present in the row and add the REDfly identifier to the row.
    // The entity and version will be removed of successful and the "redfly_id"
    // property of the row will be set.
    // @param $row Reference to a query result row
    // --------------------------------------------------------------------------------
    private function addRedflyIdToResultRow(&$row)
    {
        if ( (! isset($row["entity_id"])) &&
            (! isset($row["version"])) &&
            (! isset($row["id"])) ) {
            $errorMessage = "";
            if ( ! isset($row["entity_id"]) ) {
                $errorMessage = "Result does not contain \"entity_id\"";
            }
            if ( ! isset($row["version"]) ) {
                if ( $errorMessage ===  "" ) {
                    $errorMessage = "Result does not contain \"version\"";
                } else {
                    $errorMessage += PHP_EOL . "Result does not contain \"version\"";
                }
            }
            if ( ! isset($row["id"]) ) {
                if ( $errorMessage ===  "" ) {
                    $errorMessage = "Result does not contain \"id\"";
                } else {
                    $errorMessage += PHP_EOL . "Result does not contain \"id\"";
                }
            }
            throw new Exception($errorMessage);
        }
        $row["redfly_id"] = $this->helper->entityId(
            self::EntityCode,
            $row["entity_id"],
            $row["version"],
            $row["id"]
        );
        $row["redfly_id_unversioned"] = $this->helper->unversionedEntityId(
            self::EntityCode,
            $row["entity_id"],
            $row["id"]
        );
        unset($row["entity_id"]);
        unset($row["version"]);

        return true;
    }
    // --------------------------------------------------------------------------------
    // Helper to parse a REDfly identifier into the type, the entity number, and the
    // version and generate an SQL fragment that can be used to search for that
    // entity.
    // @param $redflyId A valid REDfly identifier
    // @returns An SQL fragment for querying the entity matching the supplied
    //   REDfly identifier
    // --------------------------------------------------------------------------------
    private function redflyIdToSql($redflyId)
    {
        $type = $entityId = $version = $dbId = null;
        $retval = $this->helper->parseEntityId(
            $redflyId,
            $type,
            $entityId,
            $version,
            $dbId
        );
        if ( ($retval === false) ||
            ( $type !== self::EntityCode ) ) {
            throw new Exception("Not a predicted CRM id: \"$redflyId\"");
        }
        if ( $dbId !== null ) {
            $sqlFragment = "( pcrm.predicted_crm_id = $dbId )";
        } elseif ( $version !== null ) {
            $sqlFragment = "( pcrm.entity_id = $entityId AND pcrm.version = $version )";
        } else {
            $sqlFragment = "( pcrm.entity_id = $entityId AND pcrm.state = 'current' )";
        }

        return $sqlFragment;
    }
    // --------------------------------------------------------------------------------
    // Query the database for a summary list of predicted CRM(s) based on the criteria
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
        SELECT SQL_CALC_FOUND_ROWS pcrm.predicted_crm_id AS id,
            sfs.scientific_name AS sequence_from_species_scientific_name,
            pcrm.name,
            CONCAT('RFPCRM', ':', LPAD(CAST(pcrm.entity_id AS CHAR), 10, '0'), '.' , LPAD(CAST(pcrm.version AS CHAR), 3, '0')) AS redfly_id,
            CONCAT(c.name, ':', pcrm.current_start, '..', pcrm.current_end) AS coordinates,
            pcrm.gene_locus,
            pcrm.pubmed_id
        FROM PredictedCRM pcrm
        INNER JOIN Species sfs ON pcrm.sequence_from_species_id = sfs.species_id
        INNER JOIN Chromosome c ON pcrm.chromosome_id = c.chromosome_id
SQL;
        $sqlGroupBy[] = "pcrm.predicted_crm_id";
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
    // Query the database for a full list of predicted CRM(s) based on the criteria
    // provided.
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
    private function queryFullList(
        array $additionalJoins,
        array $sqlCriteria,
        array $sqlGroupBy,
        array $sqlOrderBy,
        $limitStr,
        array $options = array()
    ) {
        $sql = <<<SQL
        SELECT sfs.species_id AS sequence_from_species_id,
            sfs.short_name AS sequence_from_species_short_name,
            sfs.scientific_name AS sequence_from_species_scientific_name,
            pcrm.predicted_crm_id AS id,
            pcrm.name,
            pcrm.entity_id,
            pcrm.version,
            pcrm.current_genome_assembly_release_version AS release_version,
            pcrm.current_start AS start,
            pcrm.current_end AS end,
            pcrm.archived_genome_assembly_release_versions,
            pcrm.archived_starts,
            pcrm.archived_ends,
            pcrm.notes,
            pcrm.pubmed_id,
            pcrm.sequence,
            pcrm.gene_identifiers,
            pcrm.gene_locus,
            c.name AS chromosome,
            e.term AS evidence_term,
            IF(ISNULL(pcrm.evidence_subtype_id), '', es.term) AS evidence_subtype_term,
            ss.term AS sequence_source,
            pcrm.date_added,
            CONCAT(curator.first_name, ' ', curator.last_name) AS curator_full_name,
            pcrm.last_update,
            IF(ISNULL(pcrm.auditor_id), '', CONCAT(auditor.first_name, ' ', auditor.last_name)) AS auditor_full_name,
            pcrm.last_audit,
            cite.contents,
            sfs.public_database_names,
            sfs.public_database_links,
            sfs.public_browser_names,
            sfs.public_browser_links
        FROM PredictedCRM pcrm
        INNER JOIN Species sfs ON pcrm.sequence_from_species_id = sfs.species_id
        INNER JOIN Chromosome c ON pcrm.chromosome_id = c.chromosome_id
        INNER JOIN EvidenceTerm e ON pcrm.evidence_id = e.evidence_id
        LEFT OUTER JOIN EvidenceSubtypeTerm es ON pcrm.evidence_subtype_id = es.evidence_subtype_id
        INNER JOIN SequenceSourceTerm ss ON pcrm.sequence_source_id = ss.source_id
        INNER JOIN Users curator ON pcrm.curator_id = curator.user_id
        LEFT OUTER JOIN Users auditor ON pcrm.auditor_id = auditor.user_id
        INNER JOIN Citation cite ON pcrm.pubmed_id = cite.external_id AND
            cite.citation_type = 'PUBMED'
SQL;
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
                $tmpRow = $row;
                $tmpRow["redfly_id"] = $this->helper->entityId(
                    self::EntityCode,
                    $row["entity_id"],
                    $row["version"],
                    $row["id"]
                );
                $tmpRow["redfly_id_unversioned"] = $this->helper->unversionedEntityId(
                    self::EntityCode,
                    $row["entity_id"],
                    $row["id"]
                );
                $tmpRow["coordinates"] = $this->helper->formatCoordinates(
                    $row["chromosome"],
                    $row["start"],
                    $row["end"]
                );
                $archivedStarts = explode(",", $row["archived_starts"]);
                $archivedEnds = explode(",", $row["archived_ends"]);
                $archivedCoordinatesNumber = count($archivedStarts);
                $tmpRow["archived_coordinates"] = "";
                for ( $index = 0; $index < $archivedCoordinatesNumber; $index++ ) {
                    $archivedCoordinates = $this->helper->formatCoordinates(
                        $row["chromosome"],
                        $archivedStarts[$index],
                        $archivedEnds[$index]
                    );
                    if ( $tmpRow["archived_coordinates"] === "" ) {
                        $tmpRow["archived_coordinates"] = $archivedCoordinates;
                    } else {
                        $tmpRow["archived_coordinates"] .= "," . $archivedCoordinates;
                    }
                }
                // Strip spaces out of the sequence
                $tmpRow["sequence"] = preg_replace(
                    "/\s+/",
                    "",
                    $tmpRow["sequence"]
                );
                $tmpRow["previous_curator_full_names"] = $this->helper->getPreviousCurators(
                    self::EntityCode,
                    $row["entity_id"],
                    $row["curator_full_name"]
                );
                unset($tmpRow["entity_id"]);
                unset($tmpRow["version"]);
                unset($tmpRow["archived_starts"]);
                unset($tmpRow["archived_ends"]);
                $results[] = $tmpRow;
            }
            $response = RestResponse::factory(
                true,
                null,
                $results
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
    // Query the database for a predicted CRM list based on the criteria provided.
    // @param $additionalJoins Array containing additional join info
    // @param $sqlCriteria Array containing SQL where clauses (these will be
    //   and-ed together)
    // @param $sqlGroupBy Array containing SQL group by clauses (these will be
    //   and-ed together)
    // @param $sqlOrderBy Array containing SQL order by clauses (these will be
    //   and-ed together)
    // @param $limitStr Optional limit string for paging results
    // @returns A RestResponse object containing the search results
    // --------------------------------------------------------------------------------
    private function querySinglePredictedCRM(
        array $additionalJoins,
        array $sqlCriteria,
        array $sqlGroupBy,
        array $sqlOrderBy,
        $limitStr
    ) {
        $pcrmId = null;
        $sql = <<<SQL
        SELECT sfs.species_id AS sequence_from_species_id,
            sfs.short_name AS sequence_from_species_short_name,
            sfs.scientific_name AS sequence_from_species_scientific_name,
            pcrm.predicted_crm_id AS id, 
            pcrm.name, 
            pcrm.entity_id, 
            pcrm.version,
            pcrm.state,
            pcrm.chromosome_id, 
            chr.name AS chromosome,
            pcrm.evidence_id, 
            e.term AS evidence_term,
            pcrm.evidence_subtype_id,
            IF(ISNULL(pcrm.evidence_subtype_id), '', es.term) AS evidence_subtype_term,
            pcrm.sequence_source_id,
            sst.term AS sequence_source_term,
            pcrm.pubmed_id,
            pcrm.notes,
            pcrm.sequence, 
            pcrm.size, 
            pcrm.current_start AS start, 
            pcrm.current_end AS end,
            cite.contents AS citation, 
            cite.author_email,
            pcrm.date_added,
            DATE_FORMAT(pcrm.date_added, '%M %D, %Y at %l:%i:%s%p') AS date_added_formatted,
            curator.user_id AS curator_id,
            CONCAT(curator.first_name, ' ', curator.last_name) AS curator_full_name,
            pcrm.last_update,
            DATE_FORMAT(pcrm.last_update, '%M %D, %Y at %l:%i:%s%p') AS last_update_formatted,
            auditor.user_id AS auditor_id,
            IF(ISNULL(pcrm.auditor_id), '', CONCAT(auditor.first_name, ' ', auditor.last_name)) AS auditor_full_name,
            pcrm.last_audit,
            DATE_FORMAT(pcrm.last_audit, '%M %D, %Y at %l:%i:%s%p') AS last_audit_formatted,
            pcrm.archive_date,
            DATE_FORMAT(pcrm.archive_date, '%M %D, %Y at %l:%i:%s%p') AS archive_date_formatted
        FROM PredictedCRM pcrm
        INNER JOIN Species sfs ON pcrm.sequence_from_species_id = sfs.species_id
        INNER JOIN Chromosome c ON pcrm.chromosome_id = c.chromosome_id
        INNER JOIN EvidenceTerm e ON pcrm.evidence_id = e.evidence_id
        LEFT OUTER JOIN EvidenceSubtypeTerm es ON pcrm.evidence_subtype_id = es.evidence_subtype_id
        INNER JOIN SequenceSourceTerm sst ON pcrm.sequence_source_id = sst.source_id
        INNER JOIN Citation cite ON pcrm.pubmed_id = cite.external_id AND
            cite.citation_type = 'PUBMED'
        INNER JOIN Users curator ON pcrm.curator_id = curator.user_id
        LEFT OUTER JOIN Users auditor ON pcrm.auditor_id = auditor.user_id
SQL;
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
            $row = $queryResult->fetch_assoc();
            // Decode HTML entities
            $row["citation"] = html_entity_decode($row["citation"]);
            // Strip spaces out of the sequence
            $row["sequence"] = preg_replace(
                "/\s+/",
                "",
                $row["sequence"]
            );
            $this->addRedflyIdToResultRow($row);
            $pcrmId = $row["id"];
            if ( $pcrmId !== null ) {
                $sql = <<<SQL
                SELECT pcrm.predicted_crm_id,
                    pcrm.pubmed_id,
                    et.identifier, 
                    et.term_id AS id, 
                    et.term
                FROM PredictedCRM pcrm
                INNER JOIN PredictedCRM_has_Expression_Term map ON pcrm.predicted_crm_id = map.predicted_crm_id
                INNER JOIN ExpressionTerm et ON map.term_id = et.term_id
SQL;
                $sqlCriteria = array("pcrm.predicted_crm_id = $pcrmId");
                $sqlOrderBy = array("et.term");
                $this->helper->constructQuery(
                    $sql,
                    array(),
                    $sqlCriteria,
                    $sqlGroupBy,
                    $sqlOrderBy,
                    $limitStr
                );
                $queryResult = $this->db->query($sql);
                $anatomicalExpressionResults = array();
                while ( $anatomicalExpressionRow = $queryResult->fetch_assoc() ) {
                    $anatomicalExpressionResults[] = $anatomicalExpressionRow;
                }
                $row["expression_terms"] = json_encode($anatomicalExpressionResults);
                $predictedCrmTsHelper = new CrmSegmentTsHelper();
                $stagingDataResults = $predictedCrmTsHelper->getAllData($pcrmId);
                $row["staging_data"] = json_encode($stagingDataResults);
            }
        } catch ( Exception $e ) {
            return RestResponse::factory(
                false,
                $e->getMessage()
            );
        }

        return RestResponse::factory(
            true,
            null,
            array($row)
        );
    }
    // --------------------------------------------------------------------------------
    // Query the database for a list of predicted CRM summaries based on the
    // criteria provided. This search is tailored to the curator search tool.
    // @param $additionalJoins Array containing additional join info
    // @param $sqlCriteria Array containing SQL where clauses (these will be
    //   and-ed together)
    // @param $sqlGroupBy Array containing SQL group by clauses (these will be
    //   and-ed together)
    // @param $sqlOrderBy Array containing SQL order by clauses (these will be
    //   and-ed together)
    // @returns A RestResponse object containing the search results
    // --------------------------------------------------------------------------------
    private function queryCuratorSummaryList(
        array $additionalJoins,
        array $sqlCriteria,
        array $sqlGroupBy,
        array $sqlOrderBy
    ) {
        // Determine if a state is specified in the list of SQL criteria.
        // This is used to return the results of entities with the
        // specified state or return a list of the last entity versions
        // containing "approval", "approved", "archived", "current",
        // "deleted", and "editing" states if there is no state selected.
        // A criteria clause such as "pcrm.state = 'current'" cannot be used
        // directly because an "editing" version of a "current" predicted CRM
        // may exist and so the "current" version should not be returned.
        // Likewise for the "approval", "approved", "archived", and
        // "deleted" versions.
        $pattern = '/
            ^           # Start
            \s*         # Possible whitespace
            pcrm\.state # state column
            \s*         # Possible whitespace
            =           # Literal equal sign
            \s*         # Possible whitespace
            ([\'"])     # Opening quote
            (\w+)       # state value
            \1          # Closing quote
            \s*         # Possible whitespace
            $           # End
            /x';
        $stateValue = "";
        foreach ( $sqlCriteria as $key => $criteria ) {
            if ( preg_match(
                $pattern,
                $criteria,
                $matches
            ) ) {
                // If more than one state is found, return an empty array since
                // only a state is accepted to be included in the SQL criteria
                if ( $stateValue !== "" ) {
                    return RestResponse::factory(
                        true,
                        null,
                        array()
                    );
                }
                $stateValue = $matches[2];
            }
        }
        // Construct the query using any optional join conditions, SQL criteria,
        // order, and limit information. For the null state value, the curator
        // search will display the last version of all entities.
        // Notice that an entity can have more than one version with the same
        // archived state while the other states must be unique inside the
        // existing versions of such an entity.
        $sqlBase = <<<SQL
        SELECT pcrm.predicted_crm_id AS id,
            pcrm.entity_id,
            pcrm.version,
            pcrm.name,
            pcrm.state,
            sfs.species_id AS sequence_from_species_id,
            sfs.short_name AS sequence_from_species_short_name,
            sfs.scientific_name AS sequence_from_species_scientific_name,
            c.name AS chromosome,
            pcrm.current_genome_assembly_release_version AS release_version,
            pcrm.current_start AS start,
            pcrm.current_end AS end,
            pcrm.pubmed_id AS pmid,
            pcrm.curator_id,
            CONCAT(curator.first_name, ' ', curator.last_name) AS curator_full_name,
            DATE_FORMAT(pcrm.date_added, '%Y-%m-%d %h%p:%i:%s') AS date_added,
            DATE_FORMAT(pcrm.last_update, '%Y-%m-%d %h%p:%i:%s') AS last_update,
            pcrm.auditor_id,
            IF(ISNULL(pcrm.auditor_id), '', CONCAT(auditor.first_name, ' ', auditor.last_name)) AS auditor_full_name,
            IF(ISNULL(pcrm.auditor_id), '', DATE_FORMAT(pcrm.last_audit, '%Y-%m-%d %h%p:%i:%s')) AS last_audit,
            DATE_FORMAT(pcrm.archive_date, '%Y-%m-%d %h%p:%i:%s') AS archive_date
SQL;
        switch ( $stateValue ) {
            case "":
                // 1) All the new entities (still unversioned)
                //    which entity identifier is null and
                // 2) the latest versions, that is, internal identifiers
                //    (rc_id), of all the entities which entity identifier
                //    is not null
                $sql = $sqlBase . <<<SQL
                FROM (SELECT predicted_crm_id,
                          entity_id
                      FROM PredictedCRM
                      WHERE entity_id IS NULL
                      UNION
                      SELECT MAX(predicted_crm_id) AS predicted_crm_id,
                          entity_id
                      FROM PredictedCRM
                      WHERE entity_id IS NOT NULL
                      GROUP BY entity_id) AS optimized_pcrm
                INNER JOIN PredictedCRM pcrm ON optimized_pcrm.predicted_crm_id = pcrm.predicted_crm_id
                INNER JOIN Species sfs ON pcrm.sequence_from_species_id = sfs.species_id
                INNER JOIN Chromosome c ON pcrm.chromosome_id = c.chromosome_id
                INNER JOIN Users curator ON pcrm.curator_id = curator.user_id
                LEFT OUTER JOIN Users auditor ON pcrm.auditor_id = auditor.user_id
SQL;
                break;
            // There can be more than one version of a common entity
            // having the same "archived" state so the latest archived
            // version is shown from all the archived ones of each entity
            // although it will be never edited at any moment.
            case "archived":
                $sql = $sqlBase . <<<SQL
                FROM (SELECT entity_id,
                          MAX(version) AS last_archived_version
                      FROM PredictedCRM
                      WHERE state = 'archived'
                      GROUP BY entity_id) AS optimized_pcrm
                INNER JOIN PredictedCRM pcrm ON optimized_pcrm.entity_id = pcrm.entity_id AND
                    optimized_pcrm.last_archived_version = pcrm.version
                INNER JOIN Species sfs ON pcrm.sequence_from_species_id = sfs.species_id
                INNER JOIN Chromosome c ON pcrm.chromosome_id = c.chromosome_id
                INNER JOIN Users curator ON pcrm.curator_id = curator.user_id
                LEFT OUTER JOIN Users auditor ON pcrm.auditor_id = auditor.user_id
SQL;
                break;
            // These states are unique and must not be repeated at any version
            // of a common entity.
            // Note: it is supposed that any predicted CRM can not be edited
            // at the moment due to its computational generation.
            case "approval":
            case "approved":
            case "current":
            case "editing":
            case "deleted":
                $sql = $sqlBase . <<<SQL
                FROM PredictedCRM pcrm
                INNER JOIN Species sfs ON pcrm.sequence_from_species_id = sfs.species_id
                INNER JOIN Chromosome c ON pcrm.chromosome_id = c.chromosome_id
                INNER JOIN Users curator ON pcrm.curator_id = curator.user_id
                LEFT OUTER JOIN Users auditor ON pcrm.auditor_id = auditor.user_id
SQL;
                break;
            default:
                return RestResponse::factory(
                    false,
                    "Unknown state: " . $stateValue
                );
        }
        $this->helper->constructQuery(
            $sql,
            $additionalJoins,
            $sqlCriteria,
            $sqlGroupBy,
            $sqlOrderBy,
            ""
        );
        try {
            $queryResults = $this->db->query($sql);
        } catch ( Exception $e ) {
            return RestResponse::factory(
                false,
                $e->getMessage()
            );
        }
        $results = array();
        foreach ( $queryResults as $row ) {
            $row["redfly_id"] = $this->helper->entityId(
                self::EntityCode,
                $row["entity_id"],
                $row["version"],
                $row["id"]
            );
            $row["redfly_id_unversioned"] = $this->helper->unversionedEntityId(
                self::EntityCode,
                $row["entity_id"],
                $row["id"]
            );
            $row["coordinates"] = $this->helper->formatCoordinates(
                $row["chromosome"],
                $row["start"],
                $row["end"]
            );
            unset($row["id"]);
            unset($row["version"]);
            unset($row["chromosome"]);
            unset($row["start"]);
            unset($row["end"]);
            $results[] = $row;
        }

        return RestResponse::factory(
            true,
            null,
            $results
        );
    }
    // --------------------------------------------------------------------------------
    // Return help for the "search" action
    // --------------------------------------------------------------------------------
    public function searchHelp()
    {
        $description = "Return a list of predicted CRM(s) matching the specified criteria.";
        // Note: the gene_locus, name, pmid, and state are used only for the user view...
        // So sort all these fields basing on the following list as TD
        $options = array(
            "anatomical_expression_identifier"       => "List only entities containing this anatomical expression term (FBbt)",
            "auditor_id"                             => "List only entities audited by the auditor",
            "biological_process_identifier"          => "List only entities containing this biological process term (GO)",
            "chr_end"                                => "List only entities having the coordinate end with its predefined error margin " .
                                                        "(most recent coordinate release)",
            "chr_id"                                 => "List only entities with this chromosome (internal id)",
            "chr_start"                              => "List only entities having the coordinate start with its predefined error margin " .
                                                        "(most recent coordinate release)",
            "curator_id"                             => "List only entities curated by the curator",
            "date_added"                             => "List only entities addedd based on this date " .
                                                        "(UNIX timestamp in seconds since epoch)",
            "developmental_stage_identifier"         => "List only entities containing this developmental stage identifier",
            "enhancer_attribute_excluded"            => "List only entities not having any enhancer attribute",
            "enhancer_attribute_included"            => "List only entities having an enhancer attribute, at least",
            "evidence_id"                            => "List only entities with this evidence term id",
            "evidence_subtype_id"                    => "List only entities with this evidence subtype term id",
            "exact_anatomical_expression_identifier" => "If TRUE list only entities containing the exact anatomical expression identifier, " .
                                                        "if FALSE list entities containing the term and any descendants " .
                                                        "according to the ontology",
            "exact_biological_process_identifier"    => "If TRUE list only entities containing the exact biological process identifier, " .
                                                        "if FALSE list entities containing the identifier and any descendants " .
                                                        "according to the ontology",
            "exact_developmental_stage_identifier"   => "If TRUE list only entities containing the exact developmental stage identifier, " .
                                                        "if FALSE list entities containing the identifier and any descendants " .
                                                        "according to the ontology",
            "gene_locus"                             => "List only entities by the gene locus",
            "gene_search"                            => "It must be TRUE so that the gene_locus argument can be accepted",
            "in_exon"                                => "List only entities that overlap or are included within an exon",
            "in_intron"                              => "List only entities that overlap or are included within an intron",
            "include_range"                          => "It must be TRUE so that the search_range argument can be rejected",
            "last_audit"                             => "List only entities last auditor audited based on this date " .
                                                        "(UNIX timestamp in seconds since epoch)",
            "last_update"                            => "List only entities last curator updated based on this date " .
                                                        "(UNIX timestamp in seconds since epoch)",
            "limit"                                  => "Maximum number of entities to return",
            "maximum_sequence_size"                  => "List only entities with a sequence of this size or less",
            "name"                                   => "List only entities that match this name",
            "pubmed_id"                              => "List only entities that match this Pubmed Id as primary or secondary",
            "redfly_id"                              => "The REDfly identifier (or an array of them) for the predicted CRM " .
                                                        "(e.g., RFPCRM:0000014.001)",
            "sequence_from_species_id"               => "List only entities that have a \"Sequence From\" species",
            "silencer_attribute_excluded"            => "List only entities not having any silencer attribute",
            "silencer_attribute_included"            => "List only entities having an silencer attribute, at least",
            "sort"                                   => "Sort field. Valid options are: name, gene, pmid, chr",
            "state"                                  => "List only entities having such a state",
            "view"                                   => "Set the view to use for the returned results. " .
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
        $pubmedId = "";
        $coordinateStart = 0;
        $coordinateEnd = 0;
        // Enhancer attribute included
        $enhancerAttributeIncluded = false;
        // Silencer attribute included
        $silencerAttributeIncluded = false;
        // Enhancer attribute excluded
        $enhancerAttributeExcluded = false;
        // Silencer attribute excluded
        $silencerAttributeExcluded = false;
        // The anatomical expression identifier provided
        $anatomicalExpressionIdentifier = "";
        // Match the exact anatomical expression identifier, if provided
        $exactAnatomicalExpressionIdentifier = false;
        // The developmental stage identifier provided
        $developmentalStageIdentifier = "";
        // Match the exact developmental stage identifier, if provided
        $exactDevelopmentalStageIdentifier = false;
        // The biological process identifier provided
        $biologicalProcessIdentifier = "";
        // Match the exact biological process identifier, if provided
        $exactBiologicalProcessIdentifier = false;
        $redflyIdProvided = false;
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
                case "auditor_id":
                    $sqlCriteria[] = "pcrm.auditor_id = " . $value;
                    break;
                case "biological_process_identifier":
                    $biologicalProcessIdentifier = $value;
                    break;
                case "chr_end":
                    if ( is_numeric($value) ) {
                        $coordinateEnd = (int)$value;
                    }
                    break;
                case "chr_id":
                    if ( is_numeric($value) ) {
                        $sqlCriteria[] = "pcrm.chromosome_id = " . $value;
                    }
                    break;
                case "chr_start":
                    if ( is_numeric($value) ) {
                        $coordinateStart = (int)$value;
                    }
                    break;
                case "curator_id":
                    $sqlCriteria[] = "pcrm.curator_id = " . $value;
                    break;
                case "date_added":
                    $sqlCriteria[] = "pcrm.date_added " . $sqlOperator . " FROM_UNIXTIME(" . $value . ")";
                    break;
                case "developmental_stage_identifier":
                    $developmentalStageIdentifier = $value;
                    break;
                case "enhancer_attribute_excluded":
                    $enhancerAttributeExcluded = $this->helper->convertValueToBool($value);
                    break;
                case "enhancer_attribute_included":
                    $enhancerAttributeIncluded = $this->helper->convertValueToBool($value);
                    break;
                case "evidence_id":
                    if ( is_numeric($value) ) {
                        $sqlCriteria[] = "pcrm.evidence_id = " . $value;
                    }
                    break;
                case "evidence_subtype_id":
                    if ( is_numeric($value) ) {
                        $sqlCriteria[] = "pcrm.evidence_subtype_id = " . $value;
                    }
                    break;
                case "exact_biological_process_identifier":
                    $exactBiologicalProcessIdentifier = $this->helper->convertValueToBool($value);
                    break;
                case "exact_anatomical_expression_identifier":
                    $exactAnatomicalExpressionIdentifier = $this->helper->convertValueToBool($value);
                    break;
                case "exact_developmental_stage_identifier":
                    $exactDevelopmentalStageIdentifier = $this->helper->convertValueToBool($value);
                    break;
                case "fbtp_identifier":
                    // As any FlyBase transgenic construct identifier is not applied for predicted CRMs,
                    // a zero is given so that nothing is returned from the search consult.
                    $sqlCriteria[] = "0";
                    break;
                case "five_prime":
                    // As any gene identifier is not applied for predicted CRMs,
                    // a zero is given so that nothing is returned from the search consult.
                    if ( $value === "true" ) {
                        $sqlCriteria[] = "0";
                    }
                    break;
                case "gene_id":
                    // As any gene identifier is not applied for predicted CRMs,
                    // a zero is given so that nothing is returned from the search consult
                    $sqlCriteria[] = "0";
                case "gene_search":
                    // The first value, "by_locus", is set by default and the other value, "by_name",
                    // does not apply to this entity, predicted CRM
                    if ( $value === "true" ) {
                        if ( $arguments["gene_locus"] === "" ) {
                            $sqlCriteria[] = "0";
                        } else {
                            $sqlCriteria[] = "pcrm.gene_locus LIKE '%" .
                                str_replace(
                                    "_",
                                    "\_",
                                    $arguments["gene_locus"]
                                ) . "%'";
                        }
                    }
                    break;
                case "in_exon":
                    // As any gene identifier is not applied for predicted CRMs,
                    // a zero is given so that nothing is returned from the search consult.
                    if ( $value === "true" ) {
                        $sqlCriteria[] = "0";
                    }
                    break;
                case "in_intron":
                    // As any gene identifier is not applied for predicted CRMs,
                    // a zero is given so that nothing is returned from the search consult.
                    if ( $value === "true" ) {
                        $sqlCriteria[] = "0";
                    }
                    break;
                case "include_range":
                    // As any gene identifier is not applied for predicted CRMs, a zero is given so that
                    // nothing is returned from the search consult.
                    // It is always true by default from the front side
                    if ( $value === "true" ) {
                        // The search interval range is 10000 by default from the front side.
                        // So if an interval different from 10000, then it must return nothing
                        if ( $arguments["search_range"] <> "10000" ) {
                            $sqlCriteria[] = "0";
                        }
                    }
                    break;
                case "last_audit":
                    $sqlCriteria[] = "pcrm.last_audit " . $sqlOperator . " FROM_UNIXTIME(" . $value . ")";
                    break;
                case "last_update":
                    $sqlCriteria[] = "pcrm.last_update " . $sqlOperator . " FROM_UNIXTIME(" . $value . ")";
                    break;
                case "limit":
                    $limitStr = $this->helper->constructLimitStr($arguments);
                    break;
                case "maximum_sequence_size":
                    if ( is_numeric($value) ) {
                        $sqlCriteria[] = "pcrm.size <= " . $value;
                    }
                    break;
                case "name":
                    if ( $value !== "%" ) {
                        $sqlCriteria[] = str_replace(
                            "_",
                            "\_",
                            "LOWER(pcrm.name) LIKE LOWER(" . $this->db->escape($value, true) . ")"
                        );
                    }
                    break;
                case "pubmed_id":
                    $pubmedId = $this->db->escape($value, true);
                    break;
                // This option is not advertized to the API
                // but is available for efficiency internally
                case "predicted_crm_id":
                    $value = ( ! is_array($value)
                        ? array($value)
                        : $value );
                    $tmpSqlCriteria = array();
                    foreach ( $value as $id ) {
                        $tmpSqlCriteria[] = $id;
                    }
                    $sqlCriteria[] = "predicted_crm_id IN (" . implode(",", $tmpSqlCriteria) . ")";
                    break;
                case "redfly_id":
                    if ( $sqlOperator !== "=" ) {
                        throw new Exception("SQL operator \"" . $sqlOperator . "\" not allowed with redfly_id");
                    }
                    $redflyIdProvided = true;
                    $value = ( ! is_array($value)
                        ? array($value)
                        : $value );
                    $tmpSqlCriteria = array();
                    foreach ( $value as $id ) {
                        $tmpSqlCriteria[] = $this->redflyIdToSql($id);
                    }
                    $sqlCriteria[] = "(" . implode(" OR ", $tmpSqlCriteria) . ")";
                    break;
                case "sequence_from_species_id":
                    $sqlCriteria[] = "pcrm.sequence_from_species_id = " . $value;
                    break;
                case "silencer_attribute_excluded":
                    $silencerAttributeExcluded = $this->helper->convertValueToBool($value);
                    break;
                case "silencer_attribute_included":
                    $silencerAttributeIncluded = $this->helper->convertValueToBool($value);
                    break;
                case "sort":
                    $sortInformation = $this->helper->extractSortInformation($value);
                    foreach ( $sortInformation as $sortColumn => $direction ) {
                        switch ( $sortColumn ) {
                            case "chr":
                                $sqlOrderBy[] = "c.chromosome " . $direction;
                                break;
                            case "name":
                                $sqlOrderBy[] = "pcrm.name " . $direction;
                                break;
                            case "pubmed_id":
                                $sqlOrderBy[] = "pcrm.pubmed_id " . $direction;
                                break;
                            default:
                                break;
                        }
                    }
                    break;
                case "state":
                    $sqlCriteria[] = "pcrm.state = " . $this->db->escape($value, true);
                    break;
                case "three_prime":
                    // As any gene identifier is not applied for predicted CRMs,
                    // a zero is given so that nothing is returned from the search consult.
                    if ( $value === "true" ) {
                        $sqlCriteria[] = "0";
                    }
                    break;
                case "transcription_factor_id":
                    // Not applied here since predicted CRMs do not have any transcription_factor.
                    $sqlCriteria[] = "0";
                    break;
                case "view":
                    $returnFormat = trim($value);
                    break;
                default:
                    break;
            }
        }
        // Include criteria for searching on a PubMed Id in any predicted CRM,
        // as well as, its staging data associated
        if ( $pubmedId !== "" ) {
            $additionalJoins[] = "INNER JOIN (SELECT predicted_crm_id
                                              FROM PredictedCRM
                                              WHERE pubmed_id = " . $pubmedId . "
                                              UNION
                                              SELECT DISTINCT predicted_crm_id
                                              FROM triplestore_predicted_crm
                                              WHERE pubmed_id = " . $pubmedId . ") AS pubmed
                                  ON pcrm.predicted_crm_id = pubmed.predicted_crm_id";
        }
        // Include the criteria for searching anyone from the coordinate extremes
        $predictedCrmErrorMargin = $GLOBALS["options"]->predicted_crm->error_margin;
        if ( ($coordinateStart !== 0) &&
            ($coordinateEnd !== 0) ) {
            $sqlCriteria[] = ($coordinateStart - $predictedCrmErrorMargin) . " <= pcrm.current_start";
            $sqlCriteria[] = "pcrm.current_end <= " . ($coordinateEnd + $predictedCrmErrorMargin);
        } else {
            if ( ($coordinateStart !== 0) &&
                ($coordinateEnd === 0) ) {
                $sqlCriteria[] = ($coordinateStart - $predictedCrmErrorMargin) . " <= pcrm.current_start";
                if ( ($coordinateStart === 0) &&
                    ($coordinateEnd !== 0) ) {
                    $sqlCriteria[] = "pcrm.current_end <= " . ($coordinateEnd + $predictedCrmErrorMargin);
                }
            }
        }

        // Include a predicted CRM not having any anatomical expression or,
        // having an anatomical expression without any staging data, at least, or
        // having an anatomical expression having staging data with the enhancer attribute,
        // at least
        if ( $enhancerAttributeIncluded && (! $silencerAttributeIncluded) &&
            (! $enhancerAttributeExcluded) && (! $silencerAttributeExcluded) ) {
            $additionalJoins[] = "INNER JOIN (SELECT PredictedCRM.predicted_crm_id
                                              FROM PredictedCRM
                                              WHERE NOT EXISTS (
                                                  SELECT DISTINCT PredictedCRM_has_Expression_Term.predicted_crm_id
                                                  FROM PredictedCRM_has_Expression_Term
                                                  WHERE PredictedCRM.predicted_crm_id = PredictedCRM_has_Expression_Term.predicted_crm_id
                                              )
                                              UNION
                                              SELECT DISTINCT PredictedCRM_has_Expression_Term.predicted_crm_id
                                              FROM PredictedCRM_has_Expression_Term
                                              WHERE NOT EXISTS (
                                                  SELECT DISTINCT triplestore_predicted_crm.predicted_crm_id
                                                  FROM triplestore_predicted_crm
                                                  WHERE PredictedCRM_has_Expression_Term.predicted_crm_id = triplestore_predicted_crm.predicted_crm_id
                                              )
                                              UNION
                                              SELECT DISTINCT triplestore_predicted_crm.predicted_crm_id
                                              FROM triplestore_predicted_crm
                                              WHERE triplestore_predicted_crm.silencer = 'enhancer') AS enhancer
                                  ON pcrm.predicted_crm_id = enhancer.predicted_crm_id";
        }
        // Include a predicted CRM having an anatomical expression having staging data
        // with the silencer attribute, at least
        if ( (! $enhancerAttributeIncluded) && $silencerAttributeIncluded &&
            (! $enhancerAttributeExcluded) && (! $silencerAttributeExcluded) ) {
            $additionalJoins[] = "INNER JOIN (SELECT DISTINCT predicted_crm_id
                                              FROM triplestore_predicted_crm
                                              WHERE silencer = 'silencer') AS silencer
                                  ON pcrm.predicted_crm_id = silencer.predicted_crm_id";
        }
        // Exclude a predicted CRM not having any anatomical expression or,
        // having any anatomical expression without any staging data, at least, or
        // having any anatomical expression having staging data with the enhancer attribute,
        // at least
        if ( (! $enhancerAttributeIncluded) && (! $silencerAttributeIncluded) &&
            $enhancerAttributeExcluded && (! $silencerAttributeExcluded) ) {
            $additionalJoins[] = "INNER JOIN (SELECT DISTINCT predicted_crm_id
                                              FROM triplestore_predicted_crm
                                              EXCEPT
                                              SELECT DISTINCT predicted_crm_id
                                              FROM triplestore_predicted_crm
                                              WHERE silencer = 'enhancer') AS non_enhancer
                                   ON pcrm.predicted_crm_id = non_enhancer.predicted_crm_id";
        }
        // Exclude a predicted CRM having any anatomical expression having staging data
        // with the silencer attribute
        if ( (! $enhancerAttributeIncluded) && (! $silencerAttributeIncluded) &&
            (! $enhancerAttributeExcluded) && $silencerAttributeExcluded ) {
            $additionalJoins[] = "INNER JOIN (SELECT PredictedCRM.predicted_crm_id
                                              FROM PredictedCRM
                                              WHERE NOT EXISTS (
                                                  SELECT DISTINCT PredictedCRM_has_Expression_Term.predicted_crm_id
                                                  FROM PredictedCRM_has_Expression_Term
                                                  WHERE PredictedCRM.predicted_crm_id = PredictedCRM_has_Expression_Term.predicted_crm_id
                                              )
                                              UNION
                                              SELECT DISTINCT PredictedCRM_has_Expression_Term.predicted_crm_id
                                              FROM PredictedCRM_has_Expression_Term
                                              WHERE NOT EXISTS (
                                                  SELECT DISTINCT triplestore_predicted_crm.predicted_crm_id
                                                  FROM triplestore_predicted_crm
                                                  WHERE PredictedCRM_has_Expression_Term.predicted_crm_id = triplestore_predicted_crm.predicted_crm_id
                                              )
                                              UNION
                                              (SELECT DISTINCT predicted_crm_id
                                               FROM PredictedCRM_has_Expression_Term
                                               EXCEPT
                                               SELECT DISTINCT predicted_crm_id
                                               FROM triplestore_predicted_crm
                                               WHERE silencer = 'silencer')) AS non_silencer
                                  ON pcrm.predicted_crm_id = non_silencer.predicted_crm_id";
        }
        // Include both enhancer and silencer attributes
        if ( $enhancerAttributeIncluded && $silencerAttributeIncluded &&
            (! $enhancerAttributeExcluded) && (! $silencerAttributeExcluded) ) {
            $additionalJoins[] = "INNER JOIN ((SELECT DISTINCT PredictedCRM_has_Expression_Term.predicted_crm_id
                                               FROM PredictedCRM_has_Expression_Term
                                               WHERE NOT EXISTS (
                                                   SELECT triplestore_predicted_crm.predicted_crm_id
                                                   FROM triplestore_predicted_crm
                                                   WHERE PredictedCRM_has_Expression_Term.predicted_crm_id = triplestore_predicted_crm.predicted_crm_id
                                               )
                                               UNION
                                               SELECT DISTINCT triplestore_predicted_crm.predicted_crm_id
                                               FROM triplestore_predicted_crm
                                               WHERE triplestore_predicted_crm.silencer = 'enhancer')
                                              INTERSECT
                                              SELECT DISTINCT predicted_crm_id
                                              FROM triplestore_predicted_crm
                                              WHERE silencer = 'silencer') AS enhancers_and_silencers
                                  ON pcrm.predicted_crm_id = enhancers_and_silencers.predicted_crm_id";
        }
        // Enhancers only
        if ( $enhancerAttributeIncluded && (! $silencerAttributeIncluded) &&
            (! $enhancerAttributeExcluded) && $silencerAttributeExcluded ) {
            $additionalJoins[] =  "INNER JOIN (SELECT PredictedCRM.predicted_crm_id
                                               FROM PredictedCRM
                                               WHERE NOT EXISTS (
                                                   SELECT DISTINCT PredictedCRM_has_Expression_Term.predicted_crm_id
                                                   FROM PredictedCRM_has_Expression_Term
                                                   WHERE PredictedCRM.predicted_crm_id = PredictedCRM_has_Expression_Term.predicted_crm_id
                                               )
                                               UNION
                                               (SELECT DISTINCT predicted_crm_id
                                                FROM PredictedCRM_has_Expression_Term
                                                EXCEPT
                                                SELECT DISTINCT predicted_crm_id
                                                FROM triplestore_predicted_crm
                                                WHERE silencer = 'silencer')) AS enhancers_only
                                   ON pcrm.predicted_crm_id = enhancers_only.predicted_crm_id";
        }
        // Silencers only
        if ( (! $enhancerAttributeIncluded) && $silencerAttributeIncluded &&
            $enhancerAttributeExcluded && (! $silencerAttributeExcluded) ) {
            $additionalJoins[] = "INNER JOIN (SELECT DISTINCT predicted_crm_id
                                              FROM triplestore_predicted_crm
                                              EXCEPT
                                              SELECT DISTINCT predicted_crm_id
                                              FROM triplestore_predicted_crm
                                              WHERE silencer = 'enhancer') AS silencers_only
                                  ON pcrm.predicted_crm_id = silencers_only.predicted_crm_id";
        }
        // Excluding both enhancer and silencer attributes
        if ( (! $enhancerAttributeIncluded) && (! $silencerAttributeIncluded) &&
            $enhancerAttributeExcluded && $silencerAttributeExcluded ) {
            $additionalJoins[] = "INNER JOIN (SELECT PredictedCRM.predicted_crm_id
                                              FROM PredictedCRM
                                              WHERE NOT EXISTS (
                                                  SELECT DISTINCT PredictedCRM_has_Expression_Term.predicted_crm_id
                                                  FROM PredictedCRM_has_Expression_Term
                                                  WHERE PredictedCRM.predicted_crm_id = PredictedCRM_has_Expression_Term.predicted_crm_id)) AS neither_enhancers_nor_silencers
                                  ON pcrm.predicted_crm_id = neither_enhancers_nor_silencers.predicted_crm_id";
        }
        // Search the anatomical expression identifier provided
        // (only appplied for the Drosophila melanogaster species at the moment)
        if ( $anatomicalExpressionIdentifier !== "" ) {
            $additionalJoins[] = "INNER JOIN PredictedCRM_has_Expression_Term etmap ON pcrm.predicted_crm_id = etmap.predicted_crm_id";
            if ( $exactAnatomicalExpressionIdentifier ) {
                // Search only the anatomical expression identifier
                $additionalJoins[] = "INNER JOIN ExpressionTerm et ON " .
                    "pcrm.sequence_from_species_id = et.species_id AND etmap.term_id = et.term_id AND " .
                    "et.identifier = " .  $this->db->escape($anatomicalExpressionIdentifier, true);
            } else {
                // Search the anatomical expression identifier and its descendant
                // identifiers provided by the anatomy ontology
                $arguments = array("identifier" => $anatomicalExpressionIdentifier);
                $anatomyOntologyHandler = AnatomyontologyHandler::factory();
                $anatomyOntologyResponse = $anatomyOntologyHandler->listAction($arguments);
                $anatomicalExpressionIdentifiersList = array();
                foreach ( $anatomyOntologyResponse->results() as $result ) {
                    $anatomicalExpressionIdentifiersList[] = $this->db->escape($result["id"], true);
                }
                $additionalJoins[] = "INNER JOIN ExpressionTerm et ON " .
                    "pcrm.sequence_from_species_id = et.species_id AND etmap.term_id = et.term_id AND " .
                    "et.identifier IN (" . implode(",", $anatomicalExpressionIdentifiersList) . ")";
            }
        }
        // Search the developmental stage identifier provided
        // (only appplied for the Drosophila melanogaster species at the moment)
        if ( $developmentalStageIdentifier !== "" ) {
            if ( $exactDevelopmentalStageIdentifier ) {
                // Search only the development stage identifier
                $additionalJoins[] = "INNER JOIN (SELECT DISTINCT predicted_crm_id
                                                  FROM triplestore_predicted_crm
                                                  WHERE stage_on = " . $this->db->escape($developmentalStageIdentifier, true) . "
                                                  UNION
                                                  SELECT DISTINCT predicted_crm_id
                                                  FROM triplestore_predicted_crm
                                                  WHERE stage_off = " . $this->db->escape($developmentalStageIdentifier, true) . ") AS ds
                                     ON pcrm.predicted_crm_id = ds.predicted_crm_id";
            } else {
                // Search the development stage identifier and its descendant
                // identifiers provided by the development ontology
                $arguments = array("identifier" => $developmentalStageIdentifier);
                $developmentOntologyHandler = DevelopmentontologyHandler::factory();
                $developmentOntologyResponse = $developmentOntologyHandler->listAction($arguments);
                $developmentStageIdentifiersList = array();
                foreach ( $developmentOntologyResponse->results() as $result ) {
                    $developmentStageIdentifiersList[] = $this->db->escape($result["id"], true);
                }
                $additionalJoins[] = "INNER JOIN (SELECT DISTINCT predicted_crm_id
                                                  FROM triplestore_predicted_crm
                                                  WHERE stage_on IN (" . implode(",", $developmentStageIdentifiersList) . ")
                                                  UNION
                                                  SELECT DISTINCT predicted_crm_id
                                                  FROM triplestore_predicted_crm
                                                  WHERE stage_off IN (" . implode(",", $developmentStageIdentifiersList) . ")) AS ds
                                      ON pcrm.predicted_crm_id = ds.predicted_crm_id";
            }
        }
        // Search the biological process identifier provided
        if ( $biologicalProcessIdentifier !== "" ) {
            // Search only the biological process identifier
            if ( $exactBiologicalProcessIdentifier ) {
                $additionalJoins[] = "INNER JOIN (SELECT DISTINCT predicted_crm_id
                                                  FROM triplestore_predicted_crm
                                                  WHERE biological_process = " . $this->db->escape($biologicalProcessIdentifier, true) . ") AS bp
                                      ON pcrm.predicted_crm_id = bp.predicted_crm_id";
            } else {
                // Search the biological process identifier and its descendant
                // identifiers provided by the GO ontology
                $arguments = array("identifier" => $biologicalProcessIdentifier);
                $biologyOntologyHandler = BiologyontologyHandler::factory();
                $biologyOntologyResponse = $biologyOntologyHandler->listAction($arguments);
                $biologicalProcessIdentifiersList = array();
                foreach ( $biologyOntologyResponse->results() as $result ) {
                    $biologicalProcessIdentifiersList[] = $this->db->escape($result["id"], true);
                }
                $additionalJoins[] = "INNER JOIN (SELECT predicted_crm_id
                                                  FROM triplestore_predicted_crm
                                                  WHERE biological_process IN (" . implode(",", $biologicalProcessIdentifiersList) . ")) AS bp
                                      ON pcrm.predicted_crm_id = bp.predicted_crm_id";
            }
        }
        if ( $returnFormat !== self::VIEW_CURATOR ) {
            if ( $redflyIdProvided ) {
                $sqlCriteria[] = "( pcrm.state = 'current' OR pcrm.state = 'archived' )";
            } else {
                $sqlCriteria[] = "pcrm.state = 'current'";
            }
        }
        switch ( $returnFormat ) {
            // For the curator view of a list of predicted cis-regulatory modules
            case self::VIEW_CURATOR:
                $response = $this->queryCuratorSummaryList(
                    $additionalJoins,
                    $sqlCriteria,
                    $sqlGroupBy,
                    $sqlOrderBy
                );
                break;
            // For the public view of a predicted cis-regulatory module
            // chosen by its REDfly identifier
            case self::VIEW_FULL:
                $response = $this->queryFullList(
                    $additionalJoins,
                    $sqlCriteria,
                    $sqlGroupBy,
                    $sqlOrderBy,
                    $limitStr,
                    $queryOptions
                );
                break;
            // For the public view of a list of predicted cis-regulatory modules
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
    // Return help for the "get" action
    // --------------------------------------------------------------------------------
    public function getHelp()
    {
        $description = "Return data for the specified predicted CRM";
        $options = array("redfly_id" => "The REDfly identifier (or an array of them) " .
            "for the predicted CRM (e.g., RFPCRM:0000312.001)");

        return RestResponse::factory(
            true,
            $description,
            $options
        );
    }
    // --------------------------------------------------------------------------------
    // Retrieve details for one or more entities. Expression terms are queried through
    // their own API call due to a limitation of the ExtJS JsonStore to parse nested
    // arrays.
    // --------------------------------------------------------------------------------
    public function getAction(
        array $arguments,
        array $postData = null
    ) {
        $additionalJoins = array();
        $sqlCriteria = array();
        $sqlOrderBy = array();
        $limitStr = "";
        $response = null;
        $redflyIdProvided = false;
        $queryOptions = array();
        foreach ( $arguments as $argument => $value ) {
            if ( ($value !== false) &&
                (($value === null) || ($value === "")) ) {
                continue;
            }
            switch ( $argument ) {
                case "redfly_id":
                    $value = ( ! is_array($value)
                        ? array($value)
                        : $value );
                    $tmpSqlCriteria = array();
                    foreach ( $value as $id ) {
                        $tmpSqlCriteria[] = $this->redflyIdToSql($id);
                    }
                    $sqlCriteria[] = "(" . implode(" OR ", $tmpSqlCriteria) . ")";
                    $redflyIdProvided = true;
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
        $sqlCriteria[] = "( pcrm.state = 'current' OR pcrm.state = 'archived' )";
        $response = $this->queryFullList(
            $additionalJoins,
            $sqlCriteria,
            array(),
            $sqlOrderBy,
            $limitStr,
            $queryOptions
        );

        return $response;
    }
    // --------------------------------------------------------------------------------
    // Return help for the "load" action
    // --------------------------------------------------------------------------------
    public function loadHelp()
    {
        $description = "Load a predicted CRM for curation";
        $options = array("redfly_id" => "The REDfly identifier for the predicted CRM " .
            "(e.g., RFPCRM:0000312.001)");

        return RestResponse::factory(
            true,
            $description,
            $options
        );
    }
    // --------------------------------------------------------------------------------
    // Retrieve details for one or more entities. Expression terms are queried through
    // their own API call due to a limitation of the ExtJS JsonStore to parse nested
    // arrays.
    // --------------------------------------------------------------------------------
    public function loadAction(
        array $arguments,
        array $postData = null
    ) {
        try {
            Auth::authorize(array(
                "admin",
                "curator"));
        } catch ( Exception $e ) {
            $httpResponseCode = ( Auth::getUser() === null
                ? 401
                : 403);
            return RestResponse::factory(
                false,
                $e->getMessage(),
                array(),
                array(),
                $httpResponseCode
            );
        }
        $additionalJoins = array();
        $sqlCriteria = array();
        $sqlOrderBy = array();
        $limitStr = "";
        $response = null;
        $redflyIdProvided = false;
        foreach ( $arguments as $argument => $value ) {
            if ( ($value !== false) &&
                (($value === null) || ($value === "")) ) {
                continue;
            }
            switch ( $argument ) {
                case "redfly_id":
                    $value = ( ! is_array($value)
                        ? array($value)
                        : $value );
                    $tmpSqlCriteria = array();
                    foreach ( $value as $id ) {
                        $tmpSqlCriteria[] = $this->redflyIdToSql($id);
                    }
                    $sqlCriteria[] = "(" . implode(" OR ", $tmpSqlCriteria) . ")";
                    $redflyIdProvided = true;
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
        $response = $this->querySinglePredictedCRM(
            $additionalJoins,
            $sqlCriteria,
            array(),
            $sqlOrderBy,
            $limitStr
        );

        return $response;
    }
    // --------------------------------------------------------------------------------
    // Return help for the "save" action
    // --------------------------------------------------------------------------------
    public function saveHelp()
    {
        $description = "Create, update, or merge predicted CRMs";
        $options = array("redfly_id" => "The REDfly identifier for the predicted CRM " .
            "(e.g., RFPCRM:0000312.001)");

        return RestResponse::factory(
            true,
            $description,
            $options
        );
    }
    // --------------------------------------------------------------------------------
    // Save predicted CRM data.
    // --------------------------------------------------------------------------------
    public function saveAction(
        array $arguments,
        array $postData = null
    ) {
        try {
            Auth::authorize(array(
                "admin",
                "curator"
            ));
        } catch ( Exception $e ) {
            $httpResponseCode = ( Auth::getUser() === null
                ? 401
                : 403);
            return RestResponse::factory(
                false,
                $e->getMessage(),
                array(),
                array(),
                $httpResponseCode
            );
        }
        // The ExtJS store will be sending JSON encoded data under the
        // "results" key based on the root property of the reader.
        if ( ! isset($postData["results"]) ) {
            throw new Exception("Entity data not provided in \$_POST[\"results\"]");
        }
        $data = (array)json_decode(
            $postData["results"],
            true
        );
        $redflyId = ( isset($data["redfly_id"])
            ? $data["redfly_id"]
            : null );
        $action = ( isset($data["action"])
            ? $data["action"]
            : null );
        switch ( $action ) {
            case self::ACTION_submitForApproval:
                $state = self::STATE_approval;
                break;
            case self::ACTION_approve:
                $state = self::STATE_approved;
                break;
            case self::ACTION_markForDeletion:
                $state = self::STATE_deleted;
                break;
            default:
                $state = self::STATE_editing;
        }
        // This will change the state if necessary.
        $data["state"] = $state;
        // Ignore any supplied size since it will be calculated from the
        // start and end coordinates.
        unset($data["size"]);
        // The curator interface sends these in its request, but they
        // should never be changed by curators, so ignore them.
        unset($data["date_added"]);
        unset($data["archive_date"]);
        $predictedCrmHelper = PredictedCrmHelper::factory();
        try {
            $this->db->startTransaction();
            // Approve an existing predicted CRM as long as its state is "approval"
            if ( $action === self::ACTION_approve ) {
                // Ensure the correct role to approve the predicted CRM
                if ( ! Auth::getUser()->hasRole("admin") ) {
                    throw new Exception("Admin role required to approve the predicted CRM");
                }
                // If a redfly_id_list was sent in the POST this will be the list
                // of REDfly identifiers that were considered during the approval
                if ( isset($postData["redfly_id_list"]) &&
                    (! empty($postData["redfly_id_list"])) ) {
                    $redflyIdMergeList = (array)json_decode($postData["redfly_id_list"]);
                } else {
                    $redflyIdMergeList = null;
                }
                if ( $redflyIdMergeList === null ) {
                    throw new Exception("List of merged redfly ids not provided in \$_POST[\"redfly_id_list\"]");
                }
                $data = $this->approve(
                    $data,
                    $redflyIdMergeList
                );
                $redflyId = $this->helper->entityId(
                    self::EntityCode,
                    $data["entity_id"],
                    $data["version"],
                    $data["predicted_crm_id"]
                );
            } else {
                // No REDfly ID was presented, create a new predicted CRM.
                // New entities should never have an auditor.
                if ( $redflyId === null ) {
                    unset($data["auditor_id"]);
                    unset($data["last_audit"]);
                    $pcrmId = $predictedCrmHelper->create($data);
                    $data["predicted_crm_id"] = $pcrmId;
                    $redflyId = $this->helper->entityId(
                        self::EntityCode,
                        null,
                        null,
                        $pcrmId
                    );
                }
                // A REDfly ID was presented, see if this is the first edit of
                // an existing entity or if we are saving over an existing edit
                else {
                    $type = $entityId = $version = $pcrmId = null;
                    $this->helper->parseEntityId(
                        $redflyId,
                        $type,
                        $entityId,
                        $version,
                        $pcrmId
                    );
                    if ( $type !== self::EntityCode ) {
                        throw new Exception("Not a predicted CRM id: \"$redflyId\"");
                    }
                    // If the predicted CRM that is being saved has the state as "current"
                    // then we can not update the same row in the database.
                    // So we need to create a new row
                    $editingCurrentPredictedCrm = false;
                    // Find the database identifier of the entity
                    if ( $pcrmId === null ) {
                        $sql = <<<SQL
                        SELECT predicted_crm_id, 
                            state
                        FROM PredictedCRM
                        WHERE entity_id = $entityId AND 
                            version = $version
SQL;
                        $result = $this->db->query($sql);
                        if ( ($row = $result->fetch_assoc()) === null ) {
                            throw new Exception("Failed to find \"$redflyId\"");
                        }
                        $pcrmId = $row["predicted_crm_id"];
                        if ( $row["state"] === "current" ) {
                            $editingCurrentPredictedCrm = true;
                            // We do not allow changing the auditor of a current predicted CRM
                            unset($data["auditor_id"]);
                            unset($data["last_audit"]);
                        }
                    }
                    if ( $editingCurrentPredictedCrm ) {
                        $data = $predictedCrmHelper->createEdit(
                            $pcrmId,
                            $data
                        );
                    } else {
                        $data = $predictedCrmHelper->update(
                            $pcrmId,
                            $data
                        );
                    }
                    $redflyId = $this->helper->entityId(
                        self::EntityCode,
                        $data["entity_id"],
                        $data["version"],
                        $data["predicted_crm_id"]
                    );
                }
            }
            // --------------------------------------------------------------------------------
            // If the email of the author has changed then update the citation with the new
            // email of the author.
            // If the email of the author is NULL then do not change the citation.
            // --------------------------------------------------------------------------------
            $citationHandler = CitationHandler::factory();
            $citationParams = array(
                "external_id" => $data["pubmed_id"],
                "type"        => "PUBMED"
            );
            $citationResponse = $citationHandler->listAction($citationParams);
            if ( $citationResponse->results() !== null ) {
                list($citationResult) = $citationResponse->results();
                if ( isset($data["author_email"]) &&
                    ($data["author_email"] !== null) &&
                    ($citationResult["author_email"] !== $data["author_email"]) ) {
                    $citationResult["author_email"] = $data["author_email"];
                    // The saveAction() expects the new data to be JSON encoded and assigned
                    // to $_POST["results"] to be in line with the ExtJS store API
                    $citationResponse = $citationHandler->saveAction(
                        array(),
                        array("results" => json_encode($citationResult))
                    );
                    if ( ! $citationResponse->success() ) {
                        throw new Exception("Error updating author email: " . $citationResponse->message());
                    }
                }
            }
            $this->db->commit();
        } catch ( Exception $e ) {
            $this->db->rollback();
            throw $e;
        }
        $data["redfly_id"] = $redflyId;

        return RestResponse::factory(
            true,
            null,
            array($data)
        );
    }
    // --------------------------------------------------------------------------------
    // Approval helper function for saveAction
    // @param array $data Data to update the predicted CRM with
    // @param array $redflyIdMergeList A list of REDfly IDs
    // --------------------------------------------------------------------------------
    private function approve(
        array $data,
        array $redflyIdMergeList
    ) {
        $predictedCrmHelper = PredictedCrmHelper::factory();
        // Counters to keep track of the number of new and edited entities merged
        // during the approval process
        $numNewEntitiesMerged = 0;
        $numEditedEntitiesMerged = 0;
        // Count the number of new and edited (existing) entities in the merge list
        foreach ( $redflyIdMergeList as $mergeRedflyId ) {
            $type = $entityId = $version = $dbId = null;
            $this->helper->parseEntityId(
                $mergeRedflyId,
                $type,
                $entityId,
                $version,
                $dbId
            );
            if ( $type !== self::EntityCode ) {
                throw new Exception("Not a predicted CRM id: \"" . $mergeRedflyId. "\"");
            }
            if ( $dbId !== null ) {
                $numNewEntitiesMerged++;
            } else {
                $numEditedEntitiesMerged++;
            }
        }
        if ( $numEditedEntitiesMerged > 1 ) {
            throw new Exception("Cannot merge multiple edits to existing entities");
        } elseif ( ($numNewEntitiesMerged > 0) &&
            ($numEditedEntitiesMerged > 0) ) {
            throw new Exception("Cannot merge newly created and edits to existing entities");
        }
        // Take the first REDfly identifier in the merge list and use it to save the
        // merged data. If this is a new entity we can use this predicted_crm_id as well
        $redflyId = array_shift($redflyIdMergeList);
        $type = $entityId = $version = $pcrmId = null;
        $this->helper->parseEntityId(
            $redflyId,
            $type,
            $entityId,
            $version,
            $pcrmId
        );
        if ( $type !== self::EntityCode ) {
            throw new Exception("Not a predicted CRM id: \"" . $redflyId. "\"");
        }
        if ( $pcrmId === null ) {
            $sql = <<<SQL
            SELECT predicted_crm_id
            FROM PredictedCRM
            WHERE entity_id = $entityId AND 
                version = $version
SQL;
            $result = $this->db->query($sql);
            if ( ($row = $result->fetch_assoc()) === null ) {
                throw new Exception("Failed to find \"" . $redflyId . "\"");
            }
            $pcrmId = $row["predicted_crm_id"];
        }
        $data["state"] = self::STATE_approved;
        // The following code line will set both auditor identifier and last
        // audit fields updated thanks to the new "approved" state
        $data = $predictedCrmHelper->update(
            $pcrmId,
            $data
        );
        // Delete any new entities remaining in the merge list
        foreach ( $redflyIdMergeList as $mergeRedflyId ) {
            // Since we only support merging multiple new entities we only care
            // about the predicted_crm_id here.
            $mergedEntityId = $mergePcrmId = null;
            $this->helper->parseEntityId(
                $mergeRedflyId,
                $type,
                $mergedEntityId,
                $version,
                $mergePcrmId
            );
            if ( $type !== self::EntityCode ) {
                  throw new Exception("Not a predicted CRM id: \"" . $mergeRedflyId . "\"");
            }
            $predictedCrmHelper->deleteVersion($mergePcrmId);
        }

        return $data;
    }
    // --------------------------------------------------------------------------------
    // Return help for the "reject" action
    // --------------------------------------------------------------------------------
    public function rejectHelp()
    {
        $description = "Reject one or more predicted CRMs from the approval queue.";
        $options = array(
            "delete_items"   => "TRUE to mark the items for deletion from the approval queue",
            "email_curators" => "TRUE to send email to the curators and include the rejection message",
            "message"        => "Optional message to send to the curators",
            "names"          => "An array of one or more predicted CRMs and curator names encoded as JSON",
            "redfly_ids"     => "An array of one or more REDfly identifiers encoded as JSON"
        );

        return RestResponse::factory(
            true,
            $description,
            $options
        );
    }
    // --------------------------------------------------------------------------------
    // Reject one or more predicted CRMs.
    // --------------------------------------------------------------------------------
    public function rejectAction(
        array $arguments,
        array $postData = null
    ) {
        try {
            Auth::authorize(array("admin"));
        } catch ( Exception $e ) {
            $httpResponseCode = ( Auth::getUser() === null
                ? 401
                : 403);
            return RestResponse::factory(
                false,
                $e->getMessage(),
                array(),
                array(),
                $httpResponseCode
            );
        }
        $redflyIdList = json_decode($arguments["redfly_ids"]);
        $redflyIdList = ( is_array($redflyIdList)
            ? $redflyIdList
            : array($redflyIdList)
        );
        $nameList = json_decode($arguments["names"]);
        $nameList = ( is_array($nameList)
            ? $nameList
            : array($nameList)
        );
        $deleteItems = $this->helper->convertValueToBool($arguments["delete_items"]);
        $emailCurators = $this->helper->convertValueToBool($arguments["email_curators"]);
        $emailMessage = ( isset($arguments["message"]) && ! empty($arguments["message"])
            ? $arguments["message"]
            : null
        );
        try {
            $this->db->startTransaction();
            foreach ( $redflyIdList as $redflyId ) {
                $type = $entityId = $version = $dbId = null;
                $this->helper->parseEntityId(
                    $redflyId,
                    $type,
                    $entityId,
                    $version,
                    $dbId
                );
                if ( $type !== self::EntityCode ) {
                    throw new Exception("$redflyId is not a valid predicted CRM id");
                }
                $sql = "UPDATE PredictedCRM
                    SET state = '";
                $sql .= ( ! $deleteItems
                    ? "editing"
                    : "deleted");
                $sql .= "',
                    last_audit = NOW(),
                    auditor_id = " . Auth::getUser()->userId();
                $sql .= ( $dbId !== null
                    ? " WHERE entity_id IS NULL and predicted_crm_id = ?"
                    : " WHERE entity_id = ? AND version = ?" ) . " LIMIT 1";
                if ( ($statement = $this->db->getHandle()->prepare($sql)) === false ) {
                    throw new Exception("Error preparing statement: " .
                        $this->db->getError());
                }
                if ( $dbId !== null ) {
                    $statement->bind_param(
                        "i",
                        $dbId
                    );
                } else {
                    $statement->bind_param(
                        "ii",
                        $entityId,
                        $version
                    );
                }
                if ( $statement->execute() === false ) {
                    throw new Exception("Error executing statement: $sql, " .
                        $statement->error);
                }
            }
            $this->db->commit();
        } catch ( Exception $e ) {
            $this->db->rollback();
            return RestResponse::factory(
                false,
                "Error: " . $e->getMessage()
            );
        }
        $rejectList = "";
        foreach ( $redflyIdList as $index => $redflyId ) {
            $rejectList .= "$redflyId (" . $nameList[$index]->name . ", " .
                $nameList[$index]->curator . ")\n";
        }
        if ( $emailCurators ) {
            $curatorEmailList = $this->helper->getCuratorEmails($redflyIdList);
            if ( count($curatorEmailList) === 0 ) {
                return RestResponse::factory(
                    false,
                    "Error: No curator/auditor email address from the database"
                );
            }
            $body = "The following predicted CRM(s) were rejected" .
                ( $deleteItems
                    ? " and marked for deletion"
                    : "" ) .
                " by " . Auth::getUser()->fullName() . "\n\n$rejectList";
            $body .= "\n" .
                ( $emailMessage !== null
                    ? "$emailMessage"
                    : "No reason provided") .
                "\n";
            $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = "smtp.gmail.com";
            $mail->Port = 587;
            $mail->SMTPSecure = "tls";
            $mail->SMTPAuth = true;
            $mail->AuthType = "XOAUTH2";
            $mail->setOAuth(
                new PHPMailer\PHPMailer\OAuth([
                    "provider"     => new League\OAuth2\Client\Provider\Google([
                        "clientId"     => $GLOBALS["options"]->email->gmail_client_id,
                        "clientSecret" => $GLOBALS["options"]->email->gmail_client_secret
                    ]),
                    "clientId"     => $GLOBALS["options"]->email->gmail_client_id,
                    "clientSecret" => $GLOBALS["options"]->email->gmail_client_secret,
                    "refreshToken" => $GLOBALS["options"]->email->gmail_refresh_token,
                    "userName"     => $GLOBALS["options"]->email->gmail_address
                ])
            );
            $mail->CharSet = "utf-8";
            $mail->Subject = "[REDfly] Predicted CRM rejected";
            $mail->Body = $body;
            $mail->setFrom($GLOBALS["options"]->email->gmail_address);
            $mail->addReplyTo($GLOBALS["options"]->email->gmail_address);
            foreach ( $curatorEmailList as $email ) {
                $mail->addAddress($email);
            }
            $mail->send();
        }

        return RestResponse::factory(
            true,
            "Rejected:\n$rejectList"
        );
    }
}
