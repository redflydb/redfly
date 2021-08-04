\! echo "Making table transformations..."; 

UPDATE Species
SET public_database_names = 'iBeetle-Base',
    public_database_links = 'http://ibeetle-base.uni-goettingen.de/details/gene_identifier',
    public_browser_names = 'iBeetle-Base-GBrowse',
    public_browser_links = 'http://ibeetle-base.uni-goettingen.de/gb2/gbrowse/tribolium/?name=chromosome%3Astart-end;enable=iBeetle_OGS3%2BIB_TEMPLATES'
WHERE species_id = 3;

ALTER TABLE staging_gene_update
ADD COLUMN species_id INT(10) UNSIGNED
FIRST;

\! echo "Done!";

\! echo "Making function transformations..."; 

DELIMITER //

CREATE OR REPLACE FUNCTION NumberOfCuratedPublications(species_id INT)
RETURNS INT DETERMINISTIC
BEGIN
	DECLARE number_of_curated_publications INT;

    IF species_id = 0
    THEN
	    SELECT COUNT(DISTINCT u.pubmed_id)
	    INTO number_of_curated_publications
	    FROM (SELECT DISTINCT pubmed_id
		      FROM BindingSite
		      WHERE state = 'current'
		      UNION
		      SELECT DISTINCT pubmed_id
		      FROM CRMSegment
		      WHERE state = 'current' 
		      UNION
		      SELECT DISTINCT pubmed_id
		      FROM PredictedCRM
		      WHERE state = 'current'
		      UNION
		      SELECT DISTINCT pubmed_id
		      FROM ReporterConstruct
		      WHERE state = 'current') AS u;
    ELSE
	    SELECT COUNT(DISTINCT u.pubmed_id)
	    INTO number_of_curated_publications
	    FROM (SELECT DISTINCT pubmed_id
		      FROM BindingSite
		      WHERE sequence_from_species_id = species_id AND
                  state = 'current'
		      UNION
		      SELECT DISTINCT pubmed_id
		      FROM CRMSegment
		      WHERE sequence_from_species_id = species_id AND
                  state = 'current' 
		      UNION
		      SELECT DISTINCT pubmed_id
		      FROM PredictedCRM
		      WHERE sequence_from_species_id = species_id AND
                  state = 'current'
		      UNION
		      SELECT DISTINCT pubmed_id
		      FROM ReporterConstruct
		      WHERE sequence_from_species_id = species_id AND
                  state = 'current') AS u;
    END IF;

	RETURN number_of_curated_publications;
END; //

CREATE OR REPLACE FUNCTION NumberOfCurrentCisRegulatoryModuleGenes(species_id INT)
RETURNS INT DETERMINISTIC
BEGIN
	DECLARE number_of_current_cis_regulatory_module_genes INT;

    IF species_id = 0
    THEN
        SELECT COUNT(DISTINCT gene_id)
	    INTO number_of_current_cis_regulatory_module_genes
        FROM ReporterConstruct
        WHERE state = 'current' AND
            is_crm = 1;
    ELSE
        SELECT COUNT(DISTINCT gene_id)
	    INTO number_of_current_cis_regulatory_module_genes
        FROM ReporterConstruct
        WHERE sequence_from_species_id = species_id AND
            state = 'current' AND
            is_crm = 1;    
    END IF;

	RETURN number_of_current_cis_regulatory_module_genes;
END; //

CREATE OR REPLACE FUNCTION NumberOfCurrentCisRegulatoryModules(species_id INT)
RETURNS INT DETERMINISTIC
BEGIN
	DECLARE number_of_current_cis_regulatory_modules INT;

    IF species_id = 0
    THEN
        SELECT COUNT(rc_id)
	    INTO number_of_current_cis_regulatory_modules
        FROM ReporterConstruct
        WHERE state = 'current' AND
            is_crm = 1;
    ELSE
        SELECT COUNT(rc_id)
	    INTO number_of_current_cis_regulatory_modules
        FROM ReporterConstruct
        WHERE sequence_from_species_id = species_id AND
            state = 'current' AND
            is_crm = 1;    
    END IF;
				
	RETURN number_of_current_cis_regulatory_modules;
END; //

CREATE OR REPLACE FUNCTION NumberOfCurrentCisRegulatoryModuleSegmentGenes(species_id INT)
RETURNS INT DETERMINISTIC
BEGIN
	DECLARE number_of_current_cis_regulatory_module_segment_genes INT;

    IF species_id = 0
    THEN
        SELECT COUNT(DISTINCT gene_id)
	    INTO number_of_current_cis_regulatory_module_segment_genes
        FROM CRMSegment
        WHERE state = 'current';
    ELSE
        SELECT COUNT(DISTINCT gene_id)
	    INTO number_of_current_cis_regulatory_module_segment_genes
        FROM CRMSegment
        WHERE sequence_from_species_id = species_id AND
            state = 'current';
    END IF;

	RETURN number_of_current_cis_regulatory_module_segment_genes;
