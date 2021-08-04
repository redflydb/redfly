<?php
namespace CCR\REDfly\Admin\Command;

// Third-party libraries
use ParagonIE\EasyDB\EasyDB;
/**
 * The command handler for releasing all approved entities so that they are
 * viewable to the public.
 */
class ReleaseApprovedRecordsHandler
{
    private $db;
    public function __construct(EasyDB $db)
    {
        $this->db = $db;
    }
    public function __invoke(ReleaseApprovedRecords $command): void
    {
        $returnValue = $this->db->exec("CALL release_approved_records(@new_current_rcs_number,
            @new_archived_rcs_number,
            @new_current_tfbss_number,
            @new_archived_tfbss_number,
            @new_current_crm_segments_number,
            @new_archived_crm_segments_number,
            @new_current_predicted_crms_number,
            @new_archived_predicted_crms_number);");
        header("Content-Type: text/plain");
        if ( $returnValue === false ) {
            print sprintf(
                "Error approving records: %s",
                $this->db->errorInfo()
            ) . PHP_EOL;
        } else {
            $outputArguments = $this->db->query("SELECT @new_current_rcs_number,
                @new_archived_rcs_number,
                @new_current_tfbss_number,
                @new_archived_tfbss_number,
                @new_current_crm_segments_number,
                @new_archived_crm_segments_number,
                @new_current_predicted_crms_number,
                @new_archived_predicted_crms_number;");
            $resultsRow = $outputArguments->fetch();
            print sprintf(
                "Success. %d new current reporter constructs, " .
                "%d new archived reporter constructs, " .
                "%d new current transcription factor binding sites, " .
                "%d new archived transcription factor binding sites, " .
                "%d new current CRM segments, " .
                "%d new archived CRM segments, " .
                "%d new current predicted CRMs, and " .
                "%d new archived predicted CRMs",
                $resultsRow["@new_current_rcs_number"],
                $resultsRow["@new_archived_rcs_number"],
                $resultsRow["@new_current_tfbss_number"],
                $resultsRow["@new_archived_tfbss_number"],
                $resultsRow["@new_current_crm_segments_number"],
                $resultsRow["@new_archived_crm_segments_number"],
                $resultsRow["@new_current_predicted_crms_number"],
                $resultsRow["@new_archived_predicted_crms_number"]
            ) . PHP_EOL;
        }
    }
}
