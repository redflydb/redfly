\! echo "Making table transformations..."; 

ALTER TABLE Citation
MODIFY COLUMN title VARCHAR(512) DEFAULT NULL;

ALTER TABLE Citation
MODIFY COLUMN journal_name VARCHAR(256) DEFAULT NULL;

ALTER TABLE Citation
MODIFY COLUMN month VARCHAR(32) DEFAULT NULL;

ALTER TABLE Citation
MODIFY COLUMN volume VARCHAR(32) DEFAULT NULL;

ALTER TABLE Citation
MODIFY COLUMN issue VARCHAR(32) DEFAULT NULL;

\! echo "Done!";

\! echo "Restricting the statistics functions to the Drosophila melanogaster species...";

DELIMITER //

CREATE OR REPLACE FUNCTION NumberOfCurrentCisRegulatoryModules()
RETURNS INT DETERMINISTIC
BEGIN
	DECLARE number_of_current_cis_regulatory_modules INT;

    SELECT COUNT(rc_id)
	INTO number_of_current_cis_regulatory_modules
    FROM ReporterConstruct
    WHERE sequence_from_species_id = 1 AND
		assayed_in_species_id = 1 AND
        state = 'current' AND
        is_crm = 1;
				
	RETURN number_of_current_cis_regulatory_modules;
END; //

CREATE OR REPLACE FUNCTION NumberOfCurrentInVivoCisRegulatoryModules()
RETURNS INT DETERMINISTIC
BEGIN
	DECLARE number_of_current_in_vivo_cis_regulatory_modules INT;

	SELECT COUNT(rc_id)
	INTO number_of_current_in_vivo_cis_regulatory_modules
	FROM ReporterConstruct
	WHERE sequence_from_species_id = 1 AND
		assayed_in_species_id = 1 AND
        state = 'current' AND
		is_crm = 1 AND
		evidence_id = 2;

	RETURN number_of_current_in_vivo_cis_regulatory_modules;
END; //

CREATE OR REPLACE FUNCTION NumberOfCurrentCisRegulatoryModulesHavingCellLineOnly()
RETURNS INT DETERMINISTIC
BEGIN
	DECLARE number_of_current_cis_regulatory_modules_having_cell_line_only INT;

	SELECT COUNT(rc_id)
	INTO number_of_current_cis_regulatory_modules_having_cell_line_only
	FROM ReporterConstruct
	WHERE sequence_from_species_id = 1 AND
		assayed_in_species_id = 1 AND
        state = 'current' AND
		is_crm = 1 AND
		cell_culture_only = 1;

	RETURN number_of_current_cis_regulatory_modules_having_cell_line_only;
END; //

CREATE OR REPLACE FUNCTION NumberOfCurrentNonInVivoCisRegulatoryModulesHavingNoCellLine()
RETURNS INT DETERMINISTIC
BEGIN
	DECLARE number_of_current_non_in_vivo_cis_regulatory_modules_having_no_cell_line INT;

	SELECT COUNT(rc_id)
	INTO number_of_current_non_in_vivo_cis_regulatory_modules_having_no_cell_line
	FROM ReporterConstruct
	WHERE sequence_from_species_id = 1 AND
		assayed_in_species_id = 1 AND
        state = 'current' AND
		is_crm = 1 AND
		evidence_id != 2 AND
		cell_culture_only = 0;

	RETURN number_of_current_non_in_vivo_cis_regulatory_modules_having_no_cell_line;
END; //

CREATE OR REPLACE FUNCTION NumberOfCurrentCisRegulatoryModuleGenes()
RETURNS INT DETERMINISTIC
BEGIN
	DECLARE number_of_current_cis_regulatory_module_genes INT;

    SELECT COUNT(DISTINCT gene_id)
	INTO number_of_current_cis_regulatory_module_genes
    FROM ReporterConstruct
    WHERE sequence_from_species_id = 1 AND
		assayed_in_species_id = 1 AND
        state = 'current' AND
        is_crm = 1;

	RETURN number_of_current_cis_regulatory_module_genes;
END; //

CREATE OR REPLACE FUNCTION NumberOfCurrentCisRegulatoryModuleSegments()
RETURNS INT DETERMINISTIC
BEGIN
	DECLARE number_of_current_cis_regulatory_module_segments INT;

    SELECT COUNT(crm_segment_id)
	INTO number_of_current_cis_regulatory_module_segments
    FROM CRMSegment
	WHERE sequence_from_species_id = 1 AND
		assayed_in_species_id = 1 AND
        state = 'current';

	RETURN number_of_current_cis_regulatory_module_segments;
