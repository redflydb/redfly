<?php
class RemoteflybaseHandler
{
    // --------------------------------------------------------------------------------
    // Factory design pattern
    // --------------------------------------------------------------------------------
    public static function factory()
    {
        return new RemoteflybaseHandler;
    }
    // --------------------------------------------------------------------------------
    // Return help for the "list" action
    // --------------------------------------------------------------------------------
    public function pmid_to_fbalHelp()
    {
        $description = "Map the PubMed ID to the recombinant constructs in Flybase " .
            "(FBal) by remotely querying the FlyBase database";
        $options = array("pmid" => "Pubmed ID");

        return RestResponse::factory(
            true,
            $description,
            $options
        );
    }
    // --------------------------------------------------------------------------------
    // List the transgenic constructs
    // --------------------------------------------------------------------------------
    public function pmid_to_fbalAction(
        array $arguments,
        array $postData = null
    ) {
        if ( ! isset($arguments["pmid"]) ) {
            return RestResponse::factory(
                false,
                "PMID not provided"
            );
        }
        $pmid = $arguments["pmid"];
        $dbHandle = null;
        $results = array();
        $message = null;
        $returnValue = true;
        // Connect to the remote Flybase Postgres instance and
        // run the Dave Gerrard's query
        $options = $GLOBALS["options"];
        $flybaseHost = $options->flybase->host;
        $flybaseUser = $options->flybase->user;
        $flybaseDb = $options->flybase->name;
        $flybasePort = $options->flybase->port;
        $connection = "host=" . $flybaseHost .
            " port=" . $flybasePort .
            " dbname=" . $flybaseDb .
            " user=" . $flybaseUser;
        if ( ($dbHandle = @pg_connect($connection)) === false ) {
            return RestResponse::factory(
                false,
                "Error connecting to " . $flybaseHost .
                    " database with string \"" . $connection ."\""
            );
        }
        $sql = <<<SQL
        SELECT p.uniquename,
            f.uniquename,
            f.name
        FROM feature f,
            feature_cvterm fcvt,
            pub p,
            cvterm cvt,
            cv
        WHERE p.uniquename = (SELECT p.uniquename
                              FROM pub p,
                                    pub_dbxref pdbx,
                                    dbxref dbx,
                                    db
                              WHERE dbx.accession = '$pmid' AND
                                db.name = 'pubmed' AND
                                pdbx.is_current = true AND
                                p.pub_id = pdbx.pub_id AND
                                pdbx.dbxref_id = dbx.dbxref_id AND
                                dbx.db_id = db.db_id AND
                                p.is_obsolete = 'f') AND
        cv.name = 'transgene_description' AND
        fcvt.pub_id = p.pub_id AND
        f.feature_id = fcvt.feature_id AND
        fcvt.cvterm_id = cvt.cvterm_id AND
        cvt.cv_id = cv.cv_id AND
        f.is_analysis = 'f';
SQL;
        if ( ($result = pg_query($dbHandle, $sql)) === false ) {
            $message = "Error querying flybase for FBtp: \"" .
                pg_result_error($result) . "\"";
            $returnValue = false;
        } elseif ( pg_num_rows($result) === 0 ) {
            $message = "No FBtp results found in Flybase for PMID " . $pmid;
            $returnValue = false;
        } else {
            $results[] = array(
                "fbrf"  => "",
                "fbtp"  => "",
                "tname" => ""
            );
            while ( $row = pg_fetch_array($result) ) {
                $results[] = array(
                    "fbrf"  => $row[0],
                    "fbtp"  => $row[1],
                    "tname" => $row[2]
                );
            }
        }
        pg_close($dbHandle);
        return RestResponse::factory(
            $returnValue,
            $message,
            $results
        );
    }
}