END; //

CREATE OR REPLACE FUNCTION NumberOfCurrentCisRegulatoryModuleSegments(species_id INT)
RETURNS INT DETERMINISTIC
BEGIN
	DECLARE number_of_current_cis_regulatory_module_segments INT;

    IF species_id = 0
    THEN
        SELECT COUNT(crm_segment_id)
	    INTO number_of_current_cis_regulatory_module_segments
        FROM CRMSegment
	    WHERE state = 'current';
    ELSE 
        SELECT COUNT(crm_segment_id)
	    INTO number_of_current_cis_regulatory_module_segments
        FROM CRMSegment
	    WHERE sequence_from_species_id = species_id AND
            state = 'current';    
    END IF;

	RETURN number_of_current_cis_regulatory_module_segments;
END; //

CREATE OR REPLACE FUNCTION NumberOfCurrentCisRegulatoryModuleSegmentsWithoutStagingData(species_id INT)
RETURNS INT DETERMINISTIC
BEGIN
	DECLARE number_of_current_cis_regulatory_module_segments_without_staging_data INT;

    IF species_id = 0
    THEN
	    SELECT COUNT(crms.name)
	    INTO number_of_current_cis_regulatory_module_segments_without_staging_data 
	    FROM CRMSegment crms
	    WHERE crms.state = 'current' AND
		    NOT EXISTS (SELECT DISTINCT crms.crm_segment_id
	                    FROM triplestore_crm_segment tcs
		                WHERE crms.crm_segment_id = tcs.crm_segment_id);
    ELSE
	    SELECT COUNT(crms.name)
	    INTO number_of_current_cis_regulatory_module_segments_without_staging_data 
	    FROM CRMSegment crms
	    WHERE crms.sequence_from_species_id = species_id AND
            crms.state = 'current' AND
		    NOT EXISTS (SELECT DISTINCT crms.crm_segment_id
	                    FROM triplestore_crm_segment tcs
		                WHERE crms.crm_segment_id = tcs.crm_segment_id);    
    END IF;
				
	RETURN number_of_current_cis_regulatory_module_segments_without_staging_data;
END; //

CREATE OR REPLACE FUNCTION NumberOfCurrentCisRegulatoryModuleSegmentsWithStagingData(species_id INT)
RETURNS INT DETERMINISTIC
BEGIN
	DECLARE number_of_current_cis_regulatory_module_segments_with_staging_data INT;

    IF species_id = 0
    THEN
	    SELECT COUNT(crms.name)
	    INTO number_of_current_cis_regulatory_module_segments_with_staging_data 
	    FROM CRMSegment crms
	    WHERE crms.state = 'current' AND
		    EXISTS (SELECT DISTINCT crms.crm_segment_id
	                FROM triplestore_crm_segment tcs
		            WHERE crms.crm_segment_id = tcs.crm_segment_id);
    ELSE
	    SELECT COUNT(crms.name)
	    INTO number_of_current_cis_regulatory_module_segments_with_staging_data 
	    FROM CRMSegment crms
	    WHERE crms.sequence_from_species_id = species_id AND
            crms.state = 'current' AND
		    EXISTS (SELECT DISTINCT crms.crm_segment_id
	                FROM triplestore_crm_segment tcs
		            WHERE crms.crm_segment_id = tcs.crm_segment_id);    
    END IF;
				
	RETURN number_of_current_cis_regulatory_module_segments_with_staging_data;
END; //

CREATE OR REPLACE FUNCTION NumberOfCurrentCisRegulatoryModulesHavingCellLineOnly(species_id INT)
RETURNS INT DETERMINISTIC
BEGIN
	DECLARE number_of_current_cis_regulatory_modules_having_cell_line_only INT;

    IF species_id = 0
    THEN
	    SELECT COUNT(rc_id)
	    INTO number_of_current_cis_regulatory_modules_having_cell_line_only
	    FROM ReporterConstruct
	    WHERE state = 'current' AND
		    is_crm = 1 AND
		    cell_culture_only = 1;
    ELSE
	    SELECT COUNT(rc_id)
	    INTO number_of_current_cis_regulatory_modules_having_cell_line_only
	    FROM ReporterConstruct
	    WHERE sequence_from_species_id = species_id AND
            state = 'current' AND
		    is_crm = 1 AND
		    cell_culture_only = 1;    
    END IF;

	RETURN number_of_current_cis_regulatory_modules_having_cell_line_only;
END; //

