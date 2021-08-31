<?php
namespace CCR\REDfly\Import\Command;

// Standard PHP Libraries (SPL)
use Exception;
use RuntimeException;
// Third-party libraries
use GuzzleHttp\ClientInterface;
use League\Csv\Reader;
// REDfly libraries without any namespace
use Auth;
use RcHelper;
use RcEtHelper;
use ReporterconstructHandler;
use RestHandlerHelper;
use RcTsHelper;
// REDfly libraries with namespaces
use CCR\REDfly\Import\Service\{EntityExistenceDao, EntityIdDao, EntityInformationDao};
use CCR\REDfly\Service\External\BlatDataSource;
/**
 * The command handler for importing data.
 */
class ImportDataHandler
{
    private $blatDataSource;
    private $clientInterface;
    private $entityExistenceDao;
    private $entityIdDao;
    private $entityInformationDao;
    public function __construct(
        BlatDataSource $blatDataSource,
        ClientInterface $clientInterface,
        EntityExistenceDao $entityExistenceDao,
        EntityIdDao $entityIdDao,
        EntityInformationDao $entityInformationDao
    ) {
        $this->blatDataSource = $blatDataSource;
        $this->clientInterface = $clientInterface;
        $this->entityExistenceDao = $entityExistenceDao;
        $this->entityIdDao = $entityIdDao;
        $this->entityInformationDao = $entityInformationDao;
    }
    public function __invoke(ImportData $importData): void
    {
        if ( ($importData->getAttributeTsvFileUri() !== "")  &&
            ($importData->getFastaFileUri() !== "") &&
            ($importData->getAnatomicalExpressionTsvFileUri() !== "") ) {
            $this->importAttributesDataWithOrWithoutExpressionsData(
                $importData->getUsername(),
                $importData->getPassword(),
                $importData->getEntityType(),
                $importData->getAttributeTsvFileUri(),
                $importData->getFastaFileUri(),
                $importData->getAnatomicalExpressionTsvFileUri()
            );
        } else {
            if ( ($importData->getAttributeTsvFileUri() === "")  &&
                ($importData->getFastaFileUri() === "") &&
                ($importData->getAnatomicalExpressionTsvFileUri() !== "") &&
                ($importData->getUpdateAnatomicalExpressions()) ) {
                $this->updateRcAnatomicalExpressionsData(
                    $importData->getUsername(),
                    $importData->getPassword(),
                    $importData->getEntityType(),
                    $importData->getAnatomicalExpressionTsvFileUri()
                );
            }
        }
    }
    private function importAttributesDataWithOrWithoutExpressionsData(
        string $username,
        string $password,
        string $entityType,
        string $attributeTsvFileUri,
        string $fastaFileUri,
        string $anatomicalExpressionTsvFileUri
    ): void {
        $curatorId = $this->entityIdDao->getUserIdByUsername($username);
        $attributesReader = Reader::createFromPath($attributeTsvFileUri)
            ->setDelimiter("\t")
            ->setHeaderOffset(0)
            ->skipEmptyRecords();
        // Such a new array created by the "iterator_to_array" function begins by 1
        $attributeArray = $this->sanitizeInputData(iterator_to_array($attributesReader->getRecords()));
        $alignments = $this->extractAlignments(
            $this->entityInformationDao->getSpeciesShortNameByScientificName($attributeArray[1]["sequence_from_species"]),
            $fastaFileUri
        );
        if ( $anatomicalExpressionTsvFileUri !== "" ) {
            $anatomicalExpressionsReader = Reader::createFromPath($anatomicalExpressionTsvFileUri)
                ->setDelimiter("\t")
                ->setHeaderOffset(0)
                ->skipEmptyRecords();
            // Such a new array created by the "iterator_to_array" function begins by 1
            $anatomicalExpressionArray = iterator_to_array($anatomicalExpressionsReader->getRecords());
            $anatomicalExpressionRowsNumber = count($anatomicalExpressionArray);
            if ( 0 < $anatomicalExpressionRowsNumber ) {
                $anatomicalExpressionArray = $this->sanitizeInputData($anatomicalExpressionArray);
                // Such both new arrays created by the "array_column" function begin by 0
                if ( $entityType === "rc" ) {
                    $anatomicalExpressionGeneNames = array_column(
                        $anatomicalExpressionArray,
                        "gene_name"
                    );
                    $anatomicalExpressionArbitraryNames = array_column(
                        $anatomicalExpressionArray,
                        "arbitrary_name"
                    );
                    $entityNamesNumber = count($anatomicalExpressionArbitraryNames);
                    $entityNames = array();
                    for ( $rowIndex = 0; $rowIndex < $entityNamesNumber; $rowIndex++ ) {
                        $entityNames[] = $anatomicalExpressionGeneNames[$rowIndex] . "_" . $anatomicalExpressionArbitraryNames[$rowIndex];
                    }
                } else {
                    $anatomicalExpressionNames = array_column(
                        $anatomicalExpressionArray,
                        "name"
                    );
                    $entityNamesNumber = count($anatomicalExpressionNames);
                    $entityNames = array();
                    for ( $rowIndex = 0; $rowIndex < $entityNamesNumber; $rowIndex++ ) {
                        $entityNames[] = $anatomicalExpressionNames[$rowIndex];
                    }
                }
            }
        }
        foreach ( $attributeArray as $attributeRow ) {
            $alignment = $alignments[$attributeRow["coordinates"]];
            switch ( $entityType ) {
                // Reporter Construct
                case "rc":
                    $sequenceFromSpeciesScientificName = $attributeRow["sequence_from_species"];
                    $sequenceFromSpeciesId = $this->entityIdDao->getSpeciesIdByScientificName($sequenceFromSpeciesScientificName);
                    $entityName = trim($attributeRow["gene_name"]) . "_" . trim($attributeRow["arbitrary_name"]);
                    $newEntityAnatomicalExpressions = [];
                    if ( 0 < $anatomicalExpressionRowsNumber ) {
                        // Searching all the anatomical expression(s) associated to the new entity saved
                        // from the anatomical expression data
                        foreach ( array_keys($entityNames, $entityName) as $key => $value ) {
                            $anatomicalExpressionRow = $anatomicalExpressionArray[$value + 1];
                            $assayedInSpeciesScientificName = $anatomicalExpressionRow["assayed_in_species"];
                            $newEntityAnatomicalExpression = [];
                            // The first capturing group, FBbt:[0-9]{8}, refers to the Drosophila melanogaster species.
                            // The second capturing group, TGMA:[0-9]{7}, refers to both Aedes aegypti and Anopheles gambiae species.
                            // The third capturing group, TrOn:[0-9]{7}, refers to the Tribolium castaneum species.
                            if ( preg_match(
                                "/^(FBbt:[0-9]{8})|(TGMA:[0-9]{7})|(TrOn:[0-9]{7})$/",
                                $anatomicalExpressionRow["expression"]
                            ) !== 1 ) {
                                $newEntityAnatomicalExpression["expression"] = $this->entityInformationDao->getAnatomicalExpressionIdentifierByAnatomicalExpressionTerm(
                                    "",
                                    $anatomicalExpressionRow["expression"]
                                );
                            } else {
                                $newEntityAnatomicalExpression["expression"] = $anatomicalExpressionRow["expression"];
                            }
                            if ( $anatomicalExpressionRow["pmid"] === "" ) {
                                $newEntityAnatomicalExpression["pmid"] = $attributeRow["pmid"];
                            } else {
                                $newEntityAnatomicalExpression["pmid"] = $anatomicalExpressionRow["pmid"];
                            }
                            $newEntityAnatomicalExpression["stage_on"] = $anatomicalExpressionRow["stage_on"];
                            $newEntityAnatomicalExpression["stage_off"] = $anatomicalExpressionRow["stage_off"];
                            $newEntityAnatomicalExpression["biological_process"] = $anatomicalExpressionRow["biological_process"];
                            if ( $anatomicalExpressionRow["sex"] === "" ) {
                                $newEntityAnatomicalExpression["sex"] = "both";
                            } else {
                                $newEntityAnatomicalExpression["sex"] = $anatomicalExpressionRow["sex"];
                            }
                            if ( $anatomicalExpressionRow["ectopic"] === "" ) {
                                $newEntityAnatomicalExpression["ectopic"] = "0";
                            } else {
                                $newEntityAnatomicalExpression["ectopic"] = $anatomicalExpressionRow["ectopic"];
                            }
                            if ( $anatomicalExpressionRow["enhancer/silencer"] === "" ) {
                                $newEntityAnatomicalExpression["enhancer/silencer"] = "enhancer";
                            } else {
                                $newEntityAnatomicalExpression["enhancer/silencer"] = $anatomicalExpressionRow["enhancer/silencer"];
                            }
                            $newEntityAnatomicalExpressions[] = $newEntityAnatomicalExpression;
                        }
                    } else {
                        $assayedInSpeciesScientificName = $attributeRow["sequence_from_species"];
                    }
                    $assayedInSpeciesId = $this->entityIdDao->getSpeciesIdByScientificName($assayedInSpeciesScientificName);
                    $geneId = $this->entityIdDao->getGeneIdByName(
                        $sequenceFromSpeciesScientificName,
                        $attributeRow["gene_name"]
                    );
                    $chromosomeId = $this->entityIdDao->getChromosomeIdByName(
                        $sequenceFromSpeciesScientificName,
                        $alignment->chromosomeName
                    );
                    $jsonEncodedData = json_encode(
                        [
                            "action"                                  => "save",
                            "assayed_in_species_id"                   => $assayedInSpeciesId,
                            "author_email"                            => $attributeRow["author_email"],
                            "chromosome_id"                           => $chromosomeId,
                            "curator_id"                              => $curatorId,
                            "current_genome_assembly_release_version" => $this->entityInformationDao->getCurrentGenomeAssemblyReleaseVersionBySpeciesId($sequenceFromSpeciesId),
                            "end"                                     => $alignment->endCoordinate,
                            "evidence_id"                             => $this->entityIdDao->getEvidenceIdByName($attributeRow["evidence"]),
                            "fbtp"                                    => $attributeRow["transgenic_construct"],
                            "figure_labels"                           => $attributeRow["figure_label"],
                            "gene_id"                                 => $geneId,
                            "gene_name"                               => $attributeRow["gene_name"],
                            "is_negative"                             => $attributeRow["is_negative"],
                            "name"                                    => $entityName,
                            "notes"                                   => $attributeRow["notes"],
                            "pubmed_id"                               => $attributeRow["pmid"],
                            "sequence"                                => $alignment->sequence,
                            "sequence_from_species_id"                => $sequenceFromSpeciesId,
                            "sequence_source_id"                      => $this->entityIdDao->getSequenceSourceIdByName($attributeRow["sequence_source"]),
                            "start"                                   => $alignment->startCoordinate + 1
                        ],
                        JSON_INVALID_UTF8_SUBSTITUTE
                    );
                    if ( json_last_error() !== JSON_ERROR_NONE ) {
                        throw new RuntimeException(json_last_error_msg());
                    }
                    $response = json_decode(
                        $this->clientInterface->request(
                            "POST",
                            "api/rest/json/reporterconstruct/save",
                            [
                                "form_params" => [
                                    "results" =>  $jsonEncodedData
                                ],
                                "auth" => [
                                    $username,
                                    $password
                                ]
                            ]
                        )->getBody(),
                        true
                    );
                    if ( $response["success"] === 0 ) {
                        throw new RuntimeException($response["message"]);
                    }
                    $newEntityAnatomicalExpressionsNumber = count($newEntityAnatomicalExpressions);
                    if ( 0 < $newEntityAnatomicalExpressionsNumber ) {
                        $newRcId = $response["results"][0]["rc_id"];
                        for ( $newEntityAnatomicalExpressionIndex = 0; $newEntityAnatomicalExpressionIndex < $newEntityAnatomicalExpressionsNumber; $newEntityAnatomicalExpressionIndex++ ) {
                            $anatomicalExpressionRow = $newEntityAnatomicalExpressions[$newEntityAnatomicalExpressionIndex];
                            // The first capturing group, FBbt:[0-9]{8}, refers to the Drosophila melanogaster species.
                            // The second capturing group, TGMA:[0-9]{7}, refers to both Aedes aegypti and Anopheles gambiae species.
                            // The third capturing group, TrOn:[0-9]{7}, refers to the Tribolium castaneum species.
                            if ( preg_match(
                                "/^(FBbt:[0-9]{8})|(TGMA:[0-9]{7})|(TrOn:[0-9]{7})$/",
                                $anatomicalExpressionRow["expression"]
                            ) !== 1 ) {
                                $anatomicalExpressionIdentifier = $this->entityInformationDao->getAnatomicalExpressionIdentifierByAnatomicalExpressionTerm(
                                    "",
                                    $anatomicalExpressionRow["expression"]
                                );
                            } else {
                                $anatomicalExpressionIdentifier = $anatomicalExpressionRow["expression"];
                            }
                            if ( $anatomicalExpressionRow["pmid"] === "" ) {
                                $anatomicalExpressionPmid = $attributeRow["pmid"];
                            } else {
                                $anatomicalExpressionPmid = $anatomicalExpressionRow["pmid"];
                            }
                            if ( $anatomicalExpressionRow["sex"] === "" ) {
                                $anatomicalExpressionSex = "both";
                            } else {
                                $anatomicalExpressionSex = $anatomicalExpressionRow["sex"];
                            }
                            if ( $anatomicalExpressionRow["ectopic"] === "" ) {
                                $anatomicalExpressionEctopic = "0";
                            } else {
                                $anatomicalExpressionEctopic = $anatomicalExpressionRow["ectopic"];
                            }
                            if ( $anatomicalExpressionRow["enhancer/silencer"] === "" ) {
                                $anatomicalExpressionEnhancerOrSilencer = "enhancer";
                            } else {
                                $anatomicalExpressionEnhancerOrSilencer = $anatomicalExpressionRow["enhancer/silencer"];
                            }
                            $jsonEncodedData = json_encode(
                                [
                                    "action"                            => "save",
                                    "anatomical_expression_identifier"  => $anatomicalExpressionIdentifier,
                                    "biological_process_identifier"     => $anatomicalExpressionRow["biological_process"],
                                    "curator_id"                        => $curatorId,
                                    "ectopic_id"                        => $anatomicalExpressionEctopic,
                                    "enhancer_or_silencer_attribute_id" => $anatomicalExpressionEnhancerOrSilencer,
                                    "pubmed_id"                         => $anatomicalExpressionPmid,
                                    "rc_id"                             => $newRcId,
                                    "sex_id"                            => $anatomicalExpressionSex,
                                    "stage_off_identifier"              => $anatomicalExpressionRow["stage_off"],
                                    "stage_on_identifier"               => $anatomicalExpressionRow["stage_on"]
                                ],
                                JSON_INVALID_UTF8_SUBSTITUTE
                            );
                            if ( json_last_error() !== JSON_ERROR_NONE ) {
                                throw new RuntimeException(json_last_error_msg());
                            }
                            $response = json_decode(
                                $this->clientInterface->request(
                                    "POST",
                                    "api/rest/json/reporterconstructtriplestore/save",
                                    [
                                        "form_params" => [
                                            "results" => $jsonEncodedData
                                        ],
                                        "auth" => [
                                            $username,
                                            $password
                                        ]
                                    ]
                                )->getBody(),
                                true
                            );
                            if ( $response["success"] === 0 ) {
                                throw new RuntimeException($response["message"]);
                            }
                        }
                    }
                    break;
                // Predicted CRM
                case "predicted_crm":
                    $sequenceFromSpeciesScientificName = $attributeRow["sequence_from_species"];
                    $sequenceFromSpeciesId = $this->entityIdDao->getSpeciesIdByScientificName($sequenceFromSpeciesScientificName);
                    $entityName = trim($attributeRow["name"]);
                    $newEntityAnatomicalExpressions = [];
                    if ( 0 < $anatomicalExpressionRowsNumber ) {
                        // Searching all the anatomical expression(s) associated to the new entity saved
                        // from the anatomical expression data
                        foreach ( array_keys($entityNames, $entityName) as $key => $value ) {
                            $anatomicalExpressionRow = $anatomicalExpressionArray[$value + 1];
                            $newEntityAnatomicalExpression = [];
                            // The first capturing group, FBbt:[0-9]{8}, refers to the Drosophila melanogaster species.
                            // The second capturing group, TGMA:[0-9]{7}, refers to both Aedes aegypti and Anopheles gambiae species.
                            // The third capturing group, TrOn:[0-9]{7}, refers to the Tribolium castaneum species.
                            if ( preg_match(
                                "/^(FBbt:[0-9]{8})|(TGMA:[0-9]{7})|(TrOn:[0-9]{7})$/",
                                $anatomicalExpressionRow["expression"]
                            ) !== 1 ) {
                                    $newEntityAnatomicalExpression["expression"] = $this->entityInformationDao->getAnatomicalExpressionIdentifierByAnatomicalExpressionTerm(
                                        "",
                                        $anatomicalExpressionRow["expression"]
                                    );
                            } else {
                                $newEntityAnatomicalExpression["expression"] = $anatomicalExpressionRow["expression"];
                            }
                            if ( $anatomicalExpressionRow["pmid"] === "" ) {
                                $newEntityAnatomicalExpression["pmid"] = $attributeRow["pmid"];
                            } else {
                                $newEntityAnatomicalExpression["pmid"] = $anatomicalExpressionRow["pmid"];
                            }
                            $newEntityAnatomicalExpression["stage_on"] = $anatomicalExpressionRow["stage_on"];
                            $newEntityAnatomicalExpression["stage_off"] = $anatomicalExpressionRow["stage_off"];
                            $newEntityAnatomicalExpression["biological_process"] = $anatomicalExpressionRow["biological_process"];
                            if ( $anatomicalExpressionRow["sex"] === "" ) {
                                $newEntityAnatomicalExpression["sex"] = "both";
                            } else {
                                $newEntityAnatomicalExpression["sex"] = $anatomicalExpressionRow["sex"];
                            }
                            if ( $anatomicalExpressionRow["enhancer/silencer"] === "" ) {
                                $newEntityAnatomicalExpression["enhancer/silencer"] = "enhancer";
                            } else {
                                $newEntityAnatomicalExpression["enhancer/silencer"] = $anatomicalExpressionRow["enhancer/silencer"];
                            }
                            $newEntityAnatomicalExpressions[] = $newEntityAnatomicalExpression;
                        }
                    }
                    if ( $attributeRow["sequence_source"] === "" ) {
                        $sequenceSourceIdentifier = $this->entityIdDao->getSequenceSourceIdByName("Sequence ends provided in reference");
                    } else {
                        $sequenceSourceIdentifier = $this->entityIdDao->getSequenceSourceIdByName($attributeRow["sequence_source"]);
                    }
                    $chromosomeId = $this->entityIdDao->getChromosomeIdByName(
                        $sequenceFromSpeciesScientificName,
                        $alignment->chromosomeName
                    );
                    $jsonEncodedData = json_encode(
                        [
                            "author_email"                            => $attributeRow["author_email"],
                            "chromosome_id"                           => $chromosomeId,
                            "curator_id"                              => $curatorId,
                            "current_end"                             => $alignment->endCoordinate,
                            "current_genome_assembly_release_version" => $this->entityInformationDao->getCurrentGenomeAssemblyReleaseVersionBySpeciesId($sequenceFromSpeciesId),
                            "current_start"                           => $alignment->startCoordinate + 1,
                            "evidence_id"                             => $this->entityIdDao->getEvidenceIdByName($attributeRow["evidence"]),
                            "evidence_subtype_id"                     => $this->entityIdDao->getEvidenceSubtypeIdByName($attributeRow["evidence_subtype"]),
                            "name"                                    => $entityName,
                            "notes"                                   => $attributeRow["notes"],
                            "pubmed_id"                               => $attributeRow["pmid"],
                            "sequence"                                => $alignment->sequence,
                            "sequence_from_species_id"                => $sequenceFromSpeciesId,
                            "sequence_source_id"                      => $sequenceSourceIdentifier
                        ],
                        JSON_INVALID_UTF8_SUBSTITUTE
                    );
                    if ( json_last_error() !== JSON_ERROR_NONE ) {
                        throw new RuntimeException(json_last_error_msg());
                    }
                    $response = json_decode(
                        $this->clientInterface->request(
                            "POST",
                            "api/rest/json/predictedcrm/save",
                            [
                            "form_params" => [
                                "results" => $jsonEncodedData
                            ],
                            "auth" => [
                                $username,
                                $password
                            ]
                            ]
                        )->getBody(),
                        true
                    );
                    if ( $response["success"] === 0 ) {
                        throw new RuntimeException($response["message"]);
                    }
                    $newEntityAnatomicalExpressionsNumber = count($newEntityAnatomicalExpressions);
                    if ( 0 < $newEntityAnatomicalExpressionsNumber ) {
                        $newPredictedCrmId = $response["results"][0]["predicted_crm_id"];
                        for ( $newEntityAnatomicalExpressionIndex = 0; $newEntityAnatomicalExpressionIndex < $newEntityAnatomicalExpressionsNumber; $newEntityAnatomicalExpressionIndex++ ) {
                            $anatomicalExpressionRow = $newEntityAnatomicalExpressions[$newEntityAnatomicalExpressionIndex];
                            // The first capturing group, FBbt:[0-9]{8}, refers to the Drosophila melanogaster species.
                            // The second capturing group, TGMA:[0-9]{7}, refers to both Aedes aegypti and Anopheles gambiae species.
                            // The third capturing group, TrOn:[0-9]{7}, refers to the Tribolium castaneum species.
                            if ( preg_match(
                                "/^(FBbt:[0-9]{8})|(TGMA:[0-9]{7})|(TrOn:[0-9]{7})$/",
                                $anatomicalExpressionRow["expression"]
                            ) !== 1 ) {
                                $anatomicalExpressionIdentifier = $this->entityInformationDao->getAnatomicalExpressionIdentifierByAnatomicalExpressionTerm(
                                    "",
                                    $anatomicalExpressionRow["expression"]
                                );
                            } else {
                                $anatomicalExpressionIdentifier = $anatomicalExpressionRow["expression"];
                            }
                            if ( $anatomicalExpressionRow["pmid"] === "" ) {
                                $anatomicalExpressionPmid = $attributeRow["pmid"];
                            } else {
                                $anatomicalExpressionPmid = $anatomicalExpressionRow["pmid"];
                            }
                            if ( $anatomicalExpressionRow["sex"] === "" ) {
                                $anatomicalExpressionSex = "both";
                            } else {
                                $anatomicalExpressionSex = $anatomicalExpressionRow["sex"];
                            }
                            if ( $anatomicalExpressionRow["enhancer/silencer"] === "" ) {
                                $anatomicalExpressionEnhancerOrSilencer = "enhancer";
                            } else {
                                $anatomicalExpressionEnhancerOrSilencer = $anatomicalExpressionRow["enhancer/silencer"];
                            }
                            $jsonEncodedData = json_encode(
                                [
                                    "action"                            => "save",
                                    "anatomical_expression_identifier"  => $anatomicalExpressionIdentifier,
                                    "biological_process_identifier"     => $anatomicalExpressionRow["biological_process"],
                                    "curator_id"                        => $curatorId,
                                    "enhancer_or_silencer_attribute_id" => $anatomicalExpressionEnhancerOrSilencer,
                                    "predicted_crm_id"                  => $newPredictedCrmId,
                                    "pubmed_id"                         => $anatomicalExpressionPmid,
                                    "sex_id"                            => $anatomicalExpressionSex,
                                    "stage_off_identifier"              => $anatomicalExpressionRow["stage_off"],
                                    "stage_on_identifier"               => $anatomicalExpressionRow["stage_on"]
                                ],
                                JSON_INVALID_UTF8_SUBSTITUTE
                            );
                            if ( json_last_error() !== JSON_ERROR_NONE ) {
                                throw new RuntimeException(json_last_error_msg());
                            }
                            $response = json_decode(
                                $this->clientInterface->request(
                                    "POST",
                                    "api/rest/json/predictedcrmtriplestore/save",
                                    [
                                        "form_params" => [
                                            "results" => $jsonEncodedData
                                        ],
                                        "auth" => [
                                            $username,
                                            $password
                                        ]
                                    ]
                                )->getBody(),
                                true
                            );
                            if ( $response["success"] === 0 ) {
                                throw new RuntimeException($response["message"]);
                            }
                        }
                    }
                    break;
                default:
                    throw new Exception("Unknown entity type: " . $entityType .
                        " when trying to import the attributes file");
            }
        }
    }
    // Only applied for existing reporter constructs
    private function updateRcAnatomicalExpressionsData(
        string $username,
        string $password,
        string $entityType,
        string $anatomicalExpressionTsvFileUri
    ): void {
        if ( $entityType !== "rc" ) {
            throw new Exception("Unknown entity type: " . $entityType .
                " when trying to update the anatomical expressions in the reporter constructs");
        }
        $curatorId = $this->entityIdDao->getUserIdByUsername($username);
        $anatomicalExpressionsReader = Reader::createFromPath($anatomicalExpressionTsvFileUri)
            ->setDelimiter("\t")
            ->setHeaderOffset(0)
            ->skipEmptyRecords();
        // Such a new array created by the "iterator_to_array" function begins by 1
        $anatomicalExpressionArray = $this->sanitizeInputData(iterator_to_array($anatomicalExpressionsReader->getRecords()));
        $anatomicalExpressionRowsNumber = count($anatomicalExpressionArray);
        // Such both new arrays created by the "array_column" function begin by 0
        $anatomicalExpressionGeneNames = array_column(
            $anatomicalExpressionArray,
            "gene_name"
        );
        $anatomicalExpressionArbitraryNames = array_column(
            $anatomicalExpressionArray,
            "arbitrary_name"
        );
        $anatomicalExpressions = array_column(
            $anatomicalExpressionArray,
            "expression"
        );
        $anatomicalExpressionNotes = array_column(
            $anatomicalExpressionArray,
            "notes"
        );
        // First, getting all the entities names and their groups of anatomical expression
        // identifiers associated in the memory
        $entityNames = [];
        $oldRcIdentifiers = [];
        $newNotes = [];
        $anatomicalExpressionsNumber = count($anatomicalExpressions);
        for ( $anatomicalExpressionIndex = 0; $anatomicalExpressionIndex < $anatomicalExpressionsNumber; $anatomicalExpressionIndex++ ) {
            $entityName = $anatomicalExpressionGeneNames[$anatomicalExpressionIndex] . "_" . $anatomicalExpressionArbitraryNames[$anatomicalExpressionIndex];
            if ( ! in_array(
                $entityName,
                $entityNames
            ) ) {
                $entityNames[] = $entityName;
                // Getting the "old" RC identifier regarding to the maximum version of the RC
                // having one of the states targeted
                $oldRcIdentifiers[$entityName] = $this->entityIdDao->getRcIdByNameAndStates(
                    $entityName,
                    [
                        "approval",
                        "approved",
                        "current",
                        "editing"
                    ]
                );
            }
            if ( ! in_array(
                $entityName,
                array_keys($newNotes)
            ) ) {
                $newNotes[$entityName] = $anatomicalExpressionNotes[$anatomicalExpressionIndex];
            } else {
                $newNotes[$entityName] = $newNotes[$entityName] . " " . $anatomicalExpressionNotes[$anatomicalExpressionIndex];
            }
        }
        // Second, updating the entities in the database basing on their state stored in the memory,
        // not on the changing nature of the database after changing the state of each entity
        $newRcIdentifiers = [];
        $auth = new Auth();
        $auth->authenticate();
        $rcHelper = RcHelper::factory();
        $entityNamesNumber = count($entityNames);
        for ( $entityNameIndex = 0; $entityNameIndex < $entityNamesNumber; $entityNameIndex++ ) {
            $entityName = $entityNames[$entityNameIndex];
            $state = $this->entityInformationDao->getStateByRcId($oldRcIdentifiers[$entityName]);
            if ( $state === "current" ) {
                $data = $rcHelper->getData($oldRcIdentifiers[$entityName]);
                $data["state"] = "editing";
                if ( $newNotes[$entityName] !== "" ) {
                    if ( $data["notes"] === "" ) {
                        $data["notes"] = $newNotes[$entityName];
                    } else {
                        $data["notes"] = $data["notes"] . " " . $newNotes[$entityName];
                    }
                }
                $newRcData = $rcHelper->createEdit(
                    $oldRcIdentifiers[$entityName],
                    $data
                );
                $newRcIdentifiers[$entityName] = $newRcData["rc_id"];
            } else {
                if ( in_array(
                    $state,
                    [
                            "approval",
                            "approved",
                            "editing"
                        ]
                ) ) {
                    if ( $this->entityExistenceDao->hasRcEntityId($oldRcIdentifiers[$entityName]) ) {
                        $entityId = $this->entityInformationDao->getEntityIdByRcId($oldRcIdentifiers[$entityName]);
                    } else {
                        $entityId = null;
                    }
                    $version = $this->entityInformationDao->getVersionByRcId($oldRcIdentifiers[$entityName]);
                    $restHandlerHelper = RestHandlerHelper::factory();
                    $redflyId = $restHandlerHelper->entityId(
                        "RC",
                        $entityId,
                        $version,
                        $oldRcIdentifiers[$entityName]
                    );
                    $rcHandler = ReporterconstructHandler::factory();
                    $arguments = array();
                    $arguments["redfly_id"] = $redflyId;
                    $arguments["entity_id"] = $entityId;
                    $arguments["version"] = $version;
                    $arguments["id"] = $oldRcIdentifiers[$entityName];
                    $loadResponse = $rcHandler->loadAction($arguments);
                    if ( $loadResponse->success() === 0 ) {
                        throw new RuntimeException("Unable to load the data from the REDfly id: " . $redflyId);
                    }
                    $geneName = $loadResponse->results()[0]["gene_name"];
                    $data = $rcHelper->getData($oldRcIdentifiers[$entityName]);
                    $data["redfly_id"] = $redflyId;
                    $data["gene_name"] = $geneName;
                    if ( $newNotes[$entityName] !== "" ) {
                        if ( $data["notes"] === "" ) {
                            $data["notes"] = $newNotes[$entityName];
                        } else {
                            $data["notes"] = $data["notes"] . " " . $newNotes[$entityName];
                        }
                    }
                    $jsonEncodedData = json_encode(
                        $data,
                        JSON_INVALID_UTF8_SUBSTITUTE
                    );
                    if ( json_last_error() !== JSON_ERROR_NONE ) {
                        throw new RuntimeException(json_last_error_msg());
                    }
                    // To get its state back to "editing"
                    $rcHandler->saveAction(
                        array(),
                        array("results" => $jsonEncodedData)
                    );
                    if ( $loadResponse->success() === 0 ) {
                        throw new RuntimeException("Unable to save the new state for the REDfly id: " . $redflyId);
                    }
                    $newRcIdentifiers[$entityName] = $oldRcIdentifiers[$entityName];
                } else {
                    throw new RuntimeException("Unable to process the state: " . $state);
                }
            }
            // Deleting all the existing staging data associated to such anatomical expression terms
            // going to be deleted later
            $rctsHelper = RcTsHelper::factory();
            $rctsHelper->deleteAll($newRcIdentifiers[$entityName]);
            // Deleting all the existing anatomical expression terms associated to the entity name
            $rcetHelper = RcEtHelper::factory();
            $rcetHelper->deleteAll($newRcIdentifiers[$entityName]);
        }
        // Third, "updating" the anatomical expressions and their staging data
        for ( $rowIndex = 1; $rowIndex <= $anatomicalExpressionRowsNumber; $rowIndex++ ) {
            $anatomicalExpressionRow = $anatomicalExpressionArray[$rowIndex];
            $entityName = $anatomicalExpressionRow["gene_name"] . "_" . $anatomicalExpressionRow["arbitrary_name"];
            // The first capturing group, FBbt:[0-9]{8}, refers to the Drosophila melanogaster species.
            // The second capturing group, TGMA:[0-9]{7}, refers to both Aedes aegypti and Anopheles gambiae species.
            // The third capturing group, TrOn:[0-9]{7}, refers to the Tribolium castaneum species.
            if ( preg_match(
                "/^(FBbt:[0-9]{8})|(TGMA:[0-9]{7})|(TrOn:[0-9]{7})$/",
                $anatomicalExpressionRow["expression"]
            ) !== 1 ) {
                $anatomicalExpressionIdentifier = $this->entityInformationDao->getAnatomicalExpressionIdentifierByAnatomicalExpressionTerm(
                    "",
                    $anatomicalExpressionRow["expression"]
                );
            } else {
                $anatomicalExpressionIdentifier = $anatomicalExpressionRow["expression"];
            }
            if ( $anatomicalExpressionRow["pmid"] === "" ) {
                $anatomicalExpressionPmid = $this->entityInformationDao->getPubMedIdByRcId($oldRcIdentifiers[$entityName]);
            } else {
                $anatomicalExpressionPmid = $anatomicalExpressionRow["pmid"];
            }
            if ( $anatomicalExpressionRow["sex"] === "" ) {
                $anatomicalExpressionSex = "both";
            } else {
                $anatomicalExpressionSex = $anatomicalExpressionRow["sex"];
            }
            if ( $anatomicalExpressionRow["ectopic"] === "" ) {
                $anatomicalExpressionEctopic = "0";
            } else {
                $anatomicalExpressionEctopic = $anatomicalExpressionRow["ectopic"];
            }
            if ( $anatomicalExpressionRow["enhancer/silencer"] === "" ) {
                $anatomicalExpressionEnhancerOrSilencer = "enhancer";
            } else {
                $anatomicalExpressionEnhancerOrSilencer = $anatomicalExpressionRow["enhancer/silencer"];
            }
            $jsonEncodedData = json_encode(
                [
                    "action"                            => "save",
                    "anatomical_expression_identifier"  => $anatomicalExpressionIdentifier,
                    "biological_process_identifier"     => $anatomicalExpressionRow["biological_process"],
                    "curator_id"                        => $curatorId,
                    "ectopic_id"                        => $anatomicalExpressionEctopic,
                    "enhancer_or_silencer_attribute_id" => $anatomicalExpressionEnhancerOrSilencer,
                    "pubmed_id"                         => $anatomicalExpressionPmid,
                    "rc_id"                             => $newRcIdentifiers[$entityName],
                    "sex_id"                            => $anatomicalExpressionSex,
                    "stage_off_identifier"              => $anatomicalExpressionRow["stage_off"],
                    "stage_on_identifier"               => $anatomicalExpressionRow["stage_on"]
                ],
                JSON_INVALID_UTF8_SUBSTITUTE
            );
            if ( json_last_error() !== JSON_ERROR_NONE ) {
                throw new RuntimeException(json_last_error_msg());
            }
            $response = json_decode(
                $this->clientInterface->request(
                    "POST",
                    "api/rest/json/reporterconstructtriplestore/save",
                    [
                        "form_params" => [
                            "results" => $jsonEncodedData
                        ],
                        "auth" => [
                            $username,
                            $password
                        ]
                    ]
                )->getBody(),
                true
            );
            if ( $response["success"] === 0 ) {
                throw new RuntimeException($response["message"]);
            }
        }
    }
    private function sanitizeInputData(array $inputData): array
    {
        $rowsNumber = count($inputData);
        for ( $rowIndex = 1; $rowIndex <= $rowsNumber; $rowIndex++ ) {
            $rowData = $inputData[$rowIndex];
            // "Decontaminating" each array key from non-standard characters
            // and spaces
            $rowKeys = array_keys($rowData);
            $rowKeysNumber = count($rowKeys);
            for ( $keyIndex = 0; $keyIndex < $rowKeysNumber; $keyIndex++ ) {
                $newKey = strtolower(trim(
                    trim(
                        $rowKeys[$keyIndex],
                        "\x00..\x20"
                    ),
                    "\x7F..\xFF"
                ));
                // The key is "contaminated" with non-standard characters
                // (especially from non-American keyboards) and spaces
                if ( $rowKeys[$keyIndex] !== $newKey ) {
                    $rowData[$newKey] = $rowData[$rowKeys[$keyIndex]];
                    unset($rowData[$rowKeys[$keyIndex]]);
                }
            }
            // "Decontaminating" each array value from non-standard characters
            // and spaces
            $rowData = array_map(
                function ($item) {
                    return trim(
                        trim(
                            $item,
                            "\x00..\x20"
                        ),
                        "\x7F..\xFF"
                    );
                },
                $rowData
            );
            $inputData[$rowIndex] = $rowData;
        }
        return $inputData;
    }
    private function extractAlignments(
        string $speciesShortName,
        string $fastaFileUri
    ): array {
        $alignmentsMap = [];
        $fixedFastaFile = $this->fixFastaFile($fastaFileUri);
        $alignments = $this->blatDataSource->batchQuery(
            $speciesShortName,
            stream_get_meta_data($fixedFastaFile)["uri"]
        );
        foreach ( $alignments as $coordinates => $alignment ) {
            if ( (isset($alignmentsMap[$coordinates]) === false) ||
                 ($alignmentsMap[$coordinates]->score < $alignment->score) ) {
                $alignmentsMap[$coordinates] = $alignment;
            }
        }

        return $alignmentsMap;
    }
    /**
     * The FlyBase FASTA downloader places the coordinates within the metadata
     * string. We need the coordinates to follow the ">" character for the BLAT
     * query. This function ensures this.
     */
    private function fixFastaFile(string $fastaFileUri)
    {
        $fastaFile = fopen($fastaFileUri, "r");
        if ( $fastaFile === false ) {
            throw new RuntimeException("Cannot open FASTA file.");
        }
        $fixedFastaFile = tmpfile();
        $regex = "/(^>|loc=)((X|2R|2L|3R|3L|4|U|Y|UNKN|Y_unplaced|Mt|MT|NC_[0-9]+\.[0-9]+|NW_[0-9]+\.[0-9]+):[0-9]+(\.\.|-)+[0-9]+)/";
        $matches = [];
        while ( ($line = fgets($fastaFile)) ) {
            if ( ctype_space($line) === false ) {
                if ( $line[0] === ">" ) {
                    preg_match(
                        $regex,
                        $line,
                        $matches
                    );
                    if ( strpos(
                        $line,
                        ".."
                    ) !== false ) {
                        fwrite($fixedFastaFile, ">" . $matches[2] . "\n");
                    } else {
                        if ( strpos(
                            $line,
                            "(+)"
                        ) !== false ) {    
                            fwrite($fixedFastaFile, ">" . $matches[2] . "(+)\n");
                        } else {
                            fwrite($fixedFastaFile, ">" . $matches[2] . "(-)\n");
                        }
                    }
                } else {
                    fwrite($fixedFastaFile, $line);
                }
            }
        }
        fclose($fastaFile);

        return $fixedFastaFile;
    }
}
