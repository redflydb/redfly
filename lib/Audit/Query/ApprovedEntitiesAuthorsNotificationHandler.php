<?php
namespace CCR\REDfly\Audit\Query;

// Standard PHP Libraries (SPL)
use DateTime;
use DateTimeZone;
use RuntimeException;
// Third-party libraries
use Latitude\QueryBuilder\{Conditions, ValueList, QueryFactory};
use ParagonIE\EasyDB\EasyDB;
use PHPMailer\PHPMailer\{Exception, PHPMailer};
// REDfly libraries with namespaces
use CCR\REDfly\Service\Message\QueryResult;
// The handler for notifying publication authors of newly approved entities
class ApprovedEntitiesAuthorsNotificationHandler
{
    private $db;
    private $factory;
    private $mailer;
    public function __construct(
        EasyDB $db,
        QueryFactory $factory,
        PHPMailer $mailer
    ) {
        $this->db = $db;
        $this->factory = $factory;
        $this->mailer = $mailer;
    }
    public function __invoke(ApprovedEntitiesAuthorsNotification $approvedEntitiesAuthorsNotification): QueryResult
    {
        // CRM segments
        $select = $this->factory->select("pubmed_id")
            ->distinct(true)
            ->from("CRMSegment")
            ->where(Conditions::make("state = ?", "approved"));
        $results = $this->db->run(
            $select->sql(),
            ...$select->params()
        );
        $crmSegmentPmids = array();
        foreach ( $results as $row ) {
            $crmSegmentPmids[] = $row["pubmed_id"];
        }
        // Reporter constructs
        $select = $this->factory->select("pubmed_id")
            ->distinct(true)
            ->from("ReporterConstruct")
            ->where(Conditions::make("state = ?", "approved"));
        $results = $this->db->run(
            $select->sql(),
            ...$select->params()
        );
        $reporterConstructPmids = array();
        foreach ( $results as $row ) {
            $reporterConstructPmids[] = $row["pubmed_id"];
        }
        $pmids = array_unique(array_merge(
            $crmSegmentPmids,
            $reporterConstructPmids
        ));
        $citationsSelect = $this->factory->select(
            "external_id",
            "title",
            "author_email",
            "author_contacted",
            "author_contact_date"
        )
            ->distinct(true)
            ->from("Citation")
            ->where(Conditions::make("external_id IN ?", ValueList::make($approvedEntitiesAuthorsNotification->pubmedIds))
            ->andWith("external_id IN ?", ValueList::make($pmids)));
        $citationResults = $this->db->run(
            $citationsSelect->sql(),
            ...$citationsSelect->params()
        );
        $authorNotifications = array();
        // The author (or a group of authors with an unique email, baby!) of each different publication (or citation)
        // will be given a notification email if:
        //  1) the email of the author(s) is existent
        //  2) the author(s) were never notified from the REDfly team before
        //  3) ALL the entries of *ANY* entity  belonging to a common PMID have the state newly set as "approved"
        //     from the old state, "approval"
        foreach ( $citationResults as $citationRow ) {
            // The email of the author(s) is non-existent
            if ( ($citationRow["author_email"] === null) ||
                ($citationRow["author_email"] === "") ) {
                $authorNotifications[] = array(
                    "externalId"          => $citationRow["external_id"],
                    "title"               => $citationRow["title"],
                    "emailAddress"        => "",
                    "contacted"           => false,
                    "contactDate"         => "",
                    "approvalEntityNames" => "",
                    "emailed"             => false
                );
                break 1;
            }
            // The author(s) were already notified/contacted
            if ( $citationRow["author_contacted"] === 1 ) {
                $dateTime = new DateTime(
                    $citationRow["author_contact_date"],
                    new DateTimeZone("UTC")
                );
                $dateTime->setTimezone(new DateTimeZone("America/New_York"));
                $authorNotifications[] = array(
                    "externalId"          => $citationRow["external_id"],
                    "title"               => $citationRow["title"],
                    "emailAddress"        => $citationRow["author_email"],
                    "contacted"           => true,
                    "contactDate"         => $dateTime->format("Y-m-d H:i:s"),
                    "approvalEntityNames" => "",
                    "emailed"             => false
                );
                break 1;
            }
            // Ensure that there are no other entries of any entity for that PMID awaiting approval.
            // A notification email should only be sent when ALL the entries of ALL the entities for a PMID have
            // been approved.
            $approvalEntityEntries = array();
            // CRM segments
            $approvalCrmSegmentsSelect = $this->factory->select("name")
                ->from("v_cis_regulatory_module_segment_audit")
                ->where(Conditions::make("pubmed_id = ?", $citationRow["external_id"])
                ->andWith("state = ?", "approval"))
                ->orderBy(array("name"));
            $approvalCrmSegmentResults = $this->db->run(
                $approvalCrmSegmentsSelect->sql(),
                ...$approvalCrmSegmentsSelect->params()
            );
            foreach ( $approvalCrmSegmentResults as $approvalCrmSegmentRow ) {
                $approvalEntityEntries[] = $approvalCrmSegmentRow["name"] . " (CRMS)";
            }
            // Reporter constructs
            $approvalReporterConstructsSelect = $this->factory->select("name")
                ->from("v_reporter_construct_audit")
                ->where(Conditions::make("pubmed_id = ?", $citationRow["external_id"])
                ->andWith("state = ?", "approval"))
                ->orderBy(array("name"));
            $approvalReporterConstructResults = $this->db->run(
                $approvalReporterConstructsSelect->sql(),
                ...$approvalReporterConstructsSelect->params()
            );
            foreach ( $approvalReporterConstructResults as $approvalReporterConstructRow ) {
                $approvalEntityEntries[] = $approvalReporterConstructRow["name"] . " (RC)";
            }
            $approvalEntityEntriesNumber = count($approvalEntityEntries);
            switch ( $approvalEntityEntriesNumber ) {
                case 0:
                    $approvalEntityNames = "";
                    break;
                case 1:
                    $approvalEntityNames = $approvalEntityEntries[0];
                    break;
                case 2:
                    $approvalEntityNames = $approvalEntityEntries[0] . " and " . $approvalEntityEntries[1];
                    break;
                default:
                    $approvalEntityNames = $approvalEntityEntries[0];
                    for ( $index = 1; $index < ($approvalEntityEntriesNumber - 1); $index++ ) {
                        $approvalEntityNames = $approvalEntityNames . ", " . $approvalEntityEntries[$index];
                    }
                    $approvalEntityNames = $approvalEntityNames . ", and " . $approvalEntityEntries[$index];
            }
            // There is, at least, an entry from an entity belonging to a PMID not having its state as "approved"
            if ( $approvalEntityNames !== "" ) {
                $authorNotifications[] = array(
                    "externalId"              => $citationRow["external_id"],
                    "title"                   => $citationRow["title"],
                    "emailAddress"            => $citationRow["author_email"],
                    "contacted"               => false,
                    "contactDate"             => "",
                    "approvalEntityNames"     => $approvalEntityNames,
                    "emailed"                 => false
                );
                break 1;
            }
            // CRM Segments
            $approvedCrmSegmentsSelect = $this->factory->select(
                "assayed_in_species_scientific_name",
                "id",
                "name",
                "coordinates",
                "anatomical_expression_terms",
                "anatomical_expression_identifiers",
                "notes",
                "sequence_from_species_scientific_name"
            )
                ->from("v_cis_regulatory_module_segment_audit")
                ->where(Conditions::make("pubmed_id = ?", $citationRow["external_id"])
                ->andWith("state = ?", "approved"))
                ->orderBy(array("name"));
            $approvedCrmSegmentResults = $this->db->run(
                $approvedCrmSegmentsSelect->sql(),
                ...$approvedCrmSegmentsSelect->params()
            );
            $approvedCrmSegments = array();
            $approvedCrmSegmentStagingData = array();
            // The CRM segment(s) data associated to such a Pubmed identifier
            foreach ( $approvedCrmSegmentResults as $approvedCrmSegmentRow ) {
                $approvedCrmSegments[] = $approvedCrmSegmentRow;
                $approvedCrmSegmentId = $approvedCrmSegmentRow["id"];
                // Such expression terms are already alphabetically sorted from the database view
                $anatomicalExpressionTerms = explode(",", $approvedCrmSegmentRow["anatomical_expression_terms"]);
                // Such expression identifiers are following their expression terms alphabetically sorted
                $anatomicalExpressionIdentifiers = explode(",", $approvedCrmSegmentRow["anatomical_expression_identifiers"]);
                // The staging data associated to such a CRM segment identifier
                for ( $index = 0; $index < count($anatomicalExpressionIdentifiers); $index++ ) {
                    $approvedCrmSegmentStagingDataSelect = $this->factory->select(
                        "crm_segment_id",
                        "stage_on_term",
                        "stage_off_term",
                        "biological_process_term",
                        "sex_term",
                        "ectopic_term",
                        "enhancer_or_silencer"
                    )
                    ->from("v_cis_regulatory_module_segment_ts_notify_author")
                    ->where(Conditions::make("crm_segment_id = ?", $approvedCrmSegmentId)
                        ->andWith("expression_identifier = ?", $anatomicalExpressionIdentifiers[$index]));
                    $approvedCrmSegmentStagingDataEntries = $this->db->run(
                        $approvedCrmSegmentStagingDataSelect->sql(),
                        ...$approvedCrmSegmentStagingDataSelect->params()
                    );
                    foreach ( $approvedCrmSegmentStagingDataEntries as $approvedCrmSegmentStagingDataEntryRow ) {
                        $approvedCrmSegmentStagingDataEntryRow["expression_term"] = $anatomicalExpressionTerms[$index];
                        $approvedCrmSegmentStagingData[] = $approvedCrmSegmentStagingDataEntryRow;
                    }
                }
            }
            // Reporter Constructs
            $approvedReporterConstructsSelect = $this->factory->select(
                "assayed_in_species_scientific_name",
                "id",
                "name",
                "coordinates",
                "anatomical_expression_identifiers",
                "anatomical_expression_terms",
                "notes",
                "sequence_from_species_scientific_name"
            )
                ->from("v_reporter_construct_audit")
                ->where(Conditions::make("pubmed_id = ?", $citationRow["external_id"])
                ->andWith("state = ?", "approved"))
                ->orderBy(array("name"));
            $approvedReporterConstructResults = $this->db->run(
                $approvedReporterConstructsSelect->sql(),
                ...$approvedReporterConstructsSelect->params()
            );
            $approvedReporterConstructs = array();
            $approvedReporterConstructStagingData = array();
            // The reporter construct(s) data associated to such a Pubmed identifier
            foreach ( $approvedReporterConstructResults as $approvedReporterConstructRow ) {
                $approvedReporterConstructs[] = $approvedReporterConstructRow;
                $approvedReporterConstructId = $approvedReporterConstructRow["id"];
                // Such expression terms are already alphabetically sorted from the database view
                $anatomicalExpressionTerms = explode(",", $approvedReporterConstructRow["anatomical_expression_terms"]);
                // Such identifiers are following their expression terms alphabetically sorted
                $anatomicalExpressionIdentifiers = explode(",", $approvedReporterConstructRow["anatomical_expression_identifiers"]);
                // The staging data associated to such a reporter construct identifier
                for ( $index = 0; $index < count($anatomicalExpressionIdentifiers); $index++ ) {
                    $approvedReporterConstructStagingDataSelect = $this->factory->select(
                        "rc_id",
                        "stage_on_term",
                        "stage_off_term",
                        "biological_process_term",
                        "sex_term",
                        "ectopic_term",
                        "enhancer_or_silencer"
                    )
                        ->from("v_reporter_construct_ts_notify_author")
                        ->where(Conditions::make("rc_id = ?", $approvedReporterConstructId)
                        ->andWith("expression_identifier = ?", $anatomicalExpressionIdentifiers[$index]));
                    $approvedReporterConstructStagingDataEntries = $this->db->run(
                        $approvedReporterConstructStagingDataSelect->sql(),
                        ...$approvedReporterConstructStagingDataSelect->params()
                    );
                    foreach ( $approvedReporterConstructStagingDataEntries as $approvedReporterConstructStagingDataEntryRow ) {
                        $approvedReporterConstructStagingDataEntryRow["expression_term"] = $anatomicalExpressionTerms[$index];
                        $approvedReporterConstructStagingData[] = $approvedReporterConstructStagingDataEntryRow;
                    }
                }
            }
            // The other PHP mailer attributes are defined in the file lib/dependencies.php
            $this->mailer->isHTML(true);
            $this->mailer->addAddress($citationRow["author_email"]);
            $this->mailer->Subject = $this->buildSubject(
                $citationRow["author_email"],
                $citationRow["external_id"]
            );
            $this->mailer->Body = $this->buildHtmlMessage(
                $citationRow["title"],
                $approvedCrmSegments,
                $approvedCrmSegmentStagingData,
                $approvedReporterConstructs,
                $approvedReporterConstructStagingData
            );
            $this->mailer->AltBody = $this->buildPlainTextMessage(
                $citationRow["title"],
                $approvedCrmSegments,
                $approvedCrmSegmentStagingData,
                $approvedReporterConstructs,
                $approvedReporterConstructStagingData
            );
            if ( $this->mailer->send() === false ) {
                throw new Exception($this->mailer->ErrorInfo);
            }
            $this->mailer->clearAddresses();
            $update = $this->factory->update("Citation", [
                "author_contacted"    => true,
                "author_contact_date" => date("Y-m-d H:i:s")])->where(Conditions::make("external_id = ?", $citationRow["external_id"]));
            $this->db->run(
                $update->sql(),
                ...$update->params()
            );
            $authorNotifications[] = array(
                "externalId"          => $citationRow["external_id"],
                "title"               => $citationRow["title"],
                "emailAddress"        => $citationRow["author_email"],
                "contacted"           => false,
                "contactDate"         => "",
                "approvalEntityNames" => "",
                "emailed"             => true
            );
        }

        return QueryResult::fromArray($authorNotifications);
    }
    private function buildSubject(
        string $email,
        int $pmid
    ): string {
        return sprintf(
            "%s: Your paper (PMID: %d) is now in REDfly!",
            $email,
            $pmid
        );
    }
    private function buildHtmlMessage(
        string $title,
        array $approvedCrmSegments,
        array $approvedCrmSegmentStagingData,
        array $approvedReporterConstructs,
        array $approvedReporterConstructStagingData
    ): string {
        $data = "";
        $entryTemplate = "cis-regulatory module segment name: %s<br>" . PHP_EOL .
            "species to which the regulatory element belongs: %s<br>" . PHP_EOL .
            "coordinates: %s<br>" . PHP_EOL .
            "species in which the assay was performed: %s<br>" . PHP_EOL .
            "anatomical expression(s): %s<br>" . PHP_EOL .
            "note(s): %s<br>" . PHP_EOL . PHP_EOL;
        $newTable = false;
        $tableBeginningTemplate = "<pre><table cellspacing=\"0\" cellpadding=\"0\" width=\"640\" align=\"left\" border=\"1\">" . PHP_EOL;
        $tableHeadersTemplate = "<tr><th>Anatomical Expression</th><th>Developmental Stage On/Stage Off</th><th>Biological Process</th><th>Sex</th><th>Ectopic</th></tr>" . PHP_EOL;
        $rowTemplate = "<tr><td align=\"center\">%s</td><td align=\"center\">%s/%s</td><td align=\"center\">%s</td><td align=\"center\">%s</td><td align=\"center\">%s</td></tr>" . PHP_EOL;
        $tableEndTemplate = "</table></pre>" . PHP_EOL;
        // CRM segments
        $approvedCrmSegmentStagingDataIndex = 0;
        foreach ( $approvedCrmSegments as $row ) {
            $data .= sprintf(
                $entryTemplate,
                str_replace("%", "%%", $row["name"]),
                str_replace("%", "%%", $row["sequence_from_species_scientific_name"]),
                str_replace("%", "%%", $row["coordinates"]),
                str_replace("%", "%%", $row["assayed_in_species_scientific_name"]),
                str_replace("%", "%%", str_replace(",", ", ", $row["anatomical_expression_terms"])),
                str_replace("%", "%%", $row["notes"])
            );
            $dataRowsNumber = 0;
            while ( $approvedCrmSegmentStagingDataIndex < count($approvedCrmSegmentStagingData) ) {
                if ( $approvedCrmSegmentStagingData[$approvedCrmSegmentStagingDataIndex]["crm_segment_id"] === $row["id"] ) {
                    if ( $newTable === false ) {
                        $data .= $tableBeginningTemplate;
                        $data .= $tableHeadersTemplate;
                        $newTable = true;
                    }
                    $data .= sprintf(
                        $rowTemplate,
                        str_replace("%", "%%", $approvedCrmSegmentStagingData[$approvedCrmSegmentStagingDataIndex]["expression_term"]),
                        str_replace("%", "%%", $approvedCrmSegmentStagingData[$approvedCrmSegmentStagingDataIndex]["stage_on_term"]),
                        str_replace("%", "%%", $approvedCrmSegmentStagingData[$approvedCrmSegmentStagingDataIndex]["stage_off_term"]),
                        str_replace("%", "%%", $approvedCrmSegmentStagingData[$approvedCrmSegmentStagingDataIndex]["biological_process_term"]),
                        str_replace("%", "%%", $approvedCrmSegmentStagingData[$approvedCrmSegmentStagingDataIndex]["sex_term"]),
                        str_replace("%", "%%", $approvedCrmSegmentStagingData[$approvedCrmSegmentStagingDataIndex]["ectopic_term"]),
                        str_replace("%", "%%", $approvedCrmSegmentStagingData[$approvedCrmSegmentStagingDataIndex]["enhancer_or_silencer"])
                    );
                    $approvedCrmSegmentStagingDataIndex++;
                    $dataRowsNumber++;
                } else {
                    break;
                }
            }
            if ( $newTable === true ) {
                $data .= $tableEndTemplate;
                $newTable = false;
            }
            for ( $index = 0; $index <= ($dataRowsNumber + 1); $index++ ) {
                $data .= "<br>";
            }
            $data .= "[ ] Looks good!<br><br>" . PHP_EOL . PHP_EOL .
                "COMMENTS:<br><br>" . PHP_EOL . PHP_EOL .
                "----------------------------<br>" . PHP_EOL;
        }
        // Reporter constructs
        $entryTemplate = "reporter construct name: %s<br>" . PHP_EOL .
            "species to which the regulatory element belongs: %s<br>" . PHP_EOL .
            "coordinates: %s<br>" . PHP_EOL .
            "species in which the assay was performed: %s<br>" . PHP_EOL .
            "anatomical expression(s): %s<br>" . PHP_EOL .
            "note(s): %s<br>" . PHP_EOL . PHP_EOL;
        $approvedReporterConstructStagingDataIndex = 0;
        foreach ( $approvedReporterConstructs as $row ) {
            $data .= sprintf(
                $entryTemplate,
                str_replace("%", "%%", $row["name"]),
                str_replace("%", "%%", $row["sequence_from_species_scientific_name"]),
                str_replace("%", "%%", $row["coordinates"]),
                str_replace("%", "%%", $row["assayed_in_species_scientific_name"]),
                str_replace("%", "%%", str_replace(",", ", ", $row["anatomical_expression_terms"])),
                str_replace("%", "%%", $row["notes"])
            );
            $dataRowsNumber = 0;
            while ( $approvedReporterConstructStagingDataIndex < count($approvedReporterConstructStagingData) ) {
                if ( $approvedReporterConstructStagingData[$approvedReporterConstructStagingDataIndex]["rc_id"] === $row["id"] ) {
                    if ( $newTable === false ) {
                        $data .= $tableBeginningTemplate;
                        $data .= $tableHeadersTemplate;
                        $newTable = true;
                    }
                    $data .= sprintf(
                        $rowTemplate,
                        str_replace("%", "%%", $approvedReporterConstructStagingData[$approvedReporterConstructStagingDataIndex]["expression_term"]),
                        str_replace("%", "%%", $approvedReporterConstructStagingData[$approvedReporterConstructStagingDataIndex]["stage_on_term"]),
                        str_replace("%", "%%", $approvedReporterConstructStagingData[$approvedReporterConstructStagingDataIndex]["stage_off_term"]),
                        str_replace("%", "%%", $approvedReporterConstructStagingData[$approvedReporterConstructStagingDataIndex]["biological_process_term"]),
                        str_replace("%", "%%", $approvedReporterConstructStagingData[$approvedReporterConstructStagingDataIndex]["sex_term"]),
                        str_replace("%", "%%", $approvedReporterConstructStagingData[$approvedReporterConstructStagingDataIndex]["ectopic_term"]),
                        str_replace("%", "%%", $approvedReporterConstructStagingData[$approvedReporterConstructStagingDataIndex]["enhancer_or_silencer"])
                    );
                    $approvedReporterConstructStagingDataIndex++;
                    $dataRowsNumber++;
                } else {
                    break;
                }
            }
            if ( $newTable === true ) {
                $data .= $tableEndTemplate;
                $newTable = false;
            }
            for ( $index = 0; $index <= ($dataRowsNumber + 1); $index++ ) {
                $data .= "<br>";
            }
            $data .= "[ ] Looks good!<br><br>" . PHP_EOL . PHP_EOL .
                "COMMENTS:<br><br>" . PHP_EOL . PHP_EOL .
                "----------------------------<br>" . PHP_EOL;
        }
        $message = sprintf(
            file_get_contents(__DIR__ . "/templates/author-html-email-template.txt") . $data,
            $title,
            "https://bsky.app/profile/redfly-database.bsky.social",
            "https://bsky.app/profile/redfly-database.bsky.social",
            "https://www.surveymonkey.com/r/L83SV5B",
            "https://www.surveymonkey.com/r/L83SV5B"
        );
        if ( $message === false ) {
            throw new RuntimeException("Malformed template text.");
        }

        return $message;
    }
    private function buildPlainTextMessage(
        string $title,
        array $approvedCrmSegments,
        array $approvedCrmSegmentStagingData,
        array $approvedReporterConstructs,
        array $approvedReporterConstructStagingData
    ): string {
        $data = "";
        $entryTemplate = "cis-regulatory module segment name: %s" . PHP_EOL .
            "species to which the regulatory element belongs: %s<br>" . PHP_EOL .
            "coordinates: %s" . PHP_EOL .
            "species in which the assay was performed: %s<br>" . PHP_EOL .
            "anatomical expression(s): %s" . PHP_EOL .
            "note(s): %s" . PHP_EOL . PHP_EOL;
        $newTable = false;
        $tableHeadersTemplate = "Anatomical Expression\tDevelopmental Stage On/Stage Off\tBiological Process\tSex\tEctopic" . PHP_EOL;
        $rowTemplate = "%s\t%s/%s\t%s\t%s\t%s" . PHP_EOL;
        // CRM segments
        $approvedCrmSegmentStagingDataIndex = 0;
        foreach ( $approvedCrmSegments as $row ) {
            $data .= sprintf(
                $entryTemplate,
                str_replace("%", "%%", $row["name"]),
                str_replace("%", "%%", $row["sequence_from_species_scientific_name"]),
                str_replace("%", "%%", $row["coordinates"]),
                str_replace("%", "%%", $row["assayed_in_species_scientific_name"]),
                str_replace("%", "%%", str_replace(",", ", ", $row["anatomical_expression_terms"])),
                str_replace("%", "%%", $row["notes"])
            );
            while ( $approvedCrmSegmentStagingDataIndex < count($approvedCrmSegmentStagingData) ) {
                if ( $approvedCrmSegmentStagingData[$approvedCrmSegmentStagingDataIndex]["crm_segment_id"] === $row["id"] ) {
                    if ( $newTable === false ) {
                        $data .= $tableHeadersTemplate;
                        $newTable = true;
                    }
                    $data .= sprintf(
                        $rowTemplate,
                        str_replace("%", "%%", $approvedCrmSegmentStagingData[$approvedCrmSegmentStagingDataIndex]["expression_term"]),
                        str_replace("%", "%%", $approvedCrmSegmentStagingData[$approvedCrmSegmentStagingDataIndex]["stage_on_term"]),
                        str_replace("%", "%%", $approvedCrmSegmentStagingData[$approvedCrmSegmentStagingDataIndex]["stage_off_term"]),
                        str_replace("%", "%%", $approvedCrmSegmentStagingData[$approvedCrmSegmentStagingDataIndex]["biological_process_term"]),
                        str_replace("%", "%%", $approvedCrmSegmentStagingData[$approvedCrmSegmentStagingDataIndex]["sex_term"]),
                        str_replace("%", "%%", $approvedCrmSegmentStagingData[$approvedCrmSegmentStagingDataIndex]["ectopic_term"]),
                        str_replace("%", "%%", $approvedCrmSegmentStagingData[$approvedCrmSegmentStagingDataIndex]["enhancer_or_silencer"])
                    );
                    $approvedCrmSegmentStagingDataIndex++;
                } else {
                    break;
                }
            }
            if ( $newTable === true ) {
                $newTable = false;
            }
            $data .= PHP_EOL;
            $data .= "[ ] Looks good!" . PHP_EOL . PHP_EOL .
                "COMMENTS:" . PHP_EOL . PHP_EOL .
                "----------------------------" . PHP_EOL;
        }
        // Reporter constructs
        $entryTemplate = "reporter construct name: %s" . PHP_EOL .
            "species to which the regulatory element belongs: %s<br>" . PHP_EOL .
            "coordinates: %s" . PHP_EOL .
            "species in which the assay was performed: %s<br>" . PHP_EOL .
            "anatomical expression(s): %s" . PHP_EOL .
            "note(s): %s" . PHP_EOL . PHP_EOL;
        $approvedReporterConstructStagingDataIndex = 0;
        foreach ( $approvedReporterConstructs as $row ) {
            $data .= sprintf(
                $entryTemplate,
                str_replace("%", "%%", $row["name"]),
                str_replace("%", "%%", $row["sequence_from_species_scientific_name"]),
                str_replace("%", "%%", $row["coordinates"]),
                str_replace("%", "%%", $row["assayed_in_species_scientific_name"]),
                str_replace("%", "%%", str_replace(",", ", ", $row["anatomical_expression_terms"])),
                str_replace("%", "%%", $row["notes"])
            );
            while ( $approvedReporterConstructStagingDataIndex < count($approvedReporterConstructStagingData) ) {
                if ( $approvedReporterConstructStagingData[$approvedReporterConstructStagingDataIndex]["rc_id"] === $row["id"] ) {
                    if ( $newTable === false ) {
                        $data .= $tableHeadersTemplate;
                        $newTable = true;
                    }
                    $data .= sprintf(
                        $rowTemplate,
                        str_replace("%", "%%", $approvedReporterConstructStagingData[$approvedReporterConstructStagingDataIndex]["expression_term"]),
                        str_replace("%", "%%", $approvedReporterConstructStagingData[$approvedReporterConstructStagingDataIndex]["stage_on_term"]),
                        str_replace("%", "%%", $approvedReporterConstructStagingData[$approvedReporterConstructStagingDataIndex]["stage_off_term"]),
                        str_replace("%", "%%", $approvedReporterConstructStagingData[$approvedReporterConstructStagingDataIndex]["biological_process_term"]),
                        str_replace("%", "%%", $approvedReporterConstructStagingData[$approvedReporterConstructStagingDataIndex]["sex_term"]),
                        str_replace("%", "%%", $approvedReporterConstructStagingData[$approvedReporterConstructStagingDataIndex]["ectopic_term"]),
                        str_replace("%", "%%", $approvedReporterConstructStagingData[$approvedReporterConstructStagingDataIndex]["enhancer_or_silencer"])
                    );
                    $approvedReporterConstructStagingDataIndex++;
                } else {
                    break;
                }
            }
            if ( $newTable === true ) {
                $newTable = false;
            }
            $data .= PHP_EOL;
            $data .= "[ ] Looks good!" . PHP_EOL . PHP_EOL .
                "COMMENTS:" . PHP_EOL . PHP_EOL .
                "----------------------------" . PHP_EOL;
        }
        $message = sprintf(
            file_get_contents(__DIR__ . "/templates/author-plain-text-email-template.txt") . $data,
            $title,
            "https://bsky.app/profile/redfly-database.bsky.social",
            "https://www.surveymonkey.com/r/L83SV5B"
        );
        if ( $message === false ) {
            throw new RuntimeException("Malformed template text.");
        }

        return $message;
    }
}
