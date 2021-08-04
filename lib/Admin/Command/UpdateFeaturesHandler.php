<?php
namespace CCR\REDfly\Admin\Command;

// Third-party libraries
use GuzzleHttp\ClientInterface;
use ParagonIE\EasyDB\EasyDB;
/**
 * The command handler for updating the features in the database to synchronize the
 * data with the FlyBase database.
 */
class UpdateFeaturesHandler
{
    private $db;
    private $client;
    public function __construct(
        EasyDB $db,
        ClientInterface $client
    ) {
        $this->db = $db;
        $this->client = $client;
    }
    public function __invoke(UpdateFeatures $command): void
    {
        ini_set("memory_limit", "512M");
        $featureTypes = [
            "exon",
            "intron",
            "mrna"
        ];
        foreach ( $featureTypes as $featureTypesKey => $featureType ) {
            $featureData = json_decode(
                $this->client->get("/features/" . $featureType)->getBody(),
                true
            );
            foreach ( $featureData as $featureDataKey => $feature ) {
                $this->db->insert(
                    "staging_feature_update",
                    [
                        "type"       => $feature["feature_type"],
                        "start"      => intval($feature["start"]),
                        "end"        => intval($feature["end"]),
                        "strand"     => $feature["strand"],
                        "identifier" => $feature["id"],
                        "name"       => $feature["name"],
                        "parent"     => $feature["parent"]
                    ]
                );
            }
        }
        $returnValue = $this->db->exec("CALL update_features(@new_mrna_features_number,
            @new_exon_and_intron_features_number);");
        header("Content-Type: text/plain");
        if ( $returnValue === false ) {
            print sprintf(
                "Error updating features: %s",
                $this->db->errorInfo()
            ) . PHP_EOL;
        } else {
            $outputArguments = $this->db->query("SELECT @new_mrna_features_number,
                @new_exon_and_intron_features_number;");
            $resultsRow = $outputArguments->fetch();
            print sprintf(
                "Success. %d new mrna features and " .
                "%d new exon and intron features",
                $resultsRow["@new_mrna_features_number"],
                $resultsRow["@new_exon_and_intron_features_number"]
            ) . PHP_EOL;
            // Cleaning the unbuffered query for the next statement
            $resultsRow = $outputArguments->fetchAll();
            $this->db->exec("DELETE FROM staging_feature_update;");
        }
    }
}
