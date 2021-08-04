\! echo "Making table transformations..."; 

ALTER TABLE PredictedCRM
ADD COLUMN gene_identifiers TEXT DEFAULT ''
AFTER archived_ends;

UPDATE PredictedCRM pcrm1
SET gene_identifiers = (SELECT GROUP_CONCAT(DISTINCT g.identifier ORDER BY g.name ASC SEPARATOR ',')
                        FROM PredictedCRM pcrm2 
                        JOIN Chromosome c USING (chromosome_id)
                        LEFT OUTER JOIN Gene g ON pcrm2.chromosome_id = g.chrm_id AND
                            pcrm2.current_start > (g.start - 10000) AND
                            pcrm2.current_end < (g.stop + 10000)
                        WHERE pcrm1.predicted_crm_id = pcrm2.predicted_crm_id);

UPDATE PredictedCRM pcrm1
SET gene_locus = (SELECT GROUP_CONCAT(DISTINCT g.name ORDER BY g.name ASC SEPARATOR ',')
                  FROM PredictedCRM pcrm2 
                  JOIN Chromosome c USING (chromosome_id)
                  LEFT OUTER JOIN Gene g ON pcrm2.chromosome_id = g.chrm_id AND
                      pcrm2.current_start > (g.start - 10000) AND
                      pcrm2.current_end < (g.stop + 10000)
                  WHERE pcrm1.predicted_crm_id = pcrm2.predicted_crm_id);

ALTER TABLE CRMSegment_has_FigureLabel 
ADD PRIMARY KEY (crm_segment_id, label);

DELETE FROM RC_has_FigureLabel
WHERE rc_id = 259 AND
	label = '2F';

INSERT INTO RC_has_FigureLabel (rc_id, label)
VALUES (259, '2F');

DELETE FROM RC_has_FigureLabel
WHERE rc_id = 7218 AND
	label = '2F';

INSERT INTO RC_has_FigureLabel (rc_id, label)
VALUES (7218, '2F');

DELETE FROM RC_has_FigureLabel
WHERE rc_id = 10735 AND
	label = '2F';

INSERT INTO RC_has_FigureLabel (rc_id, label)
VALUES (10735, '2F');

DELETE FROM RC_has_FigureLabel
WHERE rc_id = 52957 AND
	label = '1F';

INSERT INTO RC_has_FigureLabel (rc_id, label)
VALUES (52957, '1F');

DELETE FROM RC_has_FigureLabel
WHERE rc_id = 50503 AND
	label = '6B';

INSERT INTO RC_has_FigureLabel (rc_id, label)
VALUES (50503, '6B');

DELETE FROM RC_has_FigureLabel
WHERE rc_id = 50566 AND
	label = 'S1H';

INSERT INTO RC_has_FigureLabel (rc_id, label)
VALUES (50566, 'S1H');

DELETE FROM RC_has_FigureLabel
WHERE rc_id = 61524 AND
	label = '1F';

INSERT INTO RC_has_FigureLabel (rc_id, label)
VALUES (61524, '1F');

DELETE FROM RC_has_FigureLabel
WHERE rc_id = 62124 AND
	label = '6B';

INSERT INTO RC_has_FigureLabel (rc_id, label)
VALUES (62124, '6B');

DELETE FROM RC_has_FigureLabel
WHERE rc_id = 63152 AND
	label = 'S1H';

INSERT INTO RC_has_FigureLabel (rc_id, label)
VALUES (63152, 'S1H');

DELETE FROM RC_has_FigureLabel
WHERE rc_id = 67800 AND
	label = '6B';

INSERT INTO RC_has_FigureLabel (rc_id, label)
VALUES (67800, '6B');

DELETE FROM RC_has_FigureLabel
WHERE rc_id = 73231 AND
	label = 'S1H';
    
INSERT INTO RC_has_FigureLabel (rc_id, label)
VALUES (73231, 'S1H');

DELETE FROM RC_has_FigureLabel
WHERE rc_id = 78571 AND
	label = '1D2';

INSERT INTO RC_has_FigureLabel (rc_id, label)
VALUES (78571, '1D2');

DELETE FROM RC_has_FigureLabel
WHERE rc_id = 76301 AND
	label = 'S1E';

