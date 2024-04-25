<?php
class TranscriptionfactorbindingsiteHandler implements iEditable
{
    // Code to be used when generating a REDfly ID for this entity
    // (e.g. RFTF:00000000.000)
    const EntityCode = "TF";
    // Return formats used by the search action to present different views of the
    // data.
    // Summary list for searching
    const VIEW_DEFAULT = "default";
    // Full list for displaying TFBSs
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
        return new TranscriptionfactorbindingsiteHandler;
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
            ($type !== self::EntityCode) ) {
            throw new Exception("Not a transcription factor binding site identifier: " . $redflyId);
        }
        if ( $dbId !== null ) {
            $sqlFragment = "( tfbs.tfbs_id = " . $dbId . " )";
        } elseif ( $version !== null ) {
            $sqlFragment = "( tfbs.entity_id = " . $entityId . " AND tfbs.version = " . $version . " )";
        } else {
            $sqlFragment = "( tfbs.entity_id = " . $entityId . " AND tfbs.state = 'current' )";
        }

        return $sqlFragment;
    }
    // --------------------------------------------------------------------------------
    // Query the database for a list of transcription factor binding site summaries
    // based on the criteria provided. It is for the public view.
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
        SELECT SQL_CALC_FOUND_ROWS tfbs.tfbs_id AS id,
            sfs.scientific_name AS sequence_from_species_scientific_name,
            ais.scientific_name AS assayed_in_species_scientific_name,
            tfbs.name,
            CONCAT('RFTF', ':', LPAD(CAST(tfbs.entity_id AS CHAR), 10, '0'), '.' , LPAD(CAST(tfbs.version AS CHAR), 3, '0')) AS redfly_id,
            tfbs.gene_id,
            g.name AS gene,
            tfbs.tf_id,
            tf.name AS tf,
            tfbs.pubmed_id,
            CONCAT(c.name, ':', tfbs.current_start, '..', tfbs.current_end) AS coordinates,
            IF(ISNULL(GROUP_CONCAT(fei.label)), 0, 1) AS has_images
        FROM BindingSite tfbs
        INNER JOIN Species sfs ON tfbs.sequence_from_species_id = sfs.species_id
        INNER JOIN Species ais ON tfbs.assayed_in_species_id = ais.species_id
        INNER JOIN Gene g ON tfbs.gene_id = g.gene_id
        INNER JOIN Gene tf ON tfbs.tf_id = tf.gene_id
        INNER JOIN Chromosome c ON tfbs.chromosome_id = c.chromosome_id
        LEFT OUTER JOIN BS_has_FigureLabel l ON tfbs.tfbs_id = l.tfbs_id
        LEFT OUTER JOIN ext_FlyExpressImage fei ON tfbs.pubmed_id = fei.pubmed_id AND
            l.label = fei.label
SQL;
        $sqlGroupBy[] = "tfbs.tfbs_id";
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
    // Query the database for a transcription factor binding site list based on the
    // criteria provided.
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
            tfbs.tfbs_id AS id,
            tfbs.name,
            tfbs.entity_id,
            tfbs.version,
            tfbs.current_genome_assembly_release_version AS release_version,
            tfbs.current_start AS start,
            tfbs.current_end AS end,
            tfbs.archived_genome_assembly_release_versions,
            tfbs.archived_starts,
            tfbs.archived_ends,
            tfbs.notes,
            tfbs.pubmed_id,
            tfbs.sequence,
            tfbs.sequence_with_flank,
            tfbs.chromosome_id AS chromosome_id,
            tfbs.evidence_id AS evidence_id,
            tfbs.gene_id AS gene_id,
            g.name AS gene_name,
            g.identifier AS gene_identifier,
            g.start AS gene_start,
            g.stop AS gene_stop,
            tfbs.tf_id AS tf_id,
            tf.name AS tf_name,
            tf.identifier AS tf_identifier,
            c.name AS chromosome,
            ev.term AS evidence_term,
            tfbs.date_added,
            tfbs.curator_id AS curator_id,
            CONCAT(curator.first_name, ' ', curator.last_name) AS curator_full_name,
            tfbs.last_update,
            IF(ISNULL(tfbs.auditor_id), '', CONCAT(auditor.first_name, ' ', auditor.last_name)) AS auditor_full_name,
            tfbs.last_audit,
            cite.contents,
            sfs.public_database_names,
            sfs.public_database_links,
            sfs.public_browser_names,
            sfs.public_browser_links
        FROM BindingSite tfbs
        INNER JOIN Species sfs ON tfbs.sequence_from_species_id = sfs.species_id
        INNER JOIN Species ais ON tfbs.assayed_in_species_id = ais.species_id
        INNER JOIN Gene g ON tfbs.gene_id = g.gene_id
        INNER JOIN Gene tf ON tfbs.tf_id = tf.gene_id
        INNER JOIN Chromosome c ON tfbs.chromosome_id = c.chromosome_id
        INNER JOIN EvidenceTerm ev ON tfbs.evidence_id = ev.evidence_id
        INNER JOIN Users curator ON tfbs.curator_id = curator.user_id
        LEFT OUTER JOIN Users auditor ON tfbs.auditor_id = auditor.user_id
        INNER JOIN Citation cite ON tfbs.pubmed_id = cite.external_id AND
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
                    "transcription_factor_binding_site",
                    $row["name"],
                    $tmpRow["id"]
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
    // Query the database for a single transcription factor binding site based on the
    // criteria provided.
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
    private function querySingleTf(
        array $additionalJoins,
        array $sqlCriteria,
        array $sqlGroupBy,
        array $sqlOrderBy,
        $limitStr
    ) {
        $sql = <<<SQL
        SELECT sfs.species_id AS sequence_from_species_id,
            sfs.short_name AS sequence_from_species_short_name,
            sfs.scientific_name AS sequence_from_species_scientific_name,
            ais.species_id AS assayed_in_species_id,
            ais.short_name AS assayed_in_species_short_name,
            ais.scientific_name AS assayed_in_species_scientific_name,
            tfbs.tfbs_id AS id,
            tfbs.name,
            tfbs.version,
            tfbs.gene_id,
            g.identifier AS gene_identifier,
            g.name AS gene_name,
            tfbs.tf_id,
            tf.identifier AS tf_identifier,
            tf.name AS tf_name,
            tfbs.evidence_id,
            e.term AS evidence_term,
            tfbs.current_genome_assembly_release_version AS release_version,
            tfbs.current_start AS start,
            tfbs.current_end AS end,
            tfbs.archived_genome_assembly_release_versions,
            tfbs.archived_starts,
            tfbs.archived_ends,
            tfbs.entity_id,
            tfbs.state,
            tfbs.chromosome_id,
            chr.name AS chromosome,
            tfbs.pubmed_id,
            tfbs.figure_labels,
            tfbs.notes,
            tfbs.sequence,
            tfbs.sequence_with_flank,
            cite.contents AS citation,
            cite.author_email,
            tfbs.date_added,
            DATE_FORMAT(tfbs.date_added, '%M %D, %Y at %l:%i:%s%p') AS date_added_formatted,
            curator.user_id AS curator_id,
            CONCAT(curator.first_name, ' ', curator.last_name) AS curator_full_name,
            tfbs.last_update,
            DATE_FORMAT(tfbs.last_update, '%M %D, %Y at %l:%i:%s%p') AS last_update_formatted,
            auditor.user_id AS auditor_id,
            IF(ISNULL(tfbs.auditor_id), '', CONCAT(auditor.first_name, ' ', auditor.last_name)) AS auditor_full_name,
            tfbs.last_audit,
            DATE_FORMAT(tfbs.last_audit, '%M %D, %Y at %l:%i:%s%p') AS last_audit_formatted,
            tfbs.archive_date,
            DATE_FORMAT(tfbs.archive_date, '%M %D, %Y at %l:%i:%s%p') AS archive_date_formatted
        FROM BindingSite tfbs
        INNER JOIN Species sfs ON tfbs.sequence_from_species_id = sfs.species_id
        INNER JOIN Species ais ON tfbs.assayed_in_species_id = ais.species_id
        INNER JOIN Gene g ON tfbs.gene_id = g.gene_id
        INNER JOIN Gene tf ON tfbs.tf_id = tf.gene_id
        INNER JOIN Chromosome chr ON tfbs.chromosome_id = chr.chromosome_id
        INNER JOIN EvidenceTerm e ON tfbs.evidence_id = e.evidence_id
        INNER JOIN Citation cite ON tfbs.pubmed_id = cite.external_id AND
            cite.citation_type = 'PUBMED'
        INNER JOIN Users curator ON tfbs.curator_id = curator.user_id
        LEFT OUTER JOIN Users auditor ON tfbs.auditor_id = auditor.user_id
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
            $row = $queryResult->fetch_assoc();
            // Strip spaces out of the sequence
            $row["sequence"] = preg_replace(
                "/\s+/",
                "",
                $row["sequence"]
            );
            $row["sequence_with_flank"] = preg_replace(
                "/\s+/",
                "",
                $row["sequence_with_flank"]
            );
            $this->addRedflyIdToResultRow($row);
            unset($row["id"]);
            $results = $row;
        } catch ( Exception $e ) {
            return RestResponse::factory(
                false,
                $e->getMessage()
            );
        }

        return RestResponse::factory(
            true,
            null,
            array($results)
        );
    }
    // --------------------------------------------------------------------------------
    // Query the database for a list of transcription factor binding site summaries
    // based on the criteria provided. This search is tailored to the curator search
    // tool.
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
        // A criteria clause such as "tfbs.state = 'current'" cannot be used
        // directly because an "editing" version of a "current" TFBS may
        // exist and so the "current" version should not be returned.
        // Likewise for the "approval", "approved", "archived", and
        // "deleted" versions.
        $pattern = '/
            ^           # Start
            \s*         # Possible whitespace
            tfbs\.state   # state column
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
        SELECT tfbs.tfbs_id AS id,
            tfbs.entity_id,
            tfbs.version,
            tfbs.name,
            tfbs.state,
            sfs.species_id AS sequence_from_species_id,
            sfs.short_name AS sequence_from_species_short_name,
            sfs.scientific_name AS sequence_from_species_scientific_name,
            ais.species_id AS assayed_in_species_id,
            ais.short_name AS assayed_in_species_short_name,
            ais.scientific_name AS assayed_in_species_scientific_name,
            tfbs.gene_id,
            g.name AS gene,
            tfbs.tf_id,
            tf.name AS transcription_factor,
            c.name AS chromosome,
            tfbs.current_genome_assembly_release_version AS release_version,
            tfbs.current_start AS start,
            tfbs.current_end AS end,
            tfbs.pubmed_id AS pmid,
            tfbs.curator_id,
            CONCAT(curator.first_name, ' ', curator.last_name) AS curator_full_name,
            DATE_FORMAT(tfbs.date_added, '%Y-%m-%d %h%p:%i:%s') AS date_added,
            DATE_FORMAT(tfbs.last_update, '%Y-%m-%d %h%p:%i:%s') AS last_update,
            tfbs.auditor_id,
            IF(ISNULL(tfbs.auditor_id), '', CONCAT(auditor.first_name, ' ', auditor.last_name)) AS auditor_full_name,
            IF(ISNULL(tfbs.auditor_id), '', DATE_FORMAT(tfbs.last_audit, '%Y-%m-%d %h%p:%i:%s')) AS last_audit,
            DATE_FORMAT(tfbs.archive_date, '%Y-%m-%d %h%p:%i:%s') AS archive_date,
