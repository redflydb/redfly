<?php
// --------------------------------------------------------------------------------
// Singleton database abstraction layer to provide higher level operations on
// the basic REDfly data objects.
// --------------------------------------------------------------------------------
class DbService
{
    // Database handle, accessible via the getters
    private $_dbHandle = null;
    private $_url = null;
    private $_user = null;
    private $_password = null;
    private $_dbName = null;
    private static $instance = null;
    // ----------------------------------------------------------------------
    // Private constructor to ensure that this class is a singleton
    // ----------------------------------------------------------------------
    private function __construct(
        $hostUrl,
        $user,
        $password,
        $dbName
    ) {
        $this->_url = $hostUrl;
        $this->_user = $user;
        $this->_password = $password;
        $this->_dbName = $dbName;
    }
    public function __destruct()
    {
        $this->disconnect();
    }
    // ----------------------------------------------------------------------
    // Instantiate a database object based on the settings in the configuration
    // file.
    // If no configuration file section is specified the settings will be
    // pulled from the "database" section.
    // @param $section Optional configuration file section
    // @returns An instance of a DbService class
    // ----------------------------------------------------------------------
    public static function factory($section = "database")
    {
        $options = $GLOBALS["options"];
        if ( ! isset($options->$section) ) {
            throw new Exception("Invalid configuration section '$section'");
        }
        if ( self::$instance === null ) {
            self::$instance = new DbService(
                $options->$section->host,
                $options->$section->user,
                $options->$section->password,
                $options->$section->name
            );
            self::$instance->connect();
        }

        return self::$instance;
    }
    public function connect()
    {
        $this->_dbHandle = new mysqli(
            $this->_url,
            $this->_user,
            $this->_password,
            $this->_dbName
        );

        return $this->_dbHandle;
    }
    public function disconnect()
    {
        if ( $this->_dbHandle !== null ) {
            $this->_dbHandle->close();
            $this->_dbHandle = null;
        }
    }
    public function getHandle()
    {
        return $this->_dbHandle;
    }
    public function getError()
    {
        return $this->_dbHandle->error;
    }
    public function startTransaction()
    {
        $this->query("START TRANSACTION");
    }
    public function commit()
    {
        $this->query("COMMIT");
    }
    public function rollback()
    {
        $this->query("ROLLBACK");
    }
    public function query($sql)
    {
        if ( empty($sql) ) {
            throw new Exception("No query provided");
        }
        ini_set("memory_limit", "512M");
        $result = $this->_dbHandle->query($sql);
        if ( ! $result ) {
            throw new Exception("Error performing query: '" . $this->_dbHandle->error .
                "', sql = '". $sql . "'");
        }

        return $result;
    }
    public function lastInsertId()
    {
        return $this->_dbHandle->insert_id;
    }
    public function escape(
        $value,
        $quote = false
    ) {
        $escapedValue = null;
        if ( is_array($value) ) {
            $escapedValue = array();
            foreach ( $value as $v ) {
                $tmp = $this->_dbHandle->escape_string($v);
                $escapedValue[] = ( $quote
                    ? "'$tmp'"
                    : $tmp );
            }
        } else {
            $escapedValue = $this->_dbHandle->escape_string($value);
            $escapedValue = ( $quote ?
                "'$escapedValue'"
                : $escapedValue );
        }

        return $escapedValue;
    }
    // --------------------------------------------------------------------------------
    // Generate the textual description of the location of this entity in relation
    // to the features that it overlaps with distances specified in Kbp and return
    // a coma-separated list of location descriptions.  The features are parsed
    // from the Flybase dmel no-analysis file by parse_dmel_gff.php and are joined
    // with the various entity tables to find the features that apply.  For example,
    // SELECT * FROM v_reporter_construct_feature_location
    // WHERE id = 14 AND type IN ('mrna', 'exon', 'intron')
    // ORDER BY id, feature_id, parent_id;
    // The result is expected to have the following fields:
    //   id, type, name, parent_id, feature_id, identifier, start, end,
    //   f_start, f_end, strand, relative_start, relative_end
    // @param $entityType The type of entity to query - this will be used to
    //   construct the view name
    // @param $entityName The name of the entity that we are comparing to the
    //   features.
    // @param $result The result of the query against the feature view.
    // @returns A coma-separated string of location descriptions
    // --------------------------------------------------------------------------------
  /*
-- Generate a view that associates RCs with the features that fall onto the same
-- gene. The relative start and end positions indicate if the entity
-- starts/ends 5' or 3' of the feature and takes into account features on the
-- negative strand. A relative start/end of 0 indicates that the RC lies within
-- the feature. Note that for features on the negative strand relative start
-- and end as well as distance of the RC from the feature are calculated 5' to
-- 3' meaning that the start and end location is reversed when calculating the
-- start and end of the RC relative to the feature. Because we are using a 5bp
-- "fudge factor" when determining relative start and end positions, the start
-- and end coordinates must be signed integers or a subtraction that results in
-- a negative number will cause an overflow.
-- For example, RFRC:00000005.001 (rc_id=10) ends 1bp into the 5' side of tx
-- FBtr0071519 but since we are using a 5bp "fudge factor" we consider it to lie
-- completely 5' of the transcript. If the start/end coordinates were unsigned
-- this would cause a problem when determining the distance between the RC and
-- the tx because the result of the subtraction would be negative causing an
-- overflow.
CREATE OR REPLACE VIEW v_reporter_construct_feature_location AS
SELECT rc.rc_id AS id,
    f.type,
    f.name AS name,
    f.parent_id,
    f.feature_id,
    f.identifier,
    rc.current_start AS start,
    rc.current_end AS end,
    f.start AS f_start,
    f.end AS f_end,
    strand,
    IF (strand = '+',
        IF (rc.current_start < f.start + 5, 5, IF(rc.current_start > f.end + 5, 3, 0)),
        IF (rc.current_end < f.start + 5, 3, IF(rc.current_end > f.end + 5, 5, 0))) AS relative_start,
    IF (strand = '+',
        IF (rc.current_end < f.start + 5, 5, IF(rc.current_end > f.end + 5, 3, 0)),
        IF (rc.current_start < f.start + 5, 3, IF(rc.current_start > f.end + 5, 5, 0))) AS relative_end,
    IF (strand = '+',
        IF (rc.current_start < f.start + 5, ABS(f.start - rc.current_start), IF(rc.current_start > f.end + 5, ABS(rc.current_start - f.end), 0)),
        IF (rc.current_end < f.start + 5, ABS(f.start - rc.current_end), IF(rc.current_end > f.end + 5, ABS(rc.current_end - f.end), 0))) AS start_dist,
    IF (strand = '+',
        IF (rc.current_end < f.start + 5, ABS(f.start - rc.current_end), IF(rc.current_end > f.end + 5, ABS(rc.current_end - f.end), 0)),
        IF (rc.current_start < f.start + 5, ABS(f.start - rc.current_start), IF(rc.current_start > f.end + 5, ABS(f.end - rc.current_start), 0))) AS end_dist
FROM Features f
JOIN ReporterConstruct rc USING (gene_id)
WHERE rc.state = 'current'
ORDER BY
    rc_id,
    feature_id,
    parent_id;
-- Generate a view that associates TFBSs with the features that fall onto the same
-- gene. The relative start and end positions indicate if the entity
-- starts/ends 5' or 3' of the feature and takes into account features on the
-- negative strand. A relative start/end of 0 indicates that the TFBS lies within
-- the feature. Note that for features on the negative strand relative start
-- and end as well as distance of the TFBS from the feature are calculated 5' to
-- 3' meaning that the start and end location is reversed when calculating the
-- start and end of the TFBS relative to the feature. Because we are using a 5bp
-- "fudge factor" when determining relative start and end positions, the start
-- and end coordinates must be signed integers or a subtraction that results in
-- a negative number will cause an overflow.
-- For example, RFRC:00000005.001 (rc_id=10) ends 1bp into the 5' side of tx
-- FBtr0071519 but since we are using a 5bp "fudge factor" we consider it to lie
-- completely 5' of the transcript.  If the start/end coordinates were unsigned
-- this would cause a problem when determining the distance between the RC and
-- the tx because the result of the subtraction would be negative causing an
-- overflow.
CREATE OR REPLACE VIEW v_transcription_factor_binding_site_feature_location AS
SELECT tfbs.tfbs_id AS id,
    f.type,
    f.name AS name,
    f.parent_id,
    f.feature_id,
    f.identifier,
    tfbs.current_start AS start,
    tfbs.current_end AS end,
    f.start AS f_start,
    f.end AS f_end,
    strand,
    IF (strand = '+',
        IF (tfbs.current_start < f.start + 5, 5, IF(tfbs.current_start > f.end + 5, 3, 0)),
        IF (tfbs.current_end < f.start + 5, 3, IF(tfbs.current_end > f.end + 5, 5, 0))) as relative_start,
    IF (strand = '+',
        IF (tfbs.current_end < f.start + 5, 5, IF(tfbs.current_end > f.end + 5, 3, 0)),
        IF (tfbs.current_start < f.start + 5, 3, IF(tfbs.current_start > f.end + 5, 5, 0))) as relative_end,
    IF (strand = '+',
        IF (tfbs.current_start < f.start + 5, ABS(f.start - tfbs.current_start), IF(tfbs.current_start > f.end + 5, ABS(tfbs.current_start - f.end), 0)),
        IF (tfbs.current_end < f.start + 5, ABS(f.start - tfbs.current_end), IF(tfbs.current_end > f.end + 5, ABS(tfbs.current_end - f.end), 0))) as start_dist,
    IF (strand = '+',
        IF (tfbs.current_end < f.start + 5, ABS(f.start - tfbs.current_end), IF(tfbs.current_end > f.end + 5, ABS(tfbs.current_end - f.end), 0)),
        IF (tfbs.current_start < f.start + 5, ABS(f.start - tfbs.current_start), IF(tfbs.current_start > f.end + 5, ABS(f.end - tfbs.current_start), 0))) as end_dist
FROM Features f
JOIN BindingSite tfbs USING (gene_id)
WHERE tfbs.state = 'current'
ORDER BY tfbs_id,
    feature_id,
    parent_id;*/
    public function generateFeatureLocationInfo(
        $entityType,
        $entityName,
        $entityId
    ) {
        // Query the feature view so that we can display this entity in relation to
        // the features that it is associated with.
        // The begin and end columns specify whether the entity starts/ends 5' or 3'
        // of the feature or if it starts/ends within it (0).
        $featureSql = "SELECT * FROM v_" . $entityType . "_feature_location " .
            "WHERE id = $entityId " .
            "AND type IN ('mrna', 'exon', 'intron') " .
            "ORDER BY id, feature_id, parent";
        $result = $this->query($featureSql);
        $locationInfo = array();
        // The transcript is the mRNA feature
        $transcriptId = null;
        while ( $featureRow = $result->fetch_assoc() ) {
            $relativeStart = $featureRow["relative_start"];
            $relativeEnd = $featureRow["relative_end"];
            $startDist = $featureRow["start_dist"];
            $endDist = $featureRow["end_dist"];
            $featureName = $featureRow["name"];
            $featureId = $featureRow["identifier"];
            $featureType = $featureRow["type"];
            //$strand = $featureRow["strand"];
            //$featureStart = $featureRow["f_start"];
            //$featureEnd = $featureRow["f_end"];
            //$start = $featureRow["start"];
            //$end = $featureRow["end"];
            $locationInfoBegin = null;
            $locationInfoEnd = null;
            $parentFeatureName = null;
            $findStart = $findEnd = false;
            if ( $featureType === "mrna" ) {
                $findOverlap = false;
                if ( ($relativeStart === $relativeEnd) &&
                    ($relativeStart !== 0) ) {
                    // The entity is completely 5' or 3' of the transcript (mrna) so store
                    // the location and skip all children of this transcript.
                    $startDistanceFromFeature = number_format($startDist / 1000, 1);
                    $endDistanceFromFeature = number_format($endDist / 1000, 1);
                    // $position = calculatePosition($strand, $relativeStart);
                    $startDescription = ( $startDist <= 5
                        ? "at"
                        : "$startDistanceFromFeature Kbp" );
                    $endDescription = ( $endDist <= 5
                        ? "at"
                        : "$endDistanceFromFeature Kbp {$relativeStart}' of" );
                    $locationInfo[] = "$entityName starts $startDescription and ends $endDescription mRNA $featureName";
                    // Skip all other features associated with this mRNA
                    $transcriptId = null;
                } elseif ( ($relativeStart === $relativeEnd) &&
                    ($relativeStart === 0) ) {
                    // The entity is completely contained within the transcript so we will
                    // need to find a containing or overlapping features.
                    $transcriptId = $featureId;
                    $parentFeatureName = $featureName;
                    $findStart = true;
                    $findEnd = true;
                } elseif ( ($relativeStart !== 0 ) &&
                    ($relativeEnd !== 0 ) &&
                    ($relativeStart !== $relativeEnd) ) {
                    // The entity spans the entire transcript
                    $startDistanceFromFeature = number_format($startDist / 1000, 1);
                    $endDistanceFromFeature = number_format($endDist / 1000, 1);
                    $locationInfo[] = "$entityName starts $startDistanceFromFeature Kbps {$relativeStart}' " .
                        "and ends $endDistanceFromFeature Kbps {$relativeEnd}' of mRNA $featureName";
                    $transcriptId = null;
                } elseif ( $relativeStart !== 0 ) {
                    // The entity starts outside of the transcript and overlaps it
                    $startDistanceFromFeature = number_format($startDist / 1000, 1);
                    $startDescription = ( $startDist <= 5
                        ? "at"
                        : "$startDistanceFromFeature Kbp {$relativeStart}' of" );
                    $locationInfoBegin = "$entityName starts $startDescription mRNA $featureName";
                    $transcriptId = $featureId;
                    $parentFeatureName = $featureName;
                    $findOverlap = true;
                    $transcriptRelativeStart = $relativeStart;
                    $transcriptRelativeEnd = $relativeEnd;
                } elseif ( $relativeEnd !== 0 ) {
                    // The entity starts overlapping the transcript and end outside of it
                    $endDistanceFromFeature = number_format($endDist / 1000, 1);
                    $endDescription = ( $endDist <= 5
                        ? "at"
                        : "$endDistanceFromFeature Kbp {$relativeEnd}' from" );
                    $locationInfoEnd = "and ends $endDescription $featureType mRNA $featureName";
                    $transcriptId = $featureId;
                    $parentFeatureName = $featureName;
                    $findOverlap = true;
                    $transcriptRelativeStart = $relativeStart;
                    $transcriptRelativeEnd = $relativeEnd;
                }
            } elseif ( $transcriptId !== null ) {
                // If we are searching for an overlap to find where an entity ends
                // compare the entity's relative start and end positions to those of a
                // transcript.
                if ( isset($findOverlap) &&
                    isset($transcriptRelativeStart) &&
                    isset($transcriptRelativeEnd) &&
                    ($relativeStart === $transcriptRelativeStart) &&
                    ($relativeEnd === $transcriptRelativeEnd) ) {
                    $locationInfo[] = ( $relativeEnd === 0
                        ? "$locationInfoBegin and ends at $featureType $featureId"
                        : "$entityName starts at $featureType $featureId $locationInfoEnd" );
                    $locationInfoBegin = null;
                    $locationInfoEnd = null;
                    $transcriptId = null;
                } elseif ( ($relativeStart === $relativeEnd) &&
                    ($relativeStart === 0) ) {
                    $locationInfo[] = "$entityName is contained within $featureType $featureId of mRNA $parentFeatureName";
                    $transcriptId = null;
                } elseif ( $findStart &&
                    ($relativeStart !== 0) &&
                    ($relativeEnd === 0) ) {
                    $startDistanceFromFeature = number_format($startDist / 1000, 1);
                    $startDescription = ( $startDist <= 5
                        ? "at"
                        : "$startDistanceFromFeature Kbp {$relativeStart}' of" );
                    $locationInfoBegin = "$entityName starts $startDescription $featureType $featureId";
                    $findStart = false;
                    if ( $locationInfoEnd !== null ) {
                        $locationInfo[] = ( $relativeEnd === 0
                            ? "$locationInfoBegin and ends at $featureType $featureId"
                            : "$entityName begins at $featureType $featureId $locationInfoEnd" );
                        $transcriptId = null;
                    }
                } elseif ( $findEnd &&
                    ($relativeStart === 0) &&
                    ($relativeEnd !== 0) ) {
                    $endDistanceFromFeature = number_format($endDist / 1000, 1);
                    $endDescription = ( $endDist <= 5
                        ? "at"
                        : "$endDistanceFromFeature Kbp {$relativeEnd}' from" );
                    $locationInfoEnd = "and ends $endDescription $featureType $featureId";
                    $findEnd = false;
                    if ( $locationInfoBegin !== null ) {
                        $locationInfo[] = ( 0 == $relativeEnd
                            ? "$locationInfoBegin and ends at $featureType $featureId"
                            : "$entityName begins at $featureType $featureId $locationInfoEnd" );
                        $transcriptId = null;
                    }
                }
            }
        }

        return implode(
            "$",
            array_unique($locationInfo)
        );
    }
    // ------------------------------------------------------------------------------------------
    // Generate a list of all RCs with associated TFBSs.  Only RCs that have associated TFBSs will be
    // returned. This method was not placed into the ReporterConstruct Handler because it exposes
    // database ids rather than REDfly ids for efficiency.
    // @returns an associative array where they keys are RC database ids and each value is also an
    //   associative array containing associated tfbs ids and names.
    //   $map[<rc_id>] = array("tfbs_id_list"   => array(<tfbs_ids>),
    //                         "tfbs_name_list" => array(<tfbs_names>));
    // ------------------------------------------------------------------------------------------
    public function generateRcWithAssocTfbsMapping()
    {
        $sql = "SELECT DISTINCT rc_id,
                    GROUP_CONCAT(bs.tfbs_id ORDER BY bs.tfbs_id) AS tfbs_id_list,
                    GROUP_CONCAT(bs.name ORDER BY bs.tfbs_id) AS tfbs_name_list
                FROM RC_associated_BS
                LEFT JOIN ReporterConstruct rc USING(rc_id)
                LEFT JOIN BindingSite bs USING(tfbs_id)
                WHERE rc.state = 'current' AND
                    bs.state = 'current'
                GROUP BY rc_id";
        $result = $this->_dbHandle->query($sql);
        $map = array();
        while ( $row = $result->fetch_assoc() ) {
            $map[$row["rc_id"]]["tfbs_id_list"] = explode(",", $row["tfbs_id_list"]);
            $map[$row["rc_id"]]["tfbs_name_list"] = explode(",", $row["tfbs_name_list"]);
        }

        return $map;
    }
    public function getGeneFlyBaseIdentifiers($geneLocus)
    {
        $geneLoci = explode(",", $geneLocus);
        $flyBaseIdentifiers = "";
        for ( $index = 0; $index < count($geneLoci); $index++ ) {
            $sql = str_replace(
                "_",
                "\_",
                "SELECT identifier
                 FROM Gene
                 WHERE LOWER(name) = LOWER('" .
                 $geneLoci[$index] . "')"
            );
            $result = $this->_dbHandle->query($sql);
            $row = $result->fetch_assoc();
            if ( $row !== null ) {
                if ( $flyBaseIdentifiers === "" ) {
                    $flyBaseIdentifiers = $row["identifier"];
                } else {
                    $flyBaseIdentifiers = $flyBaseIdentifiers . "," . $row["identifier"];
                }
            }
        }

        return $flyBaseIdentifiers;
    }
}
