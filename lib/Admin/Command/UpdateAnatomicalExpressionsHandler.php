<?php
namespace CCR\REDfly\Admin\Command;

// Third-party libraries
use GuzzleHttp\ClientInterface;
use ParagonIE\EasyDB\EasyDB;
/**
 * The command handler for updating the anatomical expressions in the database
 * to synchronize the data with the latest anatomy ontolog(ies).
 */
class UpdateAnatomicalExpressionsHandler
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
    public function __invoke(UpdateAnatomicalExpressions $command): void
    {
        $this->db->exec("DELETE FROM staging_expression_update;");
        $data = json_decode(
            $this->client->get("/anatomical_expressions")->getBody(),
            true
        );
        $this->db->insertMany(
            "staging_expression_update",
            array_map(
                function ($expression) {
                    return [
                        "species_short_name" => $expression["species_short_name"],
                        "identifier"         => $expression["identifier"],
                        "term"               => $expression["term"]
                    ];
                },
                $data
            )
        );
        $returnValue = $this->db->exec("CALL update_anatomical_expressions(
            @identifiers,
            @old_terms,
            @new_terms,
            @updated_anatomical_expressions_number,
            @deleted_anatomical_expressions_number,
            @new_anatomical_expressions_number);");
        header("Content-Type: text/plain");
        if ( $returnValue === false ) {
            print sprintf(
                "Error updating anatomical expressions: %s",
                $this->db->errorInfo()
            ) . PHP_EOL;
        } else {
            $outputArguments = $this->db->query("SELECT 
                @identifiers,
                @old_terms,
                @new_terms,
                @updated_anatomical_expressions_number,
                @deleted_anatomical_expressions_number,
                @new_anatomical_expressions_number;");
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
                print "The anatomical expression identifiers have changed their " .
                    "old terms into the new terms in the following list: " .
                    PHP_EOL;
                print $list;
            }
            print sprintf(
                "Success. %d anatomical expressions updated, " .
                "%d anatomical expressions deleted, and " .
                "%d new anatomical expressions",
                $resultsRow["@updated_anatomical_expressions_number"],
                $resultsRow["@deleted_anatomical_expressions_number"],
                $resultsRow["@new_anatomical_expressions_number"]
            ) . PHP_EOL;
            // Cleaning the unbuffered query for the next statement
            $resultsRow = $outputArguments->fetchAll();
        }
    }
}
