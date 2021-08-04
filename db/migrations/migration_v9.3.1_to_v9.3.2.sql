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
    GROUP_CONCAT(DISTINCT CONCAT(et.identifier) ORDER BY et.term ASC SEPARATOR ',') AS anatomical_expression_identifiers,
    GROUP_CONCAT(DISTINCT CONCAT(et.term) ORDER BY et.term ASC SEPARATOR ',') AS anatomical_expression_terms,
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
    GROUP_CONCAT(DISTINCT CONCAT(et.identifier) ORDER BY et.term ASC SEPARATOR ',') AS anatomical_expression_identifiers,
    GROUP_CONCAT(DISTINCT CONCAT(et.term) ORDER BY et.term ASC SEPARATOR ',') AS anatomical_expression_terms,
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

\! echo "Done!";