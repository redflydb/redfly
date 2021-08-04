<?php
// ----------------------------------------------------------------------
// Download a CSV file containing one or more data from one or more
// entities which kind can be same or different.
// The limit of downloadable same-kind entities number imposed by the
// front-end interface is 500 due to its page size.
// The limit of downloadable different-kind entities number imposed by
// the front-end interface is 4 x 500 = 2000 due to its page size.
// ----------------------------------------------------------------------
class ExportFile_Csv extends ExportFile
{
    const FILE_EXT = "csv";
    const MIME_TYPE = "text/plain";
    public static function factory(array $options = null)
    {
        return new ExportFile_Csv($options);
    }
    protected function __construct(array $options = null)
    {
        parent::__construct(
            self::MIME_TYPE,
            self::FILE_EXT,
            $options ?? []
        );
    }
    public function help()
    {
        $parentHelp = parent::help();
        $description = "Export REDfly data in the CSV format";

        return RestResponse::factory(
            true,
            $description,
            $parentHelp->results()
        );
    }
    public function generateFileHeader()
    {
        $html = array();
        if ( count($this->_predictedCisRegulatoryModuleList) === 0 ) {
            $headings = array(
                "name",
                "species_scientific_name",
                "gene_name",
                "gene_identifier",
                "coordinates",
                "sequence"
            );
        } else {
            $headings = array(
                "name",
                "species_scientific_name",
                "gene_locus",
                "gene_identifier",
                "coordinates",
                "sequence"
            );
        }
        $html[] = "\"" . implode("\",\"", $headings) . "\"";
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
        // Cis Regulatory Module Segment
        foreach ( $this->_cisRegulatoryModuleSegmentList as $crms ) {
            $items = array(
                $crms["name"],
                $crms["sequence_from_species_scientific_name"],
                $crms["gene_name"],
                $crms["gene_identifier"],
                $this->normalizeChromosomeName($crms["coordinates"]),
                $crms["sequence"]
            );
            $html[] = "\"" . implode("\",\"", $items) . "\"";
        }
        // Predicted Cis Regulatory Module
        if ( count($this->_predictedCisRegulatoryModuleList) > 0 ) {
            $db = DbService::factory();
            foreach ( $this->_predictedCisRegulatoryModuleList as $pcrm ) {
                $items = array(
                    $pcrm["name"],
                    $pcrm["sequence_from_species_scientific_name"],
                    $pcrm["gene_locus"],
                    $db->getGeneFlyBaseIdentifiers($pcrm["gene_locus"]),
                    $this->normalizeChromosomeName($pcrm["coordinates"]),
                    $pcrm["sequence"]
                );
                $html[] = "\"" . implode("\",\"", $items) . "\"";
            }
        }
        // Reporter Construct
        foreach ( $this->_reporterConstructList as $rc ) {
            $items = array(
                $rc["name"],
                $rc["sequence_from_species_scientific_name"],
                $rc["gene_name"],
                $rc["gene_identifier"],
                $this->normalizeChromosomeName($rc["coordinates"]),
                $rc["sequence"]
            );
            $html[] = "\"" . implode("\",\"", $items) . "\"";
        }
        // Transcription Factor Binding Site
        foreach ( $this->_transcriptionFactorBindingSiteList as $tfbs ) {
            $items = array(
                $tfbs["name"],
                $tfbs["sequence_from_species_scientific_name"],
                $tfbs["gene_name"],
                $tfbs["gene_identifier"],
                $this->normalizeChromosomeName($tfbs["coordinates"]),
                $tfbs["sequence"]
            );
            $html[] = "\"" . implode("\",\"", $items) . "\"";
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
