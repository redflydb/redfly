<?php
namespace CCR\REDfly\Admin\Command;

// Third-party libraries
use ParagonIE\EasyDB\EasyDB;
/**
 * The command handler for archiving all records marked for deletion.
 */
class ArchiveRecordsMarkedForDeletionHandler
{
    private $db;
    public function __construct(EasyDB $db)
    {
        $this->db = $db;
    }
    public function __invoke(ArchiveRecordsMarkedForDeletion $command): void
    {
        $returnValue = $this->db->exec("CALL archive_records_marked_for_deletion(@new_archived_rcs_number,
            @new_archived_tfbss_number,
            @new_archived_crm_segments_number,
            @new_archived_predicted_crms_number);");
        header("Content-Type: text/plain");
        if ( $returnValue === false ) {
            print sprintf(
                "Error deleting records: %s",
                $this->db->errorInfo()
            ) . PHP_EOL;
        } else {
            $outputArguments = $this->db->query("SELECT @new_archived_rcs_number,
                @new_archived_tfbss_number,
                @new_archived_crm_segments_number,
                @new_archived_predicted_crms_number;");
            $resultsRow = $outputArguments->fetch();
            print sprintf(
                "Success. %d new archived reporter constructs, " .
                "%d new archived transcription factor binding sites, " .
                "%d new archived CRM segments, and " .
                "%d new archived predicted CRMs",
                $resultsRow["@new_archived_rcs_number"],
                $resultsRow["@new_archived_tfbss_number"],
                $resultsRow["@new_archived_crm_segments_number"],
                $resultsRow["@new_archived_predicted_crms_number"]
            ) . PHP_EOL;
        }
    }
}
