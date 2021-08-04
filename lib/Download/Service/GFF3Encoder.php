<?php
namespace CCR\REDfly\Download\Service;

// Standard PHP Libraries (SPL)
use Exception;
// REDfly libraries with namespaces
use CCR\REDfly\Service\Message\QueryInterface;
/**
 * Encoding strategy for encoding a Traversable to GFF3.
 * @link https://github.com/The-Sequence-Ontology/Specifications/blob/master/gff3.md
 *       The GFF3 file specification.
 * Note: It is used ONLY for batch downloads of GFF3 data
 *       belonging to an unique REDfly entity kind
 */
class GFF3Encoder implements Encoder
{
    /**
     * @var string $redflyVersion The REDfly version for inclusion in the file
     *     header.
     */
    private $redflyVersion;
    /**
     * @var string $date The date to be written in the header.
     */
    private $date;
    public function __construct(
        $redflyVersion,
        $date
    ) {
        $this->redflyVersion = $redflyVersion;
        $this->date = $date;
    }
    public function encode(
        iterable $data,
        QueryInterface $queryInterface,
        array $stagingData
    ) {
        $result = "";
        if ( count($data) !== 0 ) {
            $stagingDataRowsNumber = count($stagingData);
            if ( $stagingDataRowsNumber !== 0 ) {
                $entityType = $stagingData[0]["entity_type"];
                if ( ! (($entityType !== null) &&
                    ($entityType !== "") &&
                    (in_array(
                        $entityType,
                        ["RFPCRM", "RFRC", "RFSEG", "RFTF"]
                    ))) ) {
                    throw new Exception("Unknown entity type, \"" .
                        $entityType .
                        "\", while building the downloadable data in the GFF3 format");
                }
            }
            $stagingDataIndex = 0;
            $result .= $this->header() . PHP_EOL;
            foreach ( $data as $line ) {
                $id = "ID=" . $line["name"] . ";";
                $factor = "";
                $entityType = explode(":", $line["redfly_id_unversioned"])[0];
                if ( $entityType !== "RFPCRM" ) {
                    $dbxref = "Dbxref=\"" . implode(
                        ",",
                        [
                            "FB:" . $line["gene_identifier"],
                            "PMID:" . $line["pubmed_id"],
                            "REDfly:" . $line["redfly_id_unversioned"]
                        ]
                    ) . "\";";
                    if ( ($entityType === "RFTF") &&
                        isset($line["tf_identifier"]) &&
                        isset($line["tf_name"]) ) {
                        $factor = "factor=FB:" . $line["tf_identifier"] . ":" . $line["tf_name"] . ";";
                    }
                    $target = "target=FB:" . $line["gene_identifier"] . ":" . $line["gene_name"] . ";";
                } else {
                    $dbxref = "Dbxref=\"" . implode(
                        ",",
                        [
                            "PMID:" . $line["pubmed_id"],
                            "REDfly:" . $line["redfly_id_unversioned"]
                        ]
                    ) . "\";";
                    $target = "target=FB:" . $line["gene_locus"] . ";";
                }
                $evidence = "evidence=" . $line["evidence_term"];
                if ( in_array(explode(":", $line["redfly_id_unversioned"])[0], array("RFSEG", "RFPCRM")) &&
                    isset($line["evidence_subtype_term"]) &&
                    ($line["evidence_subtype_term"] !== "" ) ) {
                    $evidenceSubtype = ";evidence_subtype=" . $line["evidence_subtype_term"];
                } else {
                    $evidenceSubtype = "";
                }
                if ( isset($line["fbtp"]) &&
                    ( $line["fbtp"] !== "") ) {
                    $fbtp = ";fbtp=" . $line["fbtp"];
                } else {
                    $fbtp = "";
                }
                //if ( isset($line["associated_rc"]) &&
                //    ( $line["associated_rc"] !== "") ) {
                //    $associatedRcs = ";associated_rc=" . $line["associated_rc"];
                //} else {
                //    $associatedRcs = "";
                //}
                //if ( isset($line["associated_tfbs"]) &&
                //    ( $line["associated_tfbs"] !== "") ) {
                //    $associatedTfbss = ";associated_tfbs=" . $line["associated_tfbs"];
                //} else {
                //    $associatedTfbss = "";
                //}
                if ( isset($line["ontology_term"]) &&
                    ( $line["ontology_term"] !== "") ) {
                    //if ( ($stagingDataRowsNumber !== 0) &&
                    //    in_array(
                    //    $entityType,
                    //    ["RFPCRM", "RFRC", "RFSEG"]
                    //)) {
                    //  if ( $line["name"] === $stagingData[$stagingDataIndex]["name"] ) {
                    //      $expressionFlyBaseIdentifiers = explode(
                    //            ",",
                    //            $line["ontology_term"]
                    //        );
                    //        $expressionsNumber = count($expressionFlyBaseIdentifiers);
                    //        for ( $flyBaseIdentifierIndex = 0;
                    //            $flyBaseIdentifierIndex < $expressionsNumber;
                    //            $flyBaseIdentifierIndex++) {
                    //            $ontologyTerm = ";Ontology_term=\"" . $expressionFlyBaseIdentifiers[$flyBaseIdentifierIndex];
                    //            if ( $expressionFlyBaseIdentifiers[$flyBaseIdentifierIndex] === $stagingData[$stagingDataIndex]["expression_flybase_id"] ) {
                    //                if ( ((int)$stagingData[$stagingDataIndex]["parent_pubmed_id"]) !== ((int)$stagingData[$stagingDataIndex]["pubmed_id"]) ) {
                    //                    $ontologyTerm .= ";pmid=" . $stagingData[$stagingDataIndex]["pubmed_id"];
                    //                }
                    //                if ( ($stagingData[$stagingDataIndex]["stage_on_flybase_id"] !== "") &&
                    //                    ($stagingData[$stagingDataIndex]["stage_on_flybase_id"] !== "none") ) {
                    //                    $ontologyTerm .= ";stage_on=" . $stagingData[$stagingDataIndex]["stage_on_flybase_id"];
                    //                }
                    //                if ( ($stagingData[$stagingDataIndex]["stage_off_flybase_id"] !== "") &&
                    //                    ($stagingData[$stagingDataIndex]["stage_off_flybase_id"] !== "none") ) {
                    //                    $ontologyTerm .= ";stage_off=" . $stagingData[$stagingDataIndex]["stage_off_flybase_id"];
                    //                }
                    //                if ( $stagingData[$stagingDataIndex]["biological_process_go_id"] !== "" ) {
                    //                    $ontologyTerm .= ";go_term=" . $stagingData[$stagingDataIndex]["biological_process_go_id"];
                    //                }
                    //                $ontologyTerm .= ";sex=" . $stagingData[$stagingDataIndex]["sex"];
                    //                if ( in_array(
                    //                    $entityType,
                    //                    ["RFRC", "RFSEG"]
                    //                ) ) {
                    //                    switch ($stagingData[$stagingDataIndex]["ectopic"]) {
                    //                        case 0:
                    //                            $ontologyTerm .= ";ectopic=no";
                    //                            break;
                    //                        case 1:
                    //                            $ontologyTerm .= ";ectopic=yes";
                    //                            break;
                    //                        default:
                    //                            $ontologyTerm .= ";ectopic=unknown";
                    //                    }
                    //                }
                    //                $ontologyTerm .= "\"";
                    //                $this->buildLine(
                    //                    $entityType,
                    //                    $result,
                    //                    $line,
                    //                    $id,
                    //                    $dbxref,
                    //                    $factor,
                    //                    $target,
                    //                    $evidence,
                    //                    $evidenceSubtype,
                    //                    $fbtp,
                    //                    $ontologyTerm
                    //                );
                    //                $stagingDataIndex++;
                    //            }
                    //        }
                    //    } else {
                    //        $ontologyTerm = ";Ontology_term=\"" . $line["ontology_term"] . "\"";
                    //        $this->buildLine(
                    //            $entityType,
                    //            $result,
                    //            $line,
                    //            $id,
                    //            $dbxref,
                    //            $factor,
                    //            $target,
                    //            $evidence,
                    //            $evidenceSubtype,
                    //            $fbtp,
                    //            $ontologyTerm
                    //        );
                    //  }
                    //} else {
                        $ontologyTerm = ";Ontology_term=\"" . $line["ontology_term"] . "\"";
                        $this->buildLine(
                            $entityType,
                            $result,
                            $line,
                            $id,
                            $dbxref,
                            $factor,
                            $target,
                            $evidence,
                            $evidenceSubtype,
                            $fbtp,
                            $ontologyTerm
                        );
                    //}
                } else {
                    $ontologyTerm = "";
                    $this->buildLine(
                        $entityType,
                        $result,
                        $line,
                        $id,
                        $dbxref,
                        $factor,
                        $target,
                        $evidence,
                        $evidenceSubtype,
                        $fbtp,
                        $ontologyTerm
                    );
                }
            }
        }

        return $result;
    }
    private function header()
    {
        return implode(
            PHP_EOL,
            [
                "##gff-version 3",
                "#source-version REDfly v" . $this->redflyVersion,
                "#date " . $this->date
            ]
        );
    }
    private function buildLine(
        $entityType,
        &$result,
        $line,
        $id,
        $dbxref,
        $factor,
        $target,
        $evidence,
        $evidenceSubtype,
        $fbtp,
        $ontologyTerm
    ) {
        switch ( $entityType ) {
            case "RFPCRM":
                $result .= implode(
                    "\t",
                    [
                        $this->normalizeChromosomeName($line["chromosome"]),
                        "REDfly",
                        "regulatory_region",
                        $line["start"],
                        $line["end"],
                        chr(46),
                        chr(46),
                        chr(46),
                        implode([
                            $id,
                            $dbxref,
                            $factor,
                            $target,
                            $evidence,
                            $evidenceSubtype,
                            $ontologyTerm
                        ])
                    ]
                );
                break;
            case "RFSEG":
                $result .= implode(
                    "\t",
                    [
                        $this->normalizeChromosomeName($line["chromosome"]),
                        "REDfly",
                        "regulatory_region",
                        $line["start"],
                        $line["end"],
                        chr(46),
                        chr(46),
                        chr(46),
                        implode([
                            $id,
                            $dbxref,
                            $factor,
                            $target,
                            $evidence,
                            $evidenceSubtype,
                            $fbtp,
                            $ontologyTerm
                        ])
                    ]
                );
                break;
            default:
                $result .= implode(
                    "\t",
                    [
                        $this->normalizeChromosomeName($line["chromosome"]),
                        "REDfly",
                        "regulatory_region",
                        $line["start"],
                        $line["end"],
                        chr(46),
                        chr(46),
                        chr(46),
                        implode([
                            $id,
                            $dbxref,
                            $factor,
                            $target,
                            $evidence,
                            $fbtp,
                            //$associatedRcs,
                            //$associatedTfbss,
                            $ontologyTerm
                        ])
                    ]
                );
        }
        $result .= PHP_EOL;
    }
    private function normalizeChromosomeName(string $chromosomeName)
    {
        if ( preg_match(
            "/(NC_\d+)|(NW_\d+)/",
            $chromosomeName
        ) ) {
            return $chromosomeName;
        } else {
            return "chr" . $chromosomeName;
        }
    }
}