END; //

CREATE OR REPLACE FUNCTION NumberOfCurrentCisRegulatoryModuleSegmentGenes()
RETURNS INT DETERMINISTIC
BEGIN
	DECLARE number_of_current_cis_regulatory_module_segment_genes INT;

    SELECT COUNT(DISTINCT gene_id)
	INTO number_of_current_cis_regulatory_module_segment_genes
    FROM CRMSegment
    WHERE sequence_from_species_id = 1 AND
		assayed_in_species_id = 1 AND
        state = 'current';

	RETURN number_of_current_cis_regulatory_module_segment_genes;
END; //

CREATE OR REPLACE FUNCTION NumberOfCurrentPredictedCisRegulatoryModules()
RETURNS INT DETERMINISTIC
BEGIN
	DECLARE number_of_current_predicted_cis_regulatory_modules INT;

	SELECT COUNT(predicted_crm_id)
	INTO number_of_current_predicted_cis_regulatory_modules
    FROM PredictedCRM
    WHERE sequence_from_species_id = 1 AND
		assayed_in_species_id = 1 AND
        state = 'current';

	RETURN number_of_current_predicted_cis_regulatory_modules;
END; //

CREATE OR REPLACE FUNCTION NumberOfCurrentTranscriptionFactorBindingSites()
RETURNS INT DETERMINISTIC
BEGIN
	DECLARE number_of_current_transcription_factor_binding_sites INT;

	SELECT COUNT(tfbs_id)
	INTO number_of_current_transcription_factor_binding_sites
	FROM BindingSite
	WHERE sequence_from_species_id = 1 AND
		assayed_in_species_id = 1 AND
        state = 'current';

	RETURN number_of_current_transcription_factor_binding_sites;
END; //

CREATE OR REPLACE FUNCTION NumberOfCurrentTranscriptionFactors()
RETURNS INT DETERMINISTIC
BEGIN
	DECLARE number_of_current_transcription_factors INT;

	SELECT COUNT(DISTINCT tf_id)
	INTO number_of_current_transcription_factors
	FROM BindingSite
	WHERE sequence_from_species_id = 1 AND
		assayed_in_species_id = 1 AND
        state = 'current';

	RETURN number_of_current_transcription_factors;
END; //

CREATE OR REPLACE FUNCTION NumberOfCurrentTranscriptionFactorBindingSiteGenes()
RETURNS INT DETERMINISTIC
BEGIN
	DECLARE number_of_current_transcription_factor_binding_site_genes INT;

    SELECT COUNT(DISTINCT gene_id)
	INTO number_of_current_transcription_factor_binding_site_genes
    FROM BindingSite
    WHERE sequence_from_species_id = 1 AND
		assayed_in_species_id = 1 AND
        state = 'current';

	RETURN number_of_current_transcription_factor_binding_site_genes;
END; //

CREATE OR REPLACE FUNCTION NumberOfCuratedPublications()
RETURNS INT DETERMINISTIC
BEGIN
	DECLARE number_of_curated_publications INT;

	SELECT COUNT(DISTINCT u.pubmed_id)
	INTO number_of_curated_publications
	FROM (SELECT DISTINCT pubmed_id
		  FROM BindingSite
		  WHERE sequence_from_species_id = 1 AND
		  	  assayed_in_species_id = 1 AND
              state = 'current'
		  UNION
		  SELECT DISTINCT pubmed_id
		  FROM CRMSegment
		  WHERE sequence_from_species_id = 1 AND
		  	  assayed_in_species_id = 1 AND
              state = 'current' 
		  UNION
		  SELECT DISTINCT pubmed_id
		  FROM PredictedCRM
		  WHERE sequence_from_species_id = 1 AND
		  	  assayed_in_species_id = 1 AND
              state = 'current'
		  UNION
		  SELECT DISTINCT pubmed_id
		  FROM ReporterConstruct
		  WHERE sequence_from_species_id = 1 AND
			  assayed_in_species_id = 1 AND
              state = 'current') AS u;

	RETURN number_of_curated_publications;
END; //

DELIMITER ;

\! echo "Done!";