INSERT INTO RC_has_FigureLabel (rc_id, label)
VALUES (76301, 'S1E');

DELETE FROM RC_has_FigureLabel
WHERE rc_id = 80035 AND
	label = '3C';

INSERT INTO RC_has_FigureLabel (rc_id, label)
VALUES (80035, '3C');

DELETE FROM RC_has_FigureLabel
WHERE rc_id = 80563 AND
	label = '3C';

INSERT INTO RC_has_FigureLabel (rc_id, label)
VALUES (80563, '3C');

DELETE FROM RC_has_FigureLabel
WHERE rc_id = 80630 AND
	label = '3C';

INSERT INTO RC_has_FigureLabel (rc_id, label)
VALUES (80630, '3C');

DELETE FROM RC_has_FigureLabel
WHERE rc_id = 86785 AND
	label = '3F';

INSERT INTO RC_has_FigureLabel (rc_id, label)
VALUES (86785, '3F');

ALTER TABLE RC_has_FigureLabel 
ADD PRIMARY KEY (rc_id, label);

DELETE FROM BS_has_FigureLabel
WHERE tfbs_id = 2743 AND
	label = '4G';

INSERT INTO BS_has_FigureLabel (tfbs_id, label)
VALUES (2743, '4G');

DELETE FROM BS_has_FigureLabel
WHERE tfbs_id = 5021 AND
	label = '4G';
	
INSERT INTO BS_has_FigureLabel (tfbs_id, label)
VALUES (5021, '4G');

DELETE FROM BS_has_FigureLabel
WHERE tfbs_id = 6501 AND
	label = '2R';
	
INSERT INTO BS_has_FigureLabel (tfbs_id, label)
VALUES (6501, '2R');

DELETE FROM BS_has_FigureLabel
WHERE tfbs_id = 6502 AND
	label = '2R';
	
INSERT INTO BS_has_FigureLabel (tfbs_id, label)
VALUES (6502, '2R');

DELETE FROM BS_has_FigureLabel
WHERE tfbs_id = 6078 AND
	label = '2R';
	
INSERT INTO BS_has_FigureLabel (tfbs_id, label)
VALUES (6078, '2R');

ALTER TABLE BS_has_FigureLabel 
ADD PRIMARY KEY (tfbs_id, label);

ALTER TABLE ext_FlyExpressImage
ADD PRIMARY KEY (pubmed_id, label);

ALTER TABLE inferred_crm_read_model
ADD COLUMN size INT(10) UNSIGNED NOT NULL
AFTER current_end;

UPDATE inferred_crm
SET size = current_end - current_start + 1;

UPDATE inferred_crm_read_model
SET size = current_end - current_start + 1;

\! echo "Done!";

\! echo "Making view transformations..."; 

CREATE OR REPLACE VIEW v_cis_regulatory_module_segment_audit AS
SELECT crms.crm_segment_id AS id,
    crms.state,
    crms.name,
    crms.pubmed_id,
    crms.curator_id,
    curator.username AS curator_username,
    CONCAT(curator.first_name, ' ', curator.last_name) AS curator_full_name,
    CASE crms.auditor_id
        WHEN NULL THEN 0
        ELSE crms.auditor_id
    END AS auditor_id,
    CASE crms.auditor_id
        WHEN NULL THEN ''
        ELSE auditor.username
    END AS auditor_username,
    CASE crms.auditor_id
        WHEN NULL THEN ''
        ELSE CONCAT(auditor.first_name, ' ', auditor.last_name)
    END AS auditor_full_name,
    sfs.scientific_name AS sequence_from_species_scientific_name,
    ais.scientific_name AS assayed_in_species_scientific_name,
    CONCAT(g.name, ' (', g.identifier, ')') AS gene_display,
    CONCAT(c.name, ' (', sfs.short_name, ')') AS chromosome_display,
    crms.current_start AS start,
    crms.current_end AS end,
    CONCAT(c.name, ':', crms.current_start, '..', crms.current_end) AS coordinates,
    crms.sequence,
    crms.fbtp,
    crms.figure_labels,
    e.term AS evidence,
    CASE es.term
        WHEN NULL THEN ''
        ELSE es.term
    END AS evidence_subtype,
    GROUP_CONCAT(DISTINCT CONCAT(et.term, ' (', et.identifier, ')') ORDER BY et.term ASC SEPARATOR ',') AS anatomical_expression_displays,
    crms.notes,
    ss.term AS sequence_source,
    crms.date_added,
    crms.last_update,
    crms.last_audit
