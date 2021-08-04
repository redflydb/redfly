<?php
// --------------------------------------------------------------------------------
// Singleton database abstraction layer to provide higher level operations on
// the basic REDfly data objects.
// --------------------------------------------------------------------------------
class RestHandlerHelper
{
    // Singleton instance
    private static $instance = null;
    // Ensure that this class is a singleton
    private function __construct()
    {
    }
    public static function factory()
    {
        if ( self::$instance === null ) {
            self::$instance = new RestHandlerHelper;
        }

        return self::$instance;
    }
    // --------------------------------------------------------------------------------
    // Given a value to be used in a WHERE clause, scan it for API wildcards and
    // convert them to SQL wildcards. If wildcards were found then convert them
    // to SQL wildcards and return TRUE.
    // @param $value Reference to the value that will be used in the WHERE clause
    // @returns TRUE if wildcards were found
    // --------------------------------------------------------------------------------
    public function convertWildcards(&$value)
    {
        $wildcardsFound = false;
        if ( ($value[0] === "*") ||
            ($value[strlen($value) - 1] === "*") ) {
            $value = preg_replace(
                "/^\*|\*$/",
                "%",
                $value
            );
            $wildcardsFound = true;
        }

        return $wildcardsFound;
    }
    // --------------------------------------------------------------------------------
    // Extract optional operators from the start of an argument value and return both
    // operator and argument value minus the operator.
    // Valid operators are:
    // Equal: = (default, used if no operator is present)
    // Not equal: !
    // Less Than: <
    // Greater Than: >
    // @param $value Reference to the argument value to be searched, if an argument is
    //               found then it will be removed
    // @param $op Reference to the operator found or "=" by default
    // @returns TRUE if an operator was found, FALSE otherwise.
    // --------------------------------------------------------------------------------
    public function extractOperator(
        &$value,
        &$op
    ) {
        $validOperators = array(
            "=" => "=",
            "!" => "!=",
            ">" => ">",
            "<" => "<"
        );
        $op = "=";
        $operatorFound = false;
        // Bools have no operator
        if ( is_bool($value) ) {
            return $operatorFound;
        }
        if ( array_key_exists($value[0], $validOperators) ) {
            $op = $validOperators[$value[0]];
            $value = substr($value, 1);
            $operatorFound = true;
        }

        return $operatorFound;
    }
    // --------------------------------------------------------------------------------
    // Extract sort columns and optional sort directions from the sort specification.
    // By default the sort is done in ascending order but if the field is preceded by
    // a minus then descending order is used (e.g, sort=name vs sort=-name).
    // Multiple columns may be separated by commas and the available sort operators
    // are "+" for ascending order (the default if no operator is present) and "-" for
    // descending order.
    // @param $sortSpecification Sort specification containing one or more
    //                           comma-separated sort columns and optional sort
    //                           direction
    // @returns An array containing sort information where the key is the sort column
    //          and the value is the direction. The result is order as per the sort
    //          specification.
    // --------------------------------------------------------------------------------
    public function extractSortInformation($sortSpecification)
    {
        $sortInformation = array();
        $validSortDirections = array("+", "-");
        // Multiple sort columns are comma-separated
        $sortList = explode(",", $sortSpecification);
        foreach ( $sortList as $sortColumn ) {
            if ( in_array($sortColumn[0], $validSortDirections) ) {
                $direction = ( $sortColumn[0] === "-"
                    ? "DESC"
                    : "ASC" );
                $sortColumn = substr($sortColumn, 1);
                $sortInformation[ $sortColumn ] = $direction;
            } else {
                $sortInformation[ $sortColumn ] = "ASC";
            }
        }

        return $sortInformation;
    }
    // --------------------------------------------------------------------------------
    // Construct the SQL LIMIT string based on the API arguments.
    // The following arguments are examined:
    // "limit" - The maximim number of rows to return
    // "limitoffset" - The offset of the first row to return
    // @param $args The array of API arguments
    // @returns The SQL LIMIT string or an empty string if no limit information
    //          was present.
    // --------------------------------------------------------------------------------
    public function constructLimitStr(array $args)
    {
        if ( (! array_key_exists("limit", $args)) ||
            (! is_numeric($args["limit"])) ||
            ($args["limit"] < 0) ) {
            return "";
        }
        $limitStr = "";
        if ( array_key_exists("start", $args) &&
            is_numeric($args["start"]) ) {
            $limitStr = "LIMIT " . $args["start"] . "," . $args["limit"];
        } else {
            $limitStr = "LIMIT " . $args["limit"];
        }

        return $limitStr;
    }
    // --------------------------------------------------------------------------------
    // Construct a query from the base SQL, any additional tables to join, search
    // criteria, order by information, and limit clause.
    // @param $sql Reference to the base SQL statement ending with the FROM or JOIN
    //             clauses. This will be augmented with the rest of the query.
    // @param $joinTables Optional array containing one or more tables to join with
    //                    the existing SQL statement. These are typically determined
    //                    conditionally.
    // @param $criteria Optional query criteria that will be ANDed together
    // @param $groupBy Optional grouping information
    // @param $orderBy Optional ordering information
    // @param $limit Optional limit string
    // @returns Nothing
    // --------------------------------------------------------------------------------
    public function constructQuery(
        &$sql,
        array $joinTables,
        array $criteria,
        array $groupBy = array(),
        array $orderBy = array(),
        $limit = ""
    ) {
        if ( count($joinTables) !== 0 ) {
            $sql .= " " . implode(" ", $joinTables);
        }
        if ( count($criteria) !== 0 ) {
            $sql .= " WHERE " . implode(" AND ", $criteria);
        }
        if ( count($groupBy) !== 0 ) {
            $sql .= " GROUP BY " . implode(",", $groupBy);
        }
        if ( count($orderBy) !== 0 ) {
            $sql .= " ORDER BY " . implode(",", $orderBy);
        }
        $sql .= " $limit";
    }
    // --------------------------------------------------------------------------------
    // Query the database and construct the response object. If there was an error
    // during the query then the response will contain the error message.
    // @param $db The database resource handle
    // @param $sql The SQL statement
    // @returns A RestResponse object with the query result
    // --------------------------------------------------------------------------------
    public function query(
        $db,
        $sql
    ) {
        try {
            $queryResult = $db->query($sql);
            $results = array();
            while ( $row = $queryResult->fetch_assoc() ) {
                $results[] = $row;
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
    // Convert a search or query option to a boolean value for use when querying
    // the database. Examples of true values are TRUE, 1, "yes" (or "y"), and
    // "true" (or "t") while false values are FALSE, 0, "no", and "false".
    // @param $value Value to convert
    // @returns The converted value
    // --------------------------------------------------------------------------------
    public function convertValueToBool($value)
    {
        // Need to check if it is bool before we try to lowercase it as a string
        if ( is_bool($value) ) {
            return $value;
        }
        if ( is_numeric($value) ) {
            return ( $value === 1
                ? 1
                : 0 );
        }
        $value = strtolower($value);
        $returnValue = ( (strpos($value, "t") === 0) ||
            (strpos($value, "y") === 0) )
            ? 1
            : 0;

        return $returnValue;
    }
    // --------------------------------------------------------------------------------
    // Generate the REDfly entity id. The REDfly id consists of a type, entity number,
    // and version in the format: RF<type>:<entity>.<version> where the entity is
    // zero-filled to 10 digits and the version is zero-filled to 3 digits.
    // If the entity is new and has not been assigned an entity id then the entity id
    // will be zero and the version will be the internal database identifer of the
    // entity until it is approved (e.g., RFRC:0000000000.1829)
    // @param $entityType The entity type (e.g., database resource handle)
    // @param $entityId The database entity identifier (may be NULL)
    // @param $version The entity version
    // @param $id The database identifier for the entity
    // @returns The formatted entity id
    // --------------------------------------------------------------------------------
    public function entityId(
        $entityType,
        $entityId,
        $version,
        $id = null
    ) {
        if ( $entityId === null ) {
            return "RF" . $entityType . ":" . sprintf("%010s", 0) .
                "." . sprintf("%s", $id);
        } else {
            return "RF" . $entityType . ":" . sprintf("%010s", $entityId) .
                "." . sprintf("%03s", $version);
        }
    }
    // --------------------------------------------------------------------------------
    // Generate an entity id as "entityId()" above, but without the version number.
    // @param $entityType The entity type (e.g., database resource handle)
    // @param $entityId The database entity identifier (may be NULL)
    // @param $id The database identifier for the entity
    // @returns The formatted entity id
    // --------------------------------------------------------------------------------
    public function unversionedEntityId(
        $entityType,
        $entityId,
        $id = null
    ) {
        if ( $entityId === null ) {
            return sprintf(
                "RF%s:%010s.%s",
                $entityType,
                0,
                $id
            );
        } else {
            return sprintf(
                "RF%s:%010s",
                $entityType,
                $entityId
            );
        }
    }
    // --------------------------------------------------------------------------------
    // Parse the REDfly id of the entity.
    // REDfly ID examples:
    //   RF<type>:<entityId>
    //   RF<type>:<entityId>.<version>
    //   RF<type>:0000000000.<dbId>
    // @param $redflyId The REDfly id to parse
    // @param $entityType The entity type (e.g., database resource handle)
    // @param $entityId The entity identifier
    // @param $version The entity version
    // @param $dbId The database identifer for new entities
    // @returns The formatted entity id
    // --------------------------------------------------------------------------------
    public function parseEntityId(
        $id,
        &$type,
        &$entityId,
        &$version,
        &$dbId
    ) {
        $type = null;
        $entityId = null;
        $version = null;
        $dbId = null;
        $validEntities = array(
            CrmsegmentHandler::EntityCode,
            PredictedcrmHandler::EntityCode,
            ReporterconstructHandler::EntityCode,
            TranscriptionfactorbindingsiteHandler::EntityCode
        );
        $entityIdOrAllZeroes = explode(".", explode(":", $id)[1])[0];
        if ( $entityIdOrAllZeroes !== "0000000000" ) {
            // REDfly ID examples:
            //   RF<type>:<entityId>
            //   RF<type>:<entityId>.<version>
            $regex =
            '/
            ^                                        # Beginning of string
            RF                                       # Literal "RF"
            (' . implode("|", $validEntities) . ')   # Entity Type ($matches[1])
            :                                        # Literal ":"
            ([0-9]{8,})                              # Entity ID ($matches[2])
            (?:                                      # Non-capturing group
                \.                                   # Literal "."
                ([0-9]{3,})                          # Version ($matches[3])
            )?                                       # This group is optional
            $                                        # End of string
            /x';
        } else {
            // REDfly ID examples:
            //   RF<type>:0000000000.<dbId>
            $regex =
            '/
            ^                                        # Beginning of string
            RF                                       # Literal "RF"
            (' . implode("|", $validEntities) . ')   # Entity Type ($matches[1])
            :                                        # Literal ":"
            ([0]{10})                                # All zeroes ($matches[2])
            \.                                       # Literal "."
            ([0-9]{1,})                              # Database identifier ($matches[3])
            $                                        # End of string
            /x';
        }
        $returnValue = preg_match(
            $regex,
            $id,
            $matches
        );
        if ( ($returnValue === false) ||
            ($returnValue === 0) ) {
            throw new Exception("Invalid REDfly ID \"" . $id . "\"");
        }
        $type = (string) $matches[1];
        $entityId = (int) $matches[2];
        if ( $entityId === 0 ) {
            $entityId = null;
            if ( ! isset($matches[3]) ) {
                throw new Exception("Invalid REDfly ID \"" . $id . "\"");
            }
            $dbId = (int) $matches[3];
        } elseif ( isset($matches[3]) ) {
            $version = (int) $matches[3];
        }

        return true;
    }
    // --------------------------------------------------------------------------------
    // Format the coordinates of the entity as follows chr:start..stop
    // @param $chr Chromosome
    // @param $start Start coordinate
    // @param $stop Stop coordinate
    // @returns The formatted coordinate string.
    // --------------------------------------------------------------------------------
    public function formatCoordinates(
        $chr,
        $start,
        $end
    ) {
        return $chr . ":" . $start . ".." . $end;
    }
    // --------------------------------------------------------------------------------
    // Construct an URL for accessing a Gbrowse image of the specified region.
    // The base url (e.g., http://flybase.org/cgi-bin/gbrowse_img/dmel/) is stored in
    // the entity widget.
    // @param $entityName Name of the REDfly entity
    // @param $coordinates REDfly entity coordinates
    // @param $chromosome REDfly entity chromosome
    // @param $entityStart REDfly entity start coordinate
    // @param $entityEnd REDfly entity end coordinate
    // @param $geneIdentifier Gene identifier for the gene
    // @param $geneStart Gene start coordinates
    // @param $geneEnd Gene end coordinates
    // @returns The URL for retrieving the flymine image
    // --------------------------------------------------------------------------------
    public function constructGbrowseImageUrl(
        $entityName,
        $coordinates,
        $chromosome,
        $entityStart,
        $entityEnd,
        $geneIdentifier,
        $geneStart,
        $geneEnd
    ) {
        $zoom = 4000;
        $startCoordinates = null;
        $stopCoordinates = null;
        $bufferSize = 4000;
        // Generate coordinates that will drive the size of the chromosome slice to be
        // displayed on the chart. We are calculating the window size that is relative
        // to the gene start and end positions.
        // For example, if the gene is FBgn0004102 (X:8524192..8544710) and the window
        // size is 0..20519 the gene will fill the window.
        // If we would like to display 5kbp on either side then we would use:
        // -5000..25519
        // For a description of gbrowse options see:
        // http://www.flymine.org/cgi-bin/gbrowse_img/flymine-release-17.0/
        $geneSize = $geneEnd - $geneStart;
        if ( $entityEnd <= $geneStart ) {
            // Entity is entirely 5' of the gene.
            // If on the negative strand we will want to swap window start and end
            // values.
            // $windowStart = - ($geneStart - $entityStart + $bufferSize);
            // $windowEnd = $geneSize + $bufferSize;
            $windowStart = - $bufferSize;
            $windowEnd = $geneSize + ($geneStart - $entityStart) + $bufferSize;
        } elseif ( $entityStart >= $geneEnd ) {
            // Entity is entirely 3' of the gene
            $windowStart = - $bufferSize;
            $windowEnd = $geneSize + $bufferSize;
        } elseif ( ($entityStart >= $geneStart) && ($entityEnd <= $geneEnd) ) {
            // Entity is contained within the gene
            $windowStart = - $bufferSize;
            $windowEnd = $geneSize + $bufferSize;
        } elseif ( ($entityStart <= $geneStart) && ($entityEnd >= $geneEnd) ) {
            // Gene is contained within the entity
            $windowStart = $geneStart - $entityStart - $bufferSize;
            $windowEnd = $geneSize + ($entityEnd - $geneEnd) + $bufferSize;
        } elseif ( ($entityStart < $geneStart) && ($entityEnd > $geneStart) && ($entityEnd < $geneEnd) ) {
            // Entity starts 5' of gene and ends within the gene
            $windowStart = $geneStart - $entityStart - $bufferSize;
            $windowEnd = $geneSize + $bufferSize;
        } elseif ( ($entityEnd > $geneEnd) && ($entityStart > $geneStart) && ($entityStart < $geneEnd) ) {
            // Entity starts within the gene and ends 3' of the gene
            $windowStart = - $bufferSize;
            $windowEnd = $geneSize + ($entityEnd - $geneEnd) + $bufferSize;
        }
        // Build the GBrowse image URL.
        // @see http://gmod.org/wiki/GBrowse_img
        $url = "?name=%s;type=gene+RNA+regulatory_region1;%s;grid=1;h_region=%s@yellow";
        $imageWidth = "width=550";
        $entityWindow = $chromosome . ":" . ($entityStart - 10000) . ".." . ($entityEnd + 10000);
        $highlightRegion = $chromosome . ":" . $entityStart . ".." . $entityEnd;

        return sprintf(
            $url,
            $entityWindow,
            $imageWidth,
            $highlightRegion
        );
    }
    // --------------------------------------------------------------------------------
    // Get the list of curator email addresses associated with a list of REDfly
    // identifiers.
    // @param $redflyIdList An array of REDfly identifiers
    // @returns An array of unique curator email addresses
    // --------------------------------------------------------------------------------
    public function getCuratorEmails(array $redflyIdList)
    {
        $tfbsList = array();
        $crmsList = array();
        $rcList = array();
        $emailList = array();
        $db = DbService::factory();
        if ( count($redflyIdList) === 0 ) {
            return $emailList;
        }
        foreach ( $redflyIdList as $redflyId ) {
            $type = $entityId = $version = $dbId = null;
            $this->parseEntityId(
                $redflyId,
                $type,
                $entityId,
                $version,
                $dbId
            );
            switch ( $type ) {
                case CrmsegmentHandler::EntityCode:
                    $crmsList[] = array(
                        $redflyId,
                        $entityId,
                        $version,
                        $dbId
                    );
                    break;
                case ReporterconstructHandler::EntityCode:
                    $rcList[] = array(
                        $redflyId,
                        $entityId,
                        $version,
                        $dbId
                    );
                    break;
                case TranscriptionfactorbindingsiteHandler::EntityCode:
                    $tfbsList[] = array(
                        $redflyId,
                        $entityId,
                        $version,
                        $dbId
                    );
                    break;
                default:
                    throw new Exception($redflyId . " is not a valid REDfly Id");
            }
        }
        $curatorIdList = array_unique(
            array_merge(
                $this->getCuratorIdList(
                    $db,
                    $tfbsList,
                    "BindingSite",
                    "tfbs_id"
                ),
                $this->getCuratorIdList(
                    $db,
                    $crmsList,
                    "CRMSegment",
                    "crm_segment_id"
                ),
                $this->getCuratorIdList(
                    $db,
                    $rcList,
                    "ReporterConstruct",
                    "rc_id"
                )
            )
        );
        $curatorIds = implode(",", $curatorIdList);
        if ( $curatorIds !== "" ) {
            $sql = "SELECT email
                    FROM Users
                    WHERE user_id IN (" . $curatorIds . ")";
            $result = $db->query($sql);
            while ( $row = $result->fetch_assoc() ) {
                $emailList[] = $row["email"];
            }
        }

        return $emailList;
    }
    // --------------------------------------------------------------------------------
    // Return the list of curator identifiers for the entities requested.
    // @param $db An instance of the DbService class
    // @param $entityInformationList An array of entity information tuples containing
    //                               ($redflyId, $entityId, $version, $dbId)
    // @param $tableName The table to query
    // @param $identifierColumn The identifier column
    // @returns A list of curator database identifiers (may not be unique)
    // --------------------------------------------------------------------------------
    private function getCuratorIdList(
        DbService $db,
        array $entityInformationList,
        $tableName,
        $identifierColumn
    ) {
        if ( empty($tableName) ) {
            throw new Exception("Table name not provided");
        }
        if ( empty($identifierColumn) ) {
            throw new Exception("ID column not provided");
        }
        $curatorIdList = array();
        foreach ( $entityInformationList as $entityInformation ) {
            list(
                $redflyId,
                $entityId,
                $version,
                $dbId
            ) = $entityInformation;
            $sql = "SELECT curator_id
                    FROM " . $tableName . "
                    WHERE " .
                    ( $dbId !== null
                        ? "entity_id IS NULL and " . $identifierColumn . "= ?"
                        : "entity_id = ? AND version = ?" );
            if ( ($statement = $db->getHandle()->prepare($sql)) === false ) {
                  throw new Exception("Error preparing statement: " . $db->getError());
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
                throw new Exception("Error executing statement: " . $sql .
                    ", " . $statement->error);
            }
            $curatorId = null;
            $statement->bind_result($curatorId);
            while ( $statement->fetch() ) {
                $curatorIdList[] = $curatorId;
            }
        }

        return $curatorIdList;
    }
    // --------------------------------------------------------------------------------
    // Return the previous curators from all the versions of a REDfly entity.
    // @param $type
    // @param $entityId
    // @param $curatorFullName
    // @returns A string of unique previous curator full names
    // --------------------------------------------------------------------------------
    public function getPreviousCurators(
        $type,
        $entityId,
        $curatorFullName
    ) {
        $db = DbService::factory();
        switch ( $type ) {
            case CrmsegmentHandler::EntityCode:
                $entityTable = "CRMSegment";
                break;
            case PredictedcrmHandler::EntityCode:
                $entityTable = "PredictedCRM";
                break;
            case ReporterconstructHandler::EntityCode:
                $entityTable = "ReporterConstruct";
                break;
            case TranscriptionfactorbindingsiteHandler::EntityCode:
                $entityTable = "BindingSite";
                break;
            default:
                throw new Exception($type . " is not a valid REDfly entity type");
        }
        $sql = <<<SQL
        SELECT DISTINCT(CONCAT(u.first_name, ' ', u.last_name)) AS curator_full_name
        FROM $entityTable entityTable
        JOIN Users u ON (entityTable.curator_id = u.user_id)
        WHERE entityTable.entity_id = $entityId AND
            u.username NOT IN ('avillaho', 'mshalfon', 'svekeranen')
        ORDER BY u.last_name;
SQL;
        $queryResult = $db->query($sql);
        $previousCuratorFullNameResults = "";
        while ( $previousCuratorRow = $queryResult->fetch_assoc() ) {
            if ( $previousCuratorRow["curator_full_name"] !== $curatorFullName ) {
                if ( $previousCuratorFullNameResults === "" ) {
                    $previousCuratorFullNameResults = $previousCuratorRow["curator_full_name"];
                } else {
                    $previousCuratorFullNameResults .= ", " . $previousCuratorRow["curator_full_name"];
                }
            }
        }

        return $previousCuratorFullNameResults;
    }
    // --------------------------------------------------------------------------------
    // Get the list of auditor email addresses associated with a list of REDfly
    // identifiers.
    // @param $redflyIdList An array of REDfly identifiers
    // @returns An array of unique auditor email addresses
    // --------------------------------------------------------------------------------
    public function getAuditorEmails(array $redflyIdList)
    {
        $tfbsList = array();
        $crmsList = array();
        $rcList = array();
        $emailList = array();
        $db = DbService::factory();
        if ( count($redflyIdList) === 0 ) {
            return $emailList;
        }
        foreach ( $redflyIdList as $redflyId ) {
            $type = $entityId = $version = $dbId = null;
            $this->parseEntityId(
                $redflyId,
                $type,
                $entityId,
                $version,
                $dbId
            );
            switch ( $type ) {
                case CrmsegmentHandler::EntityCode:
                    $crmsList[] = array(
                        $redflyId,
                        $entityId,
                        $version,
                        $dbId
                    );
                    break;
                case ReporterconstructHandler::EntityCode:
                    $rcList[] = array(
                        $redflyId,
                        $entityId,
                        $version,
                        $dbId
                    );
                    break;
                case TranscriptionfactorbindingsiteHandler::EntityCode:
                    $tfbsList[] = array(
                        $redflyId,
                        $entityId,
                        $version,
                        $dbId
                    );
                    break;
                default:
                    throw new Exception("$redflyId is not a valid REDfly Id");
            }
        }
        $auditorIdList = array_unique(
            array_merge(
                $this->getAuditorIdList(
                    $db,
                    $tfbsList,
                    "BindingSite",
                    "tfbs_id"
                ),
                $this->getAuditorIdList(
                    $db,
                    $crmsList,
                    "CRMSegment",
                    "crm_segment_id"
                ),
                $this->getAuditorIdList(
                    $db,
                    $rcList,
                    "ReporterConstruct",
                    "rc_id"
                )
            )
        );
        $auditorIds = implode(",", $auditorIdList);
        if ( $auditorIds !== "" ) {
            $sql = "SELECT email
                    FROM Users
                    WHERE user_id IN ($auditorIds)";
            $result = $db->query($sql);
            while ( $row = $result->fetch_assoc() ) {
                $emailList[] = $row["email"];
            }
        }

        return $emailList;
    }
    // --------------------------------------------------------------------------------
    // Return the list of auditor identifiers for the entities requested.
    // @param $db An instance of the DbService class
    // @param $entityInformationList An array of entity information tuples containing
    //                               ($redflyId, $entityId, $version, $dbId)
    // @param $tableName The table to query
    // @param $identifierColumn The identifier column
    // @returns A list of auditor database identifiers (may not be unique)
    // --------------------------------------------------------------------------------
    private function getAuditorIdList(
        DbService $db,
        array $entityInformationList,
        $tableName,
        $identifierColumn
    ) {
        if ( empty($tableName) ) {
            throw new Exception("Table name not provided");
        }
        if ( empty($identifierColumn) ) {
            throw new Exception("ID column not provided");
        }
        $auditorIdList = array();
        foreach ( $entityInformationList as $entityInformation ) {
            list(
                $redflyId,
                $entityId,
                $version,
                $dbId
            ) = $entityInformation;
            $sql = "SELECT auditor_id
                    FROM " . $tableName . "
                    WHERE " .
                    ( $dbId !== null
                        ? "entity_id IS NULL and " . $identifierColumn  . "= ?"
                        : "entity_id = ? AND version = ?" );
            if ( ($statement = $db->getHandle()->prepare($sql)) == false ) {
                  throw new Exception("Error preparing statement: " . $db->getError());
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
                  throw new Exception("Error executing statement: " . $sql .
                    ", " . $statement->error);
            }
            $auditorId = null;
            $statement->bind_result($auditorId);
            while ( $statement->fetch() ) {
                $auditorIdList[] = $auditorId;
            }
        }

        return $auditorIdList;
    }
}
