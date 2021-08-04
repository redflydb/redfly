<?php
// Note: it handles only the Drosophila melanogaster species at the moment.
// It is hoped that it will include other species as the Tribolium castaneum
// in the future as long as there are any .OWL file of developmental ontologies
// available to be deposited in ./go/termlookupserver/assets/.
class DevelopmentontologyHandler
{
    // --------------------------------------------------------------------------------
    // Factory design pattern
    // --------------------------------------------------------------------------------
    public static function factory()
    {
        return new DevelopmentontologyHandler;
    }
    // --------------------------------------------------------------------------------
    // Return help for the "list" action
    // --------------------------------------------------------------------------------
    public function listHelp()
    {
        $description = "List the developmental stage and all its descendants " .
            "from the development ontology depending on the species.";
        $options = array(
            "identifier"         => "Return the developmental stage(s) matching " .
                                    "the identifier as well as their descendants",
            "species_short_name" => "Choose the ontology regarding to the species " .
                                    "short name"
        );

        return RestResponse::factory(
            true,
            $description,
            $options
        );
    }
    // --------------------------------------------------------------------------------
    // List the developmental stage and all its descendants (if any) only from the
    // Drosophila melanogaster species at the moment
    // --------------------------------------------------------------------------------
    public function listAction(
        array $arguments,
        array $postData = null
    ) {
        $identifier = str_replace(
            "fbdv",
            "FBdv",
            strtolower($arguments["identifier"])
        );
        $guzzle = new \GuzzleHttp\Client(["base_uri" => $GLOBALS["options"]->termlookup->url]);
        $data = $guzzle->get("developmental_stages/descendants/" . $identifier)->getBody();
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
