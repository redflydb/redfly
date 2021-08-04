\! echo "Making table transformations..."; 

ALTER TABLE Citation
MODIFY COLUMN pages VARCHAR(128) DEFAULT NULL;

\! echo "Done!";

\! echo "Making view transformations..."; 

CREATE OR REPLACE VIEW v_cis_regulatory_module_segment_ts_notify_author AS
SELECT ts.crm_segment_id,
    ts.expression AS expression_identifier,
    ds_on.term AS stage_on_term,
    ds_off.term AS stage_off_term,
    CASE bp.term
        WHEN NULL THEN ''
        ELSE bp.term
    END AS biological_process_term,
    UCASE(ts.sex) AS sex_term,
    CASE ts.ectopic
        WHEN 0 THEN 'F'
        ELSE 'T'
    END AS ectopic_term
FROM CRMSegment crms
JOIN Species ais ON crms.assayed_in_species_id = ais.species_id
JOIN triplestore_crm_segment ts ON crms.crm_segment_id = ts.crm_segment_id
JOIN DevelopmentalStage ds_on ON ais.species_id = ds_on.species_id AND
	ts.stage_on = ds_on.identifier
JOIN DevelopmentalStage ds_off ON ais.species_id = ds_off.species_id AND
	ts.stage_off = ds_off.identifier
LEFT OUTER JOIN BiologicalProcess bp ON ts.biological_process = bp.go_id
WHERE crms.state = 'approved';

CREATE OR REPLACE VIEW v_predicted_cis_regulatory_module_ts_notify_author AS
SELECT ts.predicted_crm_id,
    ts.expression AS expression_identifier,
    ds_on.term AS stage_on_term,
    ds_off.term AS stage_off_term,
    CASE bp.term
        WHEN NULL THEN ''
        ELSE bp.term
    END AS biological_process_term,
    UCASE(ts.sex) AS sex_term
FROM PredictedCRM pcrm
JOIN Species ais ON pcrm.assayed_in_species_id = ais.species_id
JOIN triplestore_predicted_crm ts ON pcrm.predicted_crm_id = ts.predicted_crm_id
JOIN DevelopmentalStage ds_on ON ais.species_id = ds_on.species_id AND
	ts.stage_on = ds_on.identifier
JOIN DevelopmentalStage ds_off ON ais.species_id = ds_off.species_id AND
	ts.stage_off = ds_off.identifier
LEFT OUTER JOIN BiologicalProcess bp ON ts.biological_process = bp.go_id
WHERE pcrm.state = 'approved';

CREATE OR REPLACE VIEW v_reporter_construct_ts_notify_author AS
SELECT ts.rc_id,
    ts.expression AS expression_identifier,
    ds_on.term AS stage_on_term,
    ds_off.term AS stage_off_term,
    CASE bp.term
        WHEN NULL THEN ''
        ELSE bp.term
    END AS biological_process_term,
    UCASE(ts.sex) AS sex_term,
    CASE ts.ectopic
        WHEN 0 THEN 'F'
        ELSE 'T'
    END AS ectopic_term
FROM ReporterConstruct rc
JOIN Species ais ON rc.assayed_in_species_id = ais.species_id
JOIN triplestore_rc ts ON rc.rc_id = ts.rc_id
JOIN DevelopmentalStage ds_on ON ais.species_id = ds_on.species_id AND
	ts.stage_on = ds_on.identifier
JOIN DevelopmentalStage ds_off ON ais.species_id = ds_off.species_id AND
	ts.stage_off = ds_off.identifier
LEFT OUTER JOIN BiologicalProcess bp ON ts.biological_process = bp.go_id
WHERE rc.state = 'approved';

\! echo "Done!";

\! echo "Building up new statistics functions...";

DELIMITER //

CREATE OR REPLACE FUNCTION NumberOfCurrentCisRegulatoryModules()
RETURNS INT DETERMINISTIC
BEGIN
	DECLARE number_of_current_cis_regulatory_modules INT;

    SELECT COUNT(rc_id)
	INTO number_of_current_cis_regulatory_modules
    FROM ReporterConstruct
    WHERE state = 'current' AND
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
	WHERE state = 'current' AND
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
	WHERE state = 'current' AND
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
	WHERE state = 'current' AND
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
    WHERE state = 'current' AND
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
	WHERE state = 'current';

	RETURN number_of_current_cis_regulatory_module_segments;
END; //

CREATE OR REPLACE FUNCTION NumberOfCurrentCisRegulatoryModuleSegmentGenes()
RETURNS INT DETERMINISTIC
BEGIN
	DECLARE number_of_current_cis_regulatory_module_segment_genes INT;

    SELECT COUNT(DISTINCT gene_id)
	INTO number_of_current_cis_regulatory_module_segment_genes
    FROM CRMSegment
    WHERE state = 'current';

	RETURN number_of_current_cis_regulatory_module_segment_genes;
END; //

CREATE OR REPLACE FUNCTION NumberOfCurrentPredictedCisRegulatoryModules()
RETURNS INT DETERMINISTIC
BEGIN
	DECLARE number_of_current_predicted_cis_regulatory_modules INT;

	SELECT COUNT(predicted_crm_id)
	INTO number_of_current_predicted_cis_regulatory_modules
    FROM PredictedCRM
    WHERE state = 'current';

	RETURN number_of_current_predicted_cis_regulatory_modules;
END; //

CREATE OR REPLACE FUNCTION NumberOfCurrentTranscriptionFactorBindingSites()
RETURNS INT DETERMINISTIC
BEGIN
	DECLARE number_of_current_transcription_factor_binding_sites INT;

	SELECT COUNT(tfbs_id)
	INTO number_of_current_transcription_factor_binding_sites
	FROM BindingSite
	WHERE state = 'current';

	RETURN number_of_current_transcription_factor_binding_sites;
END; //

CREATE OR REPLACE FUNCTION NumberOfCurrentTranscriptionFactors()
RETURNS INT DETERMINISTIC
BEGIN
	DECLARE number_of_current_transcription_factors INT;

	SELECT COUNT(DISTINCT tf_id)
	INTO number_of_current_transcription_factors
	FROM BindingSite
	WHERE state = 'current';

	RETURN number_of_current_transcription_factors;
END; //

CREATE OR REPLACE FUNCTION NumberOfCurrentTranscriptionFactorBindingSiteGenes()
RETURNS INT DETERMINISTIC
BEGIN
	DECLARE number_of_current_transcription_factor_binding_site_genes INT;

    SELECT COUNT(DISTINCT gene_id)
	INTO number_of_current_transcription_factor_binding_site_genes
    FROM BindingSite
    WHERE state = 'current';

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

	RETURN number_of_curated_publications;
END; //

DELIMITER ;

\! echo "Done!";