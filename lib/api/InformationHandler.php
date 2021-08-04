<?php
class InformationHandler
{
    // --------------------------------------------------------------------------------
    // Factory design pattern
    // --------------------------------------------------------------------------------
    public static function factory()
    {
        return new InformationHandler;
    }
    // --------------------------------------------------------------------------------
    // Return help for the "api" action
    // --------------------------------------------------------------------------------
    public function apiHelp()
    {
        $description = "Interrogate the REDfly API for a list of available entities and " .
            "actions and return a 2-dimensional array where the first dimension is the " .
            "list of entities and the second dimension is the list of actions available for " .
            "each entity";

        return RestResponse::factory(
            true,
            $description
        );
    }
    // --------------------------------------------------------------------------------
    // List the API handlers and their actions
    // --------------------------------------------------------------------------------
    public function apiAction(
        array $arguments,
        array $postData = null
    ) {
        $list = array();
        // Find all Handler classes in the api directory
        $pathList = explode(":", ini_get("include_path"));
        foreach ( $pathList as $path ) {
            // Make sure the directory exists and ends in "api"
            if ( ! is_dir($path) ) {
                continue;
            }
            if ( substr(rtrim($path, "/"), -3) !== "api" ) {
                continue;
            }
            $dir = opendir($path);
            while ( ($file = readdir($dir)) !== false ) {
                if ( ($file === ".") || ($file === "..") ) {
                    continue;
                }
                // Handler class files are expected to have a name of the form
                // class_SomeHandler.php where "SomeHandler" is the name of the class.
                // Extract the class name and attempt to use the reflection API to
                // discover the list of supported actions.
                if ( substr($file, -11) ===  "Handler.php" ) {
                    $className = substr($file, 0, strlen($file) - 4);
                    $apiName = strtolower(substr($className, 0, strlen($className) - 7));
                    $rc = new ReflectionClass($className);
                    $tmpList = array();
                    foreach ( $rc->getMethods() as $methodObj ) {
                        $methodName = $methodObj->getName();
                        if ( substr($methodName, -6) === "Action" ) {
                            $tmpList[] = substr($methodName, 0, strlen($methodName) - 6);
                        }
                        if ( count($tmpList) !== 0 ) {
                            $list[$apiName] = $tmpList;
                        }
                    }
                }
            }
        }

        return RestResponse::factory(
            true,
            "",
            $list
        );
    }
    // --------------------------------------------------------------------------------
    // Return help for the "download" action
    // --------------------------------------------------------------------------------
    public function downloadHelp()
    {
        $description = "Interrogate the REDfly API for a list of available download " .
            "formats and their options. " .
            "Downloads should use the \"raw\" output format.";

        return RestResponse::factory(
            true,
            $description
        );
    }
    // --------------------------------------------------------------------------------
    // List the available download handlers and their actions
    // --------------------------------------------------------------------------------
    public function downloadAction(
        array $arguments,
        array $postData = null
    ) {
        $list = array();
        // Find all ExportFiler classes
        $pathList = explode(":", ini_get("include_path"));
        foreach ( $pathList as $path ) {
            if ( ! is_dir($path) ) {
                continue;
            }
            $dir = opendir($path);
            while ( (($file = readdir($dir)) !== false) ) {
                if ( ($file === ".") || ($file === "..") || ($file !== "ExportFile") ) {
                    continue;
                }
                $exportDirPath = "$path/$file";
                if ( ! is_dir($exportDirPath) ) {
                    continue;
                }
                $exportDir = opendir($exportDirPath);
                while ( ($exportFile = readdir($exportDir)) !== false ) {
                    if ( is_dir($exportFile) ) {
                        continue;
                    }
                    $className = "ExportFile_" . substr($exportFile, 0, strlen($exportFile) - 4);
                    $formatName = strtolower(substr($className, 11));
                    if ( $formatName === "" ) {
                        continue;
                    }
                    $exporter = ($className)::factory(array("format" => $formatName));
                    $list[$formatName] = null;
                    if ( method_exists($exporter, "help") ) {
                        $helpResponse = $exporter->help();
                        $list[$formatName] = array(
                            "message" => $helpResponse->message(),
                            "results" => $helpResponse->results()
                        );
                    }
                }
                closedir($exportDir);
            }
            closedir($dir);
        }

        return RestResponse::factory(
            true,
            "",
            $list
        );
    }
    // --------------------------------------------------------------------------------
    // Return help for the "db" action
    // --------------------------------------------------------------------------------
    public function dbHelp()
    {
        $description = "Return the information about the REDfly database";
        $options = array(
            "last_crm_update"                           => "The last update (date only, no time) from any current cis-regulatory module",
            "last_crmsegment_update"                    => "The last update (date only, no time) from any current cis-regulatory module segment",
            "last_predictedcrm_update"                  => "The last update (date only, no time) from any current predicted cis-regulatory module",
            "last_rc_update"                            => "The last update (date only, no time) from any current reporter construct",
            "last_tfbs_update"                          => "The last update (date only, no time) from any current transcription factor binding site",
            "number_crm_genes"                          => "The number of different genes associated with current cis-regulatory modules",
            "number_crms"                               => "The number of current cis-regulatory modules",
            "number_crms_cell_culture_only"             => "The number of current cis-regulatory modules and cell culture only",
            "number_crms_in_vivo"                       => "The number of current in-vivo cis-regulatory modules",
            "number_crms_not_in_vivo"                   => "The number of current non-in-vivo cis-regulatory modules",
            "number_crmsegment_genes"                   => "The number of different genes associated with current cis-regulatory module segments",
            "number_crmsegments"                        => "The number of current cis-regulatory module segments",
            "number_predictedcrms"                      => "The number of current predicted cis-regulatory modules",
            "number_last_crmsegment_update"             => "The number of current cis-regulatory module segments changed at the last update (date only, no time)",
            "number_last_predictedcrm_update"           => "The number of current predicted cis-regulatory modules changed at the last update (date only, no time)",
            "number_last_rc_update"                     => "The number of current reporter constructs changed at the last update (date only, no time)",
            "number_last_tfbs_update"                   => "The number of current transcription factor binding sites changed at the last update (date only, no time)",
            "number_publications"                       => "The number of different publications curated regarding to current reporter constructs, transcription factor " .
                                                           "binding sites, predicted cis-regulatory modules, and cis-regulatory module segments",
            "number_rc_genes"                           => "The number of different genes associated with current reporter constructs",
            "number_rcs"                                => "The number of current reporter constructs",
            "number_rcs_not_crm"                        => "The number of current non cis-regulatory modules",
            "number_rcs_not_crm_excluding_cell_culture" => "The number of current non cis-regulatory modules excluding cell culture",
            "number_tfbss"                              => "The number of current transcription factor binding sites",
            "number_tfbs_genes"                         => "The number of different genes associated with current transcription factor binding sites",
            "number_tfbs_tfs"                           => "The number of different transcription factors associated with current transcription factor binding sites"
        );

        return RestResponse::factory(
            true,
            $description,
            $options
        );
    }
    // --------------------------------------------------------------------------------
    // List the general information about the entries in the database which is
    // displayed on the main page.
    // Note: all the custom SQL functions here are used commonly by both PHP source
    // code here to show such a general information in the main web page and Go source
    // code to build up its release statistics report.
    // --------------------------------------------------------------------------------
    public function dbAction(
        array $arguments,
        array $postData = null
    ) {
        $information = array();
        $db = DbService::factory();
        // The number of current reporter constructs
        $sql = <<<SQL
        SELECT NumberOfCurrentReporterConstructs(1);
SQL;
        $queryResult = $db->query($sql);
        $row = $queryResult->fetch_row();
        $information["number_rcs"] = $row[0];
        // The number of current non cis-regulatory modules
        $sql = <<<SQL
        SELECT COUNT(rc_id)
        FROM ReporterConstruct
        WHERE sequence_from_species_id = 1 AND
            state = 'current' AND
            is_crm = 0;
SQL;
        $queryResult = $db->query($sql);
        $row = $queryResult->fetch_row();
        $information["number_rcs_not_crm"] = $row[0];
        // The number of current non cis-regulatory modules excluding cell culture
        $sql = <<<SQL
        SELECT COUNT(rc_id)
        FROM ReporterConstruct
        WHERE sequence_from_species_id = 1 AND
            state = 'current' AND
            is_crm = 0 AND
            cell_culture_only = 0;
SQL;
        $queryResult = $db->query($sql);
        $row = $queryResult->fetch_row();
        $information["number_rcs_not_crm_excluding_cell_culture"] = $row[0];
        // The number of current cis-regulatory modules
        $sql = <<<SQL
        SELECT NumberOfCurrentCisRegulatoryModules(1);
SQL;
        $queryResult = $db->query($sql);
        $row = $queryResult->fetch_row();
        $information["number_crms"] = $row[0];
        // The number of current in-vivo cis-regulatory modules
        $sql = <<<SQL
        SELECT NumberOfCurrentInVivoCisRegulatoryModules(1);
SQL;
        $queryResult = $db->query($sql);
        $row = $queryResult->fetch_row();
        $information["number_crms_in_vivo"] = $row[0];
        // The number of current non in-vivo cis-regulatory modules
        // having no cell culture
        $sql = <<<SQL
        SELECT NumberOfCurrentNonInVivoCisRegulatoryModulesHavingNoCellCulture(1);
SQL;
        $queryResult = $db->query($sql);
        $row = $queryResult->fetch_row();
        $information["number_crms_non_in_vivo_having_no_cell_culture"] = $row[0];
        // The number of current cis-regulatory modules having cell culture only
        $sql = <<<SQL
        SELECT NumberOfCurrentCisRegulatoryModulesHavingCellCultureOnly(1);
SQL;
        $queryResult = $db->query($sql);
        $row = $queryResult->fetch_row();
        $information["number_crms_cell_culture_only"] = $row[0];
        // The number of current transcription factor binding sites
        $sql = <<<SQL
        SELECT NumberOfCurrentTranscriptionFactorBindingSites(1);
SQL;
        $queryResult = $db->query($sql);
        $row = $queryResult->fetch_row();
        $information["number_tfbss"] = $row[0];
        // The number of current predicted cis-regulatory modules
        $sql = <<<SQL
        SELECT NumberOfCurrentPredictedCisRegulatoryModules(1);
SQL;
        $queryResult = $db->query($sql);
        $row = $queryResult->fetch_row();
        $information["number_predictedcrms"] = $row[0];
        // The number of current cis-regulatory module segments
        $sql = <<<SQL
        SELECT NumberOfCurrentCisRegulatoryModuleSegments(1);
SQL;
        $queryResult = $db->query($sql);
        $row = $queryResult->fetch_row();
        $information["number_crmsegments"] = $row[0];
        // The number of different genes associated with current reporter constructs
        $sql = <<<SQL
        SELECT COUNT(DISTINCT gene_id)
        FROM ReporterConstruct
        WHERE sequence_from_species_id = 1 AND
            state = 'current';
SQL;
        $queryResult = $db->query($sql);
        $row = $queryResult->fetch_row();
        $information["number_rc_genes"] = $row[0];
        // The number of different genes associated with current cis-regulatory modules
        $sql = <<<SQL
        SELECT NumberOfCurrentCisRegulatoryModuleGenes(1);
SQL;
        $queryResult = $db->query($sql);
        $row = $queryResult->fetch_row();
        $information["number_crm_genes"] = $row[0];
        // The number of different genes associated with current transcription factor binding sites
        $sql = <<<SQL
        SELECT NumberOfCurrentTranscriptionFactorBindingSiteGenes(1);
SQL;
        $queryResult = $db->query($sql);
        $row = $queryResult->fetch_row();
        $information["number_tfbs_genes"] = $row[0];
        // The number of different transcription factors associated with current transcription factor
        // binding sites
        $sql = <<<SQL
        SELECT NumberOfCurrentTranscriptionFactors(1);
SQL;
        $queryResult = $db->query($sql);
        $row = $queryResult->fetch_row();
        $information["number_tfbs_tfs"] = $row[0];
        // The number of different genes associated with current cis-regulatory module segments
        $sql = <<<SQL
        SELECT NumberOfCurrentCisRegulatoryModuleSegmentGenes(1);
SQL;
        $queryResult = $db->query($sql);
        $row = $queryResult->fetch_row();
        $information["number_crmsegment_genes"] = $row[0];
        // The number of different publications curated regarding to current reporter constructs,
        // current transcription factor binding sites, current predicted cis-regulatory modules,
        // and current cis-regulatory module segments
        $sql = <<<SQL
        SELECT NumberOfCuratedPublications(1);
SQL;
        $queryResult = $db->query($sql);
        $row = $queryResult->fetch_row();
        $information["number_publications"] = $row[0];
        // The last update (date only, no time) from any current reporter construct
        $sql = <<<SQL
        SELECT IFNULL(UNIX_TIMESTAMP(DATE(MAX(last_update))), UNIX_TIMESTAMP(DATE('1970-01-01 00:00:00')))
        FROM ReporterConstruct
        WHERE sequence_from_species_id = 1 AND
            state = 'current';
SQL;
        $queryResult = $db->query($sql);
        $row = $queryResult->fetch_row();
        $information["last_rc_update"] = $row[0];
        // The last update (date only, no time) from any current cis-regulatory module
        $sql = <<<SQL
        SELECT IFNULL(UNIX_TIMESTAMP(DATE(MAX(last_update))), UNIX_TIMESTAMP(DATE('1970-01-01 00:00:00')))
        FROM ReporterConstruct
        WHERE sequence_from_species_id = 1 AND
            state = 'current' AND
            is_crm = 1;
SQL;
        $queryResult = $db->query($sql);
        $row = $queryResult->fetch_row();
        $information["last_crm_update"] = $row[0];
        // The number of current reporter constructs changed at the last update (date only, no time)
        $lastUpdate = $information["last_rc_update"];
        $sql = <<<SQL
        SELECT COUNT(entity_id)
        FROM ReporterConstruct
        WHERE sequence_from_species_id = 1 AND
            state = 'current' AND
            DATE(last_update) = DATE(FROM_UNIXTIME({$lastUpdate}));
SQL;
        $queryResult = $db->query($sql);
        $row = $queryResult->fetch_row();
        $information["number_last_rc_update"] = $row[0];
        // The last update (date only, no time) from any current transcription factor binding site
        $sql = <<<SQL
        SELECT IFNULL(UNIX_TIMESTAMP(DATE(MAX(last_update))), UNIX_TIMESTAMP(DATE('1970-01-01 00:00:00')))
        FROM BindingSite
        WHERE sequence_from_species_id = 1 AND
            state = 'current';
SQL;
        $queryResult = $db->query($sql);
        $row = $queryResult->fetch_row();
        $information["last_tfbs_update"] = $row[0];
        // The number of current transcription factor binding sites changed at the last update
        // (date only, no time)
        $lastUpdate = $information["last_tfbs_update"];
        $sql = <<<SQL
        SELECT COUNT(entity_id)
        FROM BindingSite
        WHERE sequence_from_species_id = 1 AND
            state = 'current' AND
            DATE(last_update) = DATE(FROM_UNIXTIME({$lastUpdate}));
SQL;
        $queryResult = $db->query($sql);
        $row = $queryResult->fetch_row();
        $information["number_last_tfbs_update"] = $row[0];
        // The last update (date only, no time) from any current predicted cis-regulatory module
        $sql = <<<SQL
        SELECT IFNULL(UNIX_TIMESTAMP(DATE(MAX(last_update))), UNIX_TIMESTAMP(DATE('1970-01-01 00:00:00')))
        FROM PredictedCRM
        WHERE sequence_from_species_id = 1 AND
            state = 'current';
SQL;
        $queryResult = $db->query($sql);
        $row = $queryResult->fetch_row();
        $information["last_predictedcrm_update"] = $row[0];
        // The number of current predicted cis-regulatory modules changed at the last update
        // (date only, no time)
        $lastUpdate = $information["last_predictedcrm_update"];
        $sql = <<<SQL
        SELECT COUNT(entity_id)
        FROM PredictedCRM
        WHERE sequence_from_species_id = 1 AND
            state = 'current' AND
            DATE(last_update) = DATE(FROM_UNIXTIME({$lastUpdate}));
SQL;
        $queryResult = $db->query($sql);
        $row = $queryResult->fetch_row();
        $information["number_last_predictedcrm_update"] = $row[0];
        // The last update (date only, no time) from any current cis-regulatory module segment
        $sql = <<<SQL
        SELECT IFNULL(UNIX_TIMESTAMP(DATE(MAX(last_update))), UNIX_TIMESTAMP(DATE('1970-01-01 00:00:00')))
        FROM CRMSegment
        WHERE sequence_from_species_id = 1 AND
            state = 'current';
SQL;
        $queryResult = $db->query($sql);
        $row = $queryResult->fetch_row();
        $information["last_crmsegment_update"] = $row[0];
        // The number of current cis-regulatory module segments changed at the last update
        // (date only, no time)
        $lastUpdate = $information["last_crmsegment_update"];
        $sql = <<<SQL
        SELECT COUNT(entity_id)
        FROM CRMSegment
        WHERE sequence_from_species_id = 1 AND
            state = 'current' AND
            DATE(last_update) = DATE(FROM_UNIXTIME({$lastUpdate}));
SQL;
        $queryResult = $db->query($sql);
        $row = $queryResult->fetch_row();
        $information["number_last_crmsegment_update"] = $row[0];
        $queryResult->close();

        return RestResponse::factory(
            true,
            "",
            array($information)
        );
    }
    // --------------------------------------------------------------------------------
    // Return help for the "species" action
    // --------------------------------------------------------------------------------
    public function speciesHelp()
    {
        $description = "Return the entity information about each one of all the species " .
            "in the REDfly database";
        $options = array(
            "species" => "All the entity information grouped by species"
        );

        return RestResponse::factory(
            true,
            $description,
            $options
        );
    }
    // --------------------------------------------------------------------------------
    // List the general information about the entries of each species in the database
    // which is displayed on the species page.
    // Note: all the custom SQL functions here are used commonly by both PHP source
    // code here to show such a general information in the main web page and Go source
    // code to build up its release statistics report.
    // --------------------------------------------------------------------------------
    public function speciesAction(
        array $arguments,
        array $postData = null
    ) {
        $db = DbService::factory();
        $sql = <<<SQL
        SELECT s.species_id,
            s.scientific_name,
            ga.release_version
        FROM Species s
        INNER JOIN GenomeAssembly ga ON s.species_id = ga.species_id AND
            ga.is_deprecated = 0 
        ORDER BY s.scientific_name;
SQL;
        $queryResult = $db->query($sql);
        while ( $row = $queryResult->fetch_assoc() ) {
            $information["species"][] = $row;
        }
        $speciesNumber = count($information["species"]);
        for ( $speciesIndex = 0; $speciesIndex < $speciesNumber; $speciesIndex++ ) {
            $speciesId = $information["species"][$speciesIndex]["species_id"];
            // The number of current reporter constructs
            $sql = <<<SQL
            SELECT NumberOfCurrentReporterConstructs($speciesId);
SQL;
            $queryResult = $db->query($sql);
            $row = $queryResult->fetch_row();
            $information["species"][$speciesIndex]["rcs_number"] = $row[0];
            // The number of current cis-regulatory modules
            $sql = <<<SQL
            SELECT NumberOfCurrentCisRegulatoryModules($speciesId);
SQL;
            $queryResult = $db->query($sql);
            $row = $queryResult->fetch_row();
            $information["species"][$speciesIndex]["crms_number"] = $row[0];
            // The number of current cis-regulatory module segments
            $sql = <<<SQL
            SELECT NumberOfCurrentCisRegulatoryModuleSegments($speciesId);
SQL;
            $queryResult = $db->query($sql);
            $row = $queryResult->fetch_row();
            $information["species"][$speciesIndex]["crmss_number"] = $row[0];
            // The number of current predicted cis-regulatory modules
            $sql = <<<SQL
            SELECT NumberOfCurrentPredictedCisRegulatoryModules($speciesId);
SQL;
            $queryResult = $db->query($sql);
            $row = $queryResult->fetch_row();
            $information["species"][$speciesIndex]["pcrms_number"] = $row[0];
            // The number of current transcription factor binding sites
            $sql = <<<SQL
            SELECT NumberOfCurrentTranscriptionFactorBindingSites($speciesId);
SQL;
            $queryResult = $db->query($sql);
            $row = $queryResult->fetch_row();
            $information["species"][$speciesIndex]["tfbss_number"] = $row[0];
        }
        $queryResult->close();

        return RestResponse::factory(
            true,
            "",
            array($information)
        );
    }
}
