<?php
namespace CCR\REDfly\Admin\Command;

// Third-party libraries
use ParagonIE\EasyDB\EasyDB;
/**
 * The command handler for updating the citations to clean up unused entries and
 * update author contacted status of publications cited by current entities.
 */
class UpdateCitationsHandler
{
    private $db;
    public function __construct(EasyDB $db)
    {
        $this->db = $db;
    }
    public function __invoke(UpdateCitations $command): void
    {
        $returnValue = $this->db->exec("CALL update_citations(@deleted_citations_number,
            @updated_citations_number);");
        header("Content-Type: text/plain");
        if ( $returnValue === false ) {
            print sprintf(
                "Error updating citations: %s",
                $this->db->errorInfo()
            ) . PHP_EOL;
        } else {
            $outputArguments = $this->db->query("SELECT @deleted_citations_number,
                @updated_citations_number;");
            $resultsRow = $outputArguments->fetch();
            print sprintf(
                "Success. %d citations deleted and %d citations updated",
                $resultsRow["@deleted_citations_number"],
                $resultsRow["@updated_citations_number"]
            ) . PHP_EOL;
        }
    }
}