CREATE OR REPLACE FUNCTION NumberOfCurrentCisRegulatoryModulesWithoutStagingData(species_id INT)
RETURNS INT DETERMINISTIC
BEGIN
	DECLARE number_of_current_cis_regulatory_modules_without_staging_data INT;

    IF species_id = 0
    THEN
	    SELECT COUNT(rc.name)
	    INTO number_of_current_cis_regulatory_modules_without_staging_data 
	    FROM ReporterConstruct rc
	    WHERE rc.state = 'current' AND
            rc.is_crm = 1 AND
		    NOT EXISTS (SELECT DISTINCT rc.rc_id
	                    FROM triplestore_rc ts
		                WHERE rc.rc_id = ts.rc_id);
    ELSE 
	    SELECT COUNT(rc.name)
	    INTO number_of_current_cis_regulatory_modules_without_staging_data 
	    FROM ReporterConstruct rc
	    WHERE rc.sequence_from_species_id = species_id AND
            rc.state = 'current' AND
            rc.is_crm = 1 AND
		    NOT EXISTS (SELECT DISTINCT rc.rc_id
	                    FROM triplestore_rc ts
		                WHERE rc.rc_id = ts.rc_id);    
    END IF;
				
	RETURN number_of_current_cis_regulatory_modules_without_staging_data;
END; //

CREATE OR REPLACE FUNCTION NumberOfCurrentCisRegulatoryModulesWithStagingData(species_id INT)
RETURNS INT DETERMINISTIC
BEGIN
	DECLARE number_of_current_cis_regulatory_modules_with_staging_data INT;

    IF species_id = 0
    THEN
	    SELECT COUNT(rc.name)
	    INTO number_of_current_cis_regulatory_modules_with_staging_data 
	    FROM ReporterConstruct rc
	    WHERE rc.state = 'current' AND
            rc.is_crm = 1 AND  
		    EXISTS (SELECT DISTINCT rc.rc_id
	                FROM triplestore_rc ts
		            WHERE rc.rc_id = ts.rc_id);
    ELSE
	    SELECT COUNT(rc.name)
	    INTO number_of_current_cis_regulatory_modules_with_staging_data 
	    FROM ReporterConstruct rc
	    WHERE rc.sequence_from_species_id = species_id AND
            rc.state = 'current' AND
            rc.is_crm = 1 AND  
		    EXISTS (SELECT DISTINCT rc.rc_id
	                FROM triplestore_rc ts
		            WHERE rc.rc_id = ts.rc_id);    
    END IF;
				
	RETURN number_of_current_cis_regulatory_modules_with_staging_data;
END; //

CREATE OR REPLACE FUNCTION NumberOfCurrentInVivoCisRegulatoryModules(species_id INT)
RETURNS INT DETERMINISTIC
BEGIN
	DECLARE number_of_current_in_vivo_cis_regulatory_modules INT;

    IF species_id = 0
    THEN
	    SELECT COUNT(rc_id)
	    INTO number_of_current_in_vivo_cis_regulatory_modules
	    FROM ReporterConstruct
	    WHERE state = 'current' AND
		    is_crm = 1 AND
		    evidence_id = 2;
    ELSE
	    SELECT COUNT(rc_id)
	    INTO number_of_current_in_vivo_cis_regulatory_modules
	    FROM ReporterConstruct
	    WHERE sequence_from_species_id = species_id AND
            state = 'current' AND
		    is_crm = 1 AND
		    evidence_id = 2;    
    END IF;

	RETURN number_of_current_in_vivo_cis_regulatory_modules;
END; //

CREATE OR REPLACE FUNCTION NumberOfCurrentNonCisRegulatoryModulesWithoutStagingData(species_id INT)
RETURNS INT DETERMINISTIC
BEGIN
	DECLARE number_of_current_non_cis_regulatory_modules_without_staging_data INT;

    IF species_id = 0
    THEN
	    SELECT COUNT(rc.name)
	    INTO number_of_current_non_cis_regulatory_modules_without_staging_data 
	    FROM ReporterConstruct rc
	    WHERE rc.state = 'current' AND
            rc.is_crm = 0 AND
		    NOT EXISTS (SELECT DISTINCT rc.rc_id
	                    FROM triplestore_rc ts
		                WHERE rc.rc_id = ts.rc_id);
    ELSE
	    SELECT COUNT(rc.name)
	    INTO number_of_current_non_cis_regulatory_modules_without_staging_data 
	    FROM ReporterConstruct rc
	    WHERE rc.sequence_from_species_id = species_id AND
            rc.state = 'current' AND
            rc.is_crm = 0 AND
		    NOT EXISTS (SELECT DISTINCT rc.rc_id
	                    FROM triplestore_rc ts
		                WHERE rc.rc_id = ts.rc_id);    
    END IF;
				
	RETURN number_of_current_non_cis_regulatory_modules_without_staging_data;
END; //

