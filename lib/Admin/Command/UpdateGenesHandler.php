<?php
namespace CCR\REDfly\Admin\Command;

// Third-party libraries
use GuzzleHttp\ClientInterface;
use ParagonIE\EasyDB\EasyDB;
/**
 * The command handler for updating the genes in the database to synchronize the
 * data with the database(s) as REDfly and VectorBase.
 */
class UpdateGenesHandler
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
    public function __invoke(UpdateGenes $command): void
    {
        $this->db->exec("DELETE FROM staging_gene_update;");
        $data = json_decode(
            $this->client->get("/genes")->getBody(),
            true
        );
        $this->db->insertMany(
            "staging_gene_update",
            array_map(
                function ($gene) {
                    return [
                        "species_short_name"              => $gene["species_short_name"],
                        "genome_assembly_release_version" => $gene["genome_assembly_release_version"],
                        "identifier"                      => $gene["identifier"],
                        "name"                            => $gene["term"],
                        "chromosome_name"                 => $gene["chromosome_name"],
                        "start"                           => $gene["start"],
                        "end"                             => $gene["end"],
                        "strand"                          => $gene["strand"]
                    ];
                },
                $data
            )
        );
        // Provisionally only with the Drosophila melanogaster species
        // since its ontologies change the most than the other species
        $species = array();
        $species[0]["species_id"] = 1;
        $species[0]["scientific_name"] = "Drosophila melanogaster";
        $species[0]["short_name"] = "dmel";
        $species[0]["genome_assembly_id"] = 4;
        $species[0]["release_version"] = "dm6";
        $returnValue = $this->db->exec("CALL update_genes(" .
            $species[0]["species_id"] . ",'" .
            $species[0]["short_name"] . "'," .
            $species[0]["genome_assembly_id"] . ",'" .
            $species[0]["release_version"] . "', 
            @deleted_genes_number,
            @identifiers,
            @old_names,
            @new_names,
            @updated_genes_number_with_new_name,
            @old_identifiers,
            @new_identifiers,
            @updated_genes_number_with_new_identifier,
            @renamed_crm_segment_names,
            @updated_crm_segments_number_with_new_gene_name,
            @renamed_reporter_construct_names,
            @updated_reporter_constructs_number_with_new_gene_name,
            @renamed_transcription_factor_binding_site_names_by_transcription_factor,
            @updated_transcription_factor_binding_sites_number_with_new_transcription_factor_name,
            @renamed_transcription_factor_binding_site_names_by_gene,
            @updated_transcription_factor_binding_sites_number_with_new_gene_name,
            @new_genes_number);");
        header("Content-Type: text/plain");
        if ( $returnValue === false ) {
            print sprintf(
                "Error updating genes: %s",
                $this->db->errorInfo()
            ) . PHP_EOL;
        } else {
            $outputArguments = $this->db->query("SELECT 
                @deleted_genes_number,
                @identifiers,
                @old_names,
                @new_names,
                @updated_genes_number_with_new_name,
                @old_identifiers,
                @new_identifiers,
                @updated_genes_number_with_new_identifier,
                @renamed_crm_segment_names,
                @updated_crm_segments_number_with_new_gene_name,
                @renamed_reporter_construct_names,
                @updated_reporter_constructs_number_with_new_gene_name,
                @renamed_transcription_factor_binding_site_names_by_transcription_factor,
                @updated_transcription_factor_binding_sites_number_with_new_transcription_factor_name,
                @renamed_transcription_factor_binding_site_names_by_gene,
                @updated_transcription_factor_binding_sites_number_with_new_gene_name,
                @new_genes_number;");
            $resultsRow = $outputArguments->fetch();
            $identifiers = explode(
                "\t",
                $resultsRow["@identifiers"]
            );
            $oldNames = explode(
                "\t",
                $resultsRow["@old_names"]
            );
            $newNames = explode(
                "\t",
                $resultsRow["@new_names"]
            );
            $list = "";
            for ( $index = 0; $index < count($identifiers); $index++
            ) {
                $list .= $identifiers[$index] . "\t" .
                    $oldNames[$index] . "\t" .
                    $newNames[$index] . PHP_EOL;
            }
            if ( $identifiers[0] !== "" ) {
                print "The gene identifiers have changed their " .
                    "old names into the new names in the following list: " .
                    PHP_EOL;
                print $list;
            }
            $oldIdentifiers = explode(
                "\t",
                $resultsRow["@old_identifiers"]
            );
            $newIdentifiers = explode(
                "\t",
                $resultsRow["@new_identifiers"]
            );
            $list = "";
            for ( $index = 0; $index < count($oldIdentifiers); $index++
            ) {
                $list .= $oldIdentifiers[$index] . "\t" .
                    $newIdentifiers[$index] . PHP_EOL;
            }
            if ( $oldIdentifiers[0] !== "" ) {
                print "The genes have changed their " .
                    "old identifiers into the new identifiers in the following list: " .
                    PHP_EOL;
                print $list;
            }
            $renamedCRMSegmentNames = explode(
                "\t",
                $resultsRow["@renamed_crm_segment_names"]
            );
            $list = "";
            for ( $index = 0; $index < count($renamedCRMSegmentNames); $index++
            ) {
                $list .= $renamedCRMSegmentNames[$index] . PHP_EOL;
            }
            if ( $renamedCRMSegmentNames[0] !== "" ) {
                print "The CRM segment names renamed are in the following list: " .
                    PHP_EOL;
                print $list;
            }
            $renamedReporterConstructNames = explode(
                "\t",
                $resultsRow["@renamed_reporter_construct_names"]
            );
            $list = "";
            for ( $index = 0; $index < count($renamedReporterConstructNames); $index++
            ) {
                $list .= $renamedReporterConstructNames[$index] . PHP_EOL;
            }
            if ( $renamedReporterConstructNames[0] !== "" ) {
                print "The reporter construct names renamed are in the following list: " .
                    PHP_EOL;
                print $list;
            }
            $renamedTranscriptionFactorBindingSiteNamesByTranscriptionFactor = explode(
                "\t",
                $resultsRow["@renamed_transcription_factor_binding_site_names_by_transcription_factor"]
            );
            $list = "";
            for ( $index = 0; $index < count($renamedTranscriptionFactorBindingSiteNamesByTranscriptionFactor); $index++
            ) {
                $list .= $renamedTranscriptionFactorBindingSiteNamesByTranscriptionFactor[$index] . PHP_EOL;
            }
            if ( $renamedTranscriptionFactorBindingSiteNamesByTranscriptionFactor[0] !== "" ) {
                print "The transcription factor binding site names renamed by transcription factor are in the following list: " .
                    PHP_EOL;
                print $list;
            }
            $renamedTranscriptionFactorBindingSiteNamesByGene = explode(
                "\t",
                $resultsRow["@renamed_transcription_factor_binding_site_names_by_gene"]
            );
            $list = "";
            for ( $index = 0; $index < count($renamedTranscriptionFactorBindingSiteNamesByGene); $index++
            ) {
                $list .= $renamedTranscriptionFactorBindingSiteNamesByGene[$index] . PHP_EOL;
            }
            if ( $renamedTranscriptionFactorBindingSiteNamesByGene[0] !== "" ) {
                print "The transcription factor binding site names renamed by gene are in the following list: " .
                    PHP_EOL;
                print $list;
            }
            print sprintf(
                "Success. " . $species[0]["scientific_name"] . " species:" .
                " %d genes deleted, " .
                "%d genes updated with a new name, " .
                "%d genes updated with a new identifier, " .
                "%d CRM segments updated with a new gene name, and " .
                "%d reporter constructs updated with a new gene name, " .
                "%d transcription factor binding sites updated with a new transcription factor name, " .
                "%d transcription factor binding sites updated with a new gene name, " .
                "%d new genes",
                $resultsRow["@deleted_genes_number"],
                $resultsRow["@updated_genes_number_with_new_name"],
                $resultsRow["@updated_genes_number_with_new_identifier"],
                $resultsRow["@updated_crm_segments_number_with_new_gene_name"],
                $resultsRow["@updated_reporter_constructs_number_with_new_gene_name"],
                $resultsRow["@updated_transcription_factor_binding_sites_number_with_new_transcription_factor_name"],
                $resultsRow["@updated_transcription_factor_binding_sites_number_with_new_gene_name"],
                $resultsRow["@new_genes_number"]
            ) . PHP_EOL;
            // Cleaning the unbuffered query for the next statement
            $resultsRow = $outputArguments->fetchAll();
            //$this->printSpecialCase();
        }
    }
    /**
     * There is a special (and very computationally expensive,
     * about 43 minutes) case which both gene name and identifier do NOT match.
     * It is recommended to execute it in one from both test or development
     * environments after each new REDfly release.
     * If only "Unspecified" is printed, you are all set.
     * Otherwise, follow the instructions in the Gene update caveat section
     * of the file placed in ./README.md.
     * Hint: most, if not all, matches can be found using the coordinates
     * and/or aliaeses of the gene(s) affected.
     */
    private function printSpecialCase(): void
    {
        $results = $this->db->run("
            SELECT g.name FROM Gene AS g
            WHERE g.gene_id NOT IN (SELECT old.gene_id
                                    FROM Gene AS old
                                    JOIN Species AS s USING(species_id)
                                    JOIN staging_gene_update AS new
                                        ON (BINARY old.name = BINARY new.name AND
                                            old.identifier = new.identifier AND
                                            s.short_name = new.species_short_name)) AND
                g.gene_id NOT IN (SELECT old.gene_id
                                  FROM Gene AS old
                                  JOIN Species AS s USING(species_id)
                                  JOIN staging_gene_update AS new
                                      ON (BINARY old.name = BINARY new.name AND
                                          old.identifier != new.identifier AND
                                          s.short_name = new.species_short_name)) AND
                g.gene_id NOT IN (SELECT old.gene_id
                                  FROM Gene AS old
                                  JOIN Species AS s USING(species_id)
                                  JOIN staging_gene_update AS new
                                      ON (BINARY old.name != BINARY new.name AND
                                          old.identifier = new.identifier AND
                                          s.short_name = new.species_short_name));");
        header("Content-Type: text/plain");
        foreach ( $results as $row ) {
            if ( $row["name"] !== "Unspecified" ) {
                echo $row["name"] . PHP_EOL;
                echo "Please, follow the instructions in the \"Gene update caveat\" section of " .
                    "the file placed in ./README.md" . PHP_EOL;
            }
        }
    }
}
