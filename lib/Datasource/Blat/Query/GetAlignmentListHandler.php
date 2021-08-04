<?php
namespace CCR\REDfly\Datasource\Blat\Query;

// Standard PHP Libraries (SPL)
use Exception;
// REDfly libraries with namespaces
use CCR\REDfly\Datasource\Blat\Query\GetAlignmentList;
use CCR\REDfly\Datasource\Blat\Service\AlignmentMatcher;
use CCR\REDfly\Datasource\Blat\Service\ChromosomeIdDao;
use CCR\REDfly\Service\Message\QueryResult;
class GetAlignmentListHandler
{
    private $alignmentMatcher;
    private $chromosomeIdDao;
    public function __construct(
        AlignmentMatcher $alignmentMatcher,
        ChromosomeIdDao $chromosomeIdDao
    ) {
        $this->alignmentMatcher = $alignmentMatcher;
        $this->chromosomeIdDao = $chromosomeIdDao;
    }
    public function __invoke(GetAlignmentList $getAlignmentList): QueryResult
    {
        if ( ($getAlignmentList->getSpeciesShortName() !== "") &&
            ($getAlignmentList->getSequence() !== "") ) {
            $alignmentList = $this->alignmentMatcher->get(
                $getAlignmentList->getSpeciesShortName(),
                $getAlignmentList->getSequence()
            );
            $coordinates = array();
            foreach ( $alignmentList as $rawAlignment ) {
                $alignment = array(
                    "chromosome"    => $rawAlignment->chromosomeName,
                    "chromosome_id" => null,
                    "end"           => $rawAlignment->endCoordinate,
                    "start"         => $rawAlignment->startCoordinate
                );
                $chromosomeId = $this->chromosomeIdDao->getId(
                    $getAlignmentList->getSpeciesShortName(),
                    $rawAlignment->chromosomeName
                );
                if ( $chromosomeId === 0 ) {
                    throw new Exception("No chromosome name found matching BLAT result \"" .
                        $rawAlignment->chromosomeName . "\"");
                } else {
                    $alignment["chromosome_id"] = $chromosomeId;
                    $coordinates[] = $alignment;
                }
            }
        }

        return QueryResult::fromArray($coordinates);
    }
}