SQL;
        switch ( $stateValue ) {
            case "":
                // 1) All the new entities (still unversioned)
                //    which entity identifier is null and
                // 2) the latest versions, that is, internal identifiers
                //    (rc_id), of all the entities which entity identifier
                //    is not null
                $sql = $sqlBase . <<<SQL
                    IF(tfbs.state != 'archived', TRUE, FALSE) AS editable
                FROM (SELECT tfbs_id,
                          entity_id
                      FROM BindingSite
                      WHERE entity_id IS NULL
                      UNION
                      SELECT MAX(tfbs_id) AS tfbs_id,
                          entity_id
                      FROM BindingSite
                      WHERE entity_id IS NOT NULL
                      GROUP BY entity_id) AS optimized_tfbs
                INNER JOIN BindingSite tfbs ON optimized_tfbs.tfbs_id = tfbs.tfbs_id
                INNER JOIN Species sfs ON tfbs.sequence_from_species_id = sfs.species_id
                INNER JOIN Species ais ON tfbs.assayed_in_species_id = ais.species_id
                INNER JOIN Gene g ON tfbs.gene_id = g.gene_id
                INNER JOIN Gene tf ON tfbs.tf_id = tf.gene_id
                INNER JOIN Chromosome c ON tfbs.chromosome_id = c.chromosome_id
                INNER JOIN Users curator ON tfbs.curator_id = curator.user_id
                LEFT OUTER JOIN Users auditor ON tfbs.auditor_id = auditor.user_id
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
                      FROM BindingSite
                      WHERE state = 'archived'
                      GROUP BY entity_id) AS optimized_tfbs
                INNER JOIN BindingSite tfbs ON optimized_tfbs.entity_id = tfbs.entity_id AND
                    optimized_tfbs.last_archived_version = tfbs.version
                INNER JOIN Species sfs ON tfbs.sequence_from_species_id = sfs.species_id
                INNER JOIN Species ais ON tfbs.assayed_in_species_id = ais.species_id
                INNER JOIN Gene g ON tfbs.gene_id = g.gene_id
                INNER JOIN Gene tf ON tfbs.tf_id = tf.gene_id
                INNER JOIN Chromosome c ON tfbs.chromosome_id = c.chromosome_id
                INNER JOIN Users curator ON tfbs.curator_id = curator.user_id
                LEFT OUTER JOIN Users auditor ON tfbs.auditor_id = auditor.user_id