CREATE OR REPLACE FUNCTION NumberOfCurrentNonCisRegulatoryModulesWithStagingData(species_id INT)
RETURNS INT DETERMINISTIC
BEGIN
	DECLARE number_of_current_non_cis_regulatory_modules_with_staging_data INT;

    IF species_id = 0
    THEN
	    SELECT COUNT(rc.name)
	    INTO number_of_current_non_cis_regulatory_modules_with_staging_data 
	    FROM ReporterConstruct rc
	    WHERE rc.state = 'current' AND
            rc.is_crm = 0 AND
		    EXISTS (SELECT DISTINCT rc.rc_id
	                FROM triplestore_rc ts
		            WHERE rc.rc_id = ts.rc_id);
    ELSE
	    SELECT COUNT(rc.name)
	    INTO number_of_current_non_cis_regulatory_modules_with_staging_data 
	    FROM ReporterConstruct rc
	    WHERE rc.sequence_from_species_id = species_id AND
            rc.state = 'current' AND
            rc.is_crm = 0 AND
		    EXISTS (SELECT DISTINCT rc.rc_id
	                FROM triplestore_rc ts
		            WHERE rc.rc_id = ts.rc_id);    
    END IF;
				
	RETURN number_of_current_non_cis_regulatory_modules_with_staging_data;
END; //

CREATE OR REPLACE FUNCTION NumberOfCurrentNonInVivoCisRegulatoryModulesHavingNoCellLine(species_id INT)
RETURNS INT DETERMINISTIC
BEGIN
	DECLARE number_of_current_non_in_vivo_cis_regulatory_modules_having_no_cell_line INT;

    IF species_id = 0
    THEN
	    SELECT COUNT(rc_id)
	    INTO number_of_current_non_in_vivo_cis_regulatory_modules_having_no_cell_line
	    FROM ReporterConstruct
	    WHERE state = 'current' AND
		    is_crm = 1 AND
		    evidence_id != 2 AND
		    cell_culture_only = 0;
    ELSE
	    SELECT COUNT(rc_id)
	    INTO number_of_current_non_in_vivo_cis_regulatory_modules_having_no_cell_line
	    FROM ReporterConstruct
	    WHERE sequence_from_species_id = species_id AND
            state = 'current' AND
		    is_crm = 1 AND
		    evidence_id != 2 AND
		    cell_culture_only = 0;    
    END IF;

	RETURN number_of_current_non_in_vivo_cis_regulatory_modules_having_no_cell_line;
END; //

CREATE OR REPLACE FUNCTION NumberOfCurrentPredictedCisRegulatoryModules(species_id INT)
RETURNS INT DETERMINISTIC
BEGIN
	DECLARE number_of_current_predicted_cis_regulatory_modules INT;

    IF species_id = 0
    THEN
	    SELECT COUNT(predicted_crm_id)
	    INTO number_of_current_predicted_cis_regulatory_modules
        FROM PredictedCRM
        WHERE state = 'current';
    ELSE 
	    SELECT COUNT(predicted_crm_id)
	    INTO number_of_current_predicted_cis_regulatory_modules
        FROM PredictedCRM
        WHERE sequence_from_species_id = species_id AND
            state = 'current';    
    END IF;

	RETURN number_of_current_predicted_cis_regulatory_modules;
END; //

CREATE OR REPLACE FUNCTION NumberOfCurrentPredictedCisRegulatoryModulesWithoutStagingData(species_id INT)
RETURNS INT DETERMINISTIC
BEGIN
	DECLARE number_of_current_predicted_cis_regulatory_modules_without_staging_data INT;

    IF species_id = 0
    THEN
	    SELECT COUNT(pcrm.name)
	    INTO number_of_current_predicted_cis_regulatory_modules_without_staging_data 
	    FROM PredictedCRM pcrm
	    WHERE pcrm.state = 'current' AND
		    NOT EXISTS (SELECT DISTINCT pcrm.predicted_crm_id
	                    FROM triplestore_predicted_crm tpc
		                WHERE pcrm.predicted_crm_id = tpc.predicted_crm_id);
    ELSE
	    SELECT COUNT(pcrm.name)
	    INTO number_of_current_predicted_cis_regulatory_modules_without_staging_data 
	    FROM PredictedCRM pcrm
	    WHERE pcrm.sequence_from_species_id = species_id AND
            pcrm.state = 'current' AND
		    NOT EXISTS (SELECT DISTINCT pcrm.predicted_crm_id
	                    FROM triplestore_predicted_crm tpc
		                WHERE pcrm.predicted_crm_id = tpc.predicted_crm_id);    
    END IF;
				
	RETURN number_of_current_predicted_cis_regulatory_modules_without_staging_data;
END; //

CREATE OR REPLACE FUNCTION NumberOfCurrentPredictedCisRegulatoryModulesWithStagingData(species_id INT)
RETURNS INT DETERMINISTIC
BEGIN
	DECLARE number_of_current_predicted_cis_regulatory_modules_with_staging_data INT;

    IF species_id = 0
    THEN
	    SELECT COUNT(pcrm.name)
	    INTO number_of_current_predicted_cis_regulatory_modules_with_staging_data 
	    FROM PredictedCRM pcrm
	    WHERE pcrm.state = 'current' AND
		    EXISTS (SELECT DISTINCT pcrm.predicted_crm_id
	                FROM triplestore_predicted_crm tpc
		            WHERE pcrm.predicted_crm_id = tpc.predicted_crm_id);
    ELSE
	    SELECT COUNT(pcrm.name)
	    INTO number_of_current_predicted_cis_regulatory_modules_with_staging_data 
	    FROM PredictedCRM pcrm
	    WHERE pcrm.sequence_from_species_id = species_id AND
            pcrm.state = 'current' AND
		    EXISTS (SELECT DISTINCT pcrm.predicted_crm_id
	                FROM triplestore_predicted_crm tpc
		            WHERE pcrm.predicted_crm_id = tpc.predicted_crm_id);
    END IF;                    
				
	RETURN number_of_current_predicted_cis_regulatory_modules_with_staging_data;
