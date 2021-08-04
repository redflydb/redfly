\! echo "Making table transformations..."; 

UPDATE DevelopmentalStage
SET identifier = 'dmel:none'
WHERE stage_id = 264;

UPDATE DevelopmentalStage
SET identifier = 'agam:none'
WHERE stage_id = 265;

UPDATE DevelopmentalStage
SET identifier = 'tcas:none'
WHERE stage_id = 266;

UPDATE DevelopmentalStage
SET identifier = 'aaeg:none'
WHERE stage_id = 267;

UPDATE triplestore_rc 
SET stage_on = 'dmel:none'
WHERE stage_on = 'none' AND
	rc_id IN (SELECT rc_id
	          FROM ReporterConstruct
	          WHERE assayed_in_species_id = 1);

UPDATE triplestore_rc 
SET stage_off = 'dmel:none'
WHERE stage_off = 'none' AND
	rc_id IN (SELECT rc_id
	          FROM ReporterConstruct
	          WHERE assayed_in_species_id = 1);
	          
UPDATE triplestore_rc 
SET stage_on = 'agam:none'
WHERE stage_on = 'none' AND
	rc_id IN (SELECT rc_id
	          FROM ReporterConstruct
	          WHERE assayed_in_species_id = 2);

UPDATE triplestore_rc 
SET stage_off = 'agam:none'
WHERE stage_off = 'none' AND
	rc_id IN (SELECT rc_id
	          FROM ReporterConstruct
	          WHERE assayed_in_species_id = 2);
	          
UPDATE triplestore_rc 
SET stage_on = 'tcas:none'
WHERE stage_on = 'none' AND
	rc_id IN (SELECT rc_id
	          FROM ReporterConstruct
	          WHERE assayed_in_species_id = 3);

UPDATE triplestore_rc 
SET stage_off = 'tcas:none'
WHERE stage_off = 'none' AND
	rc_id IN (SELECT rc_id
	          FROM ReporterConstruct
	          WHERE assayed_in_species_id = 3);
	          
UPDATE triplestore_rc 
SET stage_on = 'aaeg:none'
WHERE stage_on = 'none' AND
	rc_id IN (SELECT rc_id
	          FROM ReporterConstruct
	          WHERE assayed_in_species_id = 4);

UPDATE triplestore_rc 
SET stage_off = 'aaeg:none'
WHERE stage_off = 'none' AND
	rc_id IN (SELECT rc_id
	          FROM ReporterConstruct
	          WHERE assayed_in_species_id = 4);

ALTER TABLE PredictedCRM
DROP FOREIGN KEY PredictedCRM_ibfk_9;

ALTER TABLE PredictedCRM
DROP COLUMN assayed_in_species_id;

\! echo "Done!";

\! echo "Making view transformations..."; 

CREATE OR REPLACE VIEW v_cis_regulatory_module_segment_no_ts_audit AS
SELECT crms.crm_segment_id AS id,
    sfs.scientific_name AS sequence_from_species_scientific_name,
    ais.scientific_name AS assayed_in_species_scientific_name,
    CONCAT(crms.name, ' ') AS name,
    CONCAT(curator.first_name, ' ', curator.last_name) AS curator_full_name,
    crms.state,
    crms.pubmed_id,
    CONCAT(c.name, ' (', sfs.short_name, ')') AS chromosome_display,
    crms.current_start AS start,
    crms.current_end AS end,
    CONCAT(g.name, ' (', g.identifier, ')') AS gene_display,
    CONCAT(et.term, ' (', et.identifier, ')') AS anatomical_expression_display,
    '' AS on_developmental_stage_display,
    '' AS off_developmental_stage_display,
    '' AS biological_process_display,
    '' AS sex,
    '' AS ectopic,
    '' AS enhancer_or_silencer
FROM CRMSegment crms 
INNER JOIN Users curator ON crms.curator_id = curator.user_id
INNER JOIN Species sfs ON crms.sequence_from_species_id = sfs.species_id
INNER JOIN Gene g ON crms.gene_id = g.gene_id
INNER JOIN Chromosome c ON crms.chromosome_id = c.chromosome_id
INNER JOIN Species ais ON crms.assayed_in_species_id = ais.species_id
INNER JOIN CRMSegment_has_Expression_Term chet ON crms.crm_segment_id = chet.crm_segment_id
INNER JOIN ExpressionTerm et ON chet.term_id = et.term_id
WHERE crms.state IN ('approval' , 'approved', 'deleted', 'editing') AND 
    NOT EXISTS (SELECT triplestore_crm_segment.crm_segment_id 
                FROM triplestore_crm_segment
                WHERE chet.crm_segment_id = triplestore_crm_segment.crm_segment_id)