FROM CRMSegment crms
INNER JOIN Users curator ON crms.curator_id = curator.user_id
LEFT OUTER JOIN Users auditor ON crms.auditor_id = auditor.user_id
INNER JOIN Species sfs ON crms.sequence_from_species_id = sfs.species_id
INNER JOIN Species ais ON crms.assayed_in_species_id = ais.species_id
INNER JOIN Gene g ON crms.gene_id = g.gene_id
INNER JOIN Chromosome c ON crms.chromosome_id = c.chromosome_id
INNER JOIN EvidenceTerm e ON crms.evidence_id = e.evidence_id
LEFT OUTER JOIN EvidenceSubtypeTerm es ON crms.evidence_subtype_id = es.evidence_subtype_id
INNER JOIN SequenceSourceTerm ss ON crms.sequence_source_id = ss.source_id
LEFT OUTER JOIN CRMSegment_has_Expression_Term ON crms.crm_segment_id = CRMSegment_has_Expression_Term.crm_segment_id
LEFT OUTER JOIN ExpressionTerm et ON CRMSegment_has_Expression_Term.term_id = et.term_id
WHERE crms.state IN ('approval' , 'approved', 'deleted', 'editing')
GROUP BY crms.crm_segment_id;

CREATE OR REPLACE VIEW v_cis_regulatory_module_segment_no_ts_audit AS
SELECT crms1.crm_segment_id AS id,
    sfs.scientific_name AS sequence_from_species_scientific_name,
    ais.scientific_name AS assayed_in_species_scientific_name,
    CONCAT(crms1.name, ' ') AS name,
    CONCAT(curator.first_name, ' ', curator.last_name) AS curator_full_name,
    crms1.state,
    crms1.pubmed_id,
    CONCAT(c.name, ' (', sfs.short_name, ')') AS chromosome_display,
    crms1.current_start AS start,
    crms1.current_end AS end,
    CONCAT(g.name, ' (', g.identifier, ')') AS gene_display,
    CONCAT(crms1.anatomical_expression_term, ' (', crms1.anatomical_expression_identifier, ')') AS anatomical_expression_display,
    '' AS on_developmental_stage_display,
    '' AS off_developmental_stage_display,
    '' AS biological_process_display,
    '' AS sex,
    '' AS ectopic
FROM (SELECT crms.crm_segment_id,
          crms.name,
          crms.state,
          crms.pubmed_id,
          crms.curator_id,
          crms.sequence_from_species_id,
          crms.gene_id,
          crms.chromosome_id,
          crms.current_start,
          crms.current_end,
          crms.assayed_in_species_id,
          et.identifier AS anatomical_expression_identifier,
          et.term AS anatomical_expression_term  
      FROM CRMSegment crms, 
          CRMSegment_has_Expression_Term crmshet, 
          ExpressionTerm et 
      WHERE crms.state IN ('approval' , 'approved', 'deleted', 'editing') AND 
          crms.crm_segment_id = crmshet.crm_segment_id AND
          crmshet.term_id = et.term_id) AS crms1
INNER JOIN Users curator ON crms1.curator_id = curator.user_id
INNER JOIN Species sfs ON crms1.sequence_from_species_id = sfs.species_id
INNER JOIN Gene g ON crms1.gene_id = g.gene_id
INNER JOIN Chromosome c ON crms1.chromosome_id = c.chromosome_id
INNER JOIN Species ais ON crms1.assayed_in_species_id = ais.species_id
INNER JOIN (SELECT crms.crm_segment_id,
                GROUP_CONCAT(tscrms.expression) AS anatomical_expression_identifiers
            FROM CRMSegment crms,
                triplestore_crm_segment tscrms 
            WHERE crms.state IN ('approval' , 'approved', 'deleted', 'editing') AND
                crms.crm_segment_id = tscrms.crm_segment_id 
            GROUP BY crms.crm_segment_id) AS crms2 ON crms1.crm_segment_id = crms2.crm_segment_id AND
                LOCATE(crms1.anatomical_expression_identifier, crms2.anatomical_expression_identifiers) = 0