END; //

CREATE OR REPLACE FUNCTION NumberOfCurrentReporterConstructsWithoutStagingData(species_id INT)
RETURNS INT DETERMINISTIC
BEGIN
	DECLARE number_of_current_reporter_constructs_without_staging_data INT;

    IF species_id = 0
    THEN
	    SELECT COUNT(rc.name)
	    INTO number_of_current_reporter_constructs_without_staging_data 
	    FROM ReporterConstruct rc
	    WHERE rc.state = 'current' AND
		    NOT EXISTS (SELECT DISTINCT rc.rc_id
	                    FROM triplestore_rc ts
			            WHERE rc.rc_id = ts.rc_id);
    ELSE
	    SELECT COUNT(rc.name)
	    INTO number_of_current_reporter_constructs_without_staging_data 
	    FROM ReporterConstruct rc
	    WHERE sequence_from_species_id = species_id AND
            rc.state = 'current' AND
		    NOT EXISTS (SELECT DISTINCT rc.rc_id
	                    FROM triplestore_rc ts
			            WHERE rc.sequence_from_species_id = species_id AND
                            rc.rc_id = ts.rc_id);    
    END IF;
				
	RETURN number_of_current_reporter_constructs_without_staging_data;
END; //

CREATE OR REPLACE FUNCTION NumberOfCurrentReporterConstructsWithStagingData(species_id INT)
RETURNS INT DETERMINISTIC
BEGIN
	DECLARE number_of_current_reporter_constructs_with_staging_data INT;

    IF species_id = 0
    THEN
	    SELECT COUNT(rc.name)
	    INTO number_of_current_reporter_constructs_with_staging_data 
	    FROM ReporterConstruct rc
	    WHERE rc.state = 'current' AND
		    EXISTS (SELECT DISTINCT rc.rc_id
	                FROM triplestore_rc ts
		            WHERE rc.rc_id = ts.rc_id);
    ELSE
	    SELECT COUNT(rc.name)
	    INTO number_of_current_reporter_constructs_with_staging_data 
	    FROM ReporterConstruct rc
	    WHERE sequence_from_species_id = species_id AND
            rc.state = 'current' AND
		    EXISTS (SELECT DISTINCT rc.rc_id
	                FROM triplestore_rc ts
		            WHERE rc.sequence_from_species_id = species_id AND
                        rc.rc_id = ts.rc_id);
    END IF;
				
	RETURN number_of_current_reporter_constructs_with_staging_data;
END; //

CREATE OR REPLACE FUNCTION NumberOfCurrentTranscriptionFactorBindingSiteGenes(species_id INT)
RETURNS INT DETERMINISTIC
BEGIN
	DECLARE number_of_current_transcription_factor_binding_site_genes INT;

    IF species_id = 0
    THEN
        SELECT COUNT(DISTINCT gene_id)
	    INTO number_of_current_transcription_factor_binding_site_genes
        FROM BindingSite
        WHERE state = 'current';
    ELSE
        SELECT COUNT(DISTINCT gene_id)
	    INTO number_of_current_transcription_factor_binding_site_genes
        FROM BindingSite
        WHERE sequence_from_species_id = species_id AND
            state = 'current';    
    END IF;

	RETURN number_of_current_transcription_factor_binding_site_genes;
END; //

CREATE OR REPLACE FUNCTION NumberOfCurrentTranscriptionFactorBindingSites(species_id INT)
RETURNS INT DETERMINISTIC
BEGIN
	DECLARE number_of_current_transcription_factor_binding_sites INT;

    IF species_id = 0
    THEN
	    SELECT COUNT(tfbs_id)
	    INTO number_of_current_transcription_factor_binding_sites
	    FROM BindingSite
	    WHERE state = 'current';
    ELSE 
	    SELECT COUNT(tfbs_id)
	    INTO number_of_current_transcription_factor_binding_sites
	    FROM BindingSite
	    WHERE sequence_from_species_id = species_id AND
            state = 'current';    
    END IF;

	RETURN number_of_current_transcription_factor_binding_sites;
END; //

