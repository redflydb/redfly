<?php
namespace CCR\REDfly\Datasource\Blat\Service;

// Third-party libraries
use Latitude\QueryBuilder\{Conditions, QueryFactory};
use ParagonIE\EasyDB\EasyDB;
/**
 * A data access object that focuses on fetching chromosome identities.
 */
class ChromosomeIdDao
{
    private $easyDB;
    private $queryFactory;
    public function __construct(
        EasyDB $easyDB,
        QueryFactory $queryFactory
    ) {
        $this->easyDB = $easyDB;
        $this->queryFactory = $queryFactory;
    }
    public function getId(
        string $speciesShortName,
        string $chromosomeName
    ): int {
        $select = $this->queryFactory->select("Chromosome.chromosome_id")
            ->from("Species")
            ->join("Chromosome", Conditions::make("Species.species_id = Chromosome.species_id"))
            ->where(Conditions::make("Species.short_name = ? AND Chromosome.name = ?", $speciesShortName, $chromosomeName));
        
        return $this->easyDB->cell($select->sql(), ...$select->params("Chromosome.chromosome_id"));
    }
}
