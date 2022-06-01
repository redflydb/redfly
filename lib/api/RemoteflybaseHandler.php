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

        // Query FBrf from Flybase remote database
        $sql = <<<SQL
        SELECT p.uniquename
        FROM pub AS p
        JOIN pub_dbxref AS pdbx
        ON p.pub_id = pdbx.pub_id AND
            p.is_obsolete = 'f' AND
            pdbx.is_current = true
        JOIN dbxref AS dbx
        ON dbx.accession = '$pmid' AND
            pdbx.dbxref_id = dbx.dbxref_id
        JOIN db
        ON db.name = 'pubmed' AND
            dbx.db_id = db.db_id;
        SQL;

        if ( ($result = pg_query($dbHandle, $sql)) === false ) {
            $message = "Error querying flybase for FBtp: \"" .
                pg_result_error($result) . "\"";
            $returnValue = false;
        } elseif ( pg_num_rows($result) === 0 ) {
            $message = "No FBtp results found in Flybase for PMID " . $pmid;
            $returnValue = false;
        } else {
            $fbrf = pg_fetch_array($result)[0];
        }
        pg_close($dbHandle);

        // Query FBtp From Flybase API 
        $xml = simplexml_load_string(file_get_contents("https://api.flybase.org/api/v1.0/chadoxml/" . $fbrf));
        $results[] = array(
            "fbrf"  => "",
            "fbtp"  => "",
            "tname" => ""
        );
        foreach ($xml->pub->feature_pub as $item) {
            if ($item->feature_id->feature->type_id->cvterm->name == 'transgenic_transposable_element') {
                $results[] = array(
                    "fbrf"  => $fbrf,
                    "fbtp"  => $item->feature_id->feature->uniquename->__toString(),
                    "tname" => $item->feature_id->feature->feature_synonym->synonym_id->synonym->name->__toString()
                );
            }
        }

        return RestResponse::factory(
            $returnValue,
            $message,
            $results
        );
    }
}
