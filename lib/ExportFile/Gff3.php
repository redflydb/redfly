<?php
// ----------------------------------------------------------------------
// The limit of downloadable same-kind entities number imposed by the
// front-end interface is 500 due to its page size.
// The limit of downloadable different-kind entities number imposed by
// the front-end interface is 4 x 500 = 2000 due to its page size.
// Specification: https://github.com/The-Sequence-Ontology/Specifications/blob/master/gff3.md
// ----------------------------------------------------------------------
class ExportFile_Gff3 extends ExportFile
{
    const MIME_TYPE = "text/plain";
    const FILE_EXT = "gff3";
    public static function factory(array $options = null)
    {
        return new ExportFile_Gff3($options ?? []);
    }
    protected function __construct(array $options)
    {
        parent::__construct(
            self::MIME_TYPE,
            self::FILE_EXT,
            $options
        );
    }
    public function help()
    {
        $parentHelp = parent::help();
        $description = "Export REDfly data in the GFF3 format";

        return RestResponse::factory(
            true,
            $description,
            $parentHelp->results()
        );
    }
    public function generateFileHeader()
    {
        $html = array();
        $html[] = "##gff-version 3";
        $html[] = "#source-version REDfly v" . $GLOBALS["options"]->general->redfly_version;
        $html[] = "#date " . date("Ymd");
        $this->_header = implode("\n", $html) . "\n";
        $this->_headerLength = strlen($this->_header);
    }
    public function generateFileBody()
    {
        if ( ($this->_cisRegulatoryModuleSegmentList === null) &&
            ($this->_predictedCisRegulatoryModuleList === null) &&
            ($this->_reporterConstructList === null) &&
            ($this->_transcriptionFactorBindingSiteList === null) ) {
            throw new Exception("No REDfly entity provided");
        }
        $html = array();
        $anatomicalExpressionHandler = AnatomicalexpressionHandler::factory();
        // Specification: https://github.com/The-Sequence-Ontology/Specifications/blob/master/gff3.md
        // Validator: http://genometools.org/cgi-bin/gff3validator.cgi
        // Note that UCSC blat coordinates are interbase (0-based, start at 0) if we use psl output
        // format and 1-based (start at 1) if we use hyperlink format.
        // The GFF format requires 1-based coordinates so make any corrections that we need here.
        // This only affects the start coordinate - BLAT psl coordinates, like most of UCSC data,
        // is half-open: 0-base for start, 1-base for end.
        //
        // Cis Regulatory Module Segment
        foreach ( $this->_cisRegulatoryModuleSegmentList as $crms ) {
            $line = $this->normalizeChromosomeName($crms["chromosome"]) . "\t" .
                "REDfly\t" .
                "regulatory_region\t" .
                $crms["start"] . "\t" .
                $crms["end"] . "\t" .
                ".\t.\t.\t" .
                "ID=" . $crms["name"] . ";" .
                "Dbxref=\"FB:" . $crms["gene_identifier"] .
                ",PMID:" . $crms["pubmed_id"] .
                ",REDfly:" . $crms["redfly_id_unversioned"]. "\";" .
                "target=FB:" . $crms["gene_identifier"] . ":" . $crms["gene_name"] . ";" .
                "evidence=" . $crms["evidence_term"] .
                ( isset($crms["evidence_subtype_term"]) && ($crms["evidence_subtype_term"] !== "")
                    ? ";evidence_subtype=" . $crms["evidence_subtype_term"]
                    : "" );
                ( isset($crms["fbtp"]) && ($crms["fbtp"] !== "")
                    ? ";fbtp=" . $crms["fbtp"]
                    : "" );
            $arguments = array(
                "redfly_id" => $crms["redfly_id"],
                "sort"      => "expression_identifier"
            );
            $anatomicalExpressionResponse = $anatomicalExpressionHandler->getAction($arguments);
            if ( $anatomicalExpressionResponse->numResults() > 0 ) {
                $line .= ";Ontology_term=\"";
                for ( $index = 0; $index < count($anatomicalExpressionResponse->results()); $index++ ) {
                    if ( $index !== 0 ) {
                        $line .= ",";
                    }
                    $line .= $anatomicalExpressionResponse->results()[$index]["identifier"];
                }
                $line .= "\"";
                //foreach ( $anatomicalExpressionResponse->results() as $anatomicalExpression ) {
                //    $newLine = $line;
                //    $newAnatomicalExpression = $anatomicalExpression["identifier"];
                //    if ( ($anatomicalExpression["pubmed_id"] !== null) &&
                //        ($anatomicalExpression["pubmed_id"] !== "") &&
                //        ($anatomicalExpression["pubmed_id"] !== $crms["pubmed_id"]) ) {
                //        $newAnatomicalExpression .= ";pubmed_id=" . $anatomicalExpression["pubmed_id"];
                //    }
                //    if ( ($anatomicalExpression["stage_on_flybase_id"] !== null) &&
                //        ($anatomicalExpression["stage_on_flybase_id"] !== "") &&
                //        ($anatomicalExpression["stage_on_flybase_id"] !== "none") ) {
                //        $newAnatomicalExpression .= ";stage_on=" . $anatomicalExpression["stage_on_flybase_id"];
                //    }
                //    if ( ($anatomicalExpression["stage_off_flybase_id"] !== null)  &&
                //        ($anatomicalExpression["stage_off_flybase_id"] !== "") &&
                //        ($anatomicalExpression["stage_off_flybase_id"] !== "none") ) {
                //        $newAnatomicalExpression .= ";stage_off=" . $anatomicalExpression["stage_off_flybase_id"];
                //    }
                //    if ( ($anatomicalExpression["biological_process_go_id"] !== null) &&
                //        ($anatomicalExpression["biological_process_go_id"] !== "") ) {
                //        $newAnatomicalExpression .= ";go_term=" . $anatomicalExpression["biological_process_go_id"];
                //    }
                //    if ( ($anatomicalExpression["sex"] !== null) &&
                //        ($anatomicalExpression["sex"] !== "") ) {
                //        $newAnatomicalExpression .= ";sex=" . $anatomicalExpression["sex"];
                //    }
                //    if ( ($anatomicalExpression["ectopic"] !== null) &&
                //        ($anatomicalExpression["ectopic"] !== "") ) {
                //        switch ($anatomicalExpression["ectopic"]) {
                //            case "0":
                //                $newAnatomicalExpression .= ";ectopic=no";
                //                break;
                //            case "1":
                //                $newAnatomicalExpression .= ";ectopic=yes";
                //                break;
                //            default:
                //                $newAnatomicalExpression .= ";ectopic=unknown";
                //        }
                //    }
                //    $newLine .= ";Ontology_term=\"" . $newAnatomicalExpression . "\"";
                //    $html[] = $newLine;
                //}
                $html[] = $line;
            } else {
                $html[] = $line;
            }
        }
        // Predicted Cis Regulatory Module
        if ( count($this->_predictedCisRegulatoryModuleList) > 0 ) {
            $db = DbService::factory();
            foreach ( $this->_predictedCisRegulatoryModuleList as $pcrm ) {
                $geneFlyBaseIdentifiers = $db->getGeneFlyBaseIdentifiers($pcrm["gene_locus"]);
                $line = $this->normalizeChromosomeName($pcrm["chromosome"]) . "\t" .
                    "REDfly\t" .
                    "regulatory_region\t" .
                    $pcrm["start"] . "\t" .
                    $pcrm["end"] . "\t" .
                    ".\t.\t.\t" .
                    "ID=" . $pcrm["name"] . ";" .
                    "Dbxref=\"FB:" . $geneFlyBaseIdentifiers .
                    ",PMID:" . $pcrm["pubmed_id"] .
                    ",REDfly:" . $pcrm["redfly_id_unversioned"] . "\";" .
                    "target=FB:" . $geneFlyBaseIdentifiers . ":" . $pcrm["gene_locus"] . ";" .
                    "evidence=" . $pcrm["evidence_term"] .
                    ( isset($pcrm["evidence_subtype_term"]) && ($pcrm["evidence_subtype_term"] !== "")
                        ? ";evidence_subtype=" . $pcrm["evidence_subtype_term"]
                        : "" );
                $arguments = array(
                    "redfly_id" => $pcrm["redfly_id"],
                    "sort"      => "expression_identifier"
                );
                $anatomicalExpressionResponse = $anatomicalExpressionHandler->getAction($arguments);
                if ( $anatomicalExpressionResponse->numResults() > 0 ) {
                    $line .= ";Ontology_term=\"";
                    for ( $index = 0; $index < count($anatomicalExpressionResponse->results()); $index++ ) {
                        if ( $index !== 0 ) {
                            $line .= ",";
                        }
                        $line .= $anatomicalExpressionResponse->results()[$index]["identifier"];
                    }
                    $line .= "\"";
                    //foreach ( $anatomicalExpressionResponse->results() as $anatomicalExpression ) {
                    //    $newLine = $line;
                    //    $newAnatomicalExpression = $anatomicalExpression["identifier"];
                    //    if ( ($anatomicalExpression["pubmed_id"] !== null) &&
                    //        ($anatomicalExpression["pubmed_id"] !== "") &&
                    //        ($anatomicalExpression["pubmed_id"] !== $pcrm["pubmed_id"]) ) {
                    //        $newAnatomicalExpression .= ";pubmed_id=" . $anatomicalExpression["pubmed_id"];
                    //    }
                    //    if ( ($anatomicalExpression["stage_on_flybase_id"] !== null) &&
                    //        ($anatomicalExpression["stage_on_flybase_id"] !== "") &&
                    //        ($anatomicalExpression["stage_on_flybase_id"] !== "none") ) {
                    //        $newAnatomicalExpression .= ";stage_on=" . $anatomicalExpression["stage_on_flybase_id"];
                    //    }
                    //    if ( ($anatomicalExpression["stage_off_flybase_id"] !== null)  &&
                    //        ($anatomicalExpression["stage_off_flybase_id"] !== "") &&
                    //        ($anatomicalExpression["stage_off_flybase_id"] !== "none") ) {
                    //        $newAnatomicalExpression .= ";stage_off=" . $anatomicalExpression["stage_off_flybase_id"];
                    //    }
                    //    if ( ($anatomicalExpression["biological_process_go_id"] !== null) &&
                    //        ($anatomicalExpression["biological_process_go_id"] !== "") ) {
                    //        $newAnatomicalExpression .= ";go_term=" . $anatomicalExpression["biological_process_go_id"];
                    //    }
                    //    if ( ($anatomicalExpression["sex"] !== null) &&
                    //        ($anatomicalExpression["sex"] !== "") ) {
                    //        $newAnatomicalExpression .= ";sex=" . $anatomicalExpression["sex"];
                    //    }
                    //    $newLine .= ";Ontology_term=\"" . $newAnatomicalExpression . "\"";
                    //    $html[] = $newLine;
                    $html[] = $line;
                } else {
                    $html[] = $line;
                }
            }
        }
        // Reporter Construct
        foreach ( $this->_reporterConstructList as $rc ) {
            $line = $this->normalizeChromosomeName($rc["chromosome"]) . "\t" .
                "REDfly\t" .
                "regulatory_region\t" .
                $rc["start"] . "\t" .
                $rc["end"] . "\t" .
                ".\t.\t.\t" .
                "ID=" . $rc["name"] . ";" .
                "Dbxref=\"FB:" . $rc["gene_identifier"] .
                ",PMID:" . $rc["pubmed_id"] .
                ",REDfly:" . $rc["redfly_id_unversioned"] . "\";" .
                "target=FB:" . $rc["gene_identifier"] . ":" . $rc["gene_name"] . ";" .
                "evidence=" . $rc["evidence_term"] .
                ( isset($rc["fbtp"]) && ($rc["fbtp"] !== "")
                    ? ";fbtp=" . $rc["fbtp"]
                    : "" );
            //if ( array_key_exists(
            //    "associated_tfbs",
            //    $rc
            //) ) {
            //    $line .= ";associated_tfbs=" . $rc["associated_tfbs"];
            //}
            $arguments = array(
                "redfly_id"   => $rc["redfly_id"],
                "sort"        => "expression_identifier,stage_on_identifier,stage_off_identifier,biological_process_identifier",
                "triplestore" => "true"
            );
            $anatomicalExpressionResponse = $anatomicalExpressionHandler->getAction($arguments);
            if ( $anatomicalExpressionResponse->numResults() > 0 ) {
                $line .= ";Ontology_term=\"";
                for ( $index = 0; $index < count($anatomicalExpressionResponse->results()); $index++ ) {
                    if ( $index !== 0 ) {
                        $line .= ",";
                    }
                    $line .= $anatomicalExpressionResponse->results()[$index]["identifier"];
                }
                $line .= "\"";
                //foreach ( $anatomicalExpressionResponse->results() as $anatomicalExpression ) {
                //    $newLine = $line;
                //    $newAnatomicalExpression = $anatomicalExpression["identifier"];
                //    if ( ($anatomicalExpression["pubmed_id"] !== null) &&
                //        ($anatomicalExpression["pubmed_id"] !== "") &&
                //        ($anatomicalExpression["pubmed_id"] !== $rc["pubmed_id"]) ) {
                //        $newAnatomicalExpression .= ";pmid=" . $anatomicalExpression["pubmed_id"];
                //    }
                //    if ( ($anatomicalExpression["stage_on_flybase_id"] !== null) &&
                //        ($anatomicalExpression["stage_on_flybase_id"] !== "") &&
                //        ($anatomicalExpression["stage_on_flybase_id"] !== "none") ) {
                //        $newAnatomicalExpression .= ";stage_on=" . $anatomicalExpression["stage_on_flybase_id"];
                //    }
                //    if ( ($anatomicalExpression["stage_off_flybase_id"] !== null)  &&
                //        ($anatomicalExpression["stage_off_flybase_id"] !== "") &&
                //        ($anatomicalExpression["stage_off_flybase_id"] !== "none") ) {
                //        $newAnatomicalExpression .= ";stage_off=" . $anatomicalExpression["stage_off_flybase_id"];
                //    }
                //    if ( ($anatomicalExpression["biological_process_go_id"] !== null) &&
                //        ($anatomicalExpression["biological_process_go_id"] !== "") ) {
                //        $newAnatomicalExpression .= ";go_term=" . $anatomicalExpression["biological_process_go_id"];
                //    }
                //    if ( ($anatomicalExpression["sex"] !== null) &&
                //        ($anatomicalExpression["sex"] !== "") ) {
                //        $newAnatomicalExpression .= ";sex=" . $anatomicalExpression["sex"];
                //    }
                //    if ( ($anatomicalExpression["ectopic"] !== null) &&
                //        ($anatomicalExpression["ectopic"] !== "") ) {
                //        switch ($anatomicalExpression["ectopic"]) {
                //            case "0":
                //                $newAnatomicalExpression .= ";ectopic=no";
                //                break;
                //            case "1":
                //                $newAnatomicalExpression .= ";ectopic=yes";
                //                break;
                //            default:
                //                $newAnatomicalExpression .= ";ectopic=unknown";
                //        }
                //    }
                //    $newLine .= ";Ontology_term=\"" . $newAnatomicalExpression . "\"";
                //    $html[] = $newLine;
                //}
                $html[] = $line;
            } else {
                $html[] = $line;
            }
        }
        // Transcription Factor Binding Site
        foreach ( $this->_transcriptionFactorBindingSiteList as $tfbs ) {
            $line = $this->normalizeChromosomeName($tfbs["chromosome"]) . "\t" .
                "REDfly\t" .
                "regulatory_region\t" .
                $tfbs["start"] . "\t" .
                $tfbs["end"] . "\t" .
                ".\t.\t.\t" .
                "ID=" . $tfbs["name"] . ";" .
                "Dbxref=\"FB:" . $tfbs["gene_identifier"] .
                ",PMID:" . $tfbs["pubmed_id"] .
                ",REDfly:" . $tfbs["redfly_id_unversioned"] . "\";" .
                "factor=FB:" . $tfbs["tf_identifier"] . ":" . $tfbs["tf_name"] . ";" .
                "target=FB:" . $tfbs["gene_identifier"] . ":". $tfbs["gene_name"] . ";" .
                "evidence=" . $tfbs["evidence_term"];
            //if ( array_key_exists(
            //    "associated_rc",
            //    $tfbs
            //) ) {
            //    $line .= ";associated_rc=" . $tfbs["associated_rc"];
            //}
            $arguments = array(
                "redfly_id" => $tfbs["redfly_id"],
                "sort"      => "expression_identifier"
            );
            $anatomicalExpressionResponse = $anatomicalExpressionHandler->getAction($arguments);
            if ( $anatomicalExpressionResponse->numResults() > 0 ) {
                $expressionTermIdList = array();
                foreach ( $anatomicalExpressionResponse->results() as $anatomicalExpression ) {
                    // Transcription factor binding sites inherit expression terms
                    // from their associated reporter constructs
                    if ( ! in_array(
                        $anatomicalExpression["identifier"],
                        $expressionTermIdList
                    )
                    ) {
                        $expressionTermIdList[] = $anatomicalExpression["identifier"];
                    }
                }
                $line .= ";Ontology_term=\"" . implode(",", $expressionTermIdList) . "\"";
            }
            $html[] = $line;
        }
        $this->_body = implode("\n", $html) . "\n";
        $this->_bodyLength = strlen($this->_body);
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
