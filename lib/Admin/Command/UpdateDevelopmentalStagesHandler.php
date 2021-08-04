<?php
namespace CCR\REDfly\Admin\Command;

// Third-party libraries
use GuzzleHttp\ClientInterface;
use ParagonIE\EasyDB\EasyDB;
/**
 * The command handler for updating the developmental stages in the database to
 * synchronize the data with the FlyBase developmental ontology.
 */
class UpdateDevelopmentalStagesHandler
{
    private $db;
    private $client;
    public function __construct(
        EasyDB $db,
        ClientInterface $client
    ) {
        $this->db = $db;
        $this->client = $client;
    }
    public function __invoke(UpdateDevelopmentalStages $command): void
    {
        $this->db->exec("DELETE FROM staging_developmental_stage_update;");
        $data = json_decode(
            $this->client->get("/developmental_stages")->getBody(),
            true
        );
        $this->db->insertMany(
            "staging_developmental_stage_update",
            array_map(
                function ($developmentalStage) {
                    return [
                        "species_short_name" => $developmentalStage["species_short_name"],
                        "identifier"         => $developmentalStage["identifier"],
                        "term"               => $developmentalStage["term"]
                    ];
                },
                $data
            )
        );
        $returnValue = $this->db->exec("CALL update_developmental_stages(
            @identifiers,
            @old_terms,
            @new_terms,
            @updated_developmental_stages_number_with_new_term,
            @deleted_developmental_stages_number,
            @new_developmental_stages_number);");
        header("Content-Type: text/plain");
        if ( $returnValue === false ) {
            print sprintf(
                "Error updating developmental stages: %s",
                $this->db->errorInfo()
            ) . PHP_EOL;
        } else {
            $outputArguments = $this->db->query("SELECT 
                @identifiers,
                @old_terms,
                @new_terms,
                @updated_developmental_stages_number_with_new_term,
                @deleted_developmental_stages_number,
                @new_developmental_stages_number;");
            $resultsRow = $outputArguments->fetch();
            $identifiers = explode(
                "\t",
                $resultsRow["@identifiers"]
            );
            $oldTerms = explode(
                "\t",
                $resultsRow["@old_terms"]
            );
            $newTerms = explode(
                "\t",
                $resultsRow["@new_terms"]
            );
            $list = "";
            for ( $index = 0; $index < count($identifiers); $index++
            ) {
                $list .= $identifiers[$index] . "\t" .
                    $oldTerms[$index] . "\t" .
                    $newTerms[$index] . PHP_EOL;
            }
            if ( $identifiers[0] !== "" ) {
                print "The developmental stage identifiers have changed their " .
                    "old terms into the new terms in the following list: " .
                    PHP_EOL;
                print $list;
            }
            print sprintf(
                "Success. %d developmental stages updated with a new term, " .
                "%d developmental stages deleted, and " .
                "%d new developmental stages",
                $resultsRow["@updated_developmental_stages_number_with_new_term"],
                $resultsRow["@deleted_developmental_stages_number"],
                $resultsRow["@new_developmental_stages_number"]
            ) . PHP_EOL;
            // Cleaning the unbuffered query for the next statement
            $resultsRow = $outputArguments->fetchAll();
        }
    }
}
