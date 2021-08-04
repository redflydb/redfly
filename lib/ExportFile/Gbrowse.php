<?php
// ----------------------------------------------------------------------
// The limit of downloadable same-kind entities number imposed by the
// front-end interface is 500 due to its page size.
// The limit of downloadable different-kind entities number imposed by
// the front-end interface is 4 x 500 = 2000 due to its page size.
// The file is generated using the Gbrowse Feature File format described
// at: http://flybase.org/.gb2/annotation_help.html#featurefile%20format
// ----------------------------------------------------------------------
class ExportFile_Gbrowse extends ExportFile
{
    const CRM_KEY = "REDfly_CRM";
    const CRMS_KEY = "REDfly_CRMS";
    const FILE_EXT = "fff";
    const MIME_TYPE = "text/plain";
    const PCRM_KEY = "REDfly_PCRM";
    const RC_CCO_KEY = "REDfly_RC_CCO";
    const RC_KEY = "REDfly_RC";
    const TFBS_KEY = "REDfly_TFBS";
    // Array of CRMs to include in the output
    protected $_crmList = array();
    public static function factory(array $options = null)
    {
        return new ExportFile_Gbrowse($options ?? []);
    }
    protected function __construct(array $options)
    {
        parent::__construct(
            self::MIME_TYPE,
            self::FILE_EXT,
            $options
        );
    }
    // --------------------------------------------------------------------------------
    // Set the reporter constructs to download
    // @param $list An array containing the results of a reporter construct search
    // @override
    // --------------------------------------------------------------------------------
    public function setReporterConstructs(array $list)
    {
        foreach ( $list as $rc ) {
            if ( $rc["is_crm"] ) {
                $this->_crmList[] = $rc;
            } else {
                $this->_reporterConstructList[] = $rc;
            }
        }
    }
    public function help()
    {
        $parentHelp = parent::help();
        $description = "Export REDfly data in the FFF format";

        return RestResponse::factory(
            true,
            $description,
            $parentHelp->results()
        );
    }
    public function generateFileHeader()
    {
        $html = array();
        $html[] = "# Custom annotations from REDfly v" . $GLOBALS["options"]->general->redfly_version;
        $html[] = "# Gbrowse Feature File format. See http://flybase.org/.gb2/annotation_help.html#featurefile%20format";
        $html[] = "# Date " . date("Ymd") . "\n";
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
        require_once(dirname(__FILE__) . "/../../config/linker.php");
        $baseURL = $GLOBALS["options"]->general->site_base_url;
        // Cis Regulatory Module Segment
        $currentChromosome = null;
        foreach ( $this->_cisRegulatoryModuleSegmentList as $crms ) {
            $chromosome = $crms["chromosome"];
            if ( $currentChromosome !== $chromosome ) {
                $html[] = "reference=" . $this->normalizeChromosomeName($chromosome);
                $currentChromosome = $chromosome;
            }
            $line = self::CRMS_KEY . "\t" .
                addslashes($crms["name"]) . "\t" .
                $crms["start"] . ".." . $crms["end"] . "\t" .
                "URL=" . $baseURL . "search.php?redfly_id=" . $crms["redfly_id"];
            $html[] = $line;
        }
        // Predicted Cis Regulatory Module
        $currentChromosome = null;
        foreach ( $this->_predictedCisRegulatoryModuleList as $pcrm ) {
            $chromosome = $pcrm["chromosome"];
            if ( $currentChromosome !== $chromosome ) {
                $html[] = "reference=" . $this->normalizeChromosomeName($chromosome);
                $currentChromosome = $chromosome;
            }
            $line = self::PCRM_KEY . "\t" .
                addslashes($pcrm["name"]) . "\t" .
                $pcrm["start"] . ".." . $pcrm["end"] . "\t" .
                "URL=" . $baseURL . "search.php?redfly_id=" . $pcrm["redfly_id"];
            $html[] = $line;
        }
        // Reporter Construct
        $currentChromosome = null;
        foreach ( $this->_reporterConstructList as $rc ) {
            $chromosome = $rc["chromosome"];
            if ( $currentChromosome !== $chromosome ) {
                $html[] = "reference=" . $this->normalizeChromosomeName($chromosome);
                $currentChromosome = $chromosome;
            }
            $line = self::RC_KEY . "\t" .
                addslashes($rc["name"]) . "\t" .
                $rc["start"] . ".." . $rc["end"] . "\t" .
                "URL=" . $baseURL . "search.php?redfly_id=" . $rc["redfly_id"];
            $html[] = $line;
        }
        // Reporter Construct Having Cell Culture Only
        $currentChromosome = null;
        foreach ( $this->_reporterConstructCellCultureOnlyList as $rc ) {
            $chromosome = $rc["chromosome"];
            if ( $currentChromosome !== $chromosome ) {
                $html[] = "reference=" . $this->normalizeChromosomeName($chromosome);
                $currentChromosome = $chromosome;
            }
            $line = self::RC_CCO_KEY . "\t" .
                addslashes($rc["name"]) . "\t" .
                $rc["start"] . ".." . $rc["end"] . "\t" .
                "URL=" . $baseURL . "search.php?redfly_id=" . $rc["redfly_id"];
            $html[] = $line;
        }
        // Reporter Construct Qualified as CRM
        $currentChromosome = null;
        foreach ( $this->_crmList as $crm ) {
            $chromosome = $crm["chromosome"];
            if ( $currentChromosome !== $chromosome ) {
                $html[] = "reference=" . $this->normalizeChromosomeName($chromosome);
                $currentChromosome = $chromosome;
            }
            $line = self::CRM_KEY . "\t" .
                addslashes($crm["name"]) . "\t" .
                $crm["start"] . ".." . $crm["end"] . "\t" .
                "URL=" . $baseURL . "search.php?redfly_id=" . $crm["redfly_id"];
            $html[] = $line;
        }
        // Transcription Factor Binding Site
        $currentChromosome = null;
        foreach ( $this->_transcriptionFactorBindingSiteList as $tfbs ) {
            $chromosome = $tfbs["chromosome"];
            if ( $currentChromosome !== $chromosome ) {
                $html[] = "reference=" . $this->normalizeChromosomeName($chromosome);
                $currentChromosome = $chromosome;
            }
            $line = self::TFBS_KEY . "\t" .
                addslashes($tfbs["name"]) . "\t" .
                $tfbs["start"] . ".." . $tfbs["end"] . "\t" .
                "URL=" . $baseURL . "search.php?redfly_id=" . $tfbs["redfly_id"];
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
    public function generateFileFooter()
    {
        require_once(dirname(__FILE__) . "/../../config/linker.php");
        $baseURL = $GLOBALS["options"]->general->site_base_url;
        $html = array();
        // Cis Regulatory Module Segments
        if ( count($this->_cisRegulatoryModuleSegmentList) > 0 ) {
            // blank line
            $html[] = "";
            $html[] = "[" . self::CRMS_KEY . "]";
            $html[] = "feature       = " . self::CRMS_KEY;
            $html[] = "bgcolor       = yellow";
            $html[] = "bump          = 1";
            $html[] = "glyph         = generic";
            $html[] = "fgcolor       = yellow";
            $html[] = "height        = 10";
            $html[] = "key           = Cis Regulatory Module Segment from REDfly";
            if ( count($this->_cisRegulatoryModuleSegmentList) === 1 ) {
                foreach ( $this->_cisRegulatoryModuleSegmentList as $crms ) {
                    $entityName = $crms["name"];
                }
                $html[] = "link          = ". $baseURL . "search.php?name=" . $entityName . "\n";
            }
        }
        // Predicted Cis Regulatory Modules
        if ( count($this->_predictedCisRegulatoryModuleList) > 0 ) {
            // blank line
            $html[] = "";
            $html[] = "[" . self::PCRM_KEY . "]";
            $html[] = "feature       = " . self::PCRM_KEY;
            $html[] = "bgcolor       = magenta";
            $html[] = "bump          = 1";
            $html[] = "glyph         = generic";
            $html[] = "fgcolor       = magenta";
            $html[] = "height        = 10";
            $html[] = "key           = Predicted Cis Regulatory Module from REDfly";
            if ( count($this->_predictedCisRegulatoryModuleList) === 1 ) {
                foreach ( $this->_predictedCisRegulatoryModuleList as $pcrm ) {
                    $entityName = $pcrm["name"];
                }
                $html[] = "link          = ". $baseURL . "search.php?name=" . $entityName . "\n";
            }
        }
        // Reporter Constructs
        if ( count($this->_reporterConstructList) > 0 ) {
            // blank line
            $html[] = "";
            $html[] = "[" . self::RC_KEY . "]";
            $html[] = "feature       = " . self::RC_KEY;
            $html[] = "bgcolor       = blue";
            $html[] = "bump          = 1";
            $html[] = "glyph         = generic";
            $html[] = "fgcolor       = blue";
            $html[] = "height        = 10";
            $html[] = "key           = Reporter Construct from REDfly";
            if ( count($this->_reporterConstructList) === 1 ) {
                foreach ( $this->_reporterConstructList as $rc ) {
                    $entityName = $rc["name"];
                }
                $html[] = "link          = ". $baseURL . "search.php?name=" . $entityName . "\n";
            }
        }
        // Reporter Constructs Having Cell Culture Only
        if ( count($this->_reporterConstructCellCultureOnlyList) > 0 ) {
            // blank line
            $html[] = "";
            $html[] = "[" . self::RC_CCO_KEY . "]";
            $html[] = "feature       = " . self::RC_CCO_KEY;
            $html[] = "bgcolor       = green";
            $html[] = "bump          = 1";
            $html[] = "glyph         = generic";
            $html[] = "fgcolor       = green";
            $html[] = "height        = 10";
            $html[] = "key           = Reporter Construct (cell culture only) from REDfly";
            if ( count($this->_reporterConstructCellCultureOnlyList) === 1 ) {
                foreach ( $this->_reporterConstructCellCultureOnlyList as $rc ) {
                    $entityName = $rc["name"];
                }
                $html[] = "link          = ". $baseURL . "search.php?name=" . $entityName . "\n";
            }
        }
        // Reporter Constructs Qualified as CRM
        if ( count($this->_crmList) > 0 ) {
            // blank line
            $html[] = "";
            $html[] = "[" . self::CRM_KEY . "]";
            $html[] = "feature       = " . self::CRM_KEY;
            $html[] = "bgcolor       = red";
            $html[] = "glyph         = generic";
            $html[] = "fgcolor       = red";
            $html[] = "height        = 10";
            $html[] = "key           = Cis-Regulatory Module from REDfly";
            if ( count($this->_crmList) === 1 ) {
                foreach ( $this->_crmList as $rc ) {
                    $entityName = $rc["name"];
                }
                $html[] = "link          = ". $baseURL . "search.php?name=" . $entityName . "\n";
            }
        }
        // Transcription Factor Binding Sites
        if ( count($this->_transcriptionFactorBindingSiteList) > 0 ) {
            // blank line
            $html[] = "";
            $html[] = "[" . self::TFBS_KEY . "]";
            $html[] = "feature       = " . self::TFBS_KEY;
            $html[] = "bgcolor       = cyan";
            $html[] = "glyph         = generic";
            $html[] = "fgcolor       = cyan";
            $html[] = "height        = 10";
            $html[] = "key           = Transcription Factor Binding Site from REDfly";
            if ( count($this->_transcriptionFactorBindingSiteList) === 1 ) {
                foreach ( $this->_transcriptionFactorBindingSiteList as $tfbs ) {
                    $entityName = $tfbs["name"];
                }
                $html[] = "link          = ". $baseURL . "search.php?name=" . $entityName . "\n";
            }
        }
        $this->_footer = implode("\n", $html) . "\n";
        $this->_footerLength = strlen($this->_footer);
    }
}
