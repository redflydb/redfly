<?php
namespace CCR\REDfly\Audit\Query;

// REDfly libraries with namespaces
use CCR\REDfly\Service\Message\QueryInterface;
class CrmSegmentSearch implements QueryInterface
{
    use SearchQueryTrait;
}
