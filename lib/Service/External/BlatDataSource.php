<?php
namespace CCR\REDfly\Service\External;

// Standard PHP Libraries (SPL)
use RuntimeException;
use Psr\Http\Message\StreamInterface;
// Third-party libraries
use GuzzleHttp\ClientInterface;
use League\Csv\Reader;
use League\Csv\Statement;
// REDfly libraries with namespaces
use CCR\REDfly\Service\External\Model\Alignment;
/**
 * Data source for sending individual and batch queries to a BLAT endpoint.
 * See https://genome.ucsc.edu/cgi-bin/hgBlat for more details.
 */
class BlatDataSource
{
    /**
     * @var ClientInterface $client Guzzle client.
     */
    private $client;
    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }
    /**
     * Queries the BLAT endpoint using a FASTA file and returns the results in
     * PSLX format.
     * See http://genome.ucsc.edu/FAQ/FAQformat.html#format2 for details on the
     * PSLX file format.
     * @param string $speciesShortName The species short name.
     * @param string $fasta The FASTA file URI.
     * @return iterable The alignments returned from the query.
     */
    public function batchQuery(
        string $speciesShortName,
        string $fastaFile
    ): iterable {
        // Making a multipart/form-data request
        $pslxStreamInterface = $this->client->request(
            "POST",
            "",
            [
                "multipart" => [
                    [
                        "name"     => "speciesShortName",
                        "contents" => $speciesShortName,
                    ],
                    [
                        "name"     => "input",
                        "contents" => fopen($fastaFile, "r"),
                        "filename" => "input.fa"
                    ],
                ]
            ]
        )->getBody();

        return $this->parsePslxStreamInterface($pslxStreamInterface);
    }
    /**
     * Queries the BLAT endpoint with a single sequence and returns the results
     * in PSLX format.
     * See http://genome.ucsc.edu/FAQ/FAQformat.html#format2 for details on the
     * PSLX file format.
     * @param string $speciesShortName The species short name.
     * @param string $sequence The nucleic acid sequence.
     * @return iterable The alignments returned from the query.
     */
    public function query(
        string $speciesShortName,
        string $sequence
    ): iterable {
        $fastaFile = tmpfile();
        if ( $fastaFile === false ) {
            throw new RuntimeException("Failed to create temporary FASTA file.");
        }
        fwrite(
            $fastaFile,
            ">query" . PHP_EOL . $sequence . PHP_EOL
        );
        // Making a multipart/form-data request
        $pslxStreamInterface = $this->client->request(
            "POST",
            "",
            [
                "multipart" => [
                    [
                        "name"     => "speciesShortName",
                        "contents" => $speciesShortName,
                    ],
                    [
                        "name"     => "input",
                        "contents" => $fastaFile,
                        "filename" => "input.fa"
                    ],
                ]
            ]
        )->getBody();

        return $this->parsePslxStreamInterface($pslxStreamInterface);
    }
    private function parsePslxStreamInterface(StreamInterface $pslxStreamInterface): iterable
    {
        $pslxStream = $pslxStreamInterface->detach();
        if ( $pslxStream === null ) {
            throw new RuntimeException("Failed to open PSLX stream.");
        }
        $reader = Reader::createFromStream($pslxStream)
            ->setDelimiter("\t")
            ->skipEmptyRecords();
        $statement = new Statement();
        foreach ( $statement->process($reader) as $row ) {
            yield $row[9] => $this->buildAlignment($row);
        }
    }
    private function buildAlignment(array $record): Alignment
    {
        $strand = $record[8];
        if ( strtolower(substr($record[13], 0, 3)) === "chr" ) {
            $chromosomeName = substr($record[13], 3);
        } else {
            $chromosomeName = $record[13];
        }
        $startCoordinate = $record[15];
        $endCoordinate = $record[16];
        $sequence = trim(strtoupper($record[22]), ",");
        $matchesNumber = $record[0];
        $repeatMatchesNumber = $record[2];
        $mismatchesNumber = $record[1];
        $queryInsertsNumber = $record[4];
        $targetInsertsNumber = $record[6];

        return new Alignment(
            $strand,
            $chromosomeName,
            $startCoordinate,
            $endCoordinate,
            $sequence,
            $matchesNumber,
            $repeatMatchesNumber,
            $mismatchesNumber,
            $queryInsertsNumber,
            $targetInsertsNumber
        );
    }
}