ORDER BY name,
	anatomical_expression_display;

CREATE OR REPLACE VIEW v_cis_regulatory_module_segment_ts_audit AS
SELECT crms.crm_segment_id AS id,
    sfs.scientific_name AS sequence_from_species_scientific_name,
    ais.scientific_name AS assayed_in_species_scientific_name,
    CONCAT(crms.name, ' ') AS name,
    CONCAT(curator.first_name, ' ', curator.last_name) AS curator_full_name,
    crms.state,
    CASE ts.pubmed_id
        WHEN NULL THEN ''
        ELSE ts.pubmed_id
    END AS pubmed_id,
    CONCAT(c.name, ' (', sfs.short_name, ')') AS chromosome_display,
    crms.current_start AS start,
    crms.current_end AS end,
    CONCAT(g.name, ' (', g.identifier, ')') AS gene_display,
    CONCAT(et.term, ' (', et.identifier, ')') AS anatomical_expression_display,
    CASE ds_on.identifier
        WHEN NULL THEN ''
        ELSE CONCAT(ds_on.term, ' (', ds_on.identifier, ')')
    END AS on_developmental_stage_display,    
    CASE ds_off.identifier
        WHEN NULL THEN ''
        ELSE CONCAT(ds_off.term, ' (', ds_off.identifier, ')')
    END AS off_developmental_stage_display,    
    CASE bp.go_id
        WHEN NULL THEN ''
        ELSE CONCAT(bp.term, ' (', bp.go_id, ')')
    END AS biological_process_display,
    CASE ts.sex
        WHEN NULL THEN ''
        ELSE ts.sex
    END AS sex,
    CASE ts.ectopic
        WHEN NULL THEN ''
        ELSE ts.ectopic
    END AS ectopic,
    ts.silencer AS enhancer_or_silencer
FROM CRMSegment crms
INNER JOIN Users curator ON crms.curator_id = curator.user_id
INNER JOIN Species sfs ON crms.sequence_from_species_id = sfs.species_id
INNER JOIN Gene g ON crms.gene_id = g.gene_id
INNER JOIN Chromosome c ON crms.chromosome_id = c.chromosome_id
INNER JOIN Species ais ON crms.assayed_in_species_id = ais.species_id
INNER JOIN triplestore_crm_segment ts ON crms.crm_segment_id = ts.crm_segment_id
INNER JOIN ExpressionTerm et ON ts.expression = et.identifier
INNER JOIN DevelopmentalStage ds_on ON ais.species_id = ds_on.species_id AND
    ts.stage_on = ds_on.identifier
INNER JOIN DevelopmentalStage ds_off ON ais.species_id = ds_off.species_id AND
    ts.stage_off = ds_off.identifier
LEFT OUTER JOIN BiologicalProcess bp ON ts.biological_process = bp.go_id
WHERE crms.state IN ('approval' , 'approved', 'deleted', 'editing') AND
    et.term <> ''
ORDER BY crms.name,
	et.term;

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
    END AS ectopic_term,
    UCASE(ts.silencer) AS enhancer_or_silencer
FROM CRMSegment crms
JOIN Species ais ON crms.assayed_in_species_id = ais.species_id
JOIN triplestore_crm_segment ts ON crms.crm_segment_id = ts.crm_segment_id
JOIN DevelopmentalStage ds_on ON ais.species_id = ds_on.species_id AND
	ts.stage_on = ds_on.identifier
JOIN DevelopmentalStage ds_off ON ais.species_id = ds_off.species_id AND
	ts.stage_off = ds_off.identifier
LEFT OUTER JOIN BiologicalProcess bp ON ts.biological_process = bp.go_id
WHERE crms.state = 'approved';

