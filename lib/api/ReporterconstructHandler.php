<?php
class ReporterconstructHandler implements iEditable
{
    // Code to be used when generating a REDfly ID for this entity
    // (e.g. RFRC:00000000.000)
    const EntityCode = "RC";
    // Return formats used by the search action to present different views of the
    // data.
    // Summary list for searching
    const VIEW_DEFAULT = "default";
    // Full list for displaying individual RCs
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
        return new ReporterconstructHandler;
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
    private function addRedflyIdToResultRow(&$row) : bool
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
    private function redflyIdToSql(string $redflyId): string
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
            ($type !== self::EntityCode) ) {
            throw new Exception("Not a reporter construct identifier: \"$redflyId\"");
        }
        if ( $dbId !== null ) {
            $sqlFragment = "( rc.rc_id = $dbId )";
        } elseif ( $version !== null ) {
            $sqlFragment = "( rc.entity_id = $entityId AND rc.version = $version )";
        } else {
            $sqlFragment = "( rc.entity_id = $entityId AND rc.state = 'current' )";
        }

        return $sqlFragment;
    }
    // --------------------------------------------------------------------------------
    // Query the database for a list of reporter construct summaries based on the
    // criteria provided. It is for the public view.
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
        SELECT SQL_CALC_FOUND_ROWS rc.rc_id AS id,
            sfs.scientific_name AS sequence_from_species_scientific_name,
            ais.scientific_name AS assayed_in_species_scientific_name,
            rc.name,
            CONCAT('RFRC', ':', LPAD(CAST(rc.entity_id AS CHAR), 10, '0'), '.' , LPAD(CAST(rc.version AS CHAR), 3, '0')) AS redfly_id,
            rc.gene_id,
            g.name AS gene,
            rc.is_crm,
            CONCAT(c.name, ':', rc.current_start, '..', rc.current_end) AS coordinates,
            IF(ISNULL(GROUP_CONCAT(fei.label)), 0, 1) AS has_images
        FROM ReporterConstruct rc
        INNER JOIN Species sfs ON rc.sequence_from_species_id = sfs.species_id
        INNER JOIN Species ais ON rc.assayed_in_species_id = ais.species_id
        INNER JOIN Gene g ON rc.gene_id = g.gene_id
        INNER JOIN Chromosome c ON rc.chromosome_id = c.chromosome_id
        LEFT OUTER JOIN RC_has_FigureLabel l ON rc.rc_id = l.rc_id
        LEFT OUTER JOIN ext_FlyExpressImage fei ON rc.pubmed_id = fei.pubmed_id AND
            l.label = fei.label