ORDER BY crms1.name,
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
    END AS ectopic
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
    ais.scientific_name AS assayed_in_species_scientific_name,
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
INNER JOIN Species ais ON pcrm.assayed_in_species_id = ais.species_id
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
    ais.scientific_name AS assayed_in_species_scientific_name,
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
LEFT JOIN Species ais ON pcrm.assayed_in_species_id = ais.species_id
LEFT JOIN Chromosome c ON pcrm.chromosome_id = c.chromosome_id
LEFT JOIN EvidenceTerm e ON pcrm.evidence_id = e.evidence_id
LEFT JOIN EvidenceSubtypeTerm es ON pcrm.evidence_subtype_id = es.evidence_subtype_id
LEFT JOIN PredictedCRM_has_Expression_Term ON pcrm.predicted_crm_id = PredictedCRM_has_Expression_Term.predicted_crm_id
LEFT JOIN ExpressionTerm et ON PredictedCRM_has_Expression_Term.term_id = et.term_id
WHERE pcrm.state = 'current'
GROUP BY pcrm.predicted_crm_id;

CREATE OR REPLACE VIEW v_predicted_cis_regulatory_module_no_ts_audit AS
SELECT pcrm1.predicted_crm_id AS id,
    sfs.scientific_name AS sequence_from_species_scientific_name,
    ais.scientific_name AS assayed_in_species_scientific_name,
    CONCAT(pcrm1.name, ' ') AS name,
    CONCAT(curator.first_name, ' ', curator.last_name) AS curator_full_name,
    pcrm1.state,
    pcrm1.pubmed_id,
    CONCAT(c.name, ' (', sfs.short_name, ')') AS chromosome_display,
    pcrm1.current_start AS start,
    pcrm1.current_end AS end,
    CONCAT(pcrm1.anatomical_expression_term, ' (', pcrm1.anatomical_expression_identifier, ')') AS anatomical_expression_display,
    '' AS on_developmental_stage_display,
    '' AS off_developmental_stage_display,
    '' AS biological_process_display,
    '' AS sex,
    '' AS ectopic
FROM (SELECT pcrm.predicted_crm_id,
          pcrm.name,
          pcrm.state,
          pcrm.pubmed_id,
          pcrm.curator_id,
          pcrm.sequence_from_species_id,
          pcrm.chromosome_id,
          pcrm.current_start,
          pcrm.current_end,
          pcrm.assayed_in_species_id,
          et.identifier AS anatomical_expression_identifier,
          et.term AS anatomical_expression_term  
      FROM PredictedCRM pcrm,
          PredictedCRM_has_Expression_Term pcrmhet,
          ExpressionTerm et 
      WHERE pcrm.state IN ('approval' , 'approved', 'deleted', 'editing') AND 
          pcrm.predicted_crm_id = pcrmhet.predicted_crm_id AND
          pcrmhet.term_id = et.term_id) AS pcrm1
INNER JOIN Users curator ON pcrm1.curator_id = curator.user_id
INNER JOIN Species sfs ON pcrm1.sequence_from_species_id = sfs.species_id
INNER JOIN Chromosome c ON pcrm1.chromosome_id = c.chromosome_id
INNER JOIN Species ais ON pcrm1.assayed_in_species_id = ais.species_id
INNER JOIN (SELECT pcrm.predicted_crm_id,
                GROUP_CONCAT(tspcrm.expression) AS anatomical_expression_identifiers
            FROM PredictedCRM pcrm,
                triplestore_predicted_crm tspcrm 
            WHERE pcrm.state IN ('approval' , 'approved', 'deleted', 'editing') AND
                pcrm.predicted_crm_id = tspcrm.predicted_crm_id 
            GROUP BY pcrm.predicted_crm_id) AS pcrm2 ON pcrm1.predicted_crm_id = pcrm2.predicted_crm_id AND
                LOCATE(pcrm1.anatomical_expression_identifier, pcrm2.anatomical_expression_identifiers) = 0
