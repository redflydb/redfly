<?php
// --------------------------------------------------------------------------------
// Download a FASTA file containing one or more data from one or more
// entities which kind can be same or different.
// The limit of downloadable same-kind entities number imposed by the
// front-end interface is 500 due to its page size.
// The limit of downloadable different-kind entities number imposed by
// the front-end interface is 4 x 500 = 2000 due to its page size.
// --------------------------------------------------------------------------------
class ExportFile_Fasta extends ExportFile
{
    const FILE_EXT = "fasta";
    const FLANK_ONLY = "flank";
    const MIME_TYPE = "text/plain";
    const SEQ_AND_FLANK = "both";
    const SEQUENCE_ONLY = "seq";
    // Option for sequence downloads. See list of constants above
    private $_sequenceOption = self::SEQUENCE_ONLY;
    public static function factory(array $options = null)
    {
        return new ExportFile_Fasta($options ?? []);
    }
    protected function __construct(array $options)
    {
        parent::__construct(
            self::MIME_TYPE,
            self::FILE_EXT,
            $options
        );
        $this->parseOptions($options);
    }
    // --------------------------------------------------------------------------------
    // Parse the available options and extract any option that are specific to this
    // class.
    // @param $options An array containing the options where the key is the option
    //   name.
    // --------------------------------------------------------------------------------
    private function parseOptions(array $options = null)
    {
        if ( $options === null ) {
            return;
        }
        foreach ( $options as $name => $value ) {
            if ( ($value !== false ) &&
              (($value === null) || ($value === "" )) ) {
                continue;
            }
            switch ( $name ) {
                case "fasta_seq":
                    $this->_sequenceOption = $value;
                    break;
                default:
                    break;
            }
        }
    }
    public function help()
    {
        $parentHelp = parent::help();
        $options = $parentHelp->results();
        $description = "Export REDfly data in the FASTA format";
        $options["fasta_seq"] = "Sequence specification for download (default = " .
            self::SEQUENCE_ONLY . ") options are: " .
            self::SEQUENCE_ONLY . ", " . self::FLANK_ONLY . ", " . self::SEQ_AND_FLANK;

        return RestResponse::factory(
            true,
            $description,
            $options
        );
    }
    // --------------------------------------------------------------------------------
    // Format the sequence for display, typically 80 characters per line.
    // @param $sequence The sequence string
    // @param $width The width of each line to display
    // @param $eolChar The end-of-line character
    // @returns The sequence formatted according the the request.
    // --------------------------------------------------------------------------------
    private function formatSequenceForDisplay(
        $sequence,
        $width = 80,
        $eolChar = "\n"
    ) {
        return implode(
            $eolChar,
            str_split(
                $sequence,
                $width
            )
        );
    }
    // --------------------------------------------------------------------------------
    // @see aExportFile::generateFileBody()
    // --------------------------------------------------------------------------------
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
            $line = ">" . $crms["redfly_id"] . "|" .
                $crms["name"] . "|" .
                $crms["sequence_from_species_scientific_name"] . "|" .
                $crms["gene_name"] . "|" .
                $crms["gene_identifier"] . "|" .
                $this->normalizeChromosomeName($crms["coordinates"]) . "\n" .
                $this->formatSequenceForDisplay($crms["sequence"]);
            $html[] = $line;
        }
        // Predicted Cis Regulatory Module
        if ( count($this->_predictedCisRegulatoryModuleList) > 0 ) {
            $db = DbService::factory();
            foreach ( $this->_predictedCisRegulatoryModuleList as $pcrm ) {
                $line = ">" . $pcrm["redfly_id"] . "|" .
                    $pcrm["name"] . "|" .
                    $pcrm["sequence_from_species_scientific_name"] . "|" .
                    $pcrm["gene_locus"] . "|" .
                    $db->getGeneFlyBaseIdentifiers($pcrm["gene_locus"]) . "|" .
                    $this->normalizeChromosomeName($pcrm["coordinates"]) . "\n" .
                    $this->formatSequenceForDisplay($pcrm["sequence"]);
                $html[] = $line;
            }
        }
        // Reporter Construct
        foreach ( $this->_reporterConstructList as $rc ) {
            $line = ">" . $rc["redfly_id"] . "|" .
                $rc["name"] . "|" .
                $rc["sequence_from_species_scientific_name"] . "|" .
                $rc["gene_name"] . "|" .
                $rc["gene_identifier"] . "|" .
                $this->normalizeChromosomeName($rc["coordinates"]) . "\n" .
                $this->formatSequenceForDisplay($rc["sequence"]);
            $html[] = $line;
        }
        // Transcription Factor Binding Site
        foreach ( $this->_transcriptionFactorBindingSiteList as $tfbs ) {
            $line = ">" . $tfbs["redfly_id"] . "|" .
                $tfbs["name"] . "|" .
                $tfbs["sequence_from_species_scientific_name"] . "|" .
                $tfbs["gene_name"] . "|" .
                $tfbs["gene_identifier"] . "|" .
                $this->normalizeChromosomeName($tfbs["coordinates"]);
            if ( ($this->_sequenceOption === self::SEQ_AND_FLANK) ||
               ($this->_sequenceOption === self::SEQUENCE_ONLY) ) {
                $html[] = $line . "\n" . $this->formatSequenceForDisplay($tfbs["sequence"]);
            }
            if ( ($this->_sequenceOption === self::SEQ_AND_FLANK) ||
               ($this->_sequenceOption === self::FLANK_ONLY) ) {
                $html[] = $line . "|with flank\n" . $this->formatSequenceForDisplay($tfbs["sequence_with_flank"]);
            }
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