CREATE OR REPLACE FUNCTION NumberOfCurrentTranscriptionFactors(species_id INT)
RETURNS INT DETERMINISTIC
BEGIN
	DECLARE number_of_current_transcription_factors INT;

    IF species_id = 0
    THEN
	    SELECT COUNT(DISTINCT tf_id)
	    INTO number_of_current_transcription_factors
	    FROM BindingSite
	    WHERE state = 'current';
    ELSE
	    SELECT COUNT(DISTINCT tf_id)
	    INTO number_of_current_transcription_factors
	    FROM BindingSite
	    WHERE sequence_from_species_id = species_id AND
            state = 'current';    
    END IF;

	RETURN number_of_current_transcription_factors;
END; //

DELIMITER ;

\! echo "Done!";

\! echo "Making procedure transformations..."; 

DELIMITER //

CREATE OR REPLACE PROCEDURE update_genes(
    IN  species_id_in INT,
    IN  species_short_name_in CHAR(32),
    IN  genome_assembly_id_in INT,
    IN  genome_assembly_release_version_in CHAR(32),
    OUT deleted_genes_number INT,
    OUT identifiers MEDIUMTEXT,
    OUT old_names MEDIUMTEXT,
    OUT new_names MEDIUMTEXT,
    OUT updated_genes_number_with_new_name INT,
    OUT old_identifiers MEDIUMTEXT,
    OUT new_identifiers MEDIUMTEXT,    
    OUT updated_genes_number_with_new_identifier INT,
    OUT renamed_crm_segment_names MEDIUMTEXT,
    OUT updated_crm_segments_number_with_new_gene_name INT,    
    OUT renamed_reporter_construct_names MEDIUMTEXT,
    OUT updated_reporter_constructs_number_with_new_gene_name INT,    
    OUT renamed_transcription_factor_binding_site_names_by_transcription_factor MEDIUMTEXT,
    OUT updated_transcription_factor_binding_sites_number_with_new_transcription_factor_name INT,
    OUT renamed_transcription_factor_binding_site_names_by_gene MEDIUMTEXT,    
    OUT updated_transcription_factor_binding_sites_number_with_new_gene_name INT,    
    OUT new_genes_number INT)
    MODIFIES SQL DATA
    SQL SECURITY INVOKER
    COMMENT 'Updates the genes based on the data stored in staging'