CREATE OR REPLACE VIEW v_predicted_cis_regulatory_module_audit AS
SELECT pcrm.predicted_crm_id AS id,
    pcrm.state,
    pcrm.name,
    pcrm.pubmed_id,
    pcrm.curator_id,
    curator.username AS curator_username,
    CONCAT(curator.first_name, ' ', curator.last_name) AS curator_full_name,
    CASE pcrm.auditor_id
        WHEN NULL THEN 0
        ELSE pcrm.auditor_id
    END AS auditor_id,
    CASE pcrm.auditor_id
        WHEN NULL THEN ''
        ELSE auditor.username
    END AS auditor_username,
    CASE pcrm.auditor_id
        WHEN NULL THEN ''
        ELSE CONCAT(auditor.first_name, ' ', auditor.last_name)
    END AS auditor_full_name,
    sfs.scientific_name AS sequence_from_species_scientific_name,
    CONCAT(c.name, ' (', sfs.short_name, ')') AS chromosome_display,
    pcrm.current_start AS start,
    pcrm.current_end AS end,
    CONCAT(c.name, ':', pcrm.current_start, '..', pcrm.current_end) AS coordinates,
    pcrm.sequence,
    e.term AS evidence,
    CASE es.term
        WHEN NULL THEN ''
        ELSE es.term
    END AS evidence_subtype,
    GROUP_CONCAT(DISTINCT CONCAT(et.term, ' (', et.identifier, ')') ORDER BY et.term ASC SEPARATOR ',') AS anatomical_expression_displays,
    pcrm.notes,
    ss.term AS sequence_source,
    pcrm.date_added,
    pcrm.last_update,
    pcrm.last_audit
FROM PredictedCRM pcrm
INNER JOIN Users curator ON pcrm.curator_id = curator.user_id
LEFT OUTER JOIN Users auditor ON pcrm.auditor_id = auditor.user_id
INNER JOIN Species sfs ON pcrm.sequence_from_species_id = sfs.species_id
INNER JOIN Chromosome c ON pcrm.chromosome_id = c.chromosome_id
INNER JOIN EvidenceTerm e ON pcrm.evidence_id = e.evidence_id
LEFT OUTER JOIN EvidenceSubtypeTerm es ON pcrm.evidence_subtype_id = es.evidence_subtype_id
INNER JOIN SequenceSourceTerm ss ON pcrm.sequence_source_id = ss.source_id
LEFT OUTER JOIN PredictedCRM_has_Expression_Term ON pcrm.predicted_crm_id = PredictedCRM_has_Expression_Term.predicted_crm_id
LEFT OUTER JOIN ExpressionTerm et ON PredictedCRM_has_Expression_Term.term_id = et.term_id
WHERE pcrm.state IN ('approval' , 'approved', 'deleted', 'editing')
GROUP BY pcrm.predicted_crm_id;

CREATE OR REPLACE VIEW v_predicted_cis_regulatory_module_file AS
SELECT CONCAT('RFPCRM:', LPAD(pcrm.entity_id, 10, '0'), '.', LPAD(pcrm.version, 3, '0')) AS redfly_id,
    CONCAT('RFPCRM:', LPAD(pcrm.entity_id, 10, '0')) AS redfly_id_unversioned,
    pcrm.predicted_crm_id,
    pcrm.pubmed_id,
    'REDfly_PCRM' AS label,
    pcrm.name,
    sfs.scientific_name AS sequence_from_species_scientific_name,
    IFNULL(pcrm.gene_locus, '') AS gene_locus,
    IFNULL(pcrm.gene_identifiers, '') AS gene_identifiers,
    pcrm.sequence,
    e.term AS evidence_term,
    es.term AS evidence_subtype_term,
    c.name AS chromosome,
    pcrm.current_start AS start,
    pcrm.current_end AS end,
    IFNULL(GROUP_CONCAT(DISTINCT et.identifier ORDER BY et.identifier SEPARATOR ','), '') AS ontology_term
FROM PredictedCRM pcrm
LEFT JOIN Species sfs ON pcrm.sequence_from_species_id = sfs.species_id
LEFT JOIN Chromosome c ON pcrm.chromosome_id = c.chromosome_id
LEFT JOIN EvidenceTerm e ON pcrm.evidence_id = e.evidence_id
LEFT JOIN EvidenceSubtypeTerm es ON pcrm.evidence_subtype_id = es.evidence_subtype_id
LEFT JOIN PredictedCRM_has_Expression_Term ON pcrm.predicted_crm_id = PredictedCRM_has_Expression_Term.predicted_crm_id
LEFT JOIN ExpressionTerm et ON PredictedCRM_has_Expression_Term.term_id = et.term_id
WHERE pcrm.state = 'current'
GROUP BY pcrm.predicted_crm_id;

