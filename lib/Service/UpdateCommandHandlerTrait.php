<?php
namespace CCR\REDfly\Service;

// Third-party libraries
use Doctrine\DBAL\Query\QueryBuilder;
trait UpdateCommandHandlerTrait
{
    private function executeUpdateQueries(
        QueryBuilder $builder,
        array $updates,
        array $whitelist
    ): void {
        foreach ( $updates as $update ) {
            if ( $this->isUpdateSanitized($update, $whitelist) ) {
                foreach ( $update as $property => $value ) {
                    if ( $property !== "id" ) {
                        $builder->set($property, ":" . $property);
                    }
                }
                $builder
                    ->setParameters($update)
                    ->execute();
                $builder->resetQueryPart("set");
            }
        }
    }
    private function isUpdateSanitized(
        array $update,
        array $whitelist
    ): bool {
        if ( empty($update["id"]) ) {
            return false;
        }

        return count(array_diff(array_keys($update), $whitelist)) === 1;
    }
}
