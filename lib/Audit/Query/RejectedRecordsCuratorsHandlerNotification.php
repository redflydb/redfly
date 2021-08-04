<?php
namespace CCR\REDfly\Audit\Query;

// Standard PHP Libraries (SPL)
use RuntimeException;
// Third-party libraries
use Latitude\QueryBuilder\{Conditions, ValueList, QueryFactory};
use ParagonIE\EasyDB\EasyDB;
use PHPMailer\PHPMailer\PHPMailer;
// REDfly libraries with namespaces
use CCR\REDfly\Service\Message\QueryResult;
/**
 * The handler for notifying curators of rejected records.
 */
class RejectedRecordsCuratorsNotificationHandler
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
    public function __invoke(RejectedRecordsCuratorsNotification $rejectedRecordsCuratorsNotification): QueryResult
    {
        $select = $this->factory->select("first_name", "last_name")
            ->from("Users")
            ->where(Conditions::make("username = ?", $rejectedRecordsCuratorsNotification->getAuditorUsername()));
        $results = $this->db->run($select->sql(), ...$select->params());
        $auditorFullName = $results[0]["first_name"] . " " . $results[0]["last_name"];
        $this->mailer->Subject = "REDfly Team: Notification of submission rejection(s)";
        $this->mailer->Body = $this->buildMessage(
            $auditorFullName,
            $rejectedRecordsCuratorsNotification->getRejectedRecordsList(),
            $rejectedRecordsCuratorsNotification->getReasons()
        );
        $select = $this->factory->select("username", "email")
            ->from("Users")
            ->where(Conditions::make("user_id IN ?", ValueList::make($rejectedRecordsCuratorsNotification->getCuratorIds())));
        $results = $this->db->run($select->sql(), ...$select->params());
        $curatorRejections = array();
        foreach ( $results as $row ) {
            if ( $row["email"] === "" ) {
                throw new RuntimeException("No email address from the username: " . $row["username"]);
            }
            $this->mailer->addAddress($row["email"]);
            $this->mailer->send();
            $this->mailer->clearAddresses();
        }

        return QueryResult::fromArray($curatorRejections);
    }
    private function buildMessage(
        string $auditorFullName,
        string $rejectedRecordsList,
        string $reasons
    ): string {
        $template = file_get_contents(__DIR__ . "/templates/curator-email-template.txt");
        $message = sprintf(
            $template,
            $auditorFullName,
            $rejectedRecordsList,
            $reasons,
            $_SERVER["SERVER_NAME"]
        );
        if ( $message === false ) {
            throw new RuntimeException("Malformed template text");
        }

        return wordwrap($message);
    }
}