CREATE OR REPLACE VIEW v_predicted_cis_regulatory_module_no_ts_audit AS
SELECT pcrm.predicted_crm_id AS id,
    sfs.scientific_name AS sequence_from_species_scientific_name,
    CONCAT(pcrm.name, ' ') AS name,
    CONCAT(curator.first_name, ' ', curator.last_name) AS curator_full_name,
    pcrm.state,
    pcrm.pubmed_id,
    CONCAT(c.name, ' (', sfs.short_name, ')') AS chromosome_display,
    pcrm.current_start AS start,
    pcrm.current_end AS end,
    CONCAT(et.term, ' (', et.identifier, ')') AS anatomical_expression_display,
    '' AS on_developmental_stage_display,
    '' AS off_developmental_stage_display,
    '' AS biological_process_display,
    '' AS sex,
    '' AS ectopic,
    '' AS enhancer_or_silencer
FROM PredictedCRM pcrm
INNER JOIN Users curator ON pcrm.curator_id = curator.user_id
INNER JOIN Species sfs ON pcrm.sequence_from_species_id = sfs.species_id
INNER JOIN Chromosome c ON pcrm.chromosome_id = c.chromosome_id
INNER JOIN PredictedCRM_has_Expression_Term phet ON pcrm.predicted_crm_id = phet.predicted_crm_id
INNER JOIN ExpressionTerm et ON phet.term_id = et.term_id 
WHERE pcrm.state IN ('approval' , 'approved', 'deleted', 'editing') AND
    NOT EXISTS (SELECT triplestore_predicted_crm.predicted_crm_id
                FROM triplestore_predicted_crm
                WHERE phet.predicted_crm_id = triplestore_predicted_crm.predicted_crm_id) 
ORDER BY name,
	anatomical_expression_display;

CREATE OR REPLACE VIEW v_predicted_cis_regulatory_module_ts_audit AS
SELECT pcrm.predicted_crm_id AS id,
    sfs.scientific_name AS sequence_from_species_scientific_name,
    CONCAT(pcrm.name, ' ') AS name,
    CONCAT(curator.first_name, ' ', curator.last_name) AS curator_full_name,
    pcrm.state,
    CASE ts.pubmed_id
        WHEN NULL THEN ''
        ELSE ts.pubmed_id
    END AS pubmed_id,
    CONCAT(c.name, ' (', sfs.short_name, ')') AS chromosome_display,
    pcrm.current_start AS start,
    pcrm.current_end AS end,
    CONCAT(et.term, ' (', et.identifier, ')') AS anatomical_expression_display,
    CASE ds_on.identifier
        WHEN NULL THEN ''
        ELSE CONCAT(ds_on.term, ' (', ds_on.identifier, ')')
    END AS on_developmental_stage_display,    
    CASE ds_off.identifier
        WHEN NULL THEN ''
        ELSE CONCAT(ds_off.term, ' (', ds_off.identifier, ')')
    END AS off_developmental_stage_display,    
    CASE bp.go_id
        WHEN NULL THEN ''
        ELSE CONCAT(bp.term, ' (', bp.go_id, ')')
    END AS biological_process_display,
    CASE ts.sex
        WHEN NULL THEN ''
        ELSE ts.sex
    END AS sex,
    ts.silencer AS enhancer_or_silencer
FROM PredictedCRM pcrm
INNER JOIN Users curator ON pcrm.curator_id = curator.user_id
INNER JOIN Species sfs ON pcrm.sequence_from_species_id = sfs.species_id
INNER JOIN Chromosome c ON pcrm.chromosome_id = c.chromosome_id
INNER JOIN triplestore_predicted_crm ts ON pcrm.predicted_crm_id = ts.predicted_crm_id
INNER JOIN ExpressionTerm et ON ts.expression = et.identifier
INNER JOIN DevelopmentalStage ds_on ON sfs.species_id = ds_on.species_id AND
    ts.stage_on = ds_on.identifier
INNER JOIN DevelopmentalStage ds_off ON sfs.species_id = ds_off.species_id AND
    ts.stage_off = ds_off.identifier
LEFT OUTER JOIN BiologicalProcess bp ON ts.biological_process = bp.go_id
WHERE pcrm.state IN ('approval' , 'approved', 'deleted', 'editing') AND
    et.term <> ''
