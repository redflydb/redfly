<?php
// ----------------------------------------------------------------------
// Download a BED file containing one or more data from one or more
// entities which kind can be same or different.
// The limit of downloadable same-kind entities number imposed by the
// front-end interface is 500 due to its page size.
// The limit of downloadable different-kind entities number imposed by
// the front-end interface is 4 x 500 = 2000 due to its page size.
// The file is generated using the BED format described
// at: http://genome.ucsc.edu/FAQ/FAQformat.html#format1
// ----------------------------------------------------------------------
class ExportFile_Bed extends ExportFile
{
    const FILE_EXT = "bed";
    const MIME_TYPE = "text/plain";
    private $_file_type = "browser";
    private $_track_description = "Description";
    private $_track_name = "Name";
    public static function factory(array $options = null)
    {
        return new ExportFile_Bed($options);
    }
    protected function __construct(array $options = null)
    {
        parent::__construct(
            self::MIME_TYPE,
            self::FILE_EXT,
            $options ?? []
        );
        $this->parseOptions($options);
    }
    private function parseOptions(array $options = null)
    {
        if ( $options === null ) {
            return;
        }
        foreach ( $options as $name => $value ) {
            if ( ($value !== false ) &&
                (($value === null) || ($value === "")) ) {
                continue;
            }
            switch ( $name ) {
                case "bed_file_type":
                    $this->_file_type = $value;
                    break;
                case "bed_track_description":
                    $this->_track_description = $value;
                    break;
                case "bed_track_name":
                    $this->_track_name = $value;
                    break;
                default:
                    break;
            }
        }
    }
    public function help()
    {
        $parentHelp = parent::help();
        $description = "Export REDfly data in the BED format";

        return RestResponse::factory(
            true,
            $description,
            $parentHelp->results()
        );
    }
    // This function is used to check if $input_list has unique chromosome.
    // If the chromosome in the list is unique, then we can set the browser to
    // display the chromosome within a range.
    private function uniqueChromsomes(array $input_list)
    {
        $chromosomes = array();
        foreach ( $input_list as $element ) {
            $chromosomes[] = $element["chromosome"];
        }
        if ( (count(array_unique($chromosomes)) === 1) &&
            end($chromosomes) ) {
            return array(
                true,
                end($chromosomes)
            );
        } else {
            return array(
                false,
                "multiple"
            );
        }
    }
    private function getChromosomeRange(array $input_list)
    {
        $values = array();
        foreach ( $input_list as $element ) {
            array_push(
                $values,
                (int)$element["start"],
                (int)$element["end"]
            );
        }

        return array (
            min($values) - 1,
            max($values)
        );
    }
    public function generateFileHeader()
    {
        $html = array();
        // Add BED specific annotations
        // browser position
        // Get range of items.
        // If the chromosomes differ, then skip adding browser position.
        if ( $this->_file_type === "browser" ) {
            $combinedEntities = array_merge(
                $this->_cisRegulatoryModuleSegmentList,
                $this->_predictedCisRegulatoryModuleList,
                $this->_reporterConstructList,
                $this->_transcriptionFactorBindingSiteList
            )
            ;
            list (
                $is_unique,
                $chrom_name
            ) = $this->uniqueChromsomes($combinedEntities);
            if ( $is_unique ) {
                list (
                    $min_range,
                    $max_range
                ) = $this->getChromosomeRange($combinedEntities);
                $html[] = "browser position " . $this->normalizeChromosomeName($chrom_name) . ":" . $min_range . "-" . $max_range;
            }
            // add track description and options
            $track_name  = $this->_track_name;
            $track_description = $this->_track_description;
            $html[] = "track name=\"$track_name\" description=\"$track_description\" visibility=3";
            $headings = array(
                "chrom",
                "chromStart",
                "chromEnd",
                "name",
                "score",
                "strand",
                "thickStart",
                "thickEnd"
            );
            $html[] = "#". implode(
                "\t",
                $headings
            );
            $this->_header = implode(
                "\n",
                $html
            ) . "\n";
            $this->_headerLength = strlen($this->_header);
        }
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
            $normalizedChromosomeName = $this->normalizeChromosomeName($crms["chromosome"]);
            if ( $this->_file_type === "browser" ) {
                $items = array(
                    $normalizedChromosomeName,
                    (int)$crms["start"] - 1,
                    $crms["end"],
                    $crms["name"],
                    "0",
                    ".",
                    (int)$crms["start"] - 1,
                    $crms["end"]
                );
            } else {
                $items = array(
                    $normalizedChromosomeName,
                    (int)$crms["start"] - 1,
                    $crms["end"],
                    $crms["name"]
                );
            }
            $html[] = implode(
                "\t",
                $items
            );
        }
        // Predicted Cis Regulatory Module
        foreach ( $this->_predictedCisRegulatoryModuleList as $pcrm ) {
            $normalizedChromosomeName = $this->normalizeChromosomeName($pcrm["chromosome"]);
            if ( $this->_file_type === "browser" ) {
                $items = array(
                    $normalizedChromosomeName,
                    (int)$pcrm["start"] - 1,
                    $pcrm["end"],
                    $pcrm["name"],
                    "0",
                    ".",
                    (int)$pcrm["start"] - 1,
                    $pcrm["end"]
                );
            } else {
                $items = array(
                    $normalizedChromosomeName,
                    (int)$pcrm["start"] - 1,
                    $pcrm["end"],
                    $pcrm["name"]
                );
            }
            $html[] = implode(
                "\t",
                $items
            );
        }
        // Reporter Construct
        foreach ( $this->_reporterConstructList as $rc ) {
            $normalizedChromosomeName = $this->normalizeChromosomeName($rc["chromosome"]);
            if ( $this->_file_type === "browser" ) {
                $items = array(
                    $normalizedChromosomeName,
                    (int)$rc["start"] - 1,
                    $rc["end"],
                    $rc["name"],
                    "0",
                    ".",
                    (int)$rc["start"] - 1,
                    $rc["end"]
                );
            } else {
                $items = array(
                    $normalizedChromosomeName,
                    (int)$rc["start"] - 1,
                    $rc["end"],
                    $rc["name"]
                );
            }
            $html[] = implode(
                "\t",
                $items
            );
        }
        // Transcription Factor Binding Site
        foreach ( $this->_transcriptionFactorBindingSiteList as $tfbs ) {
            $normalizedChromosomeName = $this->normalizeChromosomeName($tfbs["chromosome"]);
            if ( $this->_file_type === "browser" ) {
                $items = array(
                    $normalizedChromosomeName,
                    (int)$tfbs["start"] - 1,
                    $tfbs["end"],
                    $tfbs["name"],
                     "0",
                     ".",
                    (int)$tfbs["start"] - 1,
                    $tfbs["end"]
                );
            } else {
                $items = array(
                    $normalizedChromosomeName,
                    (int)$tfbs["start"] - 1,
                    $tfbs["end"],
                    $tfbs["name"]
                );
            }

            $html[] = implode(
                "\t",
                $items
            );
        }
        $this->_body = implode(
            "\n",
            $html
        ) . "\n";
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