BEGIN
    DELETE FROM Features;

    /* Deleting all the genes targeted by species and not used by all
       the REDfly entities so that any previous gene no longer available
       from the new ontology versions can be surely deleted */
    DELETE FROM Gene
    WHERE gene_id NOT IN (SELECT DISTINCT gene_id 
                          FROM BindingSite
                          WHERE sequence_from_species_id = species_id_in AND
                              current_genome_assembly_release_version = genome_assembly_release_version_in
                          UNION
                          SELECT DISTINCT tf_id 
                          FROM BindingSite
                          WHERE sequence_from_species_id = species_id_in AND
                              current_genome_assembly_release_version = genome_assembly_release_version_in
                          UNION
                          SELECT DISTINCT gene_id 
                          FROM ReporterConstruct
                          WHERE sequence_from_species_id = species_id_in AND
                              current_genome_assembly_release_version = genome_assembly_release_version_in
                          UNION
                          SELECT DISTINCT gene_id 
                          FROM CRMSegment
                          WHERE sequence_from_species_id = species_id_in AND
                              current_genome_assembly_release_version = genome_assembly_release_version_in) AND
        species_id = species_id_in AND
        genome_assembly_id = genome_assembly_id_in;

    SELECT ROW_COUNT()
    INTO deleted_genes_number;

    /* Updating the staging cache with the table identifiers from the REDfly
       database for all the species */
    UPDATE staging_gene_update new
    JOIN Species s ON s.short_name = new.species_short_name
    JOIN GenomeAssembly ga ON ga.is_deprecated = 0 AND
        ga.species_id = s.species_id AND
        ga.release_version = new.genome_assembly_release_version
    JOIN Chromosome c ON c.species_id = s.species_id AND
        c.genome_assembly_id = ga.genome_assembly_id AND
        c.name = new.chromosome_name
    SET new.species_id = s.species_id,
        new.genome_assembly_id = ga.genome_assembly_id,
        new.chromosome_id = c.chromosome_id;

    /* Getting the gene identifiers targeted by species having different names
       from both (old) REDfly database and (new) staging cache for the output
       information */
	SELECT GROUP_CONCAT(old.identifier ORDER BY old.identifier SEPARATOR '\t'),
        GROUP_CONCAT(old.name ORDER BY old.identifier SEPARATOR '\t'),
        GROUP_CONCAT(new.name ORDER BY old.identifier SEPARATOR '\t') INTO
        identifiers,
        old_names,
        new_names
    FROM Species s
    JOIN GenomeAssembly ga ON s.short_name = species_short_name_in AND
        ga.species_id = s.species_id AND
        ga.release_version = genome_assembly_release_version_in
    JOIN Gene old ON old.species_id = s.species_id AND
        old.genome_assembly_id = ga.genome_assembly_id
    JOIN staging_gene_update new ON new.species_short_name = species_short_name_in AND
        new.genome_assembly_release_version = genome_assembly_release_version_in AND
        new.identifier = old.identifier AND
        BINARY new.name != BINARY old.name;

    /* Updating the gene names targeted by species from the (old) REDfly 
       database with the gene names from the (new) staging cache */
    UPDATE Gene old
    JOIN Species s ON s.short_name = species_short_name_in AND
        s.species_id = old.species_id
    JOIN GenomeAssembly ga ON ga.release_version = genome_assembly_release_version_in AND
        ga.species_id = s.species_id AND
        ga.genome_assembly_id = old.genome_assembly_id
    JOIN staging_gene_update new ON new.species_short_name = species_short_name_in AND
        new.genome_assembly_release_version = genome_assembly_release_version_in AND
        new.identifier = old.identifier AND
        BINARY new.name != BINARY old.name
    SET old.name = new.name,
        old.genome_assembly_id = new.genome_assembly_id,
        old.chrm_id = new.chromosome_id,
        old.start = new.start,
        old.stop = new.end,
        old.strand = new.strand;

    SELECT ROW_COUNT()
    INTO updated_genes_number_with_new_name;

    /* Getting the gene names targeted by species having different identifiers
       from both (old) REDfly database and (new) staging cache for the output
       information */
	SELECT GROUP_CONCAT(old.identifier ORDER BY old.identifier SEPARATOR '\t'),
        GROUP_CONCAT(new.identifier ORDER BY old.identifier SEPARATOR '\t') INTO
        old_identifiers, 
        new_identifiers
    FROM Species s
    JOIN GenomeAssembly ga ON s.short_name = species_short_name_in AND
        ga.species_id = s.species_id AND
        ga.release_version = genome_assembly_release_version_in
    JOIN Gene old ON old.species_id = s.species_id AND
        old.genome_assembly_id = ga.genome_assembly_id
    JOIN staging_gene_update new ON new.species_short_name = species_short_name_in AND
        new.genome_assembly_release_version = genome_assembly_release_version_in AND
        new.identifier != old.identifier AND
        BINARY new.name = BINARY old.name;

    /* Updating the gene identifiers targeted by species from the (old) REDfly
       database with the gene identifiers from the (new) staging cache */
    UPDATE Gene old
    JOIN Species s ON s.short_name = species_short_name_in AND
        old.species_id = s.species_id
    JOIN GenomeAssembly ga ON ga.release_version = genome_assembly_release_version_in AND
        ga.species_id = s.species_id AND
        ga.genome_assembly_id = old.genome_assembly_id
    JOIN staging_gene_update new ON new.species_short_name = species_short_name_in AND
        new.genome_assembly_release_version = genome_assembly_release_version_in AND
        new.identifier != old.identifier AND
        BINARY new.name = BINARY old.name
    SET old.identifier = new.identifier,
        old.genome_assembly_id = new.genome_assembly_id,
        old.chrm_id = new.chromosome_id,
        old.start = new.start,
        old.stop = new.end,
        old.strand = new.strand;

    SELECT ROW_COUNT()
    INTO updated_genes_number_with_new_identifier;

    /* Getting the CRM segment names targeted by species having different gene
       names from both (old) REDfly database and (new) staging cache for the 
       output information */
    SELECT GROUP_CONCAT(crm_segment.name ORDER BY crm_segment.name SEPARATOR '\t')
    INTO renamed_crm_segment_names
    FROM CRMSegment crm_segment
    JOIN Gene g ON crm_segment.sequence_from_species_id = species_id_in AND
        crm_segment.current_genome_assembly_release_version = genome_assembly_release_version_in AND
        crm_segment.gene_id = g.gene_id AND
        BINARY SUBSTRING_INDEX(crm_segment.name, '_', 1) != BINARY g.name;

    /* Updating the CRM segment names targeted by species from the (old) REDfly
       database with the gene names from the (new) staging cache */
    UPDATE CRMSegment crm_segment
    JOIN Gene g ON crm_segment.sequence_from_species_id = species_id_in AND
        crm_segment.current_genome_assembly_release_version = genome_assembly_release_version_in AND
        crm_segment.gene_id = g.gene_id AND
        BINARY SUBSTRING_INDEX(crm_segment.name, '_', 1) != BINARY g.name
    SET crm_segment.name = CONCAT(g.name, SUBSTRING(crm_segment.name, LOCATE('_', crm_segment.name)));

    SELECT ROW_COUNT()
    INTO updated_crm_segments_number_with_new_gene_name;

    /* Getting the RC names targeted by species having different gene names
       from both (old) REDfly database and (new) staging cache for the output
       information */           
    SELECT GROUP_CONCAT(rc.name ORDER BY rc.name SEPARATOR '\t')
    INTO renamed_reporter_construct_names
    FROM ReporterConstruct rc
    JOIN Gene g ON rc.sequence_from_species_id = species_id_in AND
        rc.current_genome_assembly_release_version = genome_assembly_release_version_in AND
        rc.gene_id = g.gene_id AND
        BINARY SUBSTRING_INDEX(rc.name, '_', 1) != BINARY g.name;

    /* Updating the RC names targeted by species from the (old) REDfly database
       with the gene names from the (new) staging cache */
    UPDATE ReporterConstruct rc
    JOIN Gene g ON rc.sequence_from_species_id = species_id_in AND
        rc.current_genome_assembly_release_version = genome_assembly_release_version_in AND
        rc.gene_id = g.gene_id AND
        BINARY SUBSTRING_INDEX(rc.name, '_', 1) != BINARY g.name
    SET rc.name = CONCAT(g.name, SUBSTRING(rc.name, LOCATE('_', rc.name)));

    SELECT ROW_COUNT()
    INTO updated_reporter_constructs_number_with_new_gene_name;

    /* Getting the TFBS names targeted by species having different transcription factor
       names from both (old) REDfly database and (new) staging cache for the 
       output information */
    SELECT GROUP_CONCAT(bs.name ORDER BY bs.name SEPARATOR '\t')
    INTO renamed_transcription_factor_binding_site_names_by_transcription_factor
    FROM BindingSite bs
    JOIN Gene g ON bs.sequence_from_species_id = species_id_in AND
        bs.current_genome_assembly_release_version = genome_assembly_release_version_in AND
        bs.tf_id = g.gene_id AND
        BINARY SUBSTRING_INDEX(bs.name, '_', 1) != BINARY g.name;

    /* Updating the TFBS names targeted by species from the (old) REDfly
       database with the transcription factor names from the (new) staging cache */
    UPDATE BindingSite bs
    JOIN Gene g ON bs.sequence_from_species_id = species_id_in AND
        bs.current_genome_assembly_release_version = genome_assembly_release_version_in AND
        bs.tf_id = g.gene_id AND
        BINARY SUBSTRING_INDEX(bs.name, '_', 1) != BINARY g.name
    SET bs.name = CONCAT(g.name,
                         SUBSTRING(bs.name, LOCATE('_', bs.name)));

    SELECT ROW_COUNT()
    INTO updated_transcription_factor_binding_sites_number_with_new_transcription_factor_name;

    /* Getting the TFBS names targeted by species having different gene
       names from both (old) REDfly database and (new) staging cache for the 
       output information */
    SELECT GROUP_CONCAT(bs.name ORDER BY bs.name SEPARATOR '\t')
    INTO renamed_transcription_factor_binding_site_names_by_gene
    FROM BindingSite bs
    JOIN Gene g ON bs.sequence_from_species_id = species_id_in AND
        bs.current_genome_assembly_release_version = genome_assembly_release_version_in AND
        bs.gene_id = g.gene_id AND
        BINARY SUBSTRING(SUBSTRING(bs.name, 1, LOCATE(':REDFLY:', bs.name) - 1), LOCATE('_', bs.name) + 1) != BINARY g.name;

    /* Updating the TFBS names targeted by species from the (old) REDfly
       database with the gene names from the (new) staging cache */
    UPDATE BindingSite bs
    JOIN Gene g ON bs.sequence_from_species_id = species_id_in AND
        bs.current_genome_assembly_release_version = genome_assembly_release_version_in AND
        bs.gene_id = g.gene_id AND
        BINARY SUBSTRING(SUBSTRING(bs.name, 1, LOCATE(':REDFLY:', bs.name) - 1), LOCATE('_', bs.name) + 1) != BINARY g.name
    SET bs.name = CONCAT(SUBSTRING(bs.name, 1, LOCATE('_', bs.name)), 
				         g.name,
                	     SUBSTRING(bs.name, LOCATE(':REDFLY:', bs.name)));    

    SELECT ROW_COUNT()
    INTO updated_transcription_factor_binding_sites_number_with_new_gene_name;

    /* Inserting the genes targeted by species from the (new) staging cache 
       into the (old) REDfly database */
    INSERT INTO Gene (
        species_id,
        name,
        identifier,
        genome_assembly_id,
        chrm_id,
        start,
        stop,
        strand)
    SELECT c.species_id,
        new.name,
        new.identifier,
        ga.genome_assembly_id,
        c.chromosome_id,
        new.start,
        new.end,
        new.strand
    FROM Species s
    JOIN GenomeAssembly ga ON s.short_name = species_short_name_in AND 
        ga.release_version = genome_assembly_release_version_in AND
        ga.species_id = s.species_id
    JOIN Chromosome c ON c.species_id = s.species_id AND
        c.genome_assembly_id = ga.genome_assembly_id
    JOIN staging_gene_update new ON new.species_id = s.species_id AND
        new.genome_assembly_id = ga.genome_assembly_id AND
        new.chromosome_name = c.name AND
        new.identifier NOT IN (SELECT identifier 
                               FROM Gene
                               WHERE species_id = species_id_in AND
                                   genome_assembly_id = genome_assembly_id_in);
    
    SELECT ROW_COUNT()
    INTO new_genes_number;
END //

\! echo "Done!";