ORDER BY pcrm.name,
	et.term;

CREATE OR REPLACE VIEW v_predicted_cis_regulatory_module_ts_notify_author AS
SELECT ts.predicted_crm_id,
    ts.expression AS expression_identifier,
    ds_on.term AS stage_on_term,
    ds_off.term AS stage_off_term,
    CASE bp.term
        WHEN NULL THEN ''
        ELSE bp.term
    END AS biological_process_term,
    UCASE(ts.sex) AS sex_term,
    UCASE(ts.silencer) AS enhancer_or_silencer
FROM PredictedCRM pcrm
JOIN Species sfs ON pcrm.sequence_from_species_id = sfs.species_id
JOIN triplestore_predicted_crm ts ON pcrm.predicted_crm_id = ts.predicted_crm_id
JOIN DevelopmentalStage ds_on ON sfs.species_id = ds_on.species_id AND
	ts.stage_on = ds_on.identifier
JOIN DevelopmentalStage ds_off ON sfs.species_id = ds_off.species_id AND
	ts.stage_off = ds_off.identifier
LEFT OUTER JOIN BiologicalProcess bp ON ts.biological_process = bp.go_id
WHERE pcrm.state = 'approved';

DROP VIEW IF EXISTS v_reporter_construct_minimalization;

CREATE OR REPLACE VIEW v_reporter_construct_no_ts_audit AS
SELECT rc.rc_id AS id,
    sfs.scientific_name AS sequence_from_species_scientific_name,
    ais.scientific_name AS assayed_in_species_scientific_name,
    CONCAT(rc.name, ' ') AS name,
    CONCAT(curator.first_name, ' ', curator.last_name) AS curator_full_name,
    rc.state,
    rc.pubmed_id,
    CONCAT(c.name, ' (', sfs.short_name, ')') AS chromosome_display,
    rc.current_start AS start,
    rc.current_end AS end,
    CONCAT(g.name, ' (', g.identifier, ')') AS gene_display,
    CONCAT(et.term , ' (', et.identifier , ')') AS anatomical_expression_display,
    '' AS on_developmental_stage_display,
    '' AS off_developmental_stage_display,
    '' AS biological_process_display,
    '' AS sex,
    '' AS ectopic,
    '' AS enhancer_or_silencer
FROM ReporterConstruct rc
INNER JOIN Users curator ON rc.curator_id = curator.user_id
INNER JOIN Species sfs ON rc.sequence_from_species_id = sfs.species_id
INNER JOIN Gene g ON rc.gene_id = g.gene_id
INNER JOIN Chromosome c ON rc.chromosome_id = c.chromosome_id
INNER JOIN Species ais ON rc.assayed_in_species_id = ais.species_id
INNER JOIN RC_has_ExprTerm rhet ON rc.rc_id = rhet.rc_id
INNER JOIN ExpressionTerm et ON rhet.term_id = et.term_id 
WHERE rc.state IN ('approval' , 'approved', 'deleted', 'editing') AND
	NOT EXISTS (SELECT triplestore_rc.rc_id 
	            FROM triplestore_rc
	            WHERE rhet.rc_id = triplestore_rc.rc_id)
ORDER BY name,
	anatomical_expression_display;

CREATE OR REPLACE VIEW v_reporter_construct_ts_audit AS
SELECT rc.rc_id AS id,
    sfs.scientific_name AS sequence_from_species_scientific_name,
    ais.scientific_name AS assayed_in_species_scientific_name,
    CONCAT(rc.name, ' ') AS name,
    CONCAT(curator.first_name, ' ', curator.last_name) AS curator_full_name,
    rc.state,
    CASE ts.pubmed_id
        WHEN NULL THEN ''
        ELSE ts.pubmed_id
    END AS pubmed_id,
    CONCAT(c.name, ' (', sfs.short_name, ')') AS chromosome_display,
    rc.current_start AS start,
    rc.current_end AS end,
    CONCAT(g.name, ' (', g.identifier, ')') AS gene_display,
    CONCAT(et.term, ' (', et.identifier, ')') AS anatomical_expression_display,
    CASE ds_on.identifier
        WHEN NULL THEN ''
        ELSE CONCAT(ds_on.term, ' (', ds_on.identifier, ')')
    END AS on_developmental_stage_display,    
    CASE ds_off.identifier
        WHEN NULL THEN ''
        ELSE CONCAT(ds_off.term, ' (', ds_off.identifier, ')')
    END AS off_developmental_stage_display,    
    CASE bp.go_id
        WHEN NULL THEN ''
        ELSE CONCAT(bp.term, ' (', bp.go_id, ')')
    END AS biological_process_display,
    CASE ts.sex
        WHEN NULL THEN ''
        ELSE ts.sex
    END AS sex,
    CASE ts.ectopic
        WHEN NULL THEN ''
        ELSE ts.ectopic
    END AS ectopic,
    ts.silencer AS enhancer_or_silencer