ORDER BY pcrm1.name,
	anatomical_expression_display;

CREATE OR REPLACE VIEW v_predicted_cis_regulatory_module_ts_audit AS
SELECT pcrm.predicted_crm_id AS id,
    sfs.scientific_name AS sequence_from_species_scientific_name,
    ais.scientific_name AS assayed_in_species_scientific_name,
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
    END AS sex
FROM PredictedCRM pcrm
INNER JOIN Users curator ON pcrm.curator_id = curator.user_id
INNER JOIN Species sfs ON pcrm.sequence_from_species_id = sfs.species_id
INNER JOIN Chromosome c ON pcrm.chromosome_id = c.chromosome_id
INNER JOIN Species ais ON pcrm.assayed_in_species_id = ais.species_id
INNER JOIN triplestore_predicted_crm ts ON pcrm.predicted_crm_id = ts.predicted_crm_id
INNER JOIN ExpressionTerm et ON ts.expression = et.identifier
INNER JOIN DevelopmentalStage ds_on ON ais.species_id = ds_on.species_id AND
    ts.stage_on = ds_on.identifier
INNER JOIN DevelopmentalStage ds_off ON ais.species_id = ds_off.species_id AND
    ts.stage_off = ds_off.identifier
LEFT OUTER JOIN BiologicalProcess bp ON ts.biological_process = bp.go_id
WHERE pcrm.state IN ('approval' , 'approved', 'deleted', 'editing') AND
    et.term <> ''
ORDER BY pcrm.name,
	et.term;

CREATE OR REPLACE VIEW v_reporter_construct_audit AS
SELECT rc.rc_id AS id,
    rc.state,
    rc.name,
    rc.pubmed_id,
    rc.curator_id,
    curator.username AS curator_username,
    CONCAT(curator.first_name, ' ', curator.last_name) AS curator_full_name,
    CASE rc.auditor_id
        WHEN NULL THEN 0
        ELSE rc.auditor_id
    END AS auditor_id,
    CASE rc.auditor_id
        WHEN NULL THEN ''
        ELSE auditor.username
    END AS auditor_username,
    CASE rc.auditor_id
        WHEN NULL THEN ''
        ELSE CONCAT(auditor.first_name, ' ', auditor.last_name)
    END AS auditor_full_name,
    sfs.scientific_name AS sequence_from_species_scientific_name,
    ais.scientific_name AS assayed_in_species_scientific_name,
    CONCAT(g.name, ' (', g.identifier, ')') AS gene_display,
    CONCAT(c.name, ' (', sfs.short_name, ')') AS chromosome_display,
    rc.current_start AS start,
    rc.current_end AS end,
    CONCAT(c.name, ':', rc.current_start, '..', rc.current_end) AS coordinates,
    rc.sequence,
    rc.fbtp,
    rc.figure_labels,
    e.term AS evidence,
    GROUP_CONCAT(DISTINCT CONCAT(et.term, ' (', et.identifier, ')') ORDER BY et.term ASC SEPARATOR ',') AS anatomical_expression_displays,
    rc.notes,
    ss.term AS sequence_source,
    rc.date_added,
    rc.last_update,
    rc.last_audit
FROM ReporterConstruct rc
INNER JOIN Users curator ON rc.curator_id = curator.user_id
LEFT OUTER JOIN Users auditor ON rc.auditor_id = auditor.user_id
INNER JOIN Species sfs ON rc.sequence_from_species_id = sfs.species_id
INNER JOIN Species ais ON rc.assayed_in_species_id = ais.species_id
INNER JOIN Gene g ON rc.gene_id = g.gene_id
INNER JOIN Chromosome c ON rc.chromosome_id = c.chromosome_id
INNER JOIN EvidenceTerm e ON rc.evidence_id = e.evidence_id
INNER JOIN SequenceSourceTerm ss ON rc.sequence_source_id = ss.source_id
LEFT OUTER JOIN RC_has_ExprTerm ON rc.rc_id = RC_has_ExprTerm.rc_id
LEFT OUTER JOIN ExpressionTerm et ON RC_has_ExprTerm.term_id = et.term_id
WHERE rc.state IN ('approval' , 'approved', 'deleted', 'editing')
GROUP BY rc.rc_id;

