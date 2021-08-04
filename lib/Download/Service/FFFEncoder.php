<?php
namespace CCR\REDfly\Download\Service;

// REDfly libraries with namespaces
use CCR\REDfly\Service\Message\QueryInterface;
/**
 * Encoding strategy for encoding a Traversable to GBrowse.
 * @link http://flybase.org/.gb2/annotation_help.html#featurefile%20format The
 *     GBrowse feature file specification.
 * Note: it is used ONLY for batch downloads of FFF data
 * belonging to an unique REDfly entity kind
 */
class FFFEncoder implements Encoder
{
    /**
     * @var string $redflyVersion The REDfly version to be written in the header.
     */
    private $redflyVersion;
    /**
     * @var string $date The date to be written in the header.
     */
    private $date;
    /**
     * @var array $data Holds the structure of the file as the data is being
     *     processed before being finalized as a string.
     */
    private $data = [];
    /**
     * @var array $flags Flags indicating whether a configuration block should
     *     be included in the footer.
     */
    private $flags = [];
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
            $result .= $this->header() . PHP_EOL;
            foreach ( $data as $line ) {
                $this->process($line);
            }
            $result .= $this->finalize();
            $result .= $this->footer();
            $this->data = [];
            $this->flags = [];
        }

        return $result;
    }
    private function header()
    {
        return implode(
            PHP_EOL,
            [
                "# Custom annotations from REDfly v" . $this->redflyVersion,
                "# GBrowse Feature File format.",
                "# Date " . $this->date,
            ]
        );
    }
    private function process(array $line)
    {
        require_once(dirname(__FILE__) . "/../../../config/linker.php");
        $baseURL = $GLOBALS["options"]->general->site_base_url;
        $this->data[$line["chromosome"]][] = implode(
            "\t",
            [
                $line["label"],
                $line["name"],
                $line["start"] . ".." . $line["end"],
                "URL=" . $baseURL . "search.php?redfly_id=" . $line["redfly_id"]
            ]
        );
        $this->flags[$line["label"]] = true;
    }
    private function finalize()
    {
        $result = PHP_EOL;
        foreach ( $this->data as $chromosome => $lines ) {
            $result .= "reference=" . $this->normalizeChromosomeName($chromosome) . PHP_EOL;
            $result .= implode(
                PHP_EOL,
                $lines
            ) . PHP_EOL;
        }

        return $result;
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
    private function footer()
    {
        $result = "";
        if ( isset($this->flags["REDfly_CRM"]) ) {
            $result .= PHP_EOL . implode(
                PHP_EOL,
                [
                    "[REDfly_CRM]",
                    "feature = REDfly_CRM",
                    "bgcolor = red",
                    "glyph = generic",
                    "fgcolor = red",
                    "height = 10",
                    "key = Cis Regulatory Module from REDfly"
                ]
            ) . PHP_EOL;
        }
        if ( isset($this->flags["REDfly_CRMS"]) ) {
            $result .= PHP_EOL . implode(
                PHP_EOL,
                [
                    "[REDfly_CRMS]",
                    "feature = REDfly_CRMS",
                    "bgcolor = red",
                    "glyph = generic",
                    "fgcolor = red",
                    "height = 10",
                    "key = Cis Regulatory Module Segment from REDfly"
                ]
            ) . PHP_EOL;
        }
        if ( isset($this->flags["REDfly_PCRM"]) ) {
            $result .= PHP_EOL . implode(
                PHP_EOL,
                [
                    "[REDfly_PCRM]",
                    "feature = REDfly_PCRM",
                    "bgcolor = red",
                    "glyph = generic",
                    "fgcolor = red",
                    "height = 10",
                    "key = Predicted Cis Regulatory Module from REDfly"
                ]
            ) . PHP_EOL;
        }
        if ( isset($this->flags["REDfly_RC"]) ) {
            $result .= PHP_EOL . implode(
                PHP_EOL,
                [
                    "[REDfly_RC]",
                    "feature = REDfly_RC",
                    "bgcolor = blue",
                    "bump = 1",
                    "glyph = generic",
                    "fgcolor = blue",
                    "height = 10",
                    "key = Reporter Construct from REDfly"
                ]
            ) . PHP_EOL;
        }
        if ( isset($this->flags["REDfly_RC_CLO"]) ) {
            $result .= PHP_EOL . implode(
                PHP_EOL,
                [
                    "[REDfly_RC_CLO]",
                    "feature = REDfly_RC_CLO",
                    "bgcolor = green",
                    "bump = 1",
                    "glyph = generic",
                    "fgcolor = green",
                    "height = 10",
                    "key = Reporter Construct (cell-line only) from REDfly"
                ]
            ) . PHP_EOL;
        }
        if ( isset($this->flags["REDfly_TFBS"]) ) {
            $result .= PHP_EOL . implode(
                PHP_EOL,
                [
                    "[REDfly_TFBS]",
                    "feature = REDfly_TFBS",
                    "bgcolor = red",
                    "glyph = generic",
                    "fgcolor = red",
                    "height = 10",
                    "key = TFBS from REDfly"
                ]
            ) . PHP_EOL;
        }

        return $result;
    }
}
