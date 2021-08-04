<?php
namespace CCR\REDfly\Download\Service;

use UnexpectedValueException;
/**
 * A simple factory that returns the appropriate Encoder object for a file
 * format.
 */
class EncoderFactory
{
    /**
     * @var string $redflyVersion Current REDfly version.
     */
    private $redflyVersion;
    public function __construct(string $redflyVersion)
    {
        $this->redflyVersion = $redflyVersion;
    }
    /**
     * Creates and returns the appropriate Encoder for the given file format.
     * @param string $fileFormat The file format to get the encoder for.
     * @return Encoder The encoder for the specified file format.
     */
    public function create($fileFormat)
    {
        switch ( $fileFormat ) {
            case "BED":
                return new BEDEncoder();
            case "CSV":
                return new CSVEncoder();
            case "FASTA":
                return new FASTAEncoder();
            case "GBrowse":
                return new FFFEncoder(
                    $this->redflyVersion,
                    date("Ymd")
                );
            case "GFF3":
                return new GFF3Encoder(
                    $this->redflyVersion,
                    date("Ymd")
                );
            default:
                throw new UnexpectedValueException($fileFormat);
        }
    }
}
