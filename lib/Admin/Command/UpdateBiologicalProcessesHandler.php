<?php
namespace CCR\REDfly\Admin\Command;

// Third-party libraries
use GuzzleHttp\ClientInterface;
use ParagonIE\EasyDB\EasyDB;
/**
 * The command handler for updating the biological processes in the database to
 * synchronize the data with the gene ontology (GO).
 */
class UpdateBiologicalProcessesHandler
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
    public function __invoke(UpdateBiologicalProcesses $command): void
    {
        $this->db->exec("DELETE FROM staging_biological_process_update;");
        $data = json_decode(
            $this->client->get("/biological_processes")->getBody(),
            true
        );
        $this->db->insertMany(
            "staging_biological_process_update",
            array_map(
                function ($biologicalProcess) {
                    return [
                        "go_id" => $biologicalProcess["identifier"],
                        "term"  => $biologicalProcess["term"]
                    ];
                },
                $data
            )
        );
        $returnValue = $this->db->exec("CALL update_biological_processes(
            @go_ids,
            @old_terms,
            @new_terms,
            @updated_biological_processes_number_with_new_term,
            @deleted_biological_processes_number,
            @new_biological_processes_number);");
        header("Content-Type: text/plain");
        if ( $returnValue === false ) {
            print sprintf(
                "Error updating biological processes: %s",
                $this->db->errorInfo()
            ) . PHP_EOL;
        } else {
            $outputArguments = $this->db->query("SELECT 
                @go_ids,
                @old_terms,
                @new_terms,
                @updated_biological_processes_number_with_new_term,
                @deleted_biological_processes_number,
                @new_biological_processes_number;");
            $resultsRow = $outputArguments->fetch();
            $goIds = explode(
                "\t",
                $resultsRow["@go_ids"]
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
            for ( $index = 0; $index < count($goIds); $index++
            ) {
                $list .= $goIds[$index] . "\t" .
                    $oldTerms[$index] . "\t" .
                    $newTerms[$index] . PHP_EOL;
            }
            if ( $goIds[0] !== "" ) {
                print "The biological process identifiers have changed their " .
                    "old terms into the new terms in the following list: " .
                    PHP_EOL;
                print $list;
            }
            print sprintf(
                "Success. %d biological processes updated with a new term, " .
                "%d biological processes deleted, and " .
                "%d new biological processes",
                $resultsRow["@updated_biological_processes_number_with_new_term"],
                $resultsRow["@deleted_biological_processes_number"],
                $resultsRow["@new_biological_processes_number"]
            ) . PHP_EOL;
            // Cleaning the unbuffered query for the next statement
            $resultsRow = $outputArguments->fetchAll();
        }
    }
}
