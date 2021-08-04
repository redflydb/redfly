<?php
namespace CCR\REDfly\Audit\Command;

// Third-party libraries
use Doctrine\DBAL\Query\QueryBuilder;
// REDfly libraries without any namespace
use Auth;
trait UpdateStatesCommandHandlerTrait
{
    private function executeUpdates(
        QueryBuilder $builder,
        array $updates
    ): void {
        $builder->set("state", ":state");
        $auth = new Auth();
        $auth->authenticate();
        $user = $auth->getUser();
        foreach ( $updates as $update ) {
            if ( $user->hasRole("admin") &&
                in_array($update["state"], ["approved", "deleted", "editing"]) ) {
                $builder->set("auditor_id", $auth->getUser()->userid());
                $builder->set("last_audit", "NOW()");
            } else {
                $builder->set("curator_id", $auth->getUser()->userid());
                $builder->set("last_update", "NOW()");
            }
            $builder
                ->setParameters($update)
                ->execute();
        }
    }
}