CREATE OR REPLACE VIEW v_reporter_construct_no_ts_audit AS
SELECT rc1.rc_id AS id,
    sfs.scientific_name AS sequence_from_species_scientific_name,
    ais.scientific_name AS assayed_in_species_scientific_name,
    CONCAT(rc1.name, ' ') AS name,
    CONCAT(curator.first_name, ' ', curator.last_name) AS curator_full_name,
    rc1.state,
    rc1.pubmed_id,
    CONCAT(c.name, ' (', sfs.short_name, ')') AS chromosome_display,
    rc1.current_start AS start,
    rc1.current_end AS end,
    CONCAT(g.name, ' (', g.identifier, ')') AS gene_display,
    CONCAT(rc1.anatomical_expression_term, ' (', rc1.anatomical_expression_identifier, ')') AS anatomical_expression_display,
    '' AS on_developmental_stage_display,
    '' AS off_developmental_stage_display,
    '' AS biological_process_display,
    '' AS sex,
    '' AS ectopic
FROM (SELECT rc.rc_id,
          rc.name,
          rc.pubmed_id,
          rc.state,
          rc.curator_id,
          rc.sequence_from_species_id,
          rc.gene_id,
          rc.chromosome_id,
          rc.current_start,
          rc.current_end,
          rc.assayed_in_species_id,
          et.identifier AS anatomical_expression_identifier,
          et.term AS anatomical_expression_term  
      FROM ReporterConstruct rc,
          RC_has_ExprTerm rchet,
          ExpressionTerm et 
      WHERE rc.state IN ('approval' , 'approved', 'deleted', 'editing') AND 
          rc.rc_id = rchet.rc_id AND
          rchet.term_id = et.term_id) AS rc1
INNER JOIN Users curator ON rc1.curator_id = curator.user_id
INNER JOIN Species sfs ON rc1.sequence_from_species_id = sfs.species_id
INNER JOIN Gene g ON rc1.gene_id = g.gene_id
INNER JOIN Chromosome c ON rc1.chromosome_id = c.chromosome_id
INNER JOIN Species ais ON rc1.assayed_in_species_id = ais.species_id
INNER JOIN (SELECT rc.rc_id,
                GROUP_CONCAT(tsrc.expression) AS anatomical_expression_identifiers
            FROM ReporterConstruct rc,
                triplestore_rc tsrc
            WHERE rc.state IN ('approval' , 'approved', 'deleted', 'editing') AND
                rc.rc_id = tsrc.rc_id 
            GROUP BY rc.rc_id) AS rc2 ON rc1.rc_id = rc2.rc_id AND
                LOCATE(rc1.anatomical_expression_identifier, rc2.anatomical_expression_identifiers) = 0
ORDER BY rc1.name,
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
    END AS ectopic
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

CREATE OR REPLACE VIEW v_transcription_factor_binding_site_audit AS
SELECT tfbs.tfbs_id AS id,
    tfbs.state,
    tfbs.name,
    tfbs.pubmed_id,
    tfbs.curator_id,
    CONCAT(u.first_name, ' ', u.last_name) AS curator_full_name,
    sfs.scientific_name AS sequence_from_species_scientific_name,
    ais.scientific_name AS assayed_in_species_scientific_name,
    CONCAT(g.name, ' (', g.identifier, ')') AS gene_display,
    CONCAT(g2.name, ' (', g2.identifier, ')') AS transcription_factor_display,
    CONCAT(c.name, ' (', sfs.short_name, ')') AS chromosome_display,
    tfbs.current_start AS start,
    tfbs.current_end AS end,
    CONCAT(c.name, ':', tfbs.current_start, '..', tfbs.current_end) AS coordinates,
    tfbs.notes,
    tfbs.date_added,
    tfbs.last_update
FROM BindingSite tfbs
INNER JOIN Species sfs ON tfbs.sequence_from_species_id = sfs.species_id
INNER JOIN Species ais ON tfbs.assayed_in_species_id = ais.species_id
INNER JOIN Users u ON tfbs.curator_id = u.user_id
INNER JOIN Gene g ON tfbs.gene_id = g.gene_id
INNER JOIN Gene g2 ON tfbs.tf_id = g2.gene_id
INNER JOIN Chromosome c ON tfbs.chromosome_id = c.chromosome_id
WHERE tfbs.state IN ('approval' , 'approved', 'editing', 'rejected')
ORDER BY tfbs.tfbs_id;