SQL;
        $sqlGroupBy[] = "rc.rc_id";
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
    // Query the database for a reporter construct list based on the criteria
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
            ais.species_id AS assayed_in_species_id,
            ais.short_name AS assayed_in_species_short_name,
            ais.scientific_name AS assayed_in_species_scientific_name,
            rc.rc_id AS id,
            rc.name,
            rc.entity_id,
            rc.version,
            rc.has_tfbs,
            rc.fbtp,
            rc.current_genome_assembly_release_version AS release_version,
            rc.current_start AS start,
            rc.current_end AS end,
            rc.archived_genome_assembly_release_versions,
            rc.archived_starts,
            rc.archived_ends,
            rc.is_crm,
            rc.is_minimalized,
            rc.is_negative,
            rc.notes,
            rc.pubmed_id,
            rc.sequence,
            rc.figure_labels,
            g.name AS gene_name,
            g.identifier AS gene_identifier,
            g.start AS gene_start,
            g.stop AS gene_stop,
            c.name AS chromosome,
            e.term AS evidence_term,
            ss.term AS sequence_source,
            rc.date_added,
            CONCAT(curator.first_name, ' ', curator.last_name) AS curator_full_name,
            rc.last_update,
            IF(ISNULL(rc.auditor_id), '', CONCAT(auditor.first_name, ' ', auditor.last_name)) AS auditor_full_name,
            rc.last_audit,
            cite.contents,
            sfs.public_database_names,
            sfs.public_database_links,
            sfs.public_browser_names,
            sfs.public_browser_links
        FROM ReporterConstruct rc
        INNER JOIN Species sfs ON rc.sequence_from_species_id = sfs.species_id
        INNER JOIN Species ais ON rc.assayed_in_species_id = ais.species_id
        INNER JOIN Gene g ON rc.gene_id = g.gene_id
        INNER JOIN Chromosome c ON rc.chromosome_id = c.chromosome_id
        INNER JOIN EvidenceTerm e ON rc.evidence_id = e.evidence_id
        INNER JOIN SequenceSourceTerm ss ON rc.sequence_source_id = ss.source_id
        INNER JOIN Users curator ON rc.curator_id = curator.user_id
        LEFT OUTER JOIN Users auditor ON rc.auditor_id = auditor.user_id
        INNER JOIN Citation cite ON rc.pubmed_id = cite.external_id AND
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
                $tmpRow["image"] = $this->helper->constructGbrowseImageUrl(
                    $row["name"],
                    $tmpRow["coordinates"],
                    $row["chromosome"],
                    $row["start"],
                    $row["end"],
                    $row["gene_identifier"],
                    $row["gene_start"],
                    $row["gene_stop"]
                );
                $tmpRow["location"] = $this->db->generateFeatureLocationInfo(
                    "reporter_construct",
                    $row["name"],
                    $tmpRow["id"]
                );
                $tmpRow["figure_labels"] = $row["figure_labels"];
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
                unset($tmpRow["gene_start"]);
                unset($tmpRow["gene_stop"]);
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
    // Query the database for a reporter construct based on the criteria provided.
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
    private function querySingleRc(
        array $additionalJoins,
        array $sqlCriteria,
        array $sqlGroupBy,
        array $sqlOrderBy,
        $limitStr
    ) {
        $rcId = null;
        $sql = <<<SQL
        SELECT sfs.species_id AS sequence_from_species_id,
            sfs.short_name AS sequence_from_species_short_name,
            sfs.scientific_name AS sequence_from_species_scientific_name,
            ais.species_id AS assayed_in_species_id,
            ais.short_name AS assayed_in_species_short_name,
            ais.scientific_name AS assayed_in_species_scientific_name,
            rc.rc_id AS id, 
            rc.name,
            rc.entity_id, 
            rc.version,
            rc.state,
            rc.gene_id, 
            g.name AS gene_name, 
            g.identifier AS gene_identifier,
            rc.chromosome_id, 
            c.name AS chromosome,
            rc.evidence_id, 
            e.term AS evidence_term,
            rc.sequence_source_id,
            sst.term AS sequence_source_term,
            rc.pubmed_id,
            rc.figure_labels, 
            rc.notes, 
            rc.is_crm, 
            rc.is_override, 
            rc.is_negative, 
            rc.is_minimalized,
            rc.fbtp, 
            rc.fbal,
            rc.sequence, 
            rc.size,
            rc.current_genome_assembly_release_version AS release_version,
            rc.current_start AS start, 
            rc.current_end AS end,
            rc.has_tfbs, 
            cite.contents AS citation, 
            cite.author_email,
            rc.date_added,
            DATE_FORMAT(rc.date_added, '%M %D, %Y at %l:%i:%s%p') AS date_added_formatted,
            curator.user_id AS curator_id,
            CONCAT(curator.first_name, ' ', curator.last_name) AS curator_full_name,
            rc.last_update,
            DATE_FORMAT(rc.last_update, '%M %D, %Y at %l:%i:%s%p') AS last_update_formatted,
            auditor.user_id AS auditor_id,
            IF(ISNULL(rc.auditor_id), '', CONCAT(auditor.first_name, ' ', auditor.last_name)) AS auditor_full_name,
            rc.last_audit,
            DATE_FORMAT(rc.last_audit, '%M %D, %Y at %l:%i:%s%p') AS last_audit_formatted,
            rc.archive_date,
            DATE_FORMAT(rc.archive_date, '%M %D, %Y at %l:%i:%s%p') AS archive_date_formatted
        FROM ReporterConstruct rc
        INNER JOIN Species sfs ON rc.sequence_from_species_id = sfs.species_id
        INNER JOIN Species ais ON rc.assayed_in_species_id = ais.species_id
        INNER JOIN Gene g ON rc.gene_id = g.gene_id
        INNER JOIN Chromosome c ON rc.chromosome_id = c.chromosome_id
        INNER JOIN EvidenceTerm e ON rc.evidence_id = e.evidence_id
        INNER JOIN SequenceSourceTerm sst ON rc.sequence_source_id = sst.source_id
        INNER JOIN Citation cite ON rc.pubmed_id = cite.external_id AND
            cite.citation_type = 'PUBMED'
        INNER JOIN Users curator ON rc.curator_id = curator.user_id
        LEFT OUTER JOIN Users auditor ON rc.auditor_id = auditor.user_id
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
            $rcId = $row["id"];
            if ( $rcId !== null ) {
                $sql = <<<SQL
                SELECT rc.rc_id,
                    rc.pubmed_id, 
                    et.identifier, 
                    et.term_id AS id, 
                    et.term
                FROM ReporterConstruct rc
                INNER JOIN RC_has_ExprTerm map ON rc.rc_id = map.rc_id
                INNER JOIN ExpressionTerm et ON map.term_id = et.term_id
SQL;
                $sqlCriteria = array("rc.rc_id = $rcId");
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
                $row["anatomical_expression_terms"] = json_encode($anatomicalExpressionResults);
                $rctsHelper = new RcTsHelper();
                $stagingDataResults = $rctsHelper->getAllData($rcId);
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
    // Query the database for a list of reporter construct summaries based on the
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
        // A criteria clause such as "rc.state = 'current'" cannot be used
        // directly because an "editing" version of a "current" RC may
        // exist and so the "current" version should not be returned.
        // Likewise for the "approval", "approved", "archived", and
        // "deleted" versions.
        $pattern = '/
            ^         # Start
            \s*       # Possible whitespace
            rc\.state # state column
            \s*       # Possible whitespace
            =         # Literal equal sign
            \s*       # Possible whitespace
            ([\'"])   # Opening quote
            (\w+)     # state value
            \1        # Closing quote
            \s*       # Possible whitespace
            $         # End
            /x';
        $stateValue = "";
        foreach ( $sqlCriteria as $key => $criteria ) {
            if ( preg_match($pattern, $criteria, $matches) ) {
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
        SELECT rc.rc_id AS id,
            rc.entity_id,
            rc.version,
            rc.name,
            rc.state,
            rc.is_crm,
            sfs.species_id AS sequence_from_species_id,
            sfs.short_name AS sequence_from_species_short_name,
            sfs.scientific_name AS sequence_from_species_scientific_name,
            ais.species_id AS assayed_in_species_id,
            ais.short_name AS assayed_in_species_short_name,
            ais.scientific_name AS assayed_in_species_scientific_name,
            rc.gene_id,
            g.name AS gene,
            c.name AS chromosome,
            rc.current_genome_assembly_release_version AS release_version,
            rc.current_start AS start,
            rc.current_end AS end,
            rc.pubmed_id AS pmid,
            rc.curator_id,
            CONCAT(curator.first_name, ' ', curator.last_name) AS curator_full_name,
            DATE_FORMAT(rc.date_added, '%Y-%m-%d %h%p:%i:%s') AS date_added,
            DATE_FORMAT(rc.last_update, '%Y-%m-%d %h%p:%i:%s') AS last_update,
            rc.auditor_id,
            IF(ISNULL(rc.auditor_id), '', CONCAT(auditor.first_name, ' ', auditor.last_name)) AS auditor_full_name,
            IF(ISNULL(rc.auditor_id), '', DATE_FORMAT(rc.last_audit, '%Y-%m-%d %h%p:%i:%s')) AS last_audit,
            DATE_FORMAT(rc.archive_date, '%Y-%m-%d %h%p:%i:%s') AS archive_date,
SQL;
        switch ( $stateValue ) {
            case "":
                // 1) All the new entities (still unversioned)
                //    which entity identifier is null and
                // 2) the latest versions, that is, internal identifiers
                //    (rc_id), of all the entities which entity identifier
                //    is not null
                $sql = $sqlBase . <<<SQL
                    IF(rc.state != 'archived', TRUE, FALSE) AS editable
                FROM (SELECT rc_id,
                          entity_id
                      FROM ReporterConstruct
                      WHERE entity_id IS NULL
                      UNION
                      SELECT MAX(rc_id) AS rc_id,
                          entity_id
                      FROM ReporterConstruct
                      WHERE entity_id IS NOT NULL
                      GROUP BY entity_id) AS optimized_rc
                INNER JOIN ReporterConstruct rc ON optimized_rc.rc_id = rc.rc_id
                INNER JOIN Species sfs ON rc.sequence_from_species_id = sfs.species_id
                INNER JOIN Species ais ON rc.assayed_in_species_id = ais.species_id
                INNER JOIN Gene g ON rc.gene_id = g.gene_id
                INNER JOIN Chromosome c ON rc.chromosome_id = c.chromosome_id
                INNER JOIN Users curator ON rc.curator_id = curator.user_id
                LEFT OUTER JOIN Users auditor ON rc.auditor_id = auditor.user_id
SQL;
                break;
            // There can be more than one version of a common entity
            // having the same "archived" state so the latest archived
            // version is shown from all the archived ones of each entity
            // although it will be never edited at any moment.
            case "archived":
                $sql = $sqlBase . <<<SQL
                    FALSE AS editable
                FROM (SELECT entity_id,
                          MAX(version) AS last_archived_version
                      FROM ReporterConstruct
                      WHERE state = 'archived'
                      GROUP BY entity_id) AS optimized_rc
                INNER JOIN ReporterConstruct rc ON optimized_rc.entity_id = rc.entity_id AND
                    optimized_rc.last_archived_version = rc.version 
                INNER JOIN Species sfs ON rc.sequence_from_species_id = sfs.species_id
                INNER JOIN Species ais ON rc.assayed_in_species_id = ais.species_id
                INNER JOIN Gene g ON rc.gene_id = g.gene_id
                INNER JOIN Chromosome c ON rc.chromosome_id = c.chromosome_id
                INNER JOIN Users curator ON rc.curator_id = curator.user_id
                LEFT OUTER JOIN Users auditor ON rc.auditor_id = auditor.user_id
SQL;
                break;
            // There can be a newer version of a common entity having a
            // state different from the "current" state. So any "current"
            // version must not be shown if there is a newer version of its
            // same entity.
            case "current":
                $sql = $sqlBase . <<<SQL
                    IF(rc.version = optimized_rc.last_version, TRUE, FALSE) AS editable
                FROM (SELECT entity_id,
                          MAX(version) AS last_version
                      FROM ReporterConstruct
                      WHERE entity_id IS NOT NULL
                      GROUP BY entity_id) AS optimized_rc
                INNER JOIN ReporterConstruct rc ON optimized_rc.entity_id = rc.entity_id AND
                    optimized_rc.last_version = rc.version
                INNER JOIN Species sfs ON rc.sequence_from_species_id = sfs.species_id
                INNER JOIN Species ais ON rc.assayed_in_species_id = ais.species_id
                INNER JOIN Gene g ON rc.gene_id = g.gene_id
                INNER JOIN Chromosome c ON rc.chromosome_id = c.chromosome_id
                INNER JOIN Users curator ON rc.curator_id = curator.user_id
                LEFT OUTER JOIN Users auditor ON rc.auditor_id = auditor.user_id
SQL;
                break;
            // These states are unique and must not be repeated at any version
            // of a common entity.
            case "approval":
            case "approved":
            case "editing":
            case "deleted":
                $sql = $sqlBase . <<<SQL
                    TRUE AS editable
                FROM ReporterConstruct rc
                INNER JOIN Species sfs ON rc.sequence_from_species_id = sfs.species_id
                INNER JOIN Species ais ON rc.assayed_in_species_id = ais.species_id
                INNER JOIN Gene g ON rc.gene_id = g.gene_id
                INNER JOIN Chromosome c ON rc.chromosome_id = c.chromosome_id
                INNER JOIN Users curator ON rc.curator_id = curator.user_id
                LEFT OUTER JOIN Users auditor ON rc.auditor_id = auditor.user_id
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
        $description = "Return a short list of reporter constructs matching the specified criteria.";
        $options = array(
            "anatomical_expression_identifier"       => "List only entities containing this anatomical expression identifier",
            "assayed_in_species_id"                  => "List only entities that have an \"Assayed In\" species",
            "auditor_id"                             => "List only entities audited by the auditor",
            "biological_process_identifier"          => "List only entities containing this biological process identifier (GO)",
            "cell_culture_only"                      => "List only entities having any cell culture",
            "chr_end"                                => "List only entities having the coordinate end with its predefined error margin " .
                                                        "(most recent coordinate release)",
            "chr_id"                                 => "List only entities with this chromosome (internal id)",
            "chr_start"                              => "List only entities having the coordinate start with its predefined error margin " .
                                                        "(most recent coordinate release)",
            "curator_id"                             => "List only entities curated by the curator",
            "date_added"                             => "List only entities addedd based on this date " .
                                                        "(UNIX timestamp in seconds since epoch)",
            "developmental_stage_identifier"         => "List only entities containing this developmental stage identifier",
            "enhancer_attribute_excluded"            => "List only entities not having an (or more) anatomical expression with the enhancer attribute",
            "enhancer_attribute_included"            => "List only entities having an (or more) anatomical expression with the enhancer attribute",
            "evidence_id"                            => "List only entities with this evidence term id",
            "exact_anatomical_expression_identifier" => "If TRUE list only entities containing the exact anatomical expression identifier, " .
                                                        "if FALSE list entities containing the identifier and any descendants " .
                                                        "according to the ontology",
            "exact_biological_process_identifier"    => "If TRUE list only entities containing the exact biological process identifier, " .
                                                        "if FALSE list entities containing the identifier and any descendants " .
                                                        "according to the ontology",
            "exact_developmental_stage_identifier"   => "If TRUE list only entities containing the exact developmental stage identifier, " .
                                                        "if FALSE list entities containing the identifier and any descendants " .
                                                        "according to the ontology",
            "fbtp_identifier"                        => "List only entities that match the FBtp identifier",
            "five_prime"                             => "List only entities that have some portion 5' of all transcripts",
            "gene_identifier"                        => "List only entities with this gene (identifier)",
            "gene_id"                                => "List only entities with this gene (internal id)",
            "has_images"                             => "List only entities that have figure information stored in the database",
            "has_tfbs"                               => "List only entities that have an associated binding site",
            "in_exon"                                => "List only entities that overlap or are included within an exon",
            "in_intron"                              => "List only entities that overlap or are included within an intron",
            "is_crm"                                 => "List only entities that are marked as CRMs",
            "is_minimalized"                         => "List only entities that have been minimalized",
            "is_negative"                            => "List only entities that are negative",
            "last_audit"                             => "List only entities last auditor audited based on this date " .
                                                        "(UNIX timestamp in seconds since epoch)",
            "last_update"                            => "List only entities last curator updated based on this date " .
                                                        "(UNIX timestamp in seconds since epoch)",
            "limit"                                  => "Maximum number of entities to return",
            "maximum_sequence_size"                  => "List only entities with a sequence of this size or less",
            "name"                                   => "List only entities that match this name",
            "pubmed_id"                              => "List only entities that match this Pubmed Id as primary or secondary",
            "redfly_id"                              => "The REDfly identifier (or an array of them) for the reporter construct " .
                                                        "(e.g., RFRC:0000014.001)",
            "sequence_from_species_id"               => "List only entities that have a \"Sequence From\" species",
            "silencer_attribute_excluded"            => "List only entities not having an (or more) anatomical expression with the silencer attribute",
            "silencer_attribute_included"            => "List only entities having an (or more) anatomical expression with the silencer attribute",
            "sort"                                   => "Sort field. Valid options are: name, gene, pmid, chr",
            "state"                                  => "List only entities having such a state",
            "three_prime"                            => "List only entities that have some portion 3' of all transcripts",
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
        // Any image, it defaults to 0 (false)
        $hasImages = 0;
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
                case "assayed_in_species_id":
                    $sqlCriteria[] = "rc.assayed_in_species_id = " . $value;
                    break;
                case "auditor_id":
                    $sqlCriteria[] = "rc.auditor_id = " . $value;
                    break;
                case "biological_process_identifier":
                    $biologicalProcessIdentifier = $value;
                    break;
                case "cell_culture_only":
                    $sqlCriteria[] = "rc.cell_culture_only = " . $this->helper->convertValueToBool($value);
                    break;
                case "chr_end":
                    if ( is_numeric($value) ) {
                        $coordinateEnd = (int)$value;
                    }
                    break;
                case "chr_id":
                    if ( is_numeric($value) ) {
                        $sqlCriteria[] = "rc.chromosome_id = " . $value;
                    }
                    break;
                case "chr_start":
                    if ( is_numeric($value) ) {
                        $coordinateStart = (int)$value;
                    }
                    break;
                case "curator_id":
                    $sqlCriteria[] = "rc.curator_id = " . $value;
                    break;
                case "date_added":
                    $sqlCriteria[] = "rc.date_added " . $sqlOperator . " FROM_UNIXTIME(" . $value . ")";
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
                        $sqlCriteria[] = "rc.evidence_id = " . $value;
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
                    $sqlCriteria[] = "rc.fbtp " . $sqlOperator . " " . $this->db->escape($value, true);
                    break;
                case "five_prime":
                    if ( $this->helper->convertValueToBool($value) ) {
                        $additionalJoins[] = "INNER JOIN v_reporter_construct_feature_location location_5 " .
                            "ON (rc.rc_id = location_5.id AND location_5.type = 'mrna')";
                        $sqlCriteria[] = "(location_5.relative_start = location_5.relative_end " .
                            "AND location_5.relative_start = 5)";
                        $sqlGroupBy[] = "location_5.id";
                    }
                    break;
                case "gene_identifier":
                    $sqlCriteria[] = "g.identifier " . $sqlOperator . " " . $this->db->escape($value, true);
                    break;
                case "gene_id":
                    if ( is_numeric($value) ) {
                        // To know if we have to include the search range or not
                        $include_range = isset($arguments["include_range"])
                            ? $this->helper->convertValueToBool($arguments["include_range"])
                            : 0;
                        if ( $include_range === 0 ) {
                            $sqlCriteria[] = "rc.gene_id = " . $value;
                        } else {
                            $search_range = isset($arguments["search_range"])
                                ? abs((int)$arguments["search_range"])
                                : 0;
                            $geneIdentifier = $value;
                            $sql = <<<SQL
                            SELECT chrm_id,
                                start,
                                stop
                            FROM Gene
                            WHERE gene_id = $geneIdentifier
SQL;
                            $result = $this->db->query($sql);
                            $row = $result->fetch_assoc();
                            $chromosomeIdentifier = (int)$row["chrm_id"];
                            $intervalStart = (int)$row["start"] - $search_range;
                            $intervalStop = (int)$row["stop"] + $search_range;
                            $sqlCriteria[] = "((rc.gene_id = " . $value .
                                ") OR (rc.chromosome_id = " . $chromosomeIdentifier . " AND " .
                                $intervalStart . " <= rc.current_start AND rc.current_end <= " . $intervalStop .
                                "))";
                        }
                    }
                    break;
                case "has_images":
                    $hasImages = $this->helper->convertValueToBool($value);
                    break;
                case "has_tfbs":
                    $sqlCriteria[] = "rc.has_tfbs = " . $this->helper->convertValueToBool($value);
                    break;
                case "in_exon":
                    if ( $this->helper->convertValueToBool($value) ) {
                        $additionalJoins[] = "INNER JOIN v_reporter_construct_feature_location location_e " .
                            "ON (rc.rc_id = location_e.id AND location_e.type = 'exon')";
                        $sqlCriteria[] = "(location_e.relative_start = 0 OR location_e.relative_end = 0)";
                        $sqlGroupBy[] = "location_e.id";
                    }
                    break;
                case "in_intron":
                    if ( $this->helper->convertValueToBool($value) ) {
                        $additionalJoins[] = "INNER JOIN v_reporter_construct_feature_location location_i " .
                            "ON (rc.rc_id = location_i.id AND location_i.type = 'intron')";
                        $sqlCriteria[] = "(location_i.relative_start = 0 OR location_i.relative_end = 0)";
                        $sqlGroupBy[] = "location_i.id";
                    }
                    break;
                case "is_crm":
                    $sqlCriteria[] = "rc.is_crm = " . $this->helper->convertValueToBool($value);
                    break;
                case "is_minimalized":
                    if ( $value === "true" ) {
                        $sqlCriteria[] = "rc.is_minimalized = " . $this->helper->convertValueToBool($value);
                    }
                    break;
                case "is_negative":
                    $sqlCriteria[] = "rc.is_negative = " . $this->helper->convertValueToBool($value);
                    break;
                case "last_audit":
                    $sqlCriteria[] = "rc.last_audit " . $sqlOperator . " FROM_UNIXTIME(" . $value . ")";
                    break;
                case "last_update":
                    $sqlCriteria[] = "rc.last_update " . $sqlOperator . " FROM_UNIXTIME(" . $value . ")";
                    break;
                case "limit":
                    $limitStr = $this->helper->constructLimitStr($arguments);
                    break;
                case "maximum_sequence_size":
                    if ( is_numeric($value) ) {
                        $sqlCriteria[] = "rc.size <= " . $value;
                    }
                    break;
                case "name":
                    if ( $value !== "%" ) {
                        $sqlCriteria[] = str_replace(
                            "_",
                            "\_",
                            "LOWER(rc.name) LIKE LOWER(" . $this->db->escape($value, true) . ")"
                        );
                    }
                    break;
                case "pubmed_id":
                    $pubmedId = $this->db->escape($value, true);
                    break;
                // This option is not advertized to the API
                // but is available for efficiency internally
                case "rc_id":
                    $value = ( ! is_array($value)
                        ? array($value)
                        : $value );
                    $tmpSqlCriteria = array();
                    foreach ( $value as $id ) {
                        $tmpSqlCriteria[] = $id;
                    }
                    $sqlCriteria[] = "rc_id IN (" . implode(",", $tmpSqlCriteria) . ")";
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
                    $sqlCriteria[] = "rc.sequence_from_species_id = " . $value;
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
                                $sqlOrderBy[] = "c.name " . $direction;
                                break;
                            case "gene":
                                $sqlOrderBy[] = "g.name " . $direction;
                                break;
                            case "name":
                                $sqlOrderBy[] = "rc.name " . $direction;
                                break;
                            case "pubmed_id":
                                $sqlOrderBy[] = "rc.pubmed_id " . $direction;
                                break;
                            default:
                                break;
                        }
                    }
                    break;
                case "state":
                    $sqlCriteria[] = "rc.state = " . $this->db->escape($value, true);
                    break;
                case "three_prime":
                    if ( $this->helper->convertValueToBool($value) ) {
                        $additionalJoins[] = "INNER JOIN v_reporter_construct_feature_location location_3 " .
                            "ON (rc.rc_id = location_3.id AND location_3.type = 'mrna')";
                        $sqlCriteria[] = "(location_3.relative_start = location_3.relative_end " .
                            "AND location_3.relative_start = 3)";
                        $sqlGroupBy[] = "location_3.id";
                    }
                    break;
                case "transcription_factor_id":
                    // Not applied here since reporter constructs do not have any transcription_factor.
                    $sqlCriteria[] = "0";
                    break;
                case "view":
                    $returnFormat = trim($value);
                    break;
                default:
                    break;
            }
        }
        // Include the criteria for searching on a PubMed Id in any reporter construct,
        // as well as, its staging data associated
        if ( $pubmedId !== "" ) {
            $additionalJoins[] = "INNER JOIN (SELECT rc_id
                                              FROM ReporterConstruct
                                              WHERE pubmed_id = " . $pubmedId . "
                                              UNION
                                              SELECT DISTINCT rc_id
                                              FROM triplestore_rc
                                              WHERE pubmed_id = " . $pubmedId . ") AS pubmed
                                  ON rc.rc_id = pubmed.rc_id";
        }
        // Include the criteria for searching anyone from the coordinate extremes
        $rcErrorMargin = $GLOBALS["options"]->rc->error_margin;
        if ( ($coordinateStart !== 0) &&
            ($coordinateEnd !== 0) ) {
            $sqlCriteria[] = ($coordinateStart - $rcErrorMargin) . " <= rc.current_start";
            $sqlCriteria[] = "rc.current_end <= " . ($coordinateEnd + $rcErrorMargin);
        } else {
            if ( ($coordinateStart !== 0) &&
                ($coordinateEnd === 0) ) {
                $sqlCriteria[] = ($coordinateStart - $rcErrorMargin) . " <= rc.current_start";
                if ( ($coordinateStart === 0) &&
                    ($coordinateEnd !== 0) ) {
                    $sqlCriteria[] = "rc.current_end <= " . ($coordinateEnd + $rcErrorMargin);
                }
            }
        }
        // Include a reporter construct not having any anatomical expression or,
        // having an anatomical expression without any staging data, at least, or
        // having an anatomical expression having staging data with the enhancer attribute,
        // at least
        if ( $enhancerAttributeIncluded && (! $silencerAttributeIncluded) &&
            (! $enhancerAttributeExcluded) && (! $silencerAttributeExcluded) ) {
            $sqlCriteria[] = "rc.is_negative = 0";
            $additionalJoins[] = "INNER JOIN (SELECT ReporterConstruct.rc_id
                                              FROM ReporterConstruct
                                              WHERE NOT EXISTS (
                                                  SELECT DISTINCT RC_has_ExprTerm.rc_id
                                                  FROM RC_has_ExprTerm
                                                  WHERE ReporterConstruct.rc_id = RC_has_ExprTerm.rc_id
                                              )
                                              UNION
                                              SELECT DISTINCT RC_has_ExprTerm.rc_id
                                              FROM RC_has_ExprTerm
                                              WHERE NOT EXISTS (
                                                  SELECT DISTINCT triplestore_rc.rc_id
                                                  FROM triplestore_rc
                                                  WHERE RC_has_ExprTerm.rc_id = triplestore_rc.rc_id
                                              )
                                              UNION
                                              SELECT DISTINCT triplestore_rc.rc_id
                                              FROM triplestore_rc
                                              WHERE triplestore_rc.silencer = 'enhancer') AS enhancer
                                  ON rc.rc_id = enhancer.rc_id";
        }
        // Include a reporter construct having an anatomical expression having staging data
        // with the silencer attribute, at least
        if ( (! $enhancerAttributeIncluded) && $silencerAttributeIncluded &&
            (! $enhancerAttributeExcluded) && (! $silencerAttributeExcluded) ) {
            $sqlCriteria[] = "rc.is_negative = 0";
            $additionalJoins[] = "INNER JOIN (SELECT DISTINCT rc_id
                                              FROM triplestore_rc
                                              WHERE silencer = 'silencer') AS silencer
                                  ON rc.rc_id = silencer.rc_id";
        }
        // Exclude a reporter construct not having any anatomical expression or,
        // having any anatomical expression without any staging data, at least, or
        // having any anatomical expression having staging data with the enhancer attribute,
        // at least
        if ( (! $enhancerAttributeIncluded) && (! $silencerAttributeIncluded) &&
            $enhancerAttributeExcluded && (! $silencerAttributeExcluded) ) {
            $sqlCriteria[] = "rc.is_negative = 0";
            $additionalJoins[] = "INNER JOIN (SELECT DISTINCT rc_id
                                              FROM triplestore_rc
                                              EXCEPT
                                              SELECT DISTINCT rc_id
                                              FROM triplestore_rc
                                              WHERE silencer = 'enhancer') AS non_enhancer
                                  ON rc.rc_id = non_enhancer.rc_id";
        }
        // Exclude a reporter construct having any anatomical expression having staging data
        // with the silencer attribute
        if ( (! $enhancerAttributeIncluded) && (! $silencerAttributeIncluded) &&
            (! $enhancerAttributeExcluded) && $silencerAttributeExcluded ) {
            $sqlCriteria[] = "rc.is_negative = 0";
            $additionalJoins[] = "INNER JOIN (SELECT ReporterConstruct.rc_id
                                              FROM ReporterConstruct
                                              WHERE NOT EXISTS (
                                                  SELECT DISTINCT RC_has_ExprTerm.rc_id
                                                  FROM RC_has_ExprTerm
                                                  WHERE ReporterConstruct.rc_id = RC_has_ExprTerm.rc_id
                                              )
                                              UNION
                                              SELECT DISTINCT RC_has_ExprTerm.rc_id
                                              FROM RC_has_ExprTerm
                                              WHERE NOT EXISTS (
                                                  SELECT DISTINCT triplestore_rc.rc_id
                                                  FROM triplestore_rc
                                                  WHERE RC_has_ExprTerm.rc_id = triplestore_rc.rc_id
                                              )
                                              UNION                                              
                                              (SELECT DISTINCT rc_id
                                               FROM RC_has_ExprTerm
                                               EXCEPT
                                               SELECT DISTINCT rc_id
                                               FROM triplestore_rc
                                               WHERE silencer = 'silencer')) AS non_silencer
                                  ON rc.rc_id = non_silencer.rc_id";
        }
        // Include both enhancer and silencer attributes
        if ( $enhancerAttributeIncluded && $silencerAttributeIncluded &&
            (! $enhancerAttributeExcluded) && (! $silencerAttributeExcluded) ) {
            $sqlCriteria[] = "rc.is_negative = 0";
            $additionalJoins[] = "INNER JOIN ((SELECT DISTINCT RC_has_ExprTerm.rc_id
                                               FROM RC_has_ExprTerm
                                               WHERE NOT EXISTS (
                                                   SELECT DISTINCT triplestore_rc.rc_id
                                                   FROM triplestore_rc
                                                   WHERE RC_has_ExprTerm.rc_id = triplestore_rc.rc_id
                                               )
                                               UNION
                                               SELECT DISTINCT triplestore_rc.rc_id
                                               FROM triplestore_rc
                                               WHERE triplestore_rc.silencer = 'enhancer')
                                              INTERSECT
                                              SELECT DISTINCT rc_id
                                              FROM triplestore_rc
                                              WHERE silencer = 'silencer') AS enhancers_and_silencers
                                  ON rc.rc_id = enhancers_and_silencers.rc_id";
        }
        // Enhancers only
        if ( $enhancerAttributeIncluded && (! $silencerAttributeIncluded) &&
            (! $enhancerAttributeExcluded) && $silencerAttributeExcluded ) {
            $sqlCriteria[] = "rc.is_negative = 0";
            $additionalJoins[] = "INNER JOIN (SELECT ReporterConstruct.rc_id
                                              FROM ReporterConstruct
                                              WHERE NOT EXISTS (
                                                  SELECT DISTINCT RC_has_ExprTerm.rc_id
                                                  FROM RC_has_ExprTerm
                                                  WHERE ReporterConstruct.rc_id = RC_has_ExprTerm.rc_id
                                              )
                                              UNION
                                              (SELECT DISTINCT rc_id
                                               FROM RC_has_ExprTerm
                                               EXCEPT
                                               SELECT DISTINCT rc_id
                                               FROM triplestore_rc
                                               WHERE silencer = 'silencer')) AS enhancers_only
                                  ON rc.rc_id = enhancers_only.rc_id";
        }
        // Silencers only
        if ( (! $enhancerAttributeIncluded) && $silencerAttributeIncluded &&
            $enhancerAttributeExcluded && (! $silencerAttributeExcluded) ) {
            $sqlCriteria[] = "rc.is_negative = 0";
            $additionalJoins[] = "INNER JOIN (SELECT DISTINCT rc_id
                                              FROM triplestore_rc
                                              EXCEPT
                                              SELECT DISTINCT rc_id
                                              FROM triplestore_rc
                                              WHERE silencer = 'enhancer') AS silencers_only
                                  ON rc.rc_id = silencers_only.rc_id";
        }
        // Excluding both enhancer and silencer attributes
        if ( (! $enhancerAttributeIncluded) && (! $silencerAttributeIncluded) &&
            $enhancerAttributeExcluded && $silencerAttributeExcluded ) {
            $sqlCriteria[] = "rc.is_negative = 1";
            $additionalJoins[] = "INNER JOIN (SELECT ReporterConstruct.rc_id
                                              FROM ReporterConstruct
                                              WHERE NOT EXISTS (
                                                  SELECT DISTINCT RC_has_ExprTerm.rc_id
                                                  FROM RC_has_ExprTerm
                                                  WHERE ReporterConstruct.rc_id = RC_has_ExprTerm.rc_id)) AS neither_enhancers_nor_silencers
                                  ON rc.rc_id = neither_enhancers_nor_silencers.rc_id";
        }
        // Include the criteria for searching images
        if ( $hasImages ) {
            // The query for the default view includes these joins so we can display
            // has_images in the search results so only add them if a different view
            // was selected.
            if ( $returnFormat !== self::VIEW_DEFAULT ) {
                $additionalJoins[] = "LEFT OUTER JOIN RC_has_FigureLabel l ON rc.rc_id = l.rc_id";
                $additionalJoins[] = "LEFT OUTER JOIN ext_FlyExpressImage fei ON rc.pubmed_id = fei.pubmed_id AND
                    l.label = fei.label";
            }
            $sqlCriteria[] = "fei.label IS NOT NULL";
        }
        // Search the anatomical expression identifier provided
        // (only appplied for the Drosophila melanogaster species at the moment)
        if ( $anatomicalExpressionIdentifier !== "" ) {
            $additionalJoins[] = "INNER JOIN RC_has_ExprTerm etmap ON rc.rc_id = etmap.rc_id";
            if ( $exactAnatomicalExpressionIdentifier ) {
                // Search only the anatomical expression identifier
                $additionalJoins[] = "INNER JOIN ExpressionTerm et ON " .
                    "rc.assayed_in_species_id = et.species_id AND etmap.term_id = et.term_id AND " .
                    "et.identifier = " . $this->db->escape($anatomicalExpressionIdentifier, true);
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
                    "rc.assayed_in_species_id = et.species_id AND etmap.term_id = et.term_id AND " .
                    "et.identifier IN (" . implode(",", $anatomicalExpressionIdentifiersList) . ")";
            }
        }
        // Search the developmental stage identifier provided
        // (only appplied for the Drosophila melanogaster species at the moment)
        if ( $developmentalStageIdentifier !== "" ) {
            if ( $exactDevelopmentalStageIdentifier ) {
                // Search only the development stage identifier
                $additionalJoins[] = "INNER JOIN (SELECT DISTINCT rc_id
                                                  FROM triplestore_rc
                                                  WHERE stage_on = " . $this->db->escape($developmentalStageIdentifier, true) . "
                                                  UNION
                                                  SELECT DISTINCT rc_id
                                                  FROM triplestore_rc
                                                  WHERE stage_off = " . $this->db->escape($developmentalStageIdentifier, true) . ") AS ds
                                      ON rc.rc_id = ds.rc_id";
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
                $additionalJoins[] = "INNER JOIN (SELECT DISTINCT rc_id
                                                  FROM triplestore_rc
                                                  WHERE stage_on IN (" . implode(",", $developmentStageIdentifiersList) . ")
                                                  UNION
                                                  SELECT DISTINCT rc_id
                                                  FROM triplestore_rc
                                                  WHERE stage_off IN (" . implode(",", $developmentStageIdentifiersList) . ")) AS ds
                                      ON rc.rc_id = ds.rc_id";
            }
        }
        // Search the biological process identifier provided
        if ( $biologicalProcessIdentifier !== "" ) {
            // Search only the biological process identifier
            if ( $exactBiologicalProcessIdentifier ) {
                $additionalJoins[] = "INNER JOIN (SELECT DISTINCT rc_id
                                                  FROM triplestore_rc
                                                  WHERE biological_process = " . $this->db->escape($biologicalProcessIdentifier, true) . ") AS bp
                                      ON rc.rc_id = bp.rc_id";
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
                $additionalJoins[] = "INNER JOIN (SELECT DISTINCT rc_id
                                                  FROM triplestore_rc
                                                  WHERE biological_process IN (" . implode(",", $biologicalProcessIdentifiersList) . ")) AS bp
                                      ON rc.rc_id = bp.rc_id";
            }
        }
        if ( $returnFormat !== self::VIEW_CURATOR ) {
            if ( $redflyIdProvided ) {
                $sqlCriteria[] = "( rc.state = 'current' OR rc.state = 'archived' )";
            } else {
                $sqlCriteria[] = "rc.state = 'current'";
            }
        }
        switch ( $returnFormat ) {
            // For the curator view of a list of reporter constructs
            case self::VIEW_CURATOR:
                $response = $this->queryCuratorSummaryList(
                    $additionalJoins,
                    $sqlCriteria,
                    $sqlGroupBy,
                    $sqlOrderBy
                );
                break;
            // For the public view of a reporter construct
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
            // For the public view of a list of reporter constructs
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
        $description = "Return data for the specified Reporter Construct";
        $options = array("redfly_id" => "The REDfly identifier (or an array of them) " .
            "for the reporter construct (e.g., RFRC:0000312.001)");

        return RestResponse::factory(
            true,
            $description,
            $options
        );
    }
    // --------------------------------------------------------------------------------
    // Retrieve details for one or more entities. Expression terms and associated
    // TFBSs are queried through their own API call due to a limitation of the
    // ExtJS JsonStore to parse nested arrays.
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
        $sqlCriteria[] = "( rc.state = 'current' OR rc.state = 'archived' )";
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
    // Return help for the "checkAnyDuplicateKind" action
    // --------------------------------------------------------------------------------
    public function checkAnyDuplicateKindHelp()
    {
        $description = "Checks any duplicate kind of RCs during the curation process";
        $options = array(
            "chromosome_id" => "The chromosome ID of the reporter construct",
            "end"           => "The end coordinate of the reporter construct",
            "name"          => "The name of the reporter construct",
            "redfly_id"     => "The REDfly identifier of the reporter construct " .
                "(e.g., RFRC:0000312.001).",
            "start"         => "The start coordinate of the reporter construct"
        );

        return RestResponse::factory(
            true,
            $description,
            $options
        );
    }
    // --------------------------------------------------------------------------------
    // Check any duplicate RC from a given RC.
    // --------------------------------------------------------------------------------
    public function checkAnyDuplicateKindAction(
        array $arguments,
        array $postData = null
    ) {
        if ( isset($arguments["redfly_id"]) &&
            (! empty($arguments["redfly_id"])) ) {
            $redflyId = $arguments["redfly_id"];
            $type = $entityId = $version = $dbId = null;
            $this->helper->parseEntityId(
                $redflyId,
                $type,
                $entityId,
                $version,
                $dbId
            );
            if ( $type !== self::EntityCode ) {
                throw new Exception("Not a reporter construct id: \"" . $redflyId . "\"");
            }
            // If the database identifier is being used then this is a new entity and has
            // not been assigned an entity id yet.
            if ( $dbId !== null ) {
                $redflyIdCriteria = "rc.rc_id != " . $dbId;
            } else {
                $redflyIdCriteria = "(rc.entity_id != " . $entityId . " OR rc.entity_id IS NULL)";
            }
        } else {
            // If no redfly_id is specified, use a criteria that is always true
            $redflyIdCriteria = "1 = 1";
        }
        if ( ! isset($arguments["name"]) ) {
            throw new Exception("Name not specified");
        }
        if ( ! (isset($arguments["chromosome_id"]) &&
                isset($arguments["start"]) &&
                isset($arguments["end"])) ) {
            throw new Exception("Coordinates not specified");
        }
        // --------------------------------------------------------------------------------
        // Check any entity name duplicate during the curation process. The entity name
        // of an RC is a duplicate if it is the same entity name of the RC being worked
        // on and is in a state from the list of states (approval, approved, current,
        // deleted, and editing). RC(s) with the same entity name having the state
        // defined as archived may have the same entity name to allow for editing a new
        // one with a different entity identifier.
        // --------------------------------------------------------------------------------
        $name = $arguments["name"];
        $nameCriteria = "LOWER(name) = LOWER(" . $this->db->escape($name, true) . ")";
        $sql = <<<SQL
        SELECT rc.rc_id,
            rc.name,
            rc.state,
            rc.date_added,
            CONCAT(curator.first_name, ' ', curator.last_name) AS curator_full_name,
            rc.last_update,
            IF(ISNULL(rc.auditor_id), '', CONCAT(auditor.first_name, ' ', auditor.last_name)) AS auditor_full_name,
            IF(ISNULL(rc.auditor_id), '', rc.last_audit) AS last_audit
        FROM ReporterConstruct rc
        INNER JOIN Users curator ON rc.curator_id = curator.user_id
        LEFT OUTER JOIN Users auditor ON rc.auditor_id = auditor.user_id
        WHERE rc.state IN ('approval', 'approved', 'current', 'deleted', 'editing') AND
            $redflyIdCriteria AND 
            $nameCriteria;
SQL;
        $result = $this->db->query($sql);
        if ( $result->num_rows !== 0 ) {
            $elementNameDuplicateDetected = true;
            $row = $result->fetch_assoc();
            if ( $row["last_update"] === null ) {
                $elementNameDuplicateMessage = sprintf(
                    "A reporter construct already exists with the name \"%s\" added on %s by %s",
                    $row["name"],
                    $row["date_added"],
                    $row["curator_full_name"]
                );
            } else {
                $elementNameDuplicateMessage = sprintf(
                    "A reporter construct already exists with the name \"%s\" edited on %s by %s",
                    $row["name"],
                    $row["last_update"],
                    $row["curator_full_name"]
                );
            };
            if ( $row["last_audit"] !== null ) {
                $elementNameDuplicateMessage .= sprintf(
                    ". It was audited on %s by %s",
                    $row["last_audit"],
                    $row["auditor_full_name"]
                );
            }
        } else {
            $elementNameDuplicateDetected = false;
            $elementNameDuplicateMessage = null;
        }
        // --------------------------------------------------------------------------------
        // Check any coordinate duplicate during the curation process. Anyone from the
        // coordinates of an RC is a duplicate if
        // 1) its state is from the list of states: approval, approved, current, deleted,
        //    and editing.
        // 2) its coordinates are determined with an error margin; this means that
        //    if both ends of the coordinates fall within the -/+ range of the provided
        //    coordinates, that set of coordinates is considered a duplicate.
        // Yet, it can be overrode by the curator in the client side if he/she believes
        // so.
        // --------------------------------------------------------------------------------
        $chromosomeId = $this->db->escape($arguments["chromosome_id"]);
        $errorMargin = (int)$GLOBALS["options"]->rc->error_margin;
        $startMin = (int)$arguments["start"] - $errorMargin;
        $startMax = (int)$arguments["start"] + $errorMargin;
        $endMin = (int)$arguments["end"] - $errorMargin;
        $endMax = (int)$arguments["end"] + $errorMargin;
        $sql = <<<SQL
        SELECT rc.rc_id,
            rc.name,
            rc.state,
            c.name AS chromosome,
            rc.current_start AS start,
            rc.current_end AS end,
            rc.date_added,
            CONCAT(curator.first_name, ' ', curator.last_name) AS curator_full_name,
            rc.last_update,
            IF(ISNULL(rc.auditor_id), '', CONCAT(auditor.first_name, ' ', auditor.last_name)) AS auditor_full_name,
            IF(ISNULL(rc.auditor_id), '', rc.last_audit) AS last_audit
        FROM ReporterConstruct rc
        INNER JOIN Chromosome c ON rc.chromosome_id = c.chromosome_id
        INNER JOIN Users curator ON rc.curator_id = curator.user_id
        LEFT OUTER JOIN Users auditor ON rc.auditor_id = auditor.user_id
        WHERE rc.state IN ('approval', 'approved', 'current', 'deleted', 'editing') AND
            $redflyIdCriteria AND
            rc.chromosome_id = $chromosomeId AND
            rc.current_start BETWEEN $startMin AND $startMax AND 
            rc.current_end BETWEEN $endMin AND $endMax;
SQL;
        $result = $this->db->query($sql);
        if ( $result->num_rows !== 0 ) {
            $coordinateDuplicateDetected = true;
            $rowsNumber = 0;
            while ( $row = $result->fetch_assoc() ) {
                if ( $rowsNumber === 0 ) {
                    $message = sprintf(
                        "The coordinates of the reporter construct, \"%s\", are %s:%d..%d.<br><br>",
                        $arguments["name"],
                        $row["chromosome"],
                        $arguments["start"],
                        $arguments["end"]
                    );
                    $message .= "The list of reporter construct duplicate(s) detected is as follows:";
                    $rowsNumber++;
                }
                if ( $row["last_update"] === null ) {
                    $message .= sprintf(
                        "<br>$rowsNumber) \"%s\", in the %s state with the coordinates %s:%d..%d (error margin %d bp) added on %s by %s",
                        $row["name"],
                        ucfirst($row["state"]),
                        $row["chromosome"],
                        $row["start"],
                        $row["end"],
                        $errorMargin,
                        $row["date_added"],
                        $row["curator_full_name"]
                    );
                } else {
                    $message .= sprintf(
                        "<br>$rowsNumber) \"%s\", in the %s state with the coordinates %s:%d..%d (error margin %d bp) edited on %s by %s",
                        $row["name"],
                        ucfirst($row["state"]),
                        $row["chromosome"],
                        $row["start"],
                        $row["end"],
                        $errorMargin,
                        $row["last_update"],
                        $row["curator_full_name"]
                    );
                }
                if ( $row["last_audit"] !== null ) {
                    $message .= sprintf(
                        ". It was audited on %s by %s",
                        $row["last_audit"],
                        $row["auditor_full_name"]
                    );
                }
                $rowsNumber++;
            }
            $coordinateDuplicateMessage = $message;
        } else {
            $coordinateDuplicateDetected = false;
            $coordinateDuplicateMessage = null;
        }
        $results = array(array(
            "elementNameDuplicateDetected" => $elementNameDuplicateDetected,
            "elementNameDuplicateMessage"  => $elementNameDuplicateMessage,
            "coordinateDuplicateDetected"  => $coordinateDuplicateDetected,
            "coordinateDuplicateMessage"   => $coordinateDuplicateMessage
        ));

        return RestResponse::factory(
            true,
            null,
            $results
        );
    }
    // --------------------------------------------------------------------------------
    // Return help for the "load" action
    // --------------------------------------------------------------------------------
    public function loadHelp()
    {
        $description = "Load a Reporter Construct for curation.";
        $options = array("redfly_id" => "The REDfly identifier for the reporter construct (e.g., RFRC:0000312.001)");

        return RestResponse::factory(
            true,
            $description,
            $options
        );
    }
    // --------------------------------------------------------------------------------
    // Retrieve details for one or more entities. Expression terms and associated
    // TFBSs are queried through their own API call due to a limitation of the
    // ExtJS JsonStore to parse nested arrays.
    // --------------------------------------------------------------------------------
    public function loadAction(
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
        $response = $this->querySingleRc(
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
        $description = "Create, update, or merge Reporter Constructs.";
        $options = array("redfly_id" => "The REDfly identifier for the reporter construct " .
            "(e.g., RFRC:0000312.001)");

        return RestResponse::factory(
            true,
            $description,
            $options
        );
    }
    // --------------------------------------------------------------------------------
    // Save details for a single RC entry or approve a RC. The ExtJS store
    // expects the following structure when receiving records and when saving
    // sends JSON data in $postData["results"].
    // array("success" => ["true" | "false"],
    //       "message" => <optional message>,
    //       "num"     => <number of results>,
    //       "results" => <array of result records/objects>);
    // If an entity is being approved, the baseParams for the store will also send
    // redfly_id_list which contains a JSON encoded list of the redfly ids that
    // were examined for approval.
    // @param $arguments Arguments passed in the query string
    // @param $postData Arguments passed in the POST
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
        $data = (array) json_decode(
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
        $data["name"] = trim($data["name"]);
        // Enforces the rule about making the element name from the string concatenation of
        // both gene and arbitrary names plus the underscore chracter between them.
        if ( substr($data["name"], 0, strlen($data["gene_name"]) + 1) !== ($data["gene_name"] . "_") ) {
            throw new Exception("The element name does not begin by both gene name and underscore.");
        }
        if ( substr($data["name"], strlen($data["gene_name"]) + 1) === "" ) {
            throw new Exception("The element name does not end by the arbitrary name.");
        }
        $data["notes"] = trim($data["notes"]);
        // This will change the state if necessary.
        $data["state"] = $state;
        // Ignore any supplied size since it will be calculated from the
        // start and end coordinates.
        unset($data["size"]);
        // The curator interface sends these in its request, but they
        // should never be changed by curators, so ignore them.
        unset($data["is_crm"]);
        unset($data["is_minimalized"]);
        unset($data["has_tfbs"]);
        unset($data["date_added"]);
        unset($data["archive_date"]);
        // ExtJS submits the value of the "empty text" attribute of comboboxes.
        // This value needs to be caught and cleared out before proceeding.
        if ( isset($data["fbtp_identifier"]) &&
            ($data["fbtp_identifier"] === "---") ) {
            unset($data["fbtp_identifier"]);
        }
        $rcHelper = RcHelper::factory();
        try {
            $this->db->startTransaction();
            // Approve an existing RC as long as its state is "approval"
            if (
                //($state === self::STATE_approval) &&
                ($action === self::ACTION_approve) ) {
                // Ensure the correct role for approval
                if ( ! Auth::getUser()->hasRole("admin") ) {
                    throw new Exception("Admin role required for approval of RC");
                }
                // If a redfly_id_list was sent in the POST this will be the list of REDfly identifiers
                // that were considered during the approval (the action should also be "approve").
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
                    $data["rc_id"]
                );
            } else {
                // No REDfly ID was presented, create a new RC.
                // New entities should never have an auditor.
                if ( $redflyId === null ) {
                    unset($data["auditor_id"]);
                    unset($data["last_audit"]);
                    $rcId = $rcHelper->create($data);
                    $data["rc_id"] = $rcId;
                    $redflyId = $this->helper->entityId(
                        self::EntityCode,
                        null,
                        null,
                        $rcId
                    );
                }
                // An REDfly ID was presented, see if this is the first edit of
                // an existing entity or if we are saving over an existing edit.
                else {
                    $type = $entityId = $version = $rcId = null;
                    $this->helper->parseEntityId(
                        $redflyId,
                        $type,
                        $entityId,
                        $version,
                        $rcId
                    );
                    if ( $type !== self::EntityCode ) {
                        throw new Exception("Not a reporter construct id: \"" . $redflyId . "\"");
                    }
                    // If the RC that is being saved has state "current", then we
                    // can not update the same row in the database, we need to create
                    // a new row.
                    $editingCurrentRc = false;
                    // Find the database identifier of the entity
                    if ( $rcId === null ) {
                        $sql = <<<SQL
                        SELECT name,
                            rc_id,
                            state
                        FROM ReporterConstruct
                        WHERE entity_id = $entityId AND 
                            version = $version
SQL;
                        $result = $this->db->query($sql);
                        if ( ($row = $result->fetch_assoc()) === null ) {
                            throw new Exception("Failed to find \"" . $redflyId . "\"");
                        }
                        $rcId = $row["rc_id"];
                        if ( $row["state"] === "current" ) {
                            $editingCurrentRc = true;
                            // We do not allow to change the name of a current RC
                            if ( $row["name"] !== $data["name"] ) {
                                throw new Exception("Changing the name of a current reporter construct is forbidden. " .
                                    "Please, consult with the PI.");
                            }
                            // We do not allow to change the auditor of a current RC
                            unset($data["auditor_id"]);
                            unset($data["last_audit"]);
                        }
                    }
                    if ( $editingCurrentRc ) {
                        $data = $rcHelper->createEdit(
                            $rcId,
                            $data
                        );
                    } else {
                        $data = $rcHelper->update(
                            $rcId,
                            $data
                        );
                    }
                    $redflyId = $this->helper->entityId(
                        self::EntityCode,
                        $data["entity_id"],
                        $data["version"],
                        $data["rc_id"]
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
    // @param array $data Data to update the RC with
    // @param array $redflyIdMergeList A list of REDfly IDs
    // --------------------------------------------------------------------------------
    private function approve(
        array $data,
        array $redflyIdMergeList
    ): array {
        $rcHelper = RcHelper::factory();
        // Counters to keep track of the number of new and edited entities merged
        // during the approval process.
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
                throw new Exception("Not a reporter construct id: \"$mergeRedflyId\"");
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
        // merged data. If this is a new entity we can use this rc_id as well.
        $redflyId = array_shift($redflyIdMergeList);
        $type = $entityId = $version = $rcId = null;
        $this->helper->parseEntityId(
            $redflyId,
            $type,
            $entityId,
            $version,
            $rcId
        );
        if ( $type !== self::EntityCode ) {
            throw new Exception("Not a reporter construct id: \"" . $redflyId . "\"");
        }
        if ( $rcId === null ) {
            $sql = <<<SQL
            SELECT rc_id
            FROM ReporterConstruct
            WHERE entity_id = $entityId AND 
                version = $version
SQL;
            $result = $this->db->query($sql);
            if ( ($row = $result->fetch_assoc()) === null ) {
                throw new Exception("Failed to find \"" . $redflyId . "\"");
            }
            $rcId = $row["rc_id"];
        }
        $data["state"] = self::STATE_approved;
        // The following code line will set both auditor identifier and last
        // audit fields updated thanks to the new "approved" state
        $data = $rcHelper->update(
            $rcId,
            $data
        );
        // Delete any new entities remaining in the merge list
        foreach ( $redflyIdMergeList as $mergeRedflyId ) {
            // Since we only support merging multiple new entities we only care
            // about the rc_id here.
            $mergedEntityId = $mergeRcId = null;
            $this->helper->parseEntityId(
                $mergeRedflyId,
                $type,
                $mergedEntityId,
                $version,
                $mergeRcId
            );
            if ( $type !== self::EntityCode ) {
                  throw new Exception("Not a reporter construct id: \"" . $mergeRedflyId . "\"");
            }
            $rcHelper->deleteVersion($mergeRcId);
        }

        return $data;
    }
    // --------------------------------------------------------------------------------
    // Return help for the "reject" action
    // --------------------------------------------------------------------------------
    public function rejectHelp()
    {
        $description = "Reject one or more RC from the approval queue.";
        $options = array(
            "email_curators" => "TRUE to send email to the curators and include the rejection message",
            "delete_items"   => "TRUE to mark the items for deletion from the approval queue",
            "message"        => "Optional message to send to the curators",
            "names"          => "An array of one or more RC and curator names encoded as JSON",
            "redfly_ids"     => "An array of one or more redfly ids encoded as JSON"
        );

        return RestResponse::factory(
            true,
            $description,
            $options
        );
    }
    // --------------------------------------------------------------------------------
    // Reject one (currently used) or more (rarely used, now it is the responsability
    // of the batch audit interface) reporter construct(s).
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
        $emailMessage = ( isset($arguments["message"]) && (! empty($arguments["message"]))
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
                    throw new Exception($redflyId . " is not a valid RC id");
                }
                $sql = "UPDATE ReporterConstruct
                    SET state = '";
                $sql .= ( ! $deleteItems
                    ? "editing"
                    : "deleted");
                $sql .= "',
                    last_audit = NOW(),
                    auditor_id = " . Auth::getUser()->userId();
                $sql .= ( $dbId !== null
                    ? " WHERE entity_id IS NULL and rc_id = ?"
                    : " WHERE entity_id = ? AND version = ?" );
                $sql .= " LIMIT 1";
                if ( ($statement = $this->db->getHandle()->prepare($sql)) === false ) {
                    throw new Exception("Error preparing statement: " .
                        $sql . ", " . $this->db->getError());
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
                    throw new Exception("Error executing statement: " .
                        $sql . ", " . $statement->error);
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
        try {
            foreach ( $redflyIdList as $index => $redflyId ) {
                $type = $entityId = $version = $dbId = null;
                $this->helper->parseEntityId(
                    $redflyId,
                    $type,
                    $entityId,
                    $version,
                    $dbId
                );
                if ( $type !== self::EntityCode ) {
                    throw new Exception($redflyId . " is not a valid RC id");
                }
                $sql = "SELECT pubmed_id 
                        FROM ReporterConstruct";
                $sql .= ( $dbId !== null
                    ? " WHERE entity_id IS NULL and rc_id = " . $dbId
                    : " WHERE entity_id = " . $entityId .  " AND version = " . $version );
                $sql .= " LIMIT 1";
                $result = $this->db->query($sql);
                if ( ($row = $result->fetch_assoc()) === null ) {
                    throw new Exception("Failed to find " . $redflyId);
                }
                $rejectList .= $redflyId . " (" .
                    "name: " . $nameList[$index]->name . ", " .
                    "PMID: " . $row["pubmed_id"] . ", " .
                    "curator: " . $nameList[$index]->curator . ")\n";
            }
        } catch ( Exception $e ) {
            return RestResponse::factory(
                false,
                "Error: " . $e->getMessage()
            );
        }
        if ( $emailCurators ) {
            $curatorEmailList = $this->helper->getCuratorEmails($redflyIdList);
            if ( count($curatorEmailList) === 0 ) {
                return RestResponse::factory(
                    false,
                    "Error: No curator/auditor email address from the database"
                );
            }
            $body = "The following RC was rejected" .
                ( $deleteItems
                    ? " and marked for deletion"
                    : "" ) .
                " by " . Auth::getUser()->fullName() . "\n\n$rejectList";
            $body .= "\n" .
                ( $emailMessage !== null
                    ? $emailMessage
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
                    "provider" => new League\OAuth2\Client\Provider\Google([
                        "clientId" => $GLOBALS["options"]->email->gmail_client_id,
                        "clientSecret" => $GLOBALS["options"]->email->gmail_client_secret
                    ]),
                    "clientId" => $GLOBALS["options"]->email->gmail_client_id,
                    "clientSecret" => $GLOBALS["options"]->email->gmail_client_secret,
                    "refreshToken" => $GLOBALS["options"]->email->gmail_refresh_token,
                    "userName" => $GLOBALS["options"]->email->gmail_address
                ])
            );
            $mail->CharSet = "utf-8";
            $mail->Subject = "[REDfly] RC rejected";
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
            "Rejected:\n" . $rejectList
        );
    }
    // --------------------------------------------------------------------------------
    // Return help for the "images" action
    // --------------------------------------------------------------------------------
    public function imagesHelp()
    {
        $description = "Return data for the specified Reporter Construct";
        $options = array("redfly_id" => "The REDfly identifier (or an array of them) " .
            "for the reporter construct (e.g., RFRC:0000312.001)");

        return RestResponse::factory(
            true,
            $description,
            $options
        );
    }
    // --------------------------------------------------------------------------------
    // Retrieve images associated with one more entities. Images must be queried
    // through their own API call due to a limitation of the ExtJS JsonStore to
    // parse nested arrays.
    // --------------------------------------------------------------------------------
    public function imagesAction(
        array $arguments,
        array $postData = null
    ) {
        $additionalJoins = array();
        $sqlCriteria = array();
        $sqlGroupBy = array();
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
        $sql = <<<SQL
        SELECT rc.rc_id AS id, 
            rc.entity_id, 
            rc.version, 
            rc.pubmed_id,
            GROUP_CONCAT(fei.label) AS flyexpress_labels
        FROM ReporterConstruct rc
        LEFT OUTER JOIN RC_has_FigureLabel l ON rc.rc_id = l.rc_id
        LEFT OUTER JOIN ext_FlyExpressImage fei ON rc.pubmed_id = fei.pubmed_id AND
            l.label = fei.label
SQL;
        $sqlCriteria[] = "( rc.state = 'current' OR rc.state = 'archived' )";
        $sqlCriteria[] = "l.label IS NOT NULL";
        $sqlGroupBy[] = "rc.rc_id";
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
            $generalOptions = $GLOBALS["options"]->general;
            while ( $row = $queryResult->fetch_assoc() ) {
                // The left outer join in the query will return a single row containing
                // all null values if there were no images found. If this is the case,
                // skip it.
                if ( ($row["entity_id"] === null) &&
                   ($row["version"] === null) &&
                   ($row["id"] === null) ) {
                    continue;
                }
                // If the RC has labels, but they are not found in ext_FlyExpressImage,
                // then flyexpress_labels will be null. If this is the case, skip it.
                if ( $row["flyexpress_labels"] === null ) {
                    continue;
                }
                $this->addRedflyIdToResultRow($row);
                $matchingLabels = explode(",", $row["flyexpress_labels"]);
                foreach ( $matchingLabels as $label ) {
                    $imageFile = "/PubMed" . $row["pubmed_id"] . "_" . $label . "_s.jpg";
                    //$imagePath = $generalOptions->site_base_dir . "/" . $generalOptions->flyexpress_image_dir;
                    $results[] = array(
                        "redfly_id" => $row["redfly_id"],
                        "image"     => $generalOptions->flyexpress_image_url . "/" . $imageFile,
                        "target"    => $generalOptions->flyexpress_url
                    );
                }
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
    // Return help for the "associated_tfbs" action
    // --------------------------------------------------------------------------------
    public function associated_tfbsHelp()
    {
        $description = "Return any binding sites associated with this reporter construct.";
        $options = array(
            "redfly_id" => "The REDfly identifier (or an array of them) for the entity " .
                "(e.g., RFRC:0000312.001)",
            "sort"      => "Sort field. Valid options are: name, fbbt"
        );

        return RestResponse::factory(
            true,
            $description,
            $options
        );
    }
    // --------------------------------------------------------------------------------
    // List the entities
    // --------------------------------------------------------------------------------
    public function associated_tfbsAction(
        array $arguments,
        array $postData = null
    ) {
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
                case "sort":
                    $sortInformation = $this->helper->extractSortInformation($value);
                    foreach ( $sortInformation as $sortColumn => $direction ) {
                        switch ( $sortColumn ) {
                            case "name":
                                $sqlOrderBy[] = "bs.name $direction";
                                break;
                            case "redfly_id":
                                $sqlOrderBy[] = "bs.entity_id $direction, bs.version $direction";
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
        if ( ! $redflyIdProvided ) {
            return RestResponse::factory(
                false,
                "REDfly id not provided"
            );
        }
        $sqlCriteria[] = "( rc.state = 'current' OR rc.state = 'archived' )";
        $sqlCriteria[] = "rc.has_tfbs = 1";
        $sql = <<<SQL
        SELECT tfbs.tfbs_id AS id, 
            tfbs.name, 
            tfbs.entity_id, 
            tfbs.version
        FROM ReporterConstruct rc
        INNER JOIN RC_associated_BS assoc ON rc.rc_id = assoc.rc_id
        INNER JOIN BindingSite tfbs ON assoc.tfbs_id = tfbs.tfbs_id
SQL;
        $this->helper->constructQuery(
            $sql,
            $additionalJoins,
            $sqlCriteria,
            array(),
            $sqlOrderBy,
            $limitStr
        );
        try {
            $queryResult = $this->db->query($sql);
            $results = array();
            while ( $row = $queryResult->fetch_assoc() ) {
                $newRow = $row;
                $newRow["redfly_id"] = $this->helper->entityId(
                    TranscriptionfactorbindingsiteHandler::EntityCode,
                    $row["entity_id"],
                    $row["version"],
                    $row["id"]
                );
                $newRow["redfly_id_unversioned"] = $this->helper->unversionedEntityId(
                    TranscriptionfactorbindingsiteHandler::EntityCode,
                    $row["entity_id"],
                    $row["id"]
                );
                unset($newRow["entity_id"]);
                unset($newRow["version"]);
                unset($newRow["id"]);
                $results[] = $newRow;
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
    // Return help for the "minimization" action
    // --------------------------------------------------------------------------------
    public function minimizationHelp()
    {
        $description = "Performs RC minimization and returns a report detailing " .
            "changes to RC minimization status";

        return RestResponse::factory(
            true,
            $description
        );
    }
    // --------------------------------------------------------------------------------
    // Perform RC minimization.
    // --------------------------------------------------------------------------------
    public function minimizationAction(
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
        $rcHelper = RcHelper::factory();
        try {
            $report = $rcHelper->minimizeAllRcs();
        } catch ( Exception $e ) {
            return RestResponse::factory(
                false,
                $e->getMessage()
            );
        }
        $data = array();
        foreach ( $report as $line ) {
            $data[] = array(
                $line["name"],
                $line["coord"],
                join("\n", $line["messages"])
            );
        }
        if ( count($data) !==  0 ) {
            $csvData = array_merge(
                array(array("Name", "Coordinates", "Messages")),
                $data
            );
            $file = $GLOBALS["options"]->general->report_dir . "/minimization-" .
                  date("Y-m-d_H-i-s") . ".csv";
            CsvHelper::createFile(
                $file,
                $csvData
            );
        }
        foreach ( $report as &$line ) {
            $line["messages"] = json_encode($line["messages"]);
        }

        return RestResponse::factory(
            true,
            null,
            $report
        );
    }
    // --------------------------------------------------------------------------------
    // Return help for the "calculateCrm" action
    // --------------------------------------------------------------------------------
    public function calculateCrmHelp()
    {
        $description = "Calculates CRMs and returns a report detailing changes to RCs " .
            "and manually overriden CRMs that enclose RCs";

        return RestResponse::factory(
            true,
            $description
        );
    }
    // --------------------------------------------------------------------------------
    // Calculate CRMs.
    // --------------------------------------------------------------------------------
    public function calculateCrmAction(
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
        $crmHelper = CrmHelper::factory();
        try {
            $report = $crmHelper->findAllCRMs();
        } catch ( Exception $e ) {
            return RestResponse::factory(
                false,
                $e->getMessage()
            );
        }
        $noticeData = array();
        $alertData = array();
        foreach ( $report as $line ) {
            if ( isset($line["override_alert"]) &&
                $line["override_alert"] ) {
                $alertData[] = array(
                    $line["name"],
                    $line["coord"],
                    join("\n", $line["messages"])
                );
            } else {
                $noticeData[] = array(
                    $line["name"],
                    $line["coord"],
                    join("\n", $line["messages"])
                );
            }
        }
        if ( (count($noticeData) !== 0) ||
            (count($alertData) !== 0) ) {
            $csvData = array_merge(
                array(array("Name", "Coordinates", "Messages")),
                $noticeData,
                array(array()),
                $alertData
            );
            $file = $GLOBALS["options"]->general->report_dir . "/crm-" .
                  date("Y-m-d_H-i-s") . ".csv";
            CsvHelper::createFile(
                $file,
                $csvData
            );
        }
        foreach ( $report as &$line ) {
            $line["messages"] = json_encode($line["messages"]);
        }

        return RestResponse::factory(
            true,
            null,
            $report
        );
    }
}
