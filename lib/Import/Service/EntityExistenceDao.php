<?php
namespace CCR\REDfly\Import\Service;

// Third-party libraries
use Latitude\QueryBuilder\{Conditions, Expression, QueryFactory, ValueList};
use ParagonIE\EasyDB\EasyDB;
// REDfly libraries with namespaces
use CCR\REDfly\Service\External\EntrezDataSource;
/**
 * A data access object that focuses on whether an entity exists in their
 * respective repositories.
 */
class EntityExistenceDao
{
    private $easyDB;
    private $queryFactory;
    private $entrezDataSource;
    private $crmSegmentErrorMargin;
    private $predictedCrmErrorMargin;
    private $rcErrorMargin;
    private $tfbsErrorMargin;
    public function __construct(
        EasyDB $easyDB,
        QueryFactory $queryFactory,
        EntrezDataSource $entrezDataSource,
        int $crmSegmentErrorMargin,
        int $predictedCrmErrorMargin,
        int $rcErrorMargin,
        int $tfbsErrorMargin
    ) {
        $this->easyDB = $easyDB;
        $this->queryFactory = $queryFactory;
        $this->entrezDataSource = $entrezDataSource;
        $this->crmSegmentErrorMargin = $crmSegmentErrorMargin;
        $this->predictedCrmErrorMargin = $predictedCrmErrorMargin;
        $this->rcErrorMargin = $rcErrorMargin;
        $this->tfbsErrorMargin = $tfbsErrorMargin;
    }
    private function valueExistsInTable(
        string $table,
        string $column,
        string $value
    ): bool {
        $select = $this->queryFactory->select($column)
            ->from($table)
            ->where(Conditions::make("LOWER(". $column . ") = ?", strtolower($value)));
        $result = $this->easyDB->cell($select->sql(), ...$select->params());

        return ( strcasecmp($result, $value) === 0 );
    }
    public function hasSpecies(string $scientificName): bool
    {
        return $this->valueExistsInTable(
            "Species",
            "scientific_name",
            $scientificName
        );
    }
    public function hasPmid(int $pmid): bool
    {
        $citation = $this->entrezDataSource->query($pmid);

        return ( $citation !== null );
    }
    public function hasGene(
        string $speciesScientificName,
        string $geneName
    ): bool {
        $select = $this->queryFactory->select("g.name")
            ->from("Species AS s")
            ->innerJoin("Gene AS g", Conditions::make("s.species_id = g.species_id"))
            ->where(Conditions::make("s.scientific_name = ?", $speciesScientificName)
                        ->andWith("g.name = ?", $geneName));
        $result = $this->easyDB->cell($select->sql(), ...$select->params());
        
        return ( strcasecmp($result, $geneName) === 0 );
    }
    public function hasRcNameAndState(
        string $rcName,
        array $statesList
    ): bool {
        $select = $this->queryFactory->select(Expression::make("MAX(version) AS max_version"))
            ->from("ReporterConstruct")
            ->where(Conditions::make("LOWER(name) = LOWER(?)", $rcName)
                        ->andWith("state IN ?", ValueList::make($statesList)));
        $maxVersion = $this->easyDB->cell($select->sql(), ...$select->params("max_version"));

        return ( -1 < $maxVersion );
    }
    public function hasPredictedCrmNameAndState(
        string $predictedCrmName,
        array $statesList
    ): bool {
        $select = $this->queryFactory->select(Expression::make("MAX(version) AS max_version"))
            ->from("PredictedCRM")
            ->where(Conditions::make("LOWER(name) = LOWER(?)", $predictedCrmName)
                        ->andWith("state IN ?", ValueList::make($statesList)));
        $maxVersion = $this->easyDB->cell($select->sql(), ...$select->params("max_version"));

        return ( -1 < $maxVersion );
    }
    public function hasRcEntityId(int $rcId): bool
    {
        $select = $this->queryFactory->select("entity_id")
            ->from("ReporterConstruct")
            ->where(Conditions::make("rc_id = ?", $rcId));
        $entityId = $this->easyDB->cell($select->sql(), ...$select->params("entity_id"));

        return ($entityId !== null);
    }
    public function hasPredictedCrmEntityId(int $predictedCrmId): bool
    {
        $select = $this->queryFactory->select("entity_id")
            ->from("PredictedCRM")
            ->where(Conditions::make("predicted_crm_id = ?", $predictedCrmId));
        $entityId = $this->easyDB->cell($select->sql(), ...$select->params("entity_id"));

        return ($entityId !== null);
    }
    public function hasEvidenceTerm(string $evidenceTerm): bool
    {
        return $this->valueExistsInTable(
            "EvidenceTerm",
            "term",
            $evidenceTerm
        );
    }
    public function hasEvidenceSubtypeTerm(string $evidenceSubtypeTerm): bool
    {
        return $this->valueExistsInTable(
            "EvidenceSubtypeTerm",
            "term",
            $evidenceSubtypeTerm
        );
    }
    public function hasSequenceSourceTerm(string $sequenceSourceTerm): bool
    {
        return $this->valueExistsInTable(
            "SequenceSourceTerm",
            "term",
            $sequenceSourceTerm
        );
    }
    /**
     * Check for any duplicate reporter constructs based on the provided coordinates.
     * Duplicate coordinates are determined with an error margin; this means that
     * if both ends of the coordinates fall within the -/+ range of the provided
     * coordinates, that set of coordinates is considered a duplicate.
     * Only approval/approved/current/deleted/editing records should be checked;
     * archived records should be ignored.
     */
    public function hasDuplicateRcCoordinates(
        string $sequenceFromSpeciesScientificName,
        string $chromosomeName,
        array $statesList,
        int $start,
        int $end
    ): bool {
        $select = $this->queryFactory->select(Expression::make("COUNT(rc.rc_id)"))
            ->from("ReporterConstruct AS rc")
            ->innerJoin("Species AS s", Conditions::make("rc.sequence_from_species_id = s.species_id"))
            ->innerJoin("Chromosome AS c", Conditions::make("rc.sequence_from_species_id = c.species_id AND rc.chromosome_id = c.chromosome_id"))
            ->where(Conditions::make("s.scientific_name = ?", $sequenceFromSpeciesScientificName)
                        ->andWith("c.name = ?", $chromosomeName)
                        ->andWith("rc.state IN ?", ValueList::make($statesList))
                        ->andWith("rc.current_start BETWEEN ? AND ?", $start - $this->rcErrorMargin, $start + $this->rcErrorMargin)
                        ->andWith("rc.current_end BETWEEN ? AND ?", $end - $this->rcErrorMargin, $end + $this->rcErrorMargin));
        $count = $this->easyDB->cell($select->sql(), ...$select->params());

        return ( 0 < $count );
    }
    /**
     * Check for any duplicate CRM segments based on the provided coordinates.
     * Duplicate coordinates are determined with an error margin; this means that
     * if both ends of the coordinates fall within the -/+ range of the provided
     * coordinates, that set of coordinates is considered a duplicate.
     * Only approval/approved/current/deleted/editing records should be checked;
     * archived records should be ignored.
     */
    public function hasDuplicateCrmSegmentCoordinates(
        string $sequenceFromSpeciesScientificName,
        string $chromosomeName,
        array $statesList,
        int $start,
        int $end
    ): bool {
        $select = $this->queryFactory->select(Expression::make("COUNT(crms.crm_segment_id)"))
            ->from("CRMSegment AS crms")
            ->innerJoin("Species AS s", Conditions::make("crms.sequence_from_species_id = s.species_id"))
            ->innerJoin("Chromosome AS c", Conditions::make("crms.sequence_from_species_id = c.species_id AND crms.chromosome_id = c.chromosome_id"))
            ->where(Conditions::make("s.scientific_name = ?", $sequenceFromSpeciesScientificName)
                        ->andWith("c.name = ?", $chromosomeName)
                        ->andWith("crms.state IN ?", ValueList::make($statesList))
                        ->andWith("crms.current_start BETWEEN ? AND ?", $start - $this->crmSegmentErrorMargin, $start + $this->crmSegmentErrorMargin)
                        ->andWith("crms.current_end BETWEEN ? AND ?", $end - $this->crmSegmentErrorMargin, $end + $this->crmSegmentErrorMargin));
        $count = $this->easyDB->cell($select->sql(), ...$select->params());

        return ( 0 < $count );
    }
    /**
     * Check for any duplicate predicted CRMs based on the provided coordinates.
     * Duplicate coordinates are determined with an error margin; this means that
     * if both ends of the coordinates fall within the -/+ range of the provided
     * coordinates, that set of coordinates is considered a duplicate.
     * Only approval/approved/current/deleted/editing records should be checked;
     * archived records should be ignored.
     */
    public function hasDuplicatePredictedCrmCoordinates(
        string $sequenceFromSpeciesScientificName,
        string $chromosomeName,
        array $statesList,
        int $start,
        int $end
    ): bool {
        $select = $this->queryFactory->select(Expression::make("COUNT(pcrm.predicted_crm_id)"))
            ->from("PredictedCRM AS pcrm")
            ->innerJoin("Species AS s", Conditions::make("pcrm.sequence_from_species_id = s.species_id"))
            ->innerJoin("Chromosome AS c", Conditions::make("pcrm.sequence_from_species_id = c.species_id AND pcrm.chromosome_id = c.chromosome_id"))
            ->where(Conditions::make("s.scientific_name = ?", $sequenceFromSpeciesScientificName)
                        ->andWith("c.name = ?", $chromosomeName)
                        ->andWith("pcrm.state IN ?", ValueList::make($statesList))
                        ->andWith("pcrm.current_start BETWEEN ? AND ?", $start - $this->predictedCrmErrorMargin, $start + $this->predictedCrmErrorMargin)
                        ->andWith("pcrm.current_end BETWEEN ? AND ?", $end - $this->predictedCrmErrorMargin, $end + $this->predictedCrmErrorMargin));
        $count = $this->easyDB->cell($select->sql(), ...$select->params());

        return ( 0 < $count );
    }
    /**
     * Check for any duplicate TFBSs based on the provided coordinates.
     * Duplicate coordinates are determined with an error margin; this means that
     * if both ends of the coordinates fall within the -/+ range of the provided
     * coordinates, that set of coordinates is considered a duplicate.
     * Only approval/approved/current/deleted/editing records should be checked;
     * archived records should be ignored.
     */
    public function hasDuplicateTfbsCoordinates(
        string $sequenceFromSpeciesScientificName,
        string $chromosomeName,
        array $statesList,
        int $start,
        int $end
    ): bool {
        $select = $this->queryFactory->select(Expression::make("COUNT(tfbs.tfbs_id)"))
            ->from("BindingSite AS tfbs")
            ->innerJoin("Species AS s", Conditions::make("tfbs.sequence_from_species_id = s.species_id"))
            ->innerJoin("Chromosome AS c", Conditions::make("tfbs.sequence_from_species_id = c.species_id AND tfbs.chromosome_id = c.chromosome_id"))
            ->where(Conditions::make("s.scientific_name = ?", $sequenceFromSpeciesScientificName)
                        ->andWith("c.name = ?", $chromosomeName)
                        ->andWith("tfbs.state IN ?", ValueList::make($statesList))
                        ->andWith("tfbs.current_start BETWEEN ? AND ?", $start - $this->tfbsErrorMargin, $start + $this->tfbsErrorMargin)
                        ->andWith("tfbs.current_end BETWEEN ? AND ?", $end - $this->tfbsErrorMargin, $end + $this->tfbsErrorMargin));
        $count = $this->easyDB->cell($select->sql(), ...$select->params());

        return ( 0 < $count );
    }
    public function hasAnatomicalExpressionIdentifier(
        string $speciesScientificName,
        string $identifier
    ): bool {
        if ( $speciesScientificName === "" ) {
            $select = $this->queryFactory->select("identifier")
                ->from("ExpressionTerm")
                ->where(Conditions::make("identifier = ?", $identifier));
        } else {
            $select = $this->queryFactory->select("e.identifier")
                ->from("Species AS s")
                ->innerJoin("ExpressionTerm AS e", Conditions::make("s.species_id = e.species_id"))
                ->where(Conditions::make("s.scientific_name = ?", $speciesScientificName)
                            ->andWith("e.identifier = ?", $identifier));
        }
        $result = $this->easyDB->cell($select->sql(), ...$select->params());
        
        return ( strcasecmp($result, $identifier) === 0 );
    }
    public function hasAnatomicalExpressionTerm(
        string $speciesScientificName,
        string $term
    ): bool {
        if ( $speciesScientificName === "" ) {
            $select = $this->queryFactory->select("term")
                ->from("ExpressionTerm")
                ->where(Conditions::make("term = ?", $term));
        } else {
            $select = $this->queryFactory->select("e.term")
                ->from("Species AS s")
                ->innerJoin("ExpressionTerm AS e", Conditions::make("s.species_id = e.species_id"))
                ->where(Conditions::make("s.scientific_name = ?", $speciesScientificName)
                            ->andWith("e.term = ?", $term));
        }
        $result = $this->easyDB->cell($select->sql(), ...$select->params());
        
        return ( strcasecmp($result, $term) === 0 );
    }
    public function hasDevelopmentalStageIdentifier(
        string $speciesScientificName,
        string $identifier
    ): bool {
        if ( $speciesScientificName === "" ) {
            $select = $this->queryFactory->select("identifier")
                ->from("DevelopmentalStage")
                ->where(Conditions::make("identifier = ?", $identifier));
        } else {
            $select = $this->queryFactory->select("d.identifier")
                ->from("Species AS s")
                ->innerJoin("DevelopmentalStage AS d", Conditions::make("s.species_id = d.species_id"))
                ->where(Conditions::make("s.scientific_name = ?", $speciesScientificName)
                            ->andWith("d.identifier = ?", $identifier));
        }
        $result = $this->easyDB->cell($select->sql(), ...$select->params());
        
        return ( strcasecmp($result, $identifier) === 0 );
    }
    public function hasBiologicalProcessIdentifier(string $identifier): bool
    {
        return $this->valueExistsInTable(
            "BiologicalProcess",
            "go_id",
            $identifier
        );
    }
    public function hasRcTripleStore(
        int $rcId,
        string $anatomicalExpressionIdentifier,
        string $developmentalStageOnIdentifier,
        string $developmentalStageOffIdentifier,
        string $biologicalProcessIdentifier
    ): bool {
        $select = $this->queryFactory->select(Expression::make("COUNT(ts_id)"))
            ->from("triplestore_rc")
            ->where(Conditions::make("rc_id = ?", $rcId)
                        ->andWith("expression = ?", $anatomicalExpressionIdentifier)
                        ->andWith("stage_on = ?", $developmentalStageOnIdentifier)
                        ->andWith("stage_off = ?", $developmentalStageOffIdentifier)
                        ->andWith("biological_process = ?", $biologicalProcessIdentifier));
        $count = $this->easyDB->cell($select->sql(), ...$select->params());

        return ( 0 < $count );
    }
    public function hasPredictedCrmTripleStore(
        int $predictedCrmId,
        string $anatomicalExpressionIdentifier,
        string $developmentalStageOnIdentifier,
        string $developmentalStageOffIdentifier,
        string $biologicalProcessIdentifier
    ): bool {
        $select = $this->queryFactory->select(Expression::make("COUNT(ts_id)"))
            ->from("triplestore_predicted_crm")
            ->where(Conditions::make("predicted_crm_id = ?", $predictedCrmId)
                        ->andWith("expression = ?", $anatomicalExpressionIdentifier)
                        ->andWith("stage_on = ?", $developmentalStageOnIdentifier)
                        ->andWith("stage_off = ?", $developmentalStageOffIdentifier)
                        ->andWith("biological_process = ?", $biologicalProcessIdentifier));
        $count = $this->easyDB->cell($select->sql(), ...$select->params());

        return ( 0 < $count );
    }
}
