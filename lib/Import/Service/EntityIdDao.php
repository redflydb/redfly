<?php
namespace CCR\REDfly\Import\Service;

// Third-party libraries
use Latitude\QueryBuilder\{Conditions, Expression, QueryFactory, ValueList};
use ParagonIE\EasyDB\EasyDB;
/**
 * A data access object that focuses on fetching entity identities.
 */
class EntityIdDao
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
    private function getId(
        string $table,
        string $nameColumn,
        string $value,
        string $idColumn
    ): int {
        $select = $this->queryFactory->select($idColumn)
            ->from($table)
            ->where(Conditions::make("LOWER(" . $nameColumn . ") = ?", strtolower($value)));

        return $this->easyDB->cell($select->sql(), ...$select->params());
    }
    public function getSpeciesIdByScientificName(string $scientificName): int
    {
        return $this->getId(
            "Species",
            "scientific_name",
            $scientificName,
            "species_id"
        );
    }
    public function getUserIdByUsername(string $username): int
    {
        return $this->getId(
            "Users",
            "username",
            $username,
            "user_id"
        );
    }
    public function getGeneIdByName(
        string $speciesScientificName,
        string $geneName
    ): int {
        $select = $this->queryFactory->select("g.gene_id")
            ->from("Species AS s")
            ->innerJoin("Gene AS g", Conditions::make("s.species_id = g.species_id"))
            ->where(Conditions::make("s.scientific_name = ?", $speciesScientificName)
                        ->andWith("g.name = ?", $geneName));

        return $this->easyDB->cell($select->sql(), ...$select->params());
    }
    public function getChromosomeIdByName(
        string $speciesScientificName,
        string $chromosomeName
    ): int {
        $select = $this->queryFactory->select("c.chromosome_id")
            ->from("Species AS s")
            ->innerJoin("Chromosome AS c", Conditions::make("s.species_id = c.species_id"))
            ->where(Conditions::make("s.scientific_name = ?", $speciesScientificName)
                        ->andWith("c.name = ?", $chromosomeName));

        return $this->easyDB->cell($select->sql(), ...$select->params());
    }
    public function getRcIdByNameAndStates(
        string $rcName,
        array $statesList
    ): int {
        $select = $this->queryFactory->select(Expression::make("MAX(version) AS max_version"))
            ->from("ReporterConstruct")
            ->where(Conditions::make("name = ?", $rcName)
                        ->andWith("state IN ?", ValueList::make($statesList)));
        $maximumVersion = $this->easyDB->cell($select->sql(), ...$select->params("max_version"));
        $select = $this->queryFactory->select("rc_id")
            ->from("ReporterConstruct")
            ->where(Conditions::make("name = ?", $rcName)
                        ->andWith("state IN ?", ValueList::make($statesList))
                        ->andWith("version = ?", $maximumVersion));

        return $this->easyDB->cell($select->sql(), ...$select->params("rc_id"));
    }
    public function getPredictedCrmIdByNameAndStates(
        string $predictedCrmName,
        array $statesList
    ): int {
        $select = $this->queryFactory->select(Expression::make("MAX(version) AS max_version"))
            ->from("PredictedCRM")
            ->where(Conditions::make("name = ?", $predictedCrmName)
                        ->andWith("state IN ?", ValueList::make($statesList)));
        $maximumVersion = $this->easyDB->cell($select->sql(), ...$select->params("max_version"));
        $select = $this->queryFactory->select("predicted_crm_id")
            ->from("PredictedCRM")
            ->where(Conditions::make("name = ?", $predictedCrmName)
                        ->andWith("state IN ?", ValueList::make($statesList))
                        ->andWith("version = ?", $maximumVersion));

        return $this->easyDB->cell($select->sql(), ...$select->params("predicted_crm_id"));
    }
    public function getEvidenceIdByName(string $evidence): int
    {
        return $this->getId(
            "EvidenceTerm",
            "term",
            $evidence,
            "evidence_id"
        );
    }
    public function getEvidenceSubtypeIdByName(string $evidenceSubtype): int
    {
        return $this->getId(
            "EvidenceSubtypeTerm",
            "term",
            $evidenceSubtype,
            "evidence_subtype_id"
        );
    }
    public function getSequenceSourceIdByName(string $sequenceSource): int
    {
        return $this->getId(
            "SequenceSourceTerm",
            "term",
            $sequenceSource,
            "source_id"
        );
    }
}