SQL;
                break;
            // There can be a newer version of a common entity having a
            // state different from the "current" state. So any "current"
            // version must not be shown if there is a newer version of its
            // same entity.
            case "current":
                $sql = $sqlBase . <<<SQL
                    IF(tfbs.version = optimized_tfbs.last_version, TRUE, FALSE) AS editable
                FROM (SELECT entity_id,
                          MAX(version) AS last_version
                      FROM BindingSite
                      WHERE entity_id IS NOT NULL
                      GROUP BY entity_id) AS optimized_tfbs
                INNER JOIN BindingSite tfbs ON optimized_tfbs.entity_id = tfbs.entity_id AND
                    optimized_tfbs.last_version = tfbs.version
                INNER JOIN Species sfs ON tfbs.sequence_from_species_id = sfs.species_id
                INNER JOIN Species ais ON tfbs.assayed_in_species_id = ais.species_id
                INNER JOIN Gene g ON tfbs.gene_id = g.gene_id
                INNER JOIN Gene tf ON tfbs.tf_id = tf.gene_id
                INNER JOIN Chromosome c ON tfbs.chromosome_id = c.chromosome_id
                INNER JOIN Users curator ON tfbs.curator_id = curator.user_id
                LEFT OUTER JOIN Users auditor ON tfbs.auditor_id = auditor.user_id
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
                FROM BindingSite tfbs
                INNER JOIN Species sfs ON tfbs.sequence_from_species_id = sfs.species_id
                INNER JOIN Species ais ON tfbs.assayed_in_species_id = ais.species_id
                INNER JOIN Gene g ON tfbs.gene_id = g.gene_id
                INNER JOIN Gene tf ON tfbs.tf_id = tf.gene_id
                INNER JOIN Chromosome c ON tfbs.chromosome_id = c.chromosome_id
                INNER JOIN Users curator ON tfbs.curator_id = curator.user_id
                LEFT OUTER JOIN Users auditor ON tfbs.auditor_id = auditor.user_id
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
        $description = "Return a short list of transcription factor binding sites matching the specified criteria. " .
            "The list will contain the following fields: " .
            "(id, species, name, gene, gene_id, tf, tf_id, redfly_id)";
        $options = array(
            "anatomical_expression_term"       => "List only entities containing this expression term inherited " .
                                                  "from any associated reporter constructs",
            "assayed_in_species_id"            => "List only entities that have an \"Assayed In\" species",
            "auditor_id"                       => "List only entities audited by the auditor",
            "chr_end"                          => "List only entities having the coordinate end with its predefined error margin " .
                                                  "(most recent coordinate release)",
            "chr_id"                           => "List only entities with this chromosome (internal id)",
            "chr_start"                        => "List only entities having the coordinate start with its predefined error margin " .
                                                  "(most recent coordinate release)",
            "curator_id"                       => "The id of the curator who maintains this entity",
            "date_added"                       => "List only entities addedd based on this date " .
                                                  "(UNIX timestamp in seconds since epoch)",
            "evidence_id"                      => "List only entities with this evidence term id",
            "exact_anatomical_expression_term" => "If TRUE list only entities containing the exact expression term, " .
                                                  "if FALSE list entities containing the term and any descendants " .
                                                  "according to the ontology",
            "five_prime"                       => "List only entities that have some portion 5' of all the transcripts",
            "gene_identifier"                  => "List only entities with this gene (identifier)",
            "gene_id"                          => "List only entities with this gene (internal id)",
            "gene_restrictions"                => "Filter each one of both target and transcription factor gene names",
            "has_images"                       => "List only entities that have figure information stored in the database",
            "has_rc"                           => "List only entities that have an associated reporter construct",
            "in_exon"                          => "List only entities that overlap or are included within an exon",
            "in_intron"                        => "List only entities that overlap or are included within an intron",
            "last_audit"                       => "List only entities last auditor audited based on this date " .
                                                  "(UNIX timestamp in seconds since epoch)",
            "last_update"                      => "List only entities last updated based on this date " .
                                                  "(UNIX timestamp in seconds since epoch)",
            "limit"                            => "Maximium number of entities to return",
            "maximum_sequence_size"            => "List only entities with a sequence of this size or less",
            "name"                             => "List only entities that match this name",
            "pubmed_id"                        => "List only entities that match this Pubmed Id",
            "redfly_id"                        => "The REDfly identifier (or an array of them) for the transcription " .
                                                  "factor binding site (e.g., RFTF:0000312.001)",
            "sequence_from_species_id"         => "List only entities that have a \"Sequence From\" species",
            "sort"                             => "Sort field. Valid options are: name, gene, tf, pmid, chr",
            "start"                            => "Offset of the first entity to return (requires \"limit\")",
            "state"                            => "List only entities having such a state",
            "tf_identifier"                    => "List only entities with this TF (identifier id)",
            "tf_id"                            => "List only entities with this TF (internal id)",
            "three_prime"                      => "List only entities that have some portion 3' of all the transcripts",
            "view"                             => "Set the view to use for the returned results. " .
                                                  "Valid values are \"default\", \"full\", \"curator\""
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
        $geneIdSqlCriteria = "";
        $tfIdSqlCriteria = "";
        $searchBothGeneAndTF = false;
        // Any image, it defaults to 0 (false)
        $hasImages = 0;
        $coordinateStart = 0;
        $coordinateEnd = 0;
        // The anatomical expression identifier provided
        $anatomicalExpressionIdentifier = "";
        // Match the exact anatomical expression identifier, if provided
        $exactAnatomicalExpressionIdentifier = false;
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
                    $sqlCriteria[] = "tfbs.assayed_in_species_id = " . $value;
                    break;
                case "auditor_id":
                    $sqlCriteria[] = "tfbs.auditor_id = " . $value;
                    break;
                case "biological_process_identifier":
                    // Not applied here since such transcription factor binding sites lack any biological process.
                    $sqlCriteria[] = "0";
                    break;
                case "chr_end":
                    if ( is_numeric($value) ) {
                        $coordinateEnd = (int)$value;
                    }
                    break;
                case "chr_id":
                    if ( is_numeric($value) ) {
                        $sqlCriteria[] = "tfbs.chromosome_id = " . $value;
                    }
                    break;
                case "chr_start":
                    if ( is_numeric($value) ) {
                        $coordinateStart = (int)$value;
                    }
                    break;
                case "curator_id":
                    $sqlCriteria[] = "tfbs.curator_id = " . $value;
                    break;
                case "date_added":
                    $sqlCriteria[] = "tfbs.date_added " . $sqlOperator . " FROM_UNIXTIME(" . $value . ")";
                    break;
                case "developmental_stage_identifier":
                    // Not applied here since such transcription factor binding sites lack any developmental stage.
                    $sqlCriteria[] = "0";
                    break;
                case "enhancer_or_silencer_attribute":
                    // Not applied here since such transcription factor binding sites lack any enhancer/silencer attribute.
                    $sqlCriteria[] = "0";
                    break;
                case "evidence_id":
                    if ( is_numeric($value) ) {
                        $sqlCriteria[] = "tfbs.evidence_id = " . $value;
                    }
                    break;
                case "exact_anatomical_expression_identifier":
                    $exactAnatomicalExpressionIdentifier = $this->helper->convertValueToBool($value);
                    break;
                case "fbtp_identifier":
                    // As any FlyBase transgenic construct identifier is not applied for TFBSs,
                    // a zero is given so that nada is returned from the search consult.
                    $sqlCriteria[] = "0";
                    break;
                case "five_prime":
                    if ( $this->helper->convertValueToBool($value) ) {
                        $additionalJoins[] = "INNER JOIN v_transcription_factor_binding_site_feature_location location_5 " .
                            "ON (tfbs.tfbs_id = location_5.id AND location_5.type = 'mrna')";
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
                            $geneIdSqlCriteria = "(tfbs.gene_id = " . $value . ")";
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
                            $geneIdSqlCriteria = "(tfbs.gene_id = " . $value .
                                ") OR (tfbs.chromosome_id = " . $chromosomeIdentifier . " AND " .
                                $intervalStart . " <= tfbs.current_start AND tfbs.current_end <= " . $intervalStop .
                                ")";
                        }
                    }
                    break;
                case "gene_restrictions":
                    // This will modify the normal filtering of gene_id, gene_fbgn, tf_id,and tf_fbgn and
                    // then search on any of them fields.
                    $searchBothGeneAndTF = ( $value === "both" );
                    break;
                case "has_images":
                    $hasImages = $this->helper->convertValueToBool($value);
                    break;
                case "has_rc":
                    $sqlCriteria[] = "tfbs.has_rc = " . $this->helper->convertValueToBool($value);
                    break;
                case "in_exon":
                    if ( $this->helper->convertValueToBool($value) ) {
                        $additionalJoins[] = "INNER JOIN v_transcription_factor_binding_site_feature_location location_e " .
                            "ON (tfbs.tfbs_id = location_e.id AND location_e.type = 'exon')";
                        $sqlCriteria[] = "(location_e.relative_start = 0 OR location_e.relative_end = 0)";
                        $sqlGroupBy[] = "location_e.id";
                    }
                    break;
                case "in_intron":
                    if ( $this->helper->convertValueToBool($value) ) {
                        $additionalJoins[] = "INNER JOIN v_transcription_factor_binding_site_feature_location location_i " .
                            "ON (tfbs.tfbs_id = location_i.id AND location_i.type = 'intron')";
                        $sqlCriteria[] = "(location_i.relative_start = 0 OR location_i.relative_end = 0)";
                        $sqlGroupBy[] = "location_i.id";
                    }
                    break;
                case "is_crm":
                    // Not applied here since such TFBSs lack any "is_crm" attribute.
                    break;
                case "is_minimalized":
                    // Not applied here since such TFBSs lack any "is_minimalized" attribute.
                    break;
                case "is_negative":
                    // Not applied here since such TFBSs lack any "is_negative" attribute.
                    break;
                case "last_audit":
                    $sqlCriteria[] = "tfbs.last_audit " . $sqlOperator . " FROM_UNIXTIME(" . $value . ")";
                    break;
                case "last_update":
                    $sqlCriteria[] = "tfbs.last_update " . $sqlOperator . " FROM_UNIXTIME(" . $value . ")";
                    break;
                case "limit":
                    $limitStr = $this->helper->constructLimitStr($arguments);
                    break;
                case "maximum_sequence_size":
                    if ( is_numeric($value) ) {
                        $sqlCriteria[] = "tfbs.size <= " . $value;
                    }
                    break;
                case "name":
                    if ( $value !== "%" ) {
                        $sqlCriteria[] = str_replace(
                            "_",
                            "\_",
                            "LOWER(tfbs.name) LIKE LOWER(" . $this->db->escape($value, true) . ")"
                        );
                    }
                    break;
                case "pubmed_id":
                    $sqlCriteria[] = "tfbs.pubmed_id = " . $this->db->escape($value, true);
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
                    $sqlCriteria[] = "tfbs.sequence_from_species_id = " . $value;
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
                                $sqlOrderBy[] = "tfbs.name " . $direction;
                                break;
                            case "pubmed_id":
                                $sqlOrderBy[] = "tfbs.pubmed_id " . $direction;
                                break;
                            case "tf":
                                $sqlOrderBy[] = "tf.name " . $direction;
                                break;
                            default:
                                break;
                        }
                    }
                    break;
                case "state":
                    $sqlCriteria[] = "tfbs.state = " . $this->db->escape($value, true);
                    break;
                case "tf_identifier":
                    $sqlCriteria[] = "tf.identifier " . $sqlOperator . " " . $this->db->escape($value, true);
                    break;
                case "transcription_factor_id":
                    $sqlCriteria[] = "tfbs.tf_id = " . $value;
                    break;
                case "tf_id":
                    if ( is_numeric($value) ) {
                        $tfIdSqlCriteria = "(tfbs.tf_id = " . $value . ")";
                    }
                    break;
                // This option is not advertized to the API
                // but is available for efficiency internally
                case "tfbs_id":
                    $value = ( ! is_array($value)
                        ? array($value)
                        : $value );
                    $tmpSqlCriteria = array();
                    foreach ( $value as $id ) {
                        $tmpSqlCriteria[] = $id;
                    }
                    $sqlCriteria[] = "tfbs_id IN (" . implode(",", $tmpSqlCriteria) . ")";
                    break;
                case "three_prime":
                    if ( $this->helper->convertValueToBool($value) ) {
                        $additionalJoins[] = "INNER JOIN v_transcription_factor_binding_site_feature_location location_3 " .
                            "ON (tfbs.tfbs_id = location_3.id AND location_3.type = 'mrna')";
                        $sqlCriteria[] = "(location_3.relative_start = location_3.relative_end " .
                            "AND location_3.relative_start = 3)";
                        $sqlGroupBy[] = "location_3.id";
                    }
                    break;
                case "view":
                    $returnFormat = trim($value);
                    break;
                default:
                    break;
            }
        }
        // If the "gene_restrictions" argument was set to "both" then filter
        // each one of both target and transcription factor gene names
        if ( ($searchBothGeneAndTF === true) &&
            ($geneIdSqlCriteria !== "") &&
            ($tfIdSqlCriteria !== "") ) {
            $sqlCriteria[] = "(". $geneIdSqlCriteria . " OR " . $tfIdSqlCriteria . ")";
        } else {
            if ( ($geneIdSqlCriteria !== "") &&
                ($tfIdSqlCriteria === "") ) {
                $sqlCriteria[] = $geneIdSqlCriteria;
            } else {
                if ( ($geneIdSqlCriteria === "") &&
                    ($tfIdSqlCriteria !== "") ) {
                    $sqlCriteria[] = $tfIdSqlCriteria;
                }
            }
        }
        // Include criteria for searching on images
        if ( $hasImages ) {
            // The query for the default view includes these joins so we can display
            // has_images in the search results so only add them if a different view
            // was selected.
            if ( $returnFormat !== self::VIEW_DEFAULT ) {
                $additionalJoins[] = "LEFT OUTER JOIN BS_has_FigureLabel l ON tfbs.tfbs_id = l.tfbs_id";
                $additionalJoins[] = "LEFT OUTER JOIN ext_FlyExpressImage fei ON tfbs.pubmed_id = fei.pubmed_id AND
                    l.label = fei.label";
            }
            $sqlCriteria[] = "fei.label IS NOT NULL";
        }
        // Include the criteria for searching anyone from the coordinate extremes
        $tfbsErrorMargin = $GLOBALS["options"]->tfbs->error_margin;
        if ( ($coordinateStart !== 0) &&
            ($coordinateEnd !== 0) ) {
            $sqlCriteria[] = ($coordinateStart - $tfbsErrorMargin) . " <= tfbs.current_start";
            $sqlCriteria[] = "tfbs.current_end <= " . ($coordinateEnd + $tfbsErrorMargin);
        } else {
            if ( ($coordinateStart !== 0) &&
                ($coordinateEnd === 0) ) {
                $sqlCriteria[] = ($coordinateStart - $tfbsErrorMargin) . " <= tfbs.current_start";
                if ( ($coordinateStart === 0) &&
                    ($coordinateEnd !== 0) ) {
                    $sqlCriteria[] = "tfbs.current_end <= " . ($coordinateEnd + $tfbsErrorMargin);
                }
            }
        }
        // Search the anatomical expression identifier provided
        // (only appplied for the Drosophila melanogaster species at the moment)
        if ( $anatomicalExpressionIdentifier !== "" ) {
            $additionalJoins[] = "INNER JOIN RC_associated_BS assoc ON tfbs.tfbs_id = assoc.tfbs_id";
            $additionalJoins[] = "INNER JOIN RC_has_ExprTerm etmap ON assoc.rc_id = etmap.rc_id";
            if ( $exactAnatomicalExpressionIdentifier ) {
                // Search only the anatomical expression identifier
                $additionalJoins[] = "INNER JOIN ExpressionTerm et ON " .
                    "(et.species_id = tfbs.assayed_in_species_id AND et.term_id = etmap.term_id AND " .
                    "et.identifier = " . $this->db->escape($anatomicalExpressionIdentifier, true) . ")";
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
                    "(et.species_id = tfbs.assayed_in_species_id AND et.term_id = etmap.term_id AND " .
                    "et.identifier IN (" .  implode(",", $anatomicalExpressionIdentifiersList) . "))";
            }
        }
        if ( $returnFormat !== self::VIEW_CURATOR ) {
            if ( $redflyIdProvided ) {
                $sqlCriteria[] = "( tfbs.state = 'current' OR tfbs.state = 'archived' )";
            } else {
                $sqlCriteria[] = "tfbs.state = 'current'";
            }
        }
        switch ( $returnFormat ) {
            // For the curator view of a list of transcription factor binding sites
            case self::VIEW_CURATOR:
                $response = $this->queryCuratorSummaryList(
                    $additionalJoins,
                    $sqlCriteria,
                    $sqlGroupBy,
                    $sqlOrderBy
                );
                break;
            // For the public view of a transcription factor binding site
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
            // For the public view of a list of transcription factor binding sites
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
        $description = "Return data for the specified transcription factor Binding Site";
        $options = array(
            "redfly_id" => "The REDfly identifier (or an array of them) " .
                "for the transcription factor binding site (e.g., RFTF:0000312.001)"
        );

        return RestResponse::factory(
            true,
            $description,
            $options
        );
    }
    // --------------------------------------------------------------------------------
    // Retrieve details for one or more entities. Associated reporter constructs and
    // expression terms (inherited through associated reporter constructs) are queried
    // through their own API call due to a limitation of the ExtJS JsonStore to parse
    // nested arrays.
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
    // Return help for the "associated_rc" action
    // --------------------------------------------------------------------------------
    public function associated_rcHelp()
    {
        $description = "Return any binding sites associated with this reporter construct.";
        $options = array(
            "redfly_id" => "The REDfly identifier (or an array of them) " .
                "for the entity (e.g., RFRC:0000312.001)",
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
    public function associated_rcAction(
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
                                $sqlOrderBy[] = "rc.name " . $direction;
                                break;
                            case "redfly_id":
                                $sqlOrderBy[] = "rc.entity_id " . $direction . ", rc.version " . $direction;
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
        // Only show current and archived items.
        $sqlCriteria[] = "( tfbs.state = 'current' OR tfbs.state = 'archived' )";
        $sqlCriteria[] = "tfbs.has_rc = 1";
        $sql = <<<SQL
        SELECT DISTINCT(rc.rc_id) AS id,
            rc.name,
            rc.entity_id,
            rc.version
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
                    ReporterconstructHandler::EntityCode,
                    $row["entity_id"],
                    $row["version"],
                    $row["id"]
                );
                $newRow["redfly_id_unversioned"] = $this->helper->unversionedEntityId(
                    ReporterconstructHandler::EntityCode,
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
    // Return help for the "checkName" action
    // --------------------------------------------------------------------------------
    public function checkNameHelp()
    {
        $description = "Return data for the specified transcription factor Binding Site";
        $options = array(
            "name"      => "The name of the transcription factor binding site",
            "redfly_id" => "The REDfly identifier for the transcription factor binding site (e.g., RFTF:0000312.001)."
        );

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
    public function checkNameAction(
        array $arguments,
        array $postData = null
    ) {
        $sqlCriteria = array();
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
            if ( $this->helper->convertWildcards($value) ) {
                $sqlOperator = "LIKE";
            }
            switch ( $argument ) {
                case "redfly_id":
                    if ( $sqlOperator !== "=" ) {
                        throw new Exception("SQL operator " . $sqlOperator . " not allowed with redfly_id");
                    }
                    $sqlCriteria[] = $this->redflyIdToSql($value);
                    break;
                case "name":
                    $sqlCriteria[] = "tfbs.name " . $sqlOperator . " " . $this->db->escape($value, true);
                    $name = $value;
                    break;
                default:
                    break;
            }
        }
        $sql = <<<SQL
        SELECT tfbs_id,
            name
        FROM BindingSite
SQL;
        if ( count($sqlCriteria) !== 0 ) {
            $sql .= " WHERE " . implode(" AND ", $sqlCriteria);
        }
        $result = $this->db->query($sql);

        return ( $result->num_rows && isset($name) !== 0
            ? RestResponse::factory(
                false,
                "A transcription factor binding site exists with the name: " . $name
            )
            : RestResponse::factory(
                true,
                null
            )
        );
    }
    // --------------------------------------------------------------------------------
    // Return help for the "checkForDuplicates" action
    // --------------------------------------------------------------------------------
    public function checkForDuplicatesHelp()
    {
        $description = "Checks any TFBS duplicate during the curation process. " .
            "Two TFBS are considered duplicates if they have the same name gene, " .
            "same transcription factor name, and same coordinates";
        $options = array(
            "chromosome_id" => "The chromosome ID of the transcription factor " .
                               "binding site",
            "end"           => "The end coordinate of the transcription factor " .
                               "binding site",
            "gene_id"       => "The gene ID of the transcription factor binding " .
                               "site",
            "redfly_id"     => "The REDfly identifier for the transcription factor " .
                               "binding site (e.g., RFTF:0000001438.002)",
            "tf_id"         => "The transcription factor ID of the transcription " .
                               "factor binding site",
            "start"         => "The start coordinate of the transcription factor " .
                               "binding site"
        );

        return RestResponse::factory(
            true,
            $description,
            $options
        );
    }
    // --------------------------------------------------------------------------------
    // Check for duplicate TFBS during the curation process. Two TFBS are considered
    // duplicates if they have the name gene, transcription factor, and coordinates,
    // and, are in the "approval", "approved", "current", "deleted", or "editing"
    // state.
    // --------------------------------------------------------------------------------
    public function checkForDuplicatesAction(
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
                throw new Exception("Not a transcription factor binding site id: \"" . $redflyId . "\"");
            }
            // If the database identifier is being used then this is a new entity and has
            // not been assigned an entity id yet.
            if ( $dbId !== null ) {
                $redflyIdCriteria = "tfbs.tfbs_id != " . $dbId;
            } else {
                $redflyIdCriteria = "(tfbs.entity_id != " . $entityId . " OR tfbs.entity_id IS NULL)";
            }
        } else {
            // If no redfly_id is specified, use a criteria that is always true
            $redflyIdCriteria = "1 = 1";
        }
        if ( ! isset($arguments["gene_id"]) ) {
            throw new Exception("Gene not specified");
        }
        if ( ! isset($arguments["tf_id"]) ) {
            throw new Exception("Transcription factor not specified");
        }
        if ( ! (isset($arguments["chromosome_id"]) &&
                isset($arguments["start"]) &&
                isset($arguments["end"])) ) {
            throw new Exception("Coordinates not specified");
        }
        $geneCriteria = "tfbs.gene_id = " . $this->db->escape($arguments["gene_id"]);
        $transcriptionFactorCriteria = "tfbs.tf_id = " . $this->db->escape($arguments["tf_id"]);
        $coordinatesCriteria =  "tfbs.chromosome_id = " . $this->db->escape($arguments["chromosome_id"]) . " AND
            tfbs.current_start = " . $this->db->escape($arguments["start"]) . " AND
            tfbs.current_end = " . $this->db->escape($arguments["end"]);
        $sql = <<<SQL
        SELECT tfbs.tfbs_id,
            tfbs.name,
            c.name AS chromosome,
            tfbs.current_start,
            tfbs.current_end,
            tfbs.state,
            tfbs.date_added,
            CONCAT(curator.first_name, ' ', curator.last_name) AS curator_full_name,
            tfbs.last_update,
            IF(ISNULL(tfbs.auditor_id), '', CONCAT(auditor.first_name, ' ', auditor.last_name)) AS auditor_full_name,
            IF(ISNULL(tfbs.auditor_id), '', tfbs.last_audit) AS last_audit
        FROM BindingSite tfbs
        INNER JOIN Chromosome c ON tfbs.chromosome_id = c.chromosome_id
        INNER JOIN Users curator ON tfbs.curator_id = curator.user_id
        LEFT OUTER JOIN Users auditor ON tfbs.auditor_id = auditor.user_id
        WHERE tfbs.state IN ('approval', 'approved', 'current', 'deleted', 'editing') AND
            $redflyIdCriteria AND
            $geneCriteria AND
            $transcriptionFactorCriteria AND
            $coordinatesCriteria
SQL;
        $tfbsDuplicateDetected = false;
        $result = $this->db->query($sql);
        if ( $result->num_rows !== 0 ) {
            $tfbsDuplicateDetected = true;
            $row = $result->fetch_assoc();
            $tfbsDuplicateMessage = sprintf(
                "A transcription factor binding site already exists in this " .
                    "%s state with the name \"%s\" and the coordinates " .
                    "%s:%s..%s",
                $row["state"],
                $row["name"],
                $row["chromosome"],
                $row["current_start"],
                $row["current_end"]
            );
            if ( $row["last_update"] <> "" ) {
                $tfbsDuplicateMessage .= sprintf(
                    " added on %s by %s",
                    $row["date_added"],
                    $row["curator_full_name"]
                );
            } else {
                $tfbsDuplicateMessage .= sprintf(
                    " edited on %s by %s",
                    $row["last_update"],
                    $row["curator_full_name"]
                );
            };
            if ( $row["last_audit"] <> "" ) {
                $tfbsDuplicateMessage .= sprintf(
                    ". It was audited on %s by %s",
                    $row["last_audit"],
                    $row["auditor_full_name"]
                );
            }
        }

        return ( $tfbsDuplicateDetected === true
            ? RestResponse::factory(
                false,
                $tfbsDuplicateMessage
            )
            : RestResponse::factory(true)
        );
    }
    // --------------------------------------------------------------------------------
    // Return help for the "load" action
    // --------------------------------------------------------------------------------
    public function loadHelp()
    {
        $description = "Load a transcription factor Binding Site for curation.";
        $options = array("redfly_id" => "The REDfly identifier for the transcription factor binding site (e.g., RFTF:0000312.001)");

        return RestResponse::factory(
            true,
            $description,
            $options
        );
    }
    // --------------------------------------------------------------------------------
    // Retrieve details for one or more entities. Expression terms and associated
    // RCs are queried through their own API call due to a limitation of the
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
        $response = $this->querySingleTf(
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
        $description = "Create, update, or merge transcription factor Binding Sites.";
        $options = array("redfly_id" => "The REDfly identifier for the transcription factor bindng site " .
            "(e.g., RFTF:0000312.001)");

        return RestResponse::factory(
            true,
            $description,
            $options
        );
    }
    // --------------------------------------------------------------------------------
    // Save details for a single TFBS entry or approve a TFBS. The ExtJS store
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
        // If set to non-null we are editing something that exists in the database
        // (as current, editing, or approval)
        $tfbsId = null;
        // TRUE to insert, false to update
        $insertEntity = true;
        // Inserting a new entity, used only for error messages
        $newEntity = true;
        $type = $entityId = $version = $dbId = null;
        // Counters to keep track of the number of new and edited entities merged
        // during the approval process.
        $numNewEntitiesMerged = 0;
        $numEditedEntitiesMerged = 0;
        try {
            Auth::authorize(array(
                "admin",
                "curator"
            ));
        } catch ( Exception $e ) {
            $httpResponseCode = ( Auth::getUser() === null
                ? 401
                : 403
            );
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
            throw new Exception("Entity data not provided in " . $_POST["results"]);
        }
        $data = (array) json_decode($postData["results"]);
        $action = ( isset($data["action"])
            ? $data["action"]
            : null );
        $redflyId = ( isset($data["redfly_id"])
            ? $data["redfly_id"]
            : null );
        switch ( $action ) {
            case self::ACTION_submitForApproval:
                $state = self::STATE_approval;
                break;
            case self::ACTION_markForDeletion:
                $state = self::STATE_deleted;
                break;
            default:
                $state = self::STATE_editing;
        }
        // If a redfly_id_list was sent in the POST this will be the list of ids
        // that were considered during the approval (the action should also be
        // "approve")
        $redflyIdMergeList = ( isset($postData["redfly_id_list"]) &&
            (! empty($postData["redfly_id_list"]) )
            ? (array) json_decode($postData["redfly_id_list"])
            : null );
        // --------------------------------------------------------------------------------
        // In approval mode there are two possible combinations of entities being
        // approved. A mix of new and modified existing entities is not supported.
        // The editing and approval process does not support approving/merging
        // multiple edits to existing entities to create a new one. Multiple
        // existing entities will throw an error.
        // 1. All new entities (RFTF:000000000.TFBSID where TFBSID is the database tfbs_id
        // 2. A single existing entity (RFTF:00000EEEE.VVVV)  Where EEEE is the
        //    entity number and VVVV is the version in the database.
        // --------------------------------------------------------------------------------
        if ( $action === self::ACTION_approve ) {
            if ( ! Auth::getUser()->hasRole(self::ROLE_admin) ) {
                throw new Exception("Admin role required for approval of TFBS");
            } elseif ( $redflyIdMergeList === null ) {
                throw new Exception("List of merged redfly ids not provided in " . $_POST["redfly_id_list"]);
            }
            // Count the number of new and edited (existing) entities in the merge list
            foreach ( $redflyIdMergeList as $mergeRedflyId ) {
                $this->helper->parseEntityId(
                    $mergeRedflyId,
                    $type,
                    $entityId,
                    $version,
                    $dbId
                );
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
            // merged data. If this is a new entity we can use this tfbs_id as well.
            $redflyId = array_shift($redflyIdMergeList);
            $this->helper->parseEntityId(
                $redflyId,
                $type,
                $entityId,
                $version,
                $tfbsId
            );
            $state = self::STATE_approved;
            $insertEntity = false;
        }
        // --------------------------------------------------------------------------------
        // If no redfly id was provided or an id for an existing entity was provided
        // we will need to query for the tfbs_id. If a redfly id was provided, see
        // which of the following is true and retrieve the tfbs_id.
        // - This is the first edit of an existing entity, or
        // - We are editing a new entity that has been previously saved but not
        //   approved, or
        // - This is a subsequent edit of an existing entity that was already in the
        //   "edit" state
        // --------------------------------------------------------------------------------
        if ( ($tfbsId === null) &&
            (! empty($redflyId)) ) {
            $newEntity = false;
            $this->helper->parseEntityId(
                $redflyId,
                $type,
                $entityId,
                $version,
                $dbId
            );
            if ( $type !== self::EntityCode ) {
                throw new Exception("Not a valid TFBS id: " . $redflyId);
            }
            $sql = <<<SQL
            SELECT tfbs_id AS id,
                name,
                entity_id,
                version,
                state
            FROM BindingSite
SQL;
            $criteria = array(
                "state IN ('approval','approved','editing')"
            );
            if ( $entityId === null ) {
                // An entity id of zero means that we are editing a new entity that has
                // been previously saved but not approved.
                $criteria[] = "tfbs_id = " . $dbId;
            } else {
                // A non-zero entity id means that we are editing an existing entity.
                // Check to see if this is the first edit (none exist with state
                // "editing" or "approval") or we are saving over previous edits (one
                // exists with state "editing" or "approval")
                $criteria[] = "entity_id = " . $entityId . " AND version = " . $version;
            }
            if ( count($criteria) !== 0 ) {
                $sql .= " WHERE " . implode(" AND ", $criteria);
            }
            $queryResult = $this->db->query($sql);
            // If a redfly id was provided and no entries were found in the database
            // using that id with the editing or approval state, then this is the
            // first time the edit has been saved.
            if ( $queryResult->num_rows !== 0 ) {
                list($tfbsId,,,,,) = $row = $queryResult->fetch_row();
                $insertEntity = false;
            }
        }
        try {
            $this->db->query("START TRANSACTION");
            // Make sure that the entity being saved is the newest version
            if ( $entityId !== null ) {
                $sql = <<<SQL
                SELECT MAX(version) AS max_version
                FROM BindingSite
                WHERE entity_id = $entityId
SQL;
                $result = $this->db->query($sql);
                if ( ($row = $result->fetch_assoc()) === null ) {
                    throw new Exception("Error fetching max version number for " . $redflyId);
                }
                if ( strval($version) !== $row["max_version"] ) {
                    throw new Exception("Attempt to update non-maximal version, please refresh TFBS list");
                }
            }
            // Validate the sequence with flank and apply proper formatting
            $helper = TfbsHelper::factory();
            $helper->validateAndFormatSequenceWithFlank($data);
            // Database failsafe to avoid new misnaming issues from the TFBS name
            $dbTfName = $helper->getGeneName($data["tf_id"]);
            $dbGeneName = $helper->getGeneName($data["gene_id"]);
            $tfName = substr($data["name"], 0, strlen($dbTfName));
            // Checking beyond the transcription factor name plus the underscore
            $geneName = explode(":REDFLY:", substr($data["name"], strlen($dbTfName) + 1))[0];
            if ( ! (($dbTfName === $tfName) &&
                ($dbGeneName === $geneName)) ) {
                throw new Exception(sprintf(
                    "TFBS name %s is expected to start with %s_%s",
                    $data["name"],
                    $dbTfName,
                    $dbGeneName
                ));
            }
            if ( $insertEntity ) {
                // This is a new record or a newly edited record.
                // Create a version for editing with the same entity id and version + 1
                $sql = <<<SQL
                INSERT INTO BindingSite (
                    sequence_from_species_id,
                    assayed_in_species_id,
                    name,
                    pubmed_id,
                    gene_id,
                    tf_id,
                    chromosome_id,
                    evidence_id,
                    notes,
                    figure_labels,
                    curator_id,
                    date_added,
                    last_update,
                    sequence,
                    sequence_with_flank,
                    size,
                    current_genome_assembly_release_version,
                    current_start,
                    current_end,
                    archived_genome_assembly_release_versions,
                    archived_starts,
                    archived_ends,
                    entity_id,
                    version,
                    state
                ) VALUES (
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    NOW(),
                    NOW(),
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?
                )
SQL;
                $size = $data["end"] - $data["start"] + 1;
                // Increment the version for edited entities or set to zero for new entities.
                $version = ( $version === null
                    ? 0
                    : $version + 1 );
                if ( ($statement = $this->db->getHandle()->prepare($sql)) === false ) {
                    throw new Exception("Error preparing statement (" .
                        basename(__FILE__) . ":" . __LINE__ . "): " . $this->db->getError());
                }
                $statement->bind_param(
                    "iissiiiississisiisssiis",
                    // Both species fields must be the same species for TFBS at the moment
                    $data["sequence_from_species_id"],
                    $data["sequence_from_species_id"],
                    $data["name"],
                    $data["pubmed_id"],
                    $data["gene_id"],
                    $data["tf_id"],
                    $data["chromosome_id"],
                    $data["evidence_id"],
                    $data["notes"],
                    $data["figure_labels"],
                    $data["curator_id"],
                    $data["sequence"],
                    $data["sequence_with_flank"],
                    $size,
                    $data["current_genome_assembly_release_version"],
                    $data["start"],
                    $data["end"],
                    $data["archived_genome_assembly_release_versions"],
                    $data["archived_starts"],
                    $data["archived_ends"],
                    $entityId,
                    $version,
                    $state
                );
                if ( $statement->execute() === false ) {
                    throw new Exception("Error creating : " . $sql .
                        ( $newEntity
                            ? "new binding site"
                            : "new edited  binding site $redflyId ") .
                        ": " . $statement->error);
                }
                $tfbsId = $this->db->lastInsertId();
                $redflyId = $this->helper->entityId(
                    self::EntityCode,
                    $entityId,
                    $version,
                    $tfbsId
                );
            } else {
                // This is an edited record and we are updating the information, or this
                // is a newly approved record and we are updating one of the records
                // listed for approval with the merged data.
                $sql = "
                UPDATE BindingSite
                SET sequence_from_species_id = ?,
                    assayed_in_species_id = ?,
                    name = ?,
                    pubmed_id = ?,
                    gene_id = ?,
                    tf_id = ?,
                    chromosome_id = ?,
                    evidence_id = ?,
                    notes = ?,
                    figure_labels = ?,
                    sequence = ?,
                    sequence_with_flank= ?,
                    size = ?,
                    current_genome_assembly_release_version = ?,
                    current_start = ?,
                    current_end = ?,
                    archived_genome_assembly_release_versions = ?,
                    archived_starts = ?,
                    archived_ends = ?,
                    state = ?, ";
                if ( $action === self::ACTION_approve ) {
                    $sql .= "auditor_id = ?,
                        last_audit = NOW() ";
                } else {
                    $sql .= "curator_id = ?,
                        last_update = NOW() ";
                }
                $sql .= "WHERE state IN ('approved', 'approval', 'editing') AND tfbs_id = ?";
                if ( ($statement = $this->db->getHandle()->prepare($sql)) === false ) {
                    throw new Exception("Error preparing statement (" .
                        basename(__FILE__) . ":" . __LINE__ . "): " . $this->db->getError());
                }
                $size = $data["end"] - $data["start"] + 1;
                $currentUserIdLogged = Auth::getUser()->userId();
                $statement->bind_param(
                    "iissiiiissssisiissssii",
                    // Both species fields must be the same species for TFBS at the moment
                    $data["sequence_from_species_id"],
                    $data["sequence_from_species_id"],
                    $data["name"],
                    $data["pubmed_id"],
                    $data["gene_id"],
                    $data["tf_id"],
                    $data["chromosome_id"],
                    $data["evidence_id"],
                    $data["notes"],
                    $data["figure_labels"],
                    $data["sequence"],
                    $data["sequence_with_flank"],
                    $size,
                    $data["current_genome_assembly_release_version"],
                    $data["start"],
                    $data["end"],
                    $data["archived_genome_assembly_release_versions"],
                    $data["archived_starts"],
                    $data["archived_ends"],
                    $state,
                    $currentUserIdLogged,
                    $tfbsId
                );
                if ( $statement->execute() === false ) {
                    throw new Exception("Error updating $redflyId: ". $statement->error);
                }
                if ( ($action === self::ACTION_approve) &&
                    ($entityId === null) &&
                    ($version === null) ) {
                        // This entity is being approved so set the name and entity id
                        $sql = <<<SQL
                        UPDATE BindingSite,
                            (SELECT MAX(entity_id) AS maxentity FROM BindingSite) m
                        SET name = REPLACE(name, 'REDFLY:XXX', CONCAT('REDFLY:TF', LPAD(m.maxentity + 1, 6, '0'))),
                            entity_id = m.maxentity + 1
                        WHERE tfbs_id = $tfbsId
SQL;
                    if ( $this->db->query($sql) === false ) {
                        throw new Exception("Error updating " . $redflyId . ": " . $statement->error);
                    }
                    // Retrieve new entity_id to update redfly_id
                    $sql = <<<SQL
                    SELECT entity_id,
                        version
                    FROM BindingSite
                    WHERE tfbs_id = $tfbsId
SQL;
                    $queryResult = $this->db->query($sql);
                    list(
                        $entityId,
                        $version
                    ) = $queryResult->fetch_row();
                    $redflyId = $this->helper->entityId(
                        self::EntityCode,
                        $entityId,
                        $version,
                        $tfbsId
                    );
                }
            }
            //--------------------------------------------------------------------------------
            // Sanitize and update the figure labels. Since there was a mix of uppercase
            // and lowercase labels on the files provided by FlyExpress and there was a
            // non-intersecting mix of figure labels provided by curators, all labels are
            // normalized to be uppercase.
            //--------------------------------------------------------------------------------
            if ( $tfbsId !== null ) {
                $sql = <<<SQL
                    DELETE
                    FROM BS_has_FigureLabel
                    WHERE tfbs_id = $tfbsId
SQL;
                $this->db->query($sql);
            }
            $normalizedFigureLabelList = array();
            if ( ! empty($data["figure_labels"]) ) {
                $sql = "INSERT INTO BS_has_FigureLabel
                        VALUES ";
                foreach ( explode("^", $data["figure_labels"]) as $figureLabel ) {
                    $normalizedFigureLabel = trim($figureLabel);
                    if ( in_array(
                        $normalizedFigureLabel,
                        $normalizedFigureLabelList
                    ) ) {
                        throw new \Exception("Figure label repeated: " . $normalizedFigureLabel);
                    }
                    $normalizedFigureLabelList[] = $normalizedFigureLabel;
                    $labelList[] = "(" . $tfbsId . ", " . $this->db->escape($normalizedFigureLabel, true) . ")";
                }
                $sql .= implode(",", $labelList ?? []);
                $this->db->query($sql);
                // Override the figure label with the normnalized figure label
                $figureLabels = ( count($normalizedFigureLabelList) !== 0
                    ? implode("^", $normalizedFigureLabelList)
                    : null );
                $sql = "UPDATE BindingSite
                        SET figure_labels = ?
                        WHERE tfbs_id = ?";
                if ( ($statement = $this->db->getHandle()->prepare($sql)) === false ) {
                    throw new Exception("Error preparing statement: " . $this->db->getError());
                }
                $statement->bind_param("si", $figureLabels, $tfbsId);
                if ( $statement->execute() === false ) {
                    throw new Exception("Error updating " . $data["redfly_id"] . ": " . $statement->error);
                }
            }
            // --------------------------------------------------------------------------------
            // If the author's email has changed update the citation with the author's email
            // --------------------------------------------------------------------------------
            $citationHandler = CitationHandler::factory();
            $citationParams = array(
                "external_id" => $data["pubmed_id"],
                "type"        => "PUBMED"
            );
            $citationResponse = $citationHandler->listAction($citationParams);
            list($citationResult) = $citationResponse->results();
            if ( ($citationResult["author_email"] !== $data["author_email"]) ) {
                $citationResult["author_email"] = $data["author_email"];
                // The saveAction() expects the new data to be json encoded and assigned
                // to $_POST["results"] to be in line with the extjs store API
                $citationResponse = $citationHandler->saveAction(
                    array(),
                    array("results" => json_encode($citationResult))
                );
                if ( ! $citationResponse->success() ) {
                    throw new Exception("Error updating author email: " . $citationResponse->message());
                }
            }
            if ( $numNewEntitiesMerged > 0 ) {
                $tfbsIdList = array();
                foreach ( $redflyIdMergeList as $mergeRedflyId ) {
                    // Since we only support merging multiple new entities we only care
                    // about the tfbs_id here.
                    $this->helper->parseEntityId(
                        $mergeRedflyId,
                        $type,
                        $entityId,
                        $version,
                        $tfbsId
                    );
                    $tfbsIdList[] = $tfbsId;
                }
                if ( count($tfbsIdList) !== 0 ) {
                    $sql = "DELETE FROM BindingSite
                            WHERE tfbs_id IN (" . implode(",", $tfbsIdList) .
                        ") AND state IN ('editing','approval')";
                    if ( $this->db->query($sql) === false ) {
                        throw new Exception("Error deleting redfly ids: " . implode(", ", $redflyIdMergeList));
                    }
                }
            }
            if ( $action === self::ACTION_approve ) {
                $helper = TfbsHelper::factory();
                $helper->updateAssociatedRc(
                    $tfbsId,
                    $data["chromosome_id"],
                    $data["start"],
                    $data["end"]
                );
            }
            $this->db->query("COMMIT");
        } catch ( Exception $e ) {
            $this->db->query("ROLLBACK");
            throw $e;
        }

        $data["redfly_id"] = $redflyId;
        return RestResponse::factory(true, null, array($data));
    }
    // --------------------------------------------------------------------------------
    // Return help for the "reject" action
    // --------------------------------------------------------------------------------
    public function rejectHelp()
    {
        $description = "Reject one or more TFBS from the approval queue.";
        $options = array(
            "delete_items"   => "TRUE to mark the items for deletion from the approval queue",
            "email_curators" => "TRUE to send email to the curators and include the rejection message",
            "message"        => "Optional message to send to the curators",
            "names"          => "An array of one or more TFBS and curator names encoded as JSON",
            "redfly_ids"     => "An array of one or more REDfly identifiers encoded as JSON"
        );

        return RestResponse::factory(
            true,
            $description,
            $options
        );
    }
    // --------------------------------------------------------------------------------
    // Reject one (currently used) or more (rarely used, now it is the responsability
    // of the batch audit interface) TFBS(s).
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
                    throw new Exception($redflyId . " is not a valid TFBS id");
                }
                $sql = "UPDATE BindingSite
                    SET state = '";
                $sql .= ( ! $deleteItems
                    ? "editing"
                    : "deleted");
                $sql .= "',
                    last_audit = NOW(),
                    auditor_id = " . Auth::getUser()->userId();
                $sql .= ( $dbId !== null
                    ? " WHERE entity_id IS NULL and tfbs_id = ?"
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
                    throw new Exception($redflyId . " is not a valid TFBS id");
                }
                $sql = "SELECT pubmed_id
                        FROM BindingSite";
                $sql .= ( $dbId !== null
                    ? " WHERE entity_id IS NULL and tfbs_id = " . $dbId
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
            $body = "The following TFBS was rejected" .
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
            $mail->Subject = "[REDfly] TFBS rejected";
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
}
