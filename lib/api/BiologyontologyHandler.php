<?php
class BiologyontologyHandler
{
    // --------------------------------------------------------------------------------
    // Factory design pattern
    // --------------------------------------------------------------------------------
    public static function factory()
    {
        return new BiologyontologyHandler;
    }
    // --------------------------------------------------------------------------------
    // Return help for the "list" action
    // --------------------------------------------------------------------------------
    public function listHelp()
    {
        $description = "List the biological process and all its descendants " .
            "from the biology ontology.";
        $options = array(
            "identifier" => "Return the biological process(es) matching " .
                            "the identifier as well as their descendants"
        );

        return RestResponse::factory(
            true,
            $description,
            $options
        );
    }
    // --------------------------------------------------------------------------------
    // List the biological process and all its descendants (if any)
    // --------------------------------------------------------------------------------
    public function listAction(
        array $arguments,
        array $postData = null
    ) {
        $identifier = str_replace(
            "go",
            "GO",
            strtolower($arguments["identifier"])
        );
        $guzzle = new \GuzzleHttp\Client(["base_uri" => $GLOBALS["options"]->termlookup->url]);
        $data = $guzzle->get("biological_processes/descendants/" . $identifier)->getBody();
        $result = json_decode(
            $data,
            true
        );
         // We also want to include the parent
        $result[] = ["id" => $identifier];

        return RestResponse::factory(
            true,
            null,
            $result
        );
    }
}
