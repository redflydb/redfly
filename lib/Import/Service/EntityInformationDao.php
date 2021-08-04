<?php
namespace CCR\REDfly\Import\Service;

// Third-party libraries
use Latitude\QueryBuilder\{Conditions, QueryFactory};
use ParagonIE\EasyDB\EasyDB;
/**
 * A data access object that focuses on any information entity
 */
class EntityInformationDao
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
    private function getInformation(
        string $table,
        string $idColumn,
        string $value,
        string $nameColumn
    ): string {
        $select = $this->queryFactory->select($nameColumn)
            ->from($table)
            ->where(Conditions::make("LOWER(" . $idColumn . ") = ?", strtolower($value)));

        return $this->easyDB->cell($select->sql(), ...$select->params());
    }
    public function getSpeciesShortNameByScientificName(string $scientificName): string
    {
        return $this->getInformation(
            "Species",
            "scientific_name",
            $scientificName,
            "short_name"
        );
    }
    public function getCurrentGenomeAssemblyReleaseVersionBySpeciesId(int $speciesId): string
    {
        $select = $this->queryFactory->select("release_version")
            ->from("GenomeAssembly")
            ->where(Conditions::make("species_id = ?", $speciesId)
                ->andWith("is_deprecated = 0"));

        return $this->easyDB->cell($select->sql(), ...$select->params());
    }
    public function getPubMedIdByRcId(int $rcId): string
    {
        return $this->getInformation(
            "ReporterConstruct",
            "rc_id",
            (string)$rcId,
            "pubmed_id"
        );
    }
    public function getPubMedIdByPredictedCrmId(int $predictedCrmId): string
    {
        return $this->getInformation(
            "PredictedCRM",
            "predicted_crm_id",
            (string)$predictedCrmId,
            "pubmed_id"
        );
    }
    public function getAnatomicalExpressionIdentifierByAnatomicalExpressionTerm(
        string $speciesScientificName,
        string $term
    ): string {
        if ( $speciesScientificName === "" ) {
            $select = $this->queryFactory->select("identifier")
                ->from("ExpressionTerm")
                ->where(Conditions::make("term = ?", $term));
        } else {
            $select = $this->queryFactory->select("e.identifier")
                ->from("Species AS s")
                ->innerJoin("ExpressionTerm AS e", Conditions::make("s.species_id = e.species_id"))
                ->where(Conditions::make("s.scientific_name = ?", $speciesScientificName)
                            ->andWith("e.term = ?", $term));
        }

        return $this->easyDB->cell($select->sql(), ...$select->params());
    }
    public function getStateByRcId(int $rcId): string
    {
        return $this->getInformation(
            "ReporterConstruct",
            "rc_id",
            (string)$rcId,
            "state"
        );
    }
    public function getStateByPredictedCrmId(int $predictedCrmId): string
    {
        return $this->getInformation(
            "PredictedCRM",
            "predicted_crm_id",
            (string)$predictedCrmId,
            "state"
        );
    }
    public function getEntityIdByRcId(int $rcId): int
    {
        return (int)$this->getInformation(
            "ReporterConstruct",
            "rc_id",
            (string)$rcId,
            "entity_id"
        );
    }
    public function getEntityIdByPredictedCrmId(int $predictedCrmId): int
    {
        return (int)$this->getInformation(
            "PredictedCRM",
            "predicted_crm_id",
            (string)$predictedCrmId,
            "entity_id"
        );
    }
    public function getVersionByRcId(int $rcId): string
    {
        return $this->getInformation(
            "ReporterConstruct",
            "rc_id",
            (string)$rcId,
            "version"
        );
    }
    public function getVersionByPredictedCrmId(int $predictedCrmId): string
    {
        return $this->getInformation(
            "PredictedCRM",
            "predicted_crm_id",
            (string)$predictedCrmId,
            "version"
        );
    }
}
