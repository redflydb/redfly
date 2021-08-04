<?php
namespace CCR\REDfly\Audit\Command;

require(dirname(__FILE__) .  "/../../Auth.php");
// Third-party libraries
use Psr\Http\Message\ServerRequestInterface;
// REDfly libraries without any namespace
use Auth;
// REDfly libraries with namespaces
use CCR\REDfly\Service\Exception\InvalidMessageException;
trait UpdateStatesCommandTrait
{
    public static function fromRequest(ServerRequestInterface $request): self
    {
        return new self(json_decode(
            $request->getBody(),
            true
        ));
    }
    private $updates;
    public function __construct(array $updates)
    {
        if ( $this->validate($updates) === false ) {
            throw new InvalidMessageException(self::class);
        }
        $this->updates = $updates;
    }
    public function getUpdates(): array
    {
        return $this->updates;
    }
    private function validate(array $updates): bool
    {
        foreach ( $updates as $update ) {
            if ( $this->isValidUpdate($update) === false ) {
                return false;
            }
        }

        return true;
    }
    private function isValidUpdate(array $update): bool
    {
        $auth = new Auth();
        $auth->authenticate();
        $user = $auth->getUser();
        if ( isset($update["id"]) && is_int($update["id"]) &&
             isset($update["state"]) && is_string($update["state"]) ) {
            // Only admininistrators can set the state of an entity as
            // "approved" (after pushing the "Approve Selected" button)
            // "editing" (after pushing the "Reject Selected" button)
            //  according to the PI criteria
            if ( $user->hasRole("admin") &&
                (($update["state"] === "approved") ||
                 ($update["state"] === "editing")) ) {
                return true;
            } else {
                // "approval" (after pushing the "Submit Selected for Approval" button for curators) and
                // "deleted" (after pushing the "Reject Selected" button for administrators or
                //            after pushing the "Deleted Selected" button for both user kinds)
                //  according to the PI criteria
                if ( ($update["state"] === "approval") ||
                     ($update["state"] === "deleted") ) {
                    return true;
                } else {
                    return false;
                }
            }
        }

        return false;
    }
    public function jsonSerialize(): array
    {
        return [];
    }
}
