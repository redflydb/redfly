<?php
class CitationHandler
{
    // --------------------------------------------------------------------------------
    // Factory design pattern
    // --------------------------------------------------------------------------------
    public static function factory()
    {
        return new CitationHandler;
    }
    // --------------------------------------------------------------------------------
    // Return help for the "list" action
    // --------------------------------------------------------------------------------
    public function listHelp()
    {
        $description = "List the citations. The results will be an array " .
            "where the key is the external id and the individual records " .
            "will consist of (id, citation_type, external_id, author_email, contents).";
        $options = array(
            "author_contacted"  => "Has the author been contacted? [0|1]",
            "author_email"      => "The email address of the author(s)",
            "external_id"       => "The external id (e.g., PUBMED id)",
            "force_update"      => "TRUE to force an update of the citation from NCBI " .
                                   "regardless it is in the cache or not",
            "id"                => "Return the citation matching the internal id",
            "limit"             => "The maximum number of citations to return",
            "limitoffset"       => "The offset of the first citation to return " .
                                   "(It requires \"limit\")",
            "load_if_not_avail" => "TRUE to load the citation from NCBI " .
                                   "if it is not in the local cache",
            "sort"              => "The sort field. The valid options are: type, " .
                                   "external_id, and author_email",
            "type"              => "Return only the citation(s) matching the type " .
                                   "(e.g., PUBMED)"
        );

        return RestResponse::factory(
            true,
            $description,
            $options
        );
    }
    // --------------------------------------------------------------------------------
    // List the citations
    // --------------------------------------------------------------------------------
    public function listAction(
        array $arguments,
        array $postData = null
    ) {
        $sqlCriteria = array();
        $sqlOrderBy = array();
        $limit = "";
        $response = null;
        $loadIfNotAvail = false;
        $forceUpdate = false;
        $externalId = null;
        $citationId = null;
        $authorEmail = null;
        $authorContacted = false;
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
                    $sqlCriteria[] = "citation_id " . $sqlOperator . " " . $db->escape($value);
                    break;
                case "type":
                    $sqlCriteria[] = "citation_type " . $sqlOperator . " " . $db->escape($value, true);
                    break;
                case "external_id":
                    $sqlCriteria[] = "external_id " . $sqlOperator . " " . $db->escape($value, true);
                    $externalId = $value;
                    break;
                case "author_contacted":
                    $sqlCriteria[] = "author_contacted " . $sqlOperator . " " . $db->escape($value, true);
                    break;
                case "author_email":
                    $value = urldecode($value);
                    $sqlCriteria[] = "author_email " . $sqlOperator . " " . $db->escape($value, true);
                    // Save the author email in case we need to create this citation in the database
                    $authorEmail = $value;
                    break;
                case "load_if_not_avail":
                    if ( $value === "1" ) {
                        $loadIfNotAvail = true;
                    }
                    break;
                case "force_update":
                    if ( $value === "1" ) {
                        $forceUpdate = true;
                    }
                    break;
                case "limit":
                    $limit = $helper->constructLimitStr($arguments);
                    break;
                case "sort":
                    $sortInformation = $helper->extractSortInformation($value);
                    foreach ( $sortInformation as $sortColumn => $direction ) {
                        switch ( $sortColumn ) {
                            case "type":
                                $sqlOrderBy[] = "type " . $direction;
                                break;
                            case "external_id":
                                $sqlOrderBy[] = "external_id " . $direction;
                                break;
                            case "author_email":
                                $sqlOrderBy[] = "author_email " . $direction;
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
        SELECT citation_id,
            citation_type,
            external_id,
            author_email,
            author_contacted,
            author_contact_date,
            author_list,
            title,
            contents,
            journal_name,
            year,
            month,
            volume,
            issue,
            pages
        FROM Citation
SQL;
        if ( count($sqlCriteria) !== 0 ) {
            $sql .= " WHERE " . implode(" AND ", $sqlCriteria);
        }
        if ( count($sqlOrderBy) !== 0 ) {
            $sql .= " ORDER BY " . implode(",", $sqlOrderBy);
        }
        $sql .= " " . $limit;
        $response = $helper->query(
            $db,
            $sql
        );
        // Save information returned by the database to be used
        // in case we are forcing an update of the data
        if ( $response->numResults() !== 0 ) {
            $resultList = $response->results();
            $results = array_shift($resultList);
            $citationId = $results["citation_id"];
            $externalId = $results["external_id"];
            $authorEmail = $results["author_email"];
            $authorContacted = $results["author_contacted"];
        }
        // If the pubmed id was not found in the database query NCBI for it
        if ( (($response->numResults() === 0) && $loadIfNotAvail) ||
            (($response->numResults() !== 0) && $forceUpdate) ) {
            $ncbi = new ExtDatasource_Pubmed();
            $param = new ExtDatasourceParameters;
            $param->recordId = $externalId;
            try {
                $ncbi->query($param);
                $citation = $ncbi->getResult();
                $result = array(
                    "citation_id"      => $citationId,
                    "citation_type"    => "PUBMED",
                    "external_id"      => $externalId,
                    "author_email"     => $authorEmail,
                    "author_contacted" => $authorContacted,
                    "author_list"      => $citation->authorList,
                    "title"            => $citation->title,
                    "contents"         => $citation->format(),
                    "journal_name"     => $citation->journalName,
                    "year"             => $citation->journalYear,
                    "month"            => $citation->journalMonth,
                    "volume"           => $citation->journalVolume,
                    "issue"            => $citation->journalIssue,
                    "pages"            => $citation->journalPages
                );
                // Automatically store it into the database.
                // Be sure to update the id with the assigned id and
                // if the save response failed pass along the response containing
                // the error.
                $saveResponse = $this->saveAction(
                    array(),
                    array("results" => json_encode($result))
                );
                if ( $saveResponse->success() ) {
                    list($saveResult) = $saveResponse->results();
                    $result["citation_id"] = $saveResult["citation_id"];
                    $response = RestResponse::factory(
                        true,
                        null,
                        array($result)
                    );
                } else {
                    $response = $saveResponse;
                }
            } catch ( Exception $e ) {
                $response = RestResponse::factory(
                    false,
                    $e->getMessage()
                );
            }
        }

        return $response;
    }
    // --------------------------------------------------------------------------------
    // Return help for the "save" action
    // --------------------------------------------------------------------------------
    public function saveHelp()
    {
        $desc = "Save a citation. Results will be an array with a single record " .
            "consisting of (citation_id, external_id).";
        $options = array(
            "author_contacted" => "Has the author been contacted? [0|1]",
            "author_email"     => "The email address of the author(s)",
            "author_list"      => "Array of author names",
            "citation_id"      => "Citation identifier",
            "citation_type"    => "Citation type (e.g., PUBMED)",
            "contents"         => "The formatted citation",
            "external_id"      => "External identifier (e.g., PUBMED id)",
            "issue"            => "The publication issue",
            "journal_name"     => "The name of the journal",
            "month"            => "The publication name",
            "pages"            => "The publication pages",
            "title"            => "The article title",
            "volume"           => "The publication volume",
            "year"             => "The publication year"
        );

        return RestResponse::factory(
            true,
            $desc,
            $options
        );
    }
    // --------------------------------------------------------------------------------
    // Save a citation
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
        // The "results" key contains the data to save and must be present.
        if ( ! isset($postData["results"]) ) {
            return RestResponse::factory(
                false,
                "Array key \"results\" not present in input"
            );
        }
        $db = DbService::factory();
        // The ExtJS store will be sending JSON encoded data under the
        // "results" key based on the root property of the reader.
        $data = (array) json_decode($postData["results"]);
        // Extract and normalize data from the post
        $citationId = ( isset($data["citation_id"]) &&
            (! empty($data["citation_id"]))
            ? $data["citation_id"]
            : null );
        $citationType = ( isset($data["citation_type"])
            ? $data["citation_type"]
            : "PUBMED" );
        $authorEmail = ( isset($data["author_email"])
            ? $data["author_email"]
            : null );
        $authorContacted = ( isset($data["author_contacted"])
            ? $data["author_contacted"]
            : false );
        $authorContactDate = null;
        $externalId = ( isset($data["external_id"])
            ? $data["external_id"]
            : null );
        $authorList = ( isset($data["author_list"])
            ? ( is_array($data["author_list"])
                ? implode(", ", $data["author_list"])
                : $data["author_list"] ) : null );
        $title = ( isset($data["title"])
            ? $data["title"]
            : null );
        if ( (! isset($data["contents"])) ||
            ($data["contents"] === "") ) {
            throw new Exception("Error about the contents being empty");
        } else {
            $contents = $data["contents"];
        }
        $journalName = ( isset($data["journal_name"])
            ? $data["journal_name"]
            : null );
        $year = ( isset($data["year"])
            ? $data["year"]
            : null );
        $month = ( isset($data["month"])
            ? $data["month"]
            : null );
        $volume = ( isset($data["volume"])
            ? $data["volume"]
            : null );
        $issue = ( isset($data["issue"])
            ? $data["issue"]
            : null );
        $pages = ( isset($data["pages"])
            ? $data["pages"]
            : null );
        if ( $citationId === null ) {
            $sql = <<<SQL
            INSERT INTO Citation (
                citation_type,
                external_id,
                author_email,
                author_contacted,
                author_contact_date,
                author_list,
                title,
                contents,
                journal_name,
                year,
                month,
                volume,
                issue,
                pages
            ) VALUES (
                ?,
                ?,
                ?,
                ?,
                NULL,
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
            if ( ($statement = $db->getHandle()->prepare($sql)) === false ) {
                throw new Exception("Error preparing statement: " . $db->getError());
            }
            $statement->bind_param(
                "sssissssissss",
                $citationType,
                $externalId,
                $authorEmail,
                $authorContacted,
                $authorList,
                $title,
                $contents,
                $journalName,
                $year,
                $month,
                $volume,
                $issue,
                $pages
            );
            if ( $statement->execute() === false ) {
                throw new Exception("Error adding citation: " . $statement->error);
            }
        } else {
            // Update an existing citation
            $sql = <<<SQL
            UPDATE Citation
            SET citation_type = ?,
                external_id = ?,
                author_email = ?,
                author_contacted = ?,
                author_contact_date = ?,
                author_list = ?,
                title = ?,
                contents = ?,
                journal_name = ?,
                year = ?,
                month = ?,
                volume = ?,
                issue = ?,
                pages = ?
            WHERE citation_id = ?
SQL;
            if ( ($statement = $db->getHandle()->prepare($sql)) === false ) {
                throw new Exception("Error preparing statement: " . $db->getError());
            }
            $statement->bind_param(
                "sssisssssissssi",
                $citationType,
                $externalId,
                $authorEmail,
                $authorContacted,
                $authorContactDate,
                $authorList,
                $title,
                $contents,
                $journalName,
                $year,
                $month,
                $volume,
                $issue,
                $pages,
                $citationId
            );
            if ( $statement->execute() === false ) {
                throw new Exception("Error updating citation: " . $statement->error);
            }
        }
        if ( ! $citationId ) {
            $citationId = $db->lastInsertId();
        }

        return RestResponse::factory(
            true,
            null,
            array(
                array(
                    "citation_id" => $citationId,
                    "external_id" => $externalId
                )
            )
        );
    }
}
