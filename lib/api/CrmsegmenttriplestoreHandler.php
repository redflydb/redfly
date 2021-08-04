<?php
class CrmsegmenttriplestoreHandler implements iEditable
{
    private $db = null;
    // --------------------------------------------------------------------------------
    // Factory design pattern
    // --------------------------------------------------------------------------------
    public static function factory()
    {
        return new CrmsegmenttriplestoreHandler;
    }
    private function __construct()
    {
        $this->db = DbService::factory();
    }
    // --------------------------------------------------------------------------------
    // Returns help for the "load" action
    // --------------------------------------------------------------------------------
    public function loadHelp()
    {
        $description = "Load staging data for curation.";
        $options = array("ts_id" => "The triple store identifier for staging data");

        return RestResponse::factory(
            true,
            $description,
            $options
        );
    }
    // --------------------------------------------------------------------------------
    // Loads triple store data.
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
        $tsIdProvided = false;
        foreach ( $arguments as $arg => $value ) {
            if ( ($value !== false) &&
                (($value === null) || ($value === "")) ) {
                continue;
            }
            switch ( $arg ) {
                case "ts_id":
                    $tsId = $value;
                    $tsIdProvided = true;
                    break;
                default:
                    break;
            }
        }
        if ( ! $tsIdProvided ) {
            return RestResponse::factory(
                false,
                "Triple store id not provided"
            );
        }
        $sql = <<<SQL
        SELECT ts.ts_id,
            ts.crm_segment_id,
            ts.expression,
            ts.pubmed_id,
            crms.assayed_in_species_id,
            ds_on.stage_id AS stage_on_id,
            ts.stage_on AS stage_on_identifier,
            ds_off.stage_id AS stage_off_id,
            ts.stage_off AS stage_off_identifier,
            (CASE WHEN bp.process_id IS NULL 
                THEN 0
                ELSE bp.process_id
            END) AS biological_process_id,
            (CASE WHEN bp.process_id IS NULL
                THEN ''
                ELSE ts.biological_process
            END) AS biological_process_identifier,
            ts.sex AS sex_id,
            ts.ectopic AS ectopic_id,
            ts.silencer AS enhancer_or_silencer_attribute_id
        FROM triplestore_crm_segment ts
        INNER JOIN CRMSegment crms ON crms.crm_segment_id = ts.crm_segment_id
        INNER JOIN DevelopmentalStage ds_on ON ds_on.identifier = ts.stage_on
        INNER JOIN DevelopmentalStage ds_off ON ds_off.identifier = ts.stage_off
        LEFT OUTER JOIN BiologicalProcess bp ON bp.go_id = ts.biological_process
        WHERE ts.ts_id = $tsId;
SQL;
        try {
            $queryResult = $this->db->query($sql);
            $row = $queryResult->fetch_assoc();
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
    // Returns help for the "save" action
    // --------------------------------------------------------------------------------
    public function saveHelp()
    {
        $description = "Create a triple store";
        $options = array("ts_id" => "The identifier for the triple store");

        return RestResponse::factory(
            true,
            $description,
            $options
        );
    }
    // --------------------------------------------------------------------------------
    // Saves triple store data.
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
        $data = (array) json_decode($postData["results"], true);
        $crmstsHelper = CrmSegmentTsHelper::factory();
        $tsId = $crmstsHelper->create($data);
        $data["ts_id"] = $tsId;

        return RestResponse::factory(
            true,
            null,
            array($data)
        );
    }
    // --------------------------------------------------------------------------------
    // Returns help for the "update" action
    // --------------------------------------------------------------------------------
    public function updateHelp()
    {
        $description = "Update triple stores.";
        $options = array("ts_id" => "The identifier for the triple store");
        
        return RestResponse::factory(
            true,
            $description,
            $options
        );
    }
    // --------------------------------------------------------------------------------
    // Updates triple store data.
    // --------------------------------------------------------------------------------
    public function updateAction(
        array $arguments,
        array $postData = null
    ) {
        // Requires authentication for this action
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
        $data = (array) json_decode($postData["results"], true);
        $crmstsHelper = CrmSegmentTsHelper::factory();
        $crmstsHelper->update($data);

        return RestResponse::factory(
            true,
            null,
            array($data)
        );
    }
}