FROM ReporterConstruct rc
INNER JOIN Users curator ON rc.curator_id = curator.user_id
INNER JOIN Species sfs ON rc.sequence_from_species_id = sfs.species_id
INNER JOIN Gene g ON rc.gene_id = g.gene_id
INNER JOIN Chromosome c ON rc.chromosome_id = c.chromosome_id
INNER JOIN Species ais ON rc.assayed_in_species_id = ais.species_id
INNER JOIN triplestore_rc ts ON rc.rc_id = ts.rc_id
INNER JOIN ExpressionTerm et ON ts.expression = et.identifier
INNER JOIN DevelopmentalStage ds_on ON ais.species_id = ds_on.species_id AND
    ts.stage_on = ds_on.identifier
INNER JOIN DevelopmentalStage ds_off ON ais.species_id = ds_off.species_id AND
    ts.stage_off = ds_off.identifier
LEFT OUTER JOIN BiologicalProcess bp ON ts.biological_process = bp.go_id
WHERE rc.state IN ('approval' , 'approved', 'deleted', 'editing') AND
    et.term <> ''
ORDER BY rc.name,
	et.term;

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
    END AS ectopic_term,
    UCASE(ts.silencer) AS enhancer_or_silencer
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

\! echo "Making function transformations..."; 

DROP FUNCTION IF EXISTS NumberOfCurrentCisRegulatoryModulesHavingCellLineOnly; 

DROP FUNCTION IF EXISTS NumberOfCurrentNonInVivoCisRegulatoryModulesHavingNoCellLine;

DELIMITER //

CREATE OR REPLACE FUNCTION NumberOfCurrentCisRegulatoryModulesHavingCellCultureOnly(species_id INT)
RETURNS INT DETERMINISTIC
BEGIN
	DECLARE number_of_current_cis_regulatory_modules_having_cell_culture_only INT;

    IF species_id = 0
    THEN
	    SELECT COUNT(rc_id)
	    INTO number_of_current_cis_regulatory_modules_having_cell_culture_only
	    FROM ReporterConstruct
	    WHERE state = 'current' AND
		    is_crm = 1 AND
		    cell_culture_only = 1;
    ELSE
	    SELECT COUNT(rc_id)
	    INTO number_of_current_cis_regulatory_modules_having_cell_culture_only
	    FROM ReporterConstruct
	    WHERE sequence_from_species_id = species_id AND
            state = 'current' AND
		    is_crm = 1 AND
		    cell_culture_only = 1;    
    END IF;

	RETURN number_of_current_cis_regulatory_modules_having_cell_culture_only;
END; //

CREATE OR REPLACE FUNCTION NumberOfCurrentNonInVivoCisRegulatoryModulesHavingNoCellCulture(species_id INT)
RETURNS INT DETERMINISTIC
BEGIN
	DECLARE number_of_current_non_in_vivo_cis_regulatory_modules_having_no_cell_culture INT;

    IF species_id = 0
    THEN
	    SELECT COUNT(rc_id)
	    INTO number_of_current_non_in_vivo_cis_regulatory_modules_having_no_cell_culture
	    FROM ReporterConstruct
	    WHERE state = 'current' AND
		    is_crm = 1 AND
		    evidence_id != 2 AND
		    cell_culture_only = 0;
    ELSE
	    SELECT COUNT(rc_id)
	    INTO number_of_current_non_in_vivo_cis_regulatory_modules_having_no_cell_culture
	    FROM ReporterConstruct
	    WHERE sequence_from_species_id = species_id AND
            state = 'current' AND
		    is_crm = 1 AND
		    evidence_id != 2 AND
		    cell_culture_only = 0;    
    END IF;

	RETURN number_of_current_non_in_vivo_cis_regulatory_modules_having_no_cell_culture;
END; //

DELIMITER ;

\! echo "Done!";