\! echo "Done!";

\! echo "Making procedure transformations..."; 

DELIMITER //

CREATE OR REPLACE PROCEDURE insert_inferred_crm(
    IN sequence_from_species_id INT UNSIGNED,
    IN assayed_in_species_id INT UNSIGNED,
    IN current_genome_assembly_release_version VARCHAR(32),
    IN chromosome_id INT UNSIGNED,
    IN current_start INT UNSIGNED,
    IN current_end INT UNSIGNED,
    IN size INT UNSIGNED,
    IN expression_ids TEXT,
    IN component_rc_ids TEXT
)   MODIFIES SQL DATA
    SQL SECURITY INVOKER
    COMMENT 'Inserts a new inferred CRM into the database.'
BEGIN
    DECLARE last_icrm_id BIGINT UNSIGNED;

    INSERT INTO inferred_crm (
        sequence_from_species_id,
        assayed_in_species_id,
        current_genome_assembly_release_version,
        chromosome_id,
        current_start,
        current_end,
        size)
    VALUES (
        sequence_from_species_id,
        assayed_in_species_id,
        current_genome_assembly_release_version,
        chromosome_id,
        current_start,
        current_end,
        size);

    SET last_icrm_id = LAST_INSERT_ID();

    INSERT INTO icrm_has_expr_term (icrm_id, term_id)
    SELECT last_icrm_id, et.term_id
      FROM ExpressionTerm AS et
     WHERE FIND_IN_SET(et.term_id, expression_ids) > 0;

    INSERT INTO icrm_has_rc (icrm_id, rc_id)
    SELECT last_icrm_id, rc.rc_id
      FROM ReporterConstruct AS rc
     WHERE FIND_IN_SET(rc.rc_id, component_rc_ids) > 0;
END //

DELIMITER ;

DELIMITER //

CREATE OR REPLACE PROCEDURE refresh_inferred_crm_read_model()
    MODIFIES SQL DATA
    SQL SECURITY INVOKER
    COMMENT 'Refreshes the inferred CRM read model with the latest data'
BEGIN
    DELETE FROM inferred_crm_read_model;

    INSERT INTO inferred_crm_read_model
    SELECT icrm.icrm_id AS id,
        icrm.sequence_from_species_id,
        icrm.assayed_in_species_id,
        GROUP_CONCAT(DISTINCT g.name ORDER BY g.name ASC SEPARATOR ',') AS gene,
        GROUP_CONCAT(DISTINCT gl.name ORDER BY gl.name ASC SEPARATOR ',') AS gene_locus,
        c.chromosome_id, 
        c.name AS chromosome,
        icrm.current_start,
        icrm.current_end,
        icrm.size,
        CONCAT(c.name, ':', icrm.current_start, '..', icrm.current_end) AS coordinates,
        GROUP_CONCAT(DISTINCT rc.name ORDER BY rc.name ASC SEPARATOR ',') AS components,
        GROUP_CONCAT(DISTINCT et.term ORDER BY et.identifier ASC SEPARATOR ',') AS expressions,
        GROUP_CONCAT(DISTINCT et.identifier ORDER BY et.identifier ASC SEPARATOR ',') AS expression_identifiers
    FROM inferred_crm AS icrm
    JOIN Chromosome AS c USING (chromosome_id)
    JOIN icrm_has_rc USING (icrm_id)
    JOIN ReporterConstruct AS rc USING (rc_id)
    JOIN Gene AS g USING (gene_id)
    JOIN icrm_has_expr_term USING (icrm_id)
    JOIN ExpressionTerm AS et USING (term_id)
    LEFT OUTER JOIN Gene AS gl ON icrm.chromosome_id = gl.chrm_id AND
        icrm.current_start > (gl.start - 10000) AND
        icrm.current_end < (gl.stop + 10000)
    GROUP BY id;
END //

DELIMITER ;

\! echo "Done!";