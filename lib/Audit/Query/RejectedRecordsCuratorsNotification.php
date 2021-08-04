<?php
namespace CCR\REDfly\Audit\Query;

// Third-party libraries
use Psr\Http\Message\ServerRequestInterface;
// REDfly libraries with namespaces
use CCR\REDfly\Service\Message\QueryInterface;
/**
 * The DTO for notifying curators of rejected records.
 */
class RejectedRecordsCuratorsNotification implements QueryInterface
{
    public static function fromRequest(ServerRequestInterface $request): self
    {
        $parsedBody = $request->getParsedBody();

        return new self(
            $request->getAttribute("username"),
            $parsedBody["rejected_records_array"],
            $parsedBody["reasons"],
            $parsedBody["curator_ids_array"]
        );
    }
    private $auditorUsername;
    private $rejectedRecordsList;
    private $reasons;
    private $curatorIds;
    public function __construct(
        string $auditorUsername,
        array $rejectedRecords,
        string $reasons,
        array $curatorIds
    ) {
        $this->auditorUsername = $auditorUsername;
        $rejectedRecordMaxLength = 0;
        foreach ( $rejectedRecords as $rejectedRecord ) {
            if ( $rejectedRecordMaxLength < strlen($rejectedRecord) ) {
                $rejectedRecordMaxLength = strlen($rejectedRecord);
            }
        }
        // Resting the length of the comma character
        $rejectedRecordMaxLength = $rejectedRecordMaxLength - 1;
        $rejectedRecordsList = "";
        foreach ( $rejectedRecords as $rejectedRecord ) {
            if ( $rejectedRecordsList === "" ) {
                $rejectedRecordsList =  "PMID" . str_repeat(" ", $rejectedRecordMaxLength - 1) . "Name";
            }
            $rejectRecordFields = explode(",", $rejectedRecord);
            $spacesNumber = $rejectedRecordMaxLength - strlen($rejectRecordFields[0]);
            $rejectedRecordsList = $rejectedRecordsList . PHP_EOL . $rejectRecordFields[0] .
                str_repeat(" ", $spacesNumber) . $rejectRecordFields[1];
        }
        $this->rejectedRecordsList = $rejectedRecordsList;
        $this->reasons = $reasons;
        $this->curatorIds = $curatorIds;
    }
    public function getAuditorUsername()
    {
        return $this->auditorUsername;
    }
    public function getRejectedRecordsList()
    {
        return $this->rejectedRecordsList;
    }
    public function getReasons()
    {
        return $this->reasons;
    }
    public function getCuratorIds()
    {
        return $this->curatorIds;
    }
    public function jsonSerialize(): array
    {
        return [
            "auditor_username"      => $this->auditorUsername,
            "rejected_records_list" => $this->rejectedRecordsList,
            "reasons"               => $this->reasons,
            "curator_ids"           => $this->curatorIds
        ];
    }
}
