\! echo "Making table transformations..."; 

\! echo "-- Fixing the EvidenceTerm table and its relationships with other tables..."; 

ALTER TABLE EvidenceTerm
MODIFY COLUMN term VARCHAR(255) NOT NULL;

ALTER TABLE EvidenceTerm
DROP FOREIGN KEY `fk_{913BE598-2237-4885-99E4-9AE032A68486}`;

ALTER TABLE EvidenceTerm
DROP COLUMN species_id;

\! echo "-- Fixing the SequenceSource table and its relationships with other tables..."; 

ALTER TABLE SequenceSourceTerm
MODIFY COLUMN term VARCHAR(255) NOT NULL;

UPDATE SequenceSourceTerm
SET term = CONCAT(UCASE(LEFT(term, 1)), SUBSTRING(term, 2));

UPDATE SequenceSourceTerm
SET term = 'Sequence inferred from restriction map - right end estimated/uncertain'
WHERE source_id = 3;

UPDATE SequenceSourceTerm
SET term = 'Sequence inferred from restriction map - left end estimated/uncertain'
WHERE source_id = 4;

UPDATE SequenceSourceTerm
SET term = 'Sequence inferred from restriction map - both ends estimated/uncertain'
WHERE source_id = 5;

ALTER TABLE SequenceSourceTerm
DROP FOREIGN KEY `fk_{28131277-A326-42CE-B8EE-D06D335B1AA2}`;

ALTER TABLE SequenceSourceTerm
DROP COLUMN species_id;

\! echo "-- Fixing the EvidenceSubtypeTerm table and its relationships with other tables..."; 

ALTER TABLE EvidenceSubtypeTerm
DROP FOREIGN KEY EvidenceSubtypeTerm_ibfk_1;

ALTER TABLE EvidenceSubtypeTerm
DROP COLUMN species_id;

\! echo "-- Fixing the Species table and its relationships with other tables as..."; 

ALTER TABLE Species
DROP COLUMN asseyed_in;

ALTER TABLE Species
CHANGE COLUMN name scientific_name VARCHAR(255) NOT NULL;

INSERT INTO Species (scientific_name, short_name)
VALUES ('Anopheles gambiae', 'agam');

ALTER TABLE Species
ADD COLUMN public_database_names TEXT;

ALTER TABLE Species
ADD COLUMN public_database_links TEXT;

UPDATE Species
SET public_database_names = 'FlyBase,FlyMine,FlyTF',
    public_database_links = 'http://flybase.org/reports/gene_identifier.html,https://flymine.org/query/portal.do?externalid=gene_identifier,https://www.mrc-lmb.cam.ac.uk/genomes/FlyTF/tfhtml/gene_identifier.html'
WHERE short_name = 'dmel';

UPDATE Species
SET public_database_names = 'VectorBase',
    public_database_links = 'https://vectorbase.org/vectorbase/app/search?q=gene_identifier'
WHERE short_name = 'agam';

ALTER TABLE Species
ADD COLUMN public_browser_names TEXT;

ALTER TABLE Species
ADD COLUMN public_browser_links TEXT;

UPDATE Species
SET public_browser_names = 'GBrowse,UCSC',
    public_browser_links = 'http://flybase.org/cgi-bin/gbrowse2/dmel/?Search=1;name=coordinates&eurl=redflyBaseUrldatadumps/redfly.gbrowse,http://genome.ucsc.edu/cgi-bin/hgTracks?db=release_version&position=chrchromosome:start-end'
WHERE short_name = 'dmel';

UPDATE Species
SET public_browser_names = 'JBrowse',
    public_browser_links = 'https://vectorbase.org/vectorbase/app/jbrowse?data=%2Fa%2Fservice%2Fjbrowse%2Ftracks%2FagamPEST&loc=AgamP4_chromosome%3Astart..end&tracks=gene&highlight='
WHERE short_name = 'agam';

\! echo "   BindingSite..."; 

ALTER TABLE BindingSite
DROP FOREIGN KEY `fk_{31605C25-FFB1-4C38-9261-9209141139EF}`;

ALTER TABLE BindingSite
CHANGE COLUMN species_id sequence_from_species_id INT(10) UNSIGNED NOT NULL;

ALTER TABLE BindingSite
ADD CONSTRAINT FOREIGN KEY (sequence_from_species_id) REFERENCES Species (species_id)
ON DELETE NO ACTION
ON UPDATE NO ACTION;

ALTER TABLE BindingSite
ADD COLUMN assayed_in_species_id INT(10) UNSIGNED NOT NULL
AFTER sequence_from_species_id;

UPDATE BindingSite
SET assayed_in_species_id = 1;

ALTER TABLE BindingSite
ADD CONSTRAINT FOREIGN KEY (assayed_in_species_id) REFERENCES Species (species_id)
ON DELETE NO ACTION
ON UPDATE NO ACTION;

\! echo "   CRMSegment..."; 

ALTER TABLE CRMSegment
DROP FOREIGN KEY CRMSegment_ibfk_1;

ALTER TABLE CRMSegment
CHANGE COLUMN species_id sequence_from_species_id INT(10) UNSIGNED NOT NULL;

ALTER TABLE CRMSegment
ADD CONSTRAINT FOREIGN KEY (sequence_from_species_id) REFERENCES Species (species_id)
ON DELETE NO ACTION
ON UPDATE NO ACTION;

ALTER TABLE CRMSegment
ADD COLUMN assayed_in_species_id INT(10) UNSIGNED NOT NULL
AFTER sequence_from_species_id;

UPDATE CRMSegment
SET assayed_in_species_id = 1;

ALTER TABLE CRMSegment
ADD CONSTRAINT FOREIGN KEY (assayed_in_species_id) REFERENCES Species (species_id)
ON DELETE NO ACTION
ON UPDATE NO ACTION;

\! echo "   inferred_crm...";

ALTER TABLE inferred_crm
ADD COLUMN sequence_from_species_id INT(10) UNSIGNED NOT NULL
AFTER icrm_id;

UPDATE inferred_crm
SET sequence_from_species_id = 1;

ALTER TABLE inferred_crm
ADD CONSTRAINT FOREIGN KEY (sequence_from_species_id) REFERENCES Species (species_id)
ON DELETE NO ACTION
ON UPDATE NO ACTION;

ALTER TABLE inferred_crm
ADD COLUMN assayed_in_species_id INT(10) UNSIGNED NOT NULL
AFTER sequence_from_species_id;

UPDATE inferred_crm
SET assayed_in_species_id = 1;

ALTER TABLE inferred_crm
ADD CONSTRAINT FOREIGN KEY (assayed_in_species_id) REFERENCES Species (species_id)
ON DELETE NO ACTION
ON UPDATE NO ACTION;

\! echo "   inferred_crm_read_model...";

ALTER TABLE inferred_crm_read_model
CHANGE start current_start INT (10) UNSIGNED DEFAULT NULL;

ALTER TABLE inferred_crm_read_model
CHANGE end current_end INT (10) UNSIGNED DEFAULT NULL;

ALTER TABLE inferred_crm_read_model
CHANGE component components TEXT DEFAULT NULL;

ALTER TABLE inferred_crm_read_model
CHANGE expression expressions TEXT DEFAULT NULL;

ALTER TABLE inferred_crm_read_model
CHANGE expression_fbbts expression_identifiers TEXT  DEFAULT NULL;

ALTER TABLE inferred_crm_read_model
ADD COLUMN sequence_from_species_id INT(10) UNSIGNED NOT NULL
AFTER id;

UPDATE inferred_crm_read_model
SET sequence_from_species_id = 1;

ALTER TABLE inferred_crm_read_model
ADD CONSTRAINT FOREIGN KEY (sequence_from_species_id) REFERENCES Species (species_id)
ON DELETE NO ACTION
ON UPDATE NO ACTION;

ALTER TABLE inferred_crm_read_model
ADD COLUMN assayed_in_species_id INT(10) UNSIGNED NOT NULL
AFTER sequence_from_species_id;

UPDATE inferred_crm_read_model
SET assayed_in_species_id = 1;

ALTER TABLE inferred_crm_read_model
ADD CONSTRAINT FOREIGN KEY (assayed_in_species_id) REFERENCES Species (species_id)
ON DELETE NO ACTION
ON UPDATE NO ACTION;

ALTER TABLE inferred_crm_read_model
ADD COLUMN chromosome_id INT(10) UNSIGNED NOT NULL
AFTER gene_locus;

\! echo "   PredictedCRM..."; 

ALTER TABLE PredictedCRM
DROP FOREIGN KEY PredictedCRM_ibfk_1;

ALTER TABLE PredictedCRM
CHANGE COLUMN species_id sequence_from_species_id INT(10) UNSIGNED NOT NULL;

ALTER TABLE PredictedCRM
ADD CONSTRAINT FOREIGN KEY (sequence_from_species_id) REFERENCES Species (species_id)
ON DELETE NO ACTION
ON UPDATE NO ACTION;

ALTER TABLE PredictedCRM
ADD COLUMN assayed_in_species_id INT(10) UNSIGNED NOT NULL
AFTER sequence_from_species_id;

UPDATE PredictedCRM
SET assayed_in_species_id = 1;

ALTER TABLE PredictedCRM
ADD CONSTRAINT FOREIGN KEY (assayed_in_species_id) REFERENCES Species (species_id)
ON DELETE NO ACTION
ON UPDATE NO ACTION;

\! echo "   ReporterConstruct..."; 

ALTER TABLE ReporterConstruct
DROP FOREIGN KEY `fk_{78851934-0C6C-4567-8EA1-A25BDA9D934B}`;

ALTER TABLE ReporterConstruct
CHANGE COLUMN species_id sequence_from_species_id INT(10) UNSIGNED NOT NULL;

ALTER TABLE ReporterConstruct
ADD CONSTRAINT FOREIGN KEY (sequence_from_species_id) REFERENCES Species (species_id)
ON DELETE NO ACTION
ON UPDATE NO ACTION;

ALTER TABLE ReporterConstruct
ADD COLUMN assayed_in_species_id INT(10) UNSIGNED NOT NULL
AFTER sequence_from_species_id;

UPDATE ReporterConstruct
SET assayed_in_species_id = 1;

ALTER TABLE ReporterConstruct
ADD CONSTRAINT FOREIGN KEY (assayed_in_species_id) REFERENCES Species (species_id)
ON DELETE NO ACTION
ON UPDATE NO ACTION;

\! echo "-- Fixing the genome assembly release relationships with other tables as..."; 
\! echo "   BindingSite..."; 

ALTER TABLE BindingSite
ADD COLUMN current_genome_assembly_release_version VARCHAR(32) DEFAULT ''
AFTER size;

ALTER TABLE BindingSite
ADD COLUMN archived_genome_assembly_release_versions VARCHAR(256) DEFAULT ''
AFTER current_genome_assembly_release_version;

UPDATE BindingSite
SET current_genome_assembly_release_version = 'dm6';

UPDATE BindingSite
SET archived_genome_assembly_release_versions = 'dm1,dm2,dm3';

\! echo "   CRMSegment..."; 

ALTER TABLE CRMSegment
ADD COLUMN current_genome_assembly_release_version VARCHAR(32) DEFAULT ''
AFTER size;

ALTER TABLE CRMSegment
ADD COLUMN archived_genome_assembly_release_versions VARCHAR(256) DEFAULT ''
AFTER current_genome_assembly_release_version;

UPDATE CRMSegment
SET current_genome_assembly_release_version = 'dm6';

\! echo "   inferred_crm..."; 

ALTER TABLE inferred_crm
ADD COLUMN current_genome_assembly_release_version VARCHAR(32) DEFAULT ''
AFTER size;

ALTER TABLE inferred_crm
ADD COLUMN archived_genome_assembly_release_versions VARCHAR(256) DEFAULT ''
AFTER current_genome_assembly_release_version;

UPDATE inferred_crm
SET current_genome_assembly_release_version = 'dm6';

\! echo "   PredictedCRM..."; 

ALTER TABLE PredictedCRM
ADD COLUMN current_genome_assembly_release_version VARCHAR(32) DEFAULT ''
AFTER size;

ALTER TABLE PredictedCRM
ADD COLUMN archived_genome_assembly_release_versions VARCHAR(256) DEFAULT ''
AFTER current_genome_assembly_release_version;

UPDATE PredictedCRM
SET current_genome_assembly_release_version = 'dm6';

\! echo "   ReporterConstruct..."; 

ALTER TABLE ReporterConstruct
ADD COLUMN current_genome_assembly_release_version VARCHAR(32) DEFAULT ''
AFTER size;

ALTER TABLE ReporterConstruct
ADD COLUMN archived_genome_assembly_release_versions VARCHAR(256) DEFAULT ''
AFTER current_genome_assembly_release_version;

UPDATE ReporterConstruct
SET current_genome_assembly_release_version = 'dm6';

UPDATE ReporterConstruct
SET archived_genome_assembly_release_versions = 'dm1,dm2,dm3';

\! echo "-- Fixing the chromosome/Chromosome tables mess..."; 

ALTER TABLE Chromosome
CHANGE COLUMN chromosome name VARCHAR(64) NOT NULL;

ALTER TABLE PredictedCRM
DROP FOREIGN KEY PredictedCRM_ibfk_2;

ALTER TABLE inferred_crm
DROP FOREIGN KEY inferred_crm_ibfk_1;

DROP TABLE chromosome;

ALTER TABLE PredictedCRM
ADD CONSTRAINT FOREIGN KEY (chromosome_id) REFERENCES Chromosome (chromosome_id)
ON DELETE NO ACTION
ON UPDATE NO ACTION;

ALTER TABLE inferred_crm
ADD CONSTRAINT FOREIGN KEY (chromosome_id) REFERENCES Chromosome (chromosome_id)
ON DELETE NO ACTION
ON UPDATE NO ACTION;

\! echo "-- Creating the new GenomeAssembly table..."; 

CREATE TABLE GenomeAssembly(
  genome_assembly_id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  species_id         INT(10) UNSIGNED NOT NULL,
  INDEX (species_id),
  release_version    VARCHAR(32) NOT NULL,
  INDEX (release_version),
  is_deprecated      TINYINT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (genome_assembly_id),
  FOREIGN KEY (species_id) REFERENCES Species (species_id)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4;

INSERT INTO GenomeAssembly (species_id, release_version, is_deprecated)
VALUES (1, 'dm1', 1);

INSERT INTO GenomeAssembly (species_id, release_version, is_deprecated)
VALUES (1, 'dm2', 1);

INSERT INTO GenomeAssembly (species_id, release_version, is_deprecated)
VALUES (1, 'dm3', 1);

INSERT INTO GenomeAssembly (species_id, release_version, is_deprecated)
VALUES (1, 'dm6', 0);

\! echo "-- Fixing the Chromosome table and its relationships with other tables..."; 

ALTER TABLE Chromosome
ADD COLUMN genome_assembly_id INT(10) UNSIGNED NOT NULL
AFTER species_id,
ADD INDEX (genome_assembly_id);

UPDATE Chromosome
SET genome_assembly_id = 4;

ALTER TABLE Chromosome
ADD CONSTRAINT FOREIGN KEY (genome_assembly_id) REFERENCES GenomeAssembly (genome_assembly_id)
ON DELETE CASCADE
ON UPDATE NO ACTION;

INSERT INTO Chromosome (species_id, genome_assembly_id, name, length)
VALUES (1, 3, 'X', 0);

INSERT INTO Chromosome (species_id, genome_assembly_id, name, length)
VALUES (1, 3, '2R',	0);

INSERT INTO Chromosome (species_id, genome_assembly_id, name, length)
VALUES (1, 3, '2L', 0);

INSERT INTO Chromosome (species_id, genome_assembly_id, name, length)
VALUES (1, 3, '3R', 0);

INSERT INTO Chromosome (species_id, genome_assembly_id, name, length)
VALUES (1, 3, '3L',	0);

INSERT INTO Chromosome (species_id, genome_assembly_id, name, length)
VALUES (1, 3, '4', 0);

INSERT INTO Chromosome (species_id, genome_assembly_id, name, length)
VALUES (1, 3, 'U', 0);

INSERT INTO Chromosome (species_id, genome_assembly_id, name, length)
VALUES (1, 3, 'Mt', 0);

INSERT INTO Chromosome (species_id, genome_assembly_id, name, length)
VALUES (1, 3, 'Unspecified', 0);

INSERT INTO GenomeAssembly (species_id, release_version, is_deprecated)
VALUES (2, 'agam4', 0);

INSERT INTO Chromosome (species_id, genome_assembly_id, name, length)
VALUES (2, 5, '2R', 61545105);

INSERT INTO Chromosome (species_id, genome_assembly_id, name, length)
VALUES (2, 5, '3R', 53200684);

INSERT INTO Chromosome (species_id, genome_assembly_id, name, length)
VALUES (2, 5, '2L', 49364325);

INSERT INTO Chromosome (species_id, genome_assembly_id, name, length)
VALUES (2, 5, 'UNKN', 42389979);

INSERT INTO Chromosome (species_id, genome_assembly_id, name, length)
VALUES (2, 5, '3L', 41963435);

INSERT INTO Chromosome (species_id, genome_assembly_id, name, length)
VALUES (2, 5, 'X', 24393108);

INSERT INTO Chromosome (species_id, genome_assembly_id, name, length)
VALUES (2, 5, 'Y_unplaced', 237045);

INSERT INTO Chromosome (species_id, genome_assembly_id, name, length)
VALUES (2, 5, 'Mt', 15363);

INSERT INTO Chromosome (species_id, genome_assembly_id, name, length)
VALUES (2, 5, 'Unspecified', 0);

\! echo "-- Fixing the coordinates mess...";
\! echo "   BindingSite...";  

ALTER TABLE BindingSite
ADD COLUMN current_start INT DEFAULT 0
AFTER current_genome_assembly_release_version;

ALTER TABLE BindingSite
ADD COLUMN current_end INT DEFAULT 0
AFTER current_start;

ALTER TABLE BindingSite
ADD COLUMN archived_starts VARCHAR(512) DEFAULT ''
AFTER archived_genome_assembly_release_versions;

ALTER TABLE BindingSite
ADD COLUMN archived_ends VARCHAR(512) DEFAULT ''
AFTER archived_starts;

UPDATE BindingSite
SET current_start = start,
    archived_starts = CONCAT(IFNULL(start_r3, 0), ',',  IFNULL(start_r4, 0), ',', IFNULL(start_r5, 0)),
    current_end = end,
    archived_ends = CONCAT(IFNULL(end_r3, 0), ',',  IFNULL(end_r4, 0), ',', IFNULL(end_r5, 0));

ALTER TABLE BindingSite
DROP COLUMN start_r3;

ALTER TABLE BindingSite
DROP COLUMN end_r3;

ALTER TABLE BindingSite
DROP COLUMN start_r4;

ALTER TABLE BindingSite
DROP COLUMN end_r4;

ALTER TABLE BindingSite
DROP COLUMN start_r5;

ALTER TABLE BindingSite
DROP COLUMN end_r5;

ALTER TABLE BindingSite
DROP COLUMN start_r6;

ALTER TABLE BindingSite
DROP COLUMN end_r6;

ALTER TABLE BindingSite
DROP COLUMN start;

ALTER TABLE BindingSite
DROP COLUMN end;

\! echo "   CRMSegment..."; 

ALTER TABLE CRMSegment
ADD COLUMN current_start INT DEFAULT 0
AFTER current_genome_assembly_release_version;

ALTER TABLE CRMSegment
ADD COLUMN current_end INT DEFAULT 0
AFTER current_start;

ALTER TABLE CRMSegment
ADD COLUMN archived_starts VARCHAR(512) DEFAULT ''
AFTER archived_genome_assembly_release_versions;

ALTER TABLE CRMSegment
ADD COLUMN archived_ends VARCHAR(512) DEFAULT ''
AFTER archived_starts;

UPDATE CRMSegment
SET current_start = start_r6,
    current_end = end_r6;    

ALTER TABLE CRMSegment
DROP COLUMN start_r6;

ALTER TABLE CRMSegment
DROP COLUMN end_r6;

ALTER TABLE CRMSegment
DROP COLUMN start;

ALTER TABLE CRMSegment
DROP COLUMN end;

\! echo "   inferred_crm..."; 

ALTER TABLE inferred_crm
ADD COLUMN current_start INT DEFAULT 0
AFTER current_genome_assembly_release_version;

ALTER TABLE inferred_crm
ADD COLUMN current_end INT DEFAULT 0
AFTER current_start;

ALTER TABLE inferred_crm
ADD COLUMN archived_starts VARCHAR(512) DEFAULT ''
AFTER archived_genome_assembly_release_versions;

ALTER TABLE inferred_crm
ADD COLUMN archived_ends VARCHAR(512) DEFAULT ''
AFTER archived_starts;

ALTER TABLE inferred_crm
MODIFY COLUMN start INT;

ALTER TABLE inferred_crm
MODIFY COLUMN end INT;

ALTER TABLE inferred_crm
MODIFY COLUMN size INT(10) UNSIGNED;

UPDATE inferred_crm
SET current_start = start,
    current_end = end;    

ALTER TABLE inferred_crm
DROP COLUMN start;

ALTER TABLE inferred_crm
DROP COLUMN end;

\! echo "   PredictedCRM..."; 

ALTER TABLE PredictedCRM
ADD COLUMN current_start INT DEFAULT 0
AFTER current_genome_assembly_release_version;

ALTER TABLE PredictedCRM
ADD COLUMN current_end INT DEFAULT 0
AFTER current_start;

ALTER TABLE PredictedCRM
ADD COLUMN archived_starts VARCHAR(512) DEFAULT ''
AFTER archived_genome_assembly_release_versions;

ALTER TABLE PredictedCRM
ADD COLUMN archived_ends VARCHAR(512) DEFAULT ''
AFTER archived_starts;

UPDATE PredictedCRM
SET current_start = start_r6,
    current_end = end_r6;    

ALTER TABLE PredictedCRM
MODIFY COLUMN start_r6 INT;

ALTER TABLE PredictedCRM
MODIFY COLUMN end_r6 INT;

ALTER TABLE PredictedCRM
MODIFY COLUMN size INT(10) UNSIGNED;

ALTER TABLE PredictedCRM
DROP COLUMN start_r6;

ALTER TABLE PredictedCRM
DROP COLUMN end_r6;

\! echo "   ReporterConstruct..."; 

ALTER TABLE ReporterConstruct
ADD COLUMN current_start INT DEFAULT 0
AFTER current_genome_assembly_release_version;

ALTER TABLE ReporterConstruct
ADD COLUMN current_end INT DEFAULT 0
AFTER current_start;

ALTER TABLE ReporterConstruct
ADD COLUMN archived_starts VARCHAR(512) DEFAULT ''
AFTER archived_genome_assembly_release_versions;

ALTER TABLE ReporterConstruct
ADD COLUMN archived_ends VARCHAR(512) DEFAULT ''
AFTER archived_starts;

UPDATE ReporterConstruct
SET current_start = start,
    archived_starts = CONCAT(IFNULL(start_r3, 0), ',',  IFNULL(start_r4, 0), ',', IFNULL(start_r5, 0)),
    current_end = end,
    archived_ends = CONCAT(IFNULL(end_r3, 0), ',',  IFNULL(end_r4, 0), ',', IFNULL(end_r5, 0));

ALTER TABLE ReporterConstruct
DROP COLUMN start_r3;

ALTER TABLE ReporterConstruct
DROP COLUMN end_r3;

ALTER TABLE ReporterConstruct
DROP COLUMN start_r4;

ALTER TABLE ReporterConstruct
DROP COLUMN end_r4;

ALTER TABLE ReporterConstruct
DROP COLUMN start_r5;

ALTER TABLE ReporterConstruct
DROP COLUMN end_r5;

ALTER TABLE ReporterConstruct
DROP COLUMN start_r6;

ALTER TABLE ReporterConstruct
DROP COLUMN end_r6;

ALTER TABLE ReporterConstruct
DROP COLUMN start;

ALTER TABLE ReporterConstruct
DROP COLUMN end;

\! echo "-- Cleaning up the external reference mess..."; 

DROP TABLE CRMSegment_has_External_Reference;

DROP TABLE RC_has_ExtRef;

\! echo "-- Fixing the DevelopmentalStage table..."; 

ALTER TABLE DevelopmentalStage
CHANGE COLUMN flybase_id identifier VARCHAR(64) NOT NULL,
ADD INDEX (identifier);

ALTER TABLE DevelopmentalStage
DROP INDEX flybase_id;

\! echo "-- Fixing the ExpressionTerm table..."; 

ALTER TABLE ExpressionTerm
CHANGE COLUMN flybase_Id identifier VARCHAR(64) NOT NULL,
ADD INDEX (identifier);

ALTER TABLE ExpressionTerm
DROP INDEX flybase;

\! echo "-- Fixing the Features table..."; 

ALTER TABLE Features
ADD COLUMN sequence_from_species_id INT(10) UNSIGNED NOT NULL
AFTER feature_id,
ADD INDEX (sequence_from_species_id),
ADD CONSTRAINT FOREIGN KEY (sequence_from_species_id) REFERENCES Species(species_id);

ALTER TABLE Features
CHANGE COLUMN flybase_id identifier VARCHAR(64) NOT NULL,
ADD INDEX (identifier);

ALTER TABLE Features
DROP INDEX flybase_id;

\! echo "-- Fixing the Gene table..."; 

ALTER TABLE Gene
CHANGE COLUMN flybase_id identifier VARCHAR(64) NOT NULL,
ADD INDEX (identifier);

ALTER TABLE Gene
DROP INDEX flybase_id;

ALTER TABLE Gene
ADD COLUMN genome_assembly_id INT(10) UNSIGNED NOT NULL
AFTER species_id,
ADD INDEX (genome_assembly_id);

UPDATE Gene
SET genome_assembly_id = 4;

ALTER TABLE Gene
ADD CONSTRAINT FOREIGN KEY (genome_assembly_id) REFERENCES GenomeAssembly (genome_assembly_id)
ON DELETE CASCADE
ON UPDATE NO ACTION;

\! echo "-- Fixing the BiologicalProcess table..."; 

ALTER TABLE BiologicalProcess
DROP FOREIGN KEY BiologicalProcess_ibfk_1;

ALTER TABLE BiologicalProcess
DROP COLUMN species_id;

\! echo "-- Fixing the triplestore_crm_segment table...";

ALTER TABLE triplestore_crm_segment
MODIFY COLUMN expression VARCHAR(32) NOT NULL;

ALTER TABLE triplestore_crm_segment
MODIFY COLUMN stage_on VARCHAR(32) NOT NULL DEFAULT 'none';

ALTER TABLE triplestore_crm_segment
MODIFY COLUMN stage_off VARCHAR(32) NOT NULL DEFAULT 'none';

\! echo "-- Fixing the triplestore_predicted_crm table...";

ALTER TABLE triplestore_predicted_crm
MODIFY COLUMN expression VARCHAR(32) NOT NULL;

ALTER TABLE triplestore_predicted_crm
MODIFY COLUMN stage_on VARCHAR(32) NOT NULL DEFAULT 'none';

ALTER TABLE triplestore_predicted_crm
MODIFY COLUMN stage_off VARCHAR(32) NOT NULL DEFAULT 'none';

\! echo "-- Fixing the triplestore_rc table...";

ALTER TABLE triplestore_rc
MODIFY COLUMN expression VARCHAR(32) NOT NULL;

ALTER TABLE triplestore_rc
MODIFY COLUMN stage_on VARCHAR(32) NOT NULL DEFAULT 'none';

ALTER TABLE triplestore_rc
MODIFY COLUMN stage_off VARCHAR(32) NOT NULL DEFAULT 'none';

\! echo "Done!";

\! echo "Making view transformations..."; 

DROP VIEW v_binding_site_search;

DROP VIEW v_crm_overlaps;

CREATE VIEW v_cis_regulatory_module_overlaps AS
SELECT ro.rc_id,
    ro.overlap_id,
    GROUP_CONCAT(rt.term_id separator ',') AS terms,
    ro.chromosome_id,
    ro.current_start AS start,
    ro.current_end AS end
FROM (SELECT r.rc_id AS rc_id,
          o.rc_id AS overlap_id,
          r.chromosome_id AS chromosome_id,
          GREATEST(r.current_start, o.current_start) AS current_start,
          LEAST(r.current_end, o.current_end) AS current_end
      FROM redfly.ReporterConstruct r
      JOIN redfly.ReporterConstruct o ON r.state = o.state AND
          r.chromosome_id = o.chromosome_id AND
          r.is_crm = o.is_crm AND
          r.is_negative = o.is_negative
      WHERE r.rc_id < o.rc_id AND
          r.state = 'current' AND
          r.is_crm = 1 AND
          r.is_negative = 0) ro
JOIN redfly.RC_has_ExprTerm rt ON ro.rc_id = rt.rc_id
JOIN redfly.RC_has_ExprTerm ot ON ro.overlap_id = ot.rc_id
WHERE rt.term_id = ot.term_id
GROUP BY ro.rc_id,
    ro.overlap_id;

CREATE OR REPLACE VIEW v_cis_regulatory_module_segment_file AS
SELECT CONCAT('RFSEG:', LPAD(crms.entity_id, 10, '0'), '.', LPAD(crms.version, 3, '0')) AS redfly_id,
    CONCAT('RFSEG:', LPAD(crms.entity_id, 10, '0')) AS redfly_id_unversioned,
    crms.crm_segment_id,
    crms.pubmed_id,
    crms.fbtp,
    'REDfly_RFSEG' AS label,
    crms.name,
    sfs.scientific_name AS sequence_from_species_scientific_name,
    ais.scientific_name AS assayed_in_species_scientific_name,
    g.name AS gene_name,
    g.identifier AS gene_identifier,
    crms.sequence,
    e.term AS evidence_term,
    IFNULL(es.term, '') AS evidence_subtype_term,
    c.name AS chromosome,
    crms.current_start AS start,
    crms.current_end AS end,
    IFNULL(GROUP_CONCAT(DISTINCT et.identifier ORDER BY et.identifier SEPARATOR ','), '') AS ontology_term
FROM CRMSegment crms
LEFT JOIN Species sfs ON crms.sequence_from_species_id = sfs.species_id
LEFT JOIN Species ais ON crms.assayed_in_species_id = ais.species_id
LEFT JOIN Gene g ON crms.gene_id = g.gene_id
LEFT JOIN Chromosome c ON crms.chromosome_id = c.chromosome_id
LEFT JOIN EvidenceTerm e ON crms.evidence_id = e.evidence_id
LEFT JOIN EvidenceSubtypeTerm es ON crms.evidence_subtype_id = es.evidence_subtype_id
LEFT JOIN CRMSegment_has_Expression_Term ON crms.crm_segment_id = CRMSegment_has_Expression_Term.crm_segment_id
LEFT JOIN ExpressionTerm et ON CRMSegment_has_Expression_Term.term_id = et.term_id
WHERE crms.state = 'current'
GROUP BY crms.crm_segment_id;

CREATE OR REPLACE VIEW v_cis_regulatory_module_segment_staging_data_file AS
SELECT 'RFSEG' AS entity_type,
    crms.crm_segment_id AS parent_id,
    crms.pubmed_id AS parent_pubmed_id,
    crms.name,
    ts.expression AS expression_identifier,
    ts.pubmed_id,
    ts.stage_on AS stage_on_identifier,
    ts.stage_off AS stage_off_identifier,
    ts.biological_process AS biological_process_identifier,
    ts.sex,
    ts.ectopic
FROM CRMSegment crms
JOIN triplestore_crm_segment ts ON crms.crm_segment_id = ts.crm_segment_id
WHERE crms.state = 'current';

DROP VIEW v_crm_segment_audit;

CREATE VIEW v_cis_regulatory_module_segment_audit AS
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
    g.name AS gene_name,
    c.name AS chromosome,
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
    GROUP_CONCAT(DISTINCT et.term ORDER BY et.term ASC SEPARATOR ',') AS expressions,
    GROUP_CONCAT(DISTINCT et.identifier ORDER BY et.term ASC SEPARATOR ',') AS expression_identifiers,
    crms.notes,
    ss.term AS sequence_source,
    crms.date_added,
    crms.last_update,
    crms.last_audit
FROM CRMSegment crms
JOIN Users curator ON crms.curator_id = curator.user_id
LEFT OUTER JOIN Users auditor ON crms.auditor_id = auditor.user_id
LEFT JOIN Species sfs ON crms.sequence_from_species_id = sfs.species_id
LEFT JOIN Species ais ON crms.assayed_in_species_id = ais.species_id
LEFT JOIN Gene g ON crms.gene_id = g.gene_id
LEFT JOIN Chromosome c ON crms.chromosome_id = c.chromosome_id
LEFT JOIN EvidenceTerm e ON crms.evidence_id = e.evidence_id
LEFT OUTER JOIN EvidenceSubtypeTerm es ON crms.evidence_subtype_id = es.evidence_subtype_id
LEFT JOIN SequenceSourceTerm ss ON crms.sequence_source_id = ss.source_id
LEFT OUTER JOIN CRMSegment_has_Expression_Term ON crms.crm_segment_id = CRMSegment_has_Expression_Term.crm_segment_id
LEFT OUTER JOIN ExpressionTerm et ON CRMSegment_has_Expression_Term.term_id = et.term_id
WHERE crms.state IN ('approval' , 'approved', 'deleted', 'editing')
GROUP BY crms.crm_segment_id;

CREATE VIEW v_cis_regulatory_module_segment_no_ts_audit AS
SELECT sfs.scientific_name AS sequence_from_species_scientific_name,
    ais.scientific_name AS assayed_in_species_scientific_name,
    CONCAT(crms.name, ' ') AS name,
    CONCAT(curator.first_name, ' ', curator.last_name) AS curator_full_name,
    crms.state,
    '' AS pubmed_id,
    c.name AS chromosome,
    CONCAT(c.name, ' (', sfs.short_name, ')') AS chromosome_display,
    crms.current_start AS start,
    crms.current_end AS end,
    g.name AS gene_name,
    et.term AS expression_term,
    '' AS stage_on_term,
    '' AS stage_off_term,
    '' AS biological_process_term,
    '' AS sex,
    '' AS ectopic
FROM CRMSegment crms
JOIN Users curator ON crms.curator_id = curator.user_id
JOIN Species sfs ON crms.sequence_from_species_id = sfs.species_id
JOIN Gene g ON crms.gene_id = g.gene_id
JOIN Chromosome c ON crms.chromosome_id = c.chromosome_id
JOIN Species ais ON crms.assayed_in_species_id = ais.species_id
JOIN CRMSegment_has_Expression_Term map ON crms.crm_segment_id = map.crm_segment_id
JOIN ExpressionTerm et ON map.term_id = et.term_id
WHERE crms.state IN ('approval' , 'approved', 'deleted', 'editing') AND
    et.term <> '' AND
    NOT EXISTS (SELECT ts.ts_id
                FROM triplestore_crm_segment ts
                WHERE map.crm_segment_id = ts.crm_segment_id AND
                    map.term_id = et.term_id AND
                    et.identifier = ts.expression)
ORDER BY crms.name,
	et.term;

DROP VIEW v_crm_segment_ts_audit;

CREATE VIEW v_cis_regulatory_module_segment_ts_audit AS
SELECT sfs.scientific_name AS sequence_from_species_scientific_name,
    ais.scientific_name AS assayed_in_species_scientific_name,
    CONCAT(crms.name, ' ') AS name,
    CONCAT(curator.first_name, ' ', curator.last_name) AS curator_full_name,
    crms.state,
    CASE ts.pubmed_id
        WHEN NULL THEN ''
        ELSE ts.pubmed_id
    END AS pubmed_id,
    c.name AS chromosome,
    CONCAT(c.name, ' (', sfs.short_name, ')') AS chromosome_display,
    crms.current_start AS start,
    crms.current_end AS end,
    g.name AS gene_name,
    et.term AS expression_term,
    CASE ds_on.term
        WHEN NULL THEN ''
        ELSE ds_on.term
    END AS stage_on_term,
    CASE ds_off.term
        WHEN NULL THEN ''
        ELSE ds_off.term
    END AS stage_off_term,
    CASE bp.term
        WHEN NULL THEN ''
        ELSE bp.term
    END AS biological_process_term,
    CASE ts.sex
        WHEN NULL THEN ''
        ELSE ts.sex
    END AS sex,
    CASE ts.ectopic
        WHEN NULL THEN ''
        ELSE ts.ectopic
    END AS ectopic
FROM CRMSegment crms
JOIN Users curator ON crms.curator_id = curator.user_id
JOIN Species sfs ON crms.sequence_from_species_id = sfs.species_id
JOIN Gene g ON crms.gene_id = g.gene_id
JOIN Chromosome c ON crms.chromosome_id = c.chromosome_id
JOIN Species ais ON crms.assayed_in_species_id = ais.species_id
JOIN triplestore_crm_segment ts ON crms.crm_segment_id = ts.crm_segment_id
JOIN ExpressionTerm et ON ts.expression = et.identifier
JOIN DevelopmentalStage ds_on ON ais.species_id = ds_on.species_id AND
    ts.stage_on = ds_on.identifier
JOIN DevelopmentalStage ds_off ON ais.species_id = ds_off.species_id AND
    ts.stage_off = ds_off.identifier
LEFT OUTER JOIN BiologicalProcess bp ON ts.biological_process = bp.go_id
WHERE crms.state IN ('approval' , 'approved', 'deleted', 'editing') AND
    et.term <> ''
ORDER BY crms.name,
	et.term;

DROP VIEW v_crm_segment_ts_notify_author;

CREATE VIEW v_cis_regulatory_module_segment_ts_notify_author AS
SELECT ts.crm_segment_id,
    ts.expression AS expression_identifier,
    ds.term AS stage_on_term,
    ds2.term AS stage_off_term,
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
LEFT JOIN triplestore_crm_segment ts ON crms.crm_segment_id = ts.crm_segment_id
LEFT JOIN DevelopmentalStage ds ON ts.stage_on = ds.identifier
LEFT JOIN DevelopmentalStage ds2 ON ts.stage_off = ds2.identifier
LEFT OUTER JOIN BiologicalProcess bp ON ts.biological_process = bp.go_id
WHERE crms.state = 'approved';

DROP VIEW v_crms_feature_location;

CREATE VIEW v_cis_regulatory_module_segment_feature_location AS
SELECT crms.crm_segment_id AS id,
    f.type,
    f.name,
    f.parent,
    f.feature_id,
    f.identifier,
    crms.current_start AS start,
    crms.current_end AS end,
    f.start AS f_start,
    f.end AS f_end,
    f.strand,
    IF(f.strand = '+',
        IF(crms.current_start < f.start + 5,
            5,
            IF(crms.current_start > f.end + 5, 3, 0)),
        IF(crms.current_end < f.start + 5,
            3,
            IF(crms.current_end > f.end + 5, 5, 0))) AS relative_start,
    IF(f.strand = '+',
        IF(crms.current_end < f.start + 5,
            5,
            IF(crms.current_end > f.end + 5, 3, 0)),
        IF(crms.current_start < f.start + 5,
            3,
            IF(crms.current_start > f.end + 5, 5, 0))) AS relative_end,
    IF(f.strand = '+',
        IF(crms.current_start < f.start + 5,
            ABS(f.start - crms.current_start),
            IF(crms.current_start > f.end + 5,
                ABS(crms.current_start - f.end),
                0)),
        IF(crms.current_end < f.start + 5,
            ABS(f.start - crms.current_end),
            IF(crms.current_end > f.end + 5,
                ABS(crms.current_end - f.end),
                0))) AS start_dist,
    IF(f.strand = '+',
        IF(crms.current_end < f.start + 5,
            ABS(f.start - crms.current_end),
            IF(crms.current_end > f.end + 5,
                ABS(crms.current_end - f.end),
                0)),
        IF(crms.current_start < f.start + 5,
            ABS(f.start - crms.current_start),
            IF(crms.current_start > f.end + 5,
                ABS(f.end - crms.current_start),
                0))) AS end_dist
FROM Features f
LEFT JOIN CRMSegment crms ON (f.gene_id = crms.gene_id)
WHERE crms.state = 'current'
ORDER BY crms.crm_segment_id,
    f.feature_id,
    f.parent;

CREATE VIEW v_inferred_cis_regulatory_module_audit AS
SELECT icrm.icrm_id AS id,
    sfs.scientific_name AS sequence_from_species_scientific_name,
    ais.scientific_name AS assayed_in_species_scientific_name,
    c.name AS chromosome,
    CONCAT(c.name, ' (', sfs.short_name, ')') AS chromosome_display,
    icrm.current_start AS start,
    icrm.current_end AS end,
    CONCAT(c.name, ':', icrm.current_start, '..', icrm.current_end) AS coordinates
FROM inferred_crm icrm
LEFT JOIN Species sfs ON icrm.sequence_from_species_id = sfs.species_id
LEFT JOIN Species ais ON icrm.assayed_in_species_id = ais.species_id
LEFT JOIN Chromosome c ON icrm.chromosome_id = c.chromosome_id
GROUP BY icrm.icrm_id;

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

CREATE OR REPLACE VIEW v_predicted_cis_regulatory_module_staging_data_file AS
SELECT 'RFPCRM' AS entity_type,
    pcrm.predicted_crm_id AS parent_id,
    pcrm.pubmed_id AS parent_pubmed_id,
    pcrm.name,
    ts.expression AS expression_identifier,
    ts.pubmed_id,
    ts.stage_on AS stage_on_identifier,
    ts.stage_off AS stage_off_identifier,
    ts.biological_process AS biological_process_identifier,
    ts.sex
FROM PredictedCRM pcrm
JOIN triplestore_predicted_crm ts ON pcrm.predicted_crm_id = ts.predicted_crm_id
WHERE pcrm.state = 'current';

DROP VIEW v_predicted_crm_audit;

CREATE VIEW v_predicted_cis_regulatory_module_audit AS
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
    c.name AS chromosome,
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
    GROUP_CONCAT(DISTINCT et.term ORDER BY et.term ASC SEPARATOR ',') AS expressions,
    GROUP_CONCAT(DISTINCT et.identifier ORDER BY et.term ASC SEPARATOR ',') AS expression_identifiers,
    pcrm.notes,
    ss.term AS sequence_source,
    pcrm.date_added,
    pcrm.last_update,
    pcrm.last_audit
FROM PredictedCRM pcrm
LEFT JOIN Users curator ON pcrm.curator_id = curator.user_id
LEFT OUTER JOIN Users auditor ON pcrm.auditor_id = auditor.user_id
LEFT JOIN Species sfs ON pcrm.sequence_from_species_id = sfs.species_id
LEFT JOIN Species ais ON pcrm.assayed_in_species_id = ais.species_id
LEFT JOIN Chromosome c ON pcrm.chromosome_id = c.chromosome_id
LEFT JOIN EvidenceTerm e ON pcrm.evidence_id = e.evidence_id
LEFT OUTER JOIN EvidenceSubtypeTerm es ON pcrm.evidence_subtype_id = es.evidence_subtype_id
LEFT JOIN SequenceSourceTerm ss ON pcrm.sequence_source_id = ss.source_id
LEFT OUTER JOIN PredictedCRM_has_Expression_Term ON pcrm.predicted_crm_id = PredictedCRM_has_Expression_Term.predicted_crm_id
LEFT OUTER JOIN ExpressionTerm et ON PredictedCRM_has_Expression_Term.term_id = et.term_id
WHERE pcrm.state IN ('approval' , 'approved', 'deleted', 'editing')
GROUP BY pcrm.predicted_crm_id;

CREATE VIEW v_predicted_cis_regulatory_module_no_ts_audit AS
SELECT sfs.scientific_name AS sequence_from_species_scientific_name,
    ais.scientific_name AS assayed_in_species_scientific_name,
    CONCAT(pcrm.name, ' ') AS name,
    CONCAT(curator.first_name, ' ', curator.last_name) AS curator_full_name,
    pcrm.state,
    '' AS pubmed_id,
    c.name AS chromosome,
    CONCAT(c.name, ' (', sfs.short_name, ')') AS chromosome_display,
    pcrm.current_start AS start,
    pcrm.current_end AS end,
    et.term AS expression_term,
    '' AS stage_on_term,
    '' AS stage_off_term,
    '' AS biological_process_term,
    '' AS sex
FROM PredictedCRM pcrm
JOIN Users curator ON pcrm.curator_id = curator.user_id
JOIN Species sfs ON pcrm.sequence_from_species_id = sfs.species_id
JOIN Chromosome c ON pcrm.chromosome_id = c.chromosome_id
JOIN Species ais ON pcrm.assayed_in_species_id = ais.species_id
JOIN PredictedCRM_has_Expression_Term map ON pcrm.predicted_crm_id = map.predicted_crm_id
JOIN ExpressionTerm et ON map.term_id = et.term_id
WHERE pcrm.state IN ('approval' , 'approved', 'deleted', 'editing') AND
    et.term <> '' AND
    NOT EXISTS (SELECT ts.ts_id
                FROM triplestore_predicted_crm ts
                WHERE map.predicted_crm_id = ts.predicted_crm_id AND
                    map.term_id = et.term_id AND
                    et.identifier = ts.expression)
ORDER BY pcrm.name,
	et.term;

DROP VIEW v_predicted_crm_ts_audit;

CREATE VIEW v_predicted_cis_regulatory_module_ts_audit AS
SELECT sfs.scientific_name AS sequence_from_species_scientific_name,
    ais.scientific_name AS assayed_in_species_scientific_name,
    CONCAT(pcrm.name, ' ') AS name,
    CONCAT(curator.first_name, ' ', curator.last_name) AS curator_full_name,
    pcrm.state,
    CASE ts.pubmed_id
        WHEN NULL THEN ''
        ELSE ts.pubmed_id
    END AS pubmed_id,
    c.name AS chromosome,
    CONCAT(c.name, ' (', sfs.short_name, ')') AS chromosome_display,
    pcrm.current_start AS start,
    pcrm.current_end AS end,
    et.term AS expression_term,
    CASE ds_on.term
        WHEN NULL THEN ''
        ELSE ds_on.term
    END AS stage_on_term,
    CASE ds_off.term
        WHEN NULL THEN ''
        ELSE ds_off.term
    END AS stage_off_term,
    CASE bp.term
        WHEN NULL THEN ''
        ELSE bp.term
    END AS biological_process_term,
    CASE ts.sex
        WHEN NULL THEN ''
        ELSE ts.sex
    END AS sex
FROM PredictedCRM pcrm
JOIN Users curator ON pcrm.curator_id = curator.user_id
JOIN Species sfs ON pcrm.sequence_from_species_id = sfs.species_id
JOIN Chromosome c ON pcrm.chromosome_id = c.chromosome_id
JOIN Species ais ON pcrm.assayed_in_species_id = ais.species_id
JOIN triplestore_predicted_crm ts ON pcrm.predicted_crm_id = ts.predicted_crm_id
JOIN ExpressionTerm et ON ts.expression = et.identifier
JOIN DevelopmentalStage ds_on ON ais.species_id = ds_on.species_id AND
    ts.stage_on = ds_on.identifier
JOIN DevelopmentalStage ds_off ON ais.species_id = ds_off.species_id AND
    ts.stage_off = ds_off.identifier
LEFT OUTER JOIN BiologicalProcess bp ON ts.biological_process = bp.go_id
WHERE pcrm.state IN ('approval' , 'approved', 'deleted', 'editing') AND
    et.term <> ''
ORDER BY pcrm.name,
	et.term;

DROP VIEW v_predicted_crm_ts_notify_author;

CREATE VIEW v_predicted_cis_regulatory_module_ts_notify_author AS
SELECT ts.predicted_crm_id,
    ts.expression AS expression_identifier,
    ds.term AS stage_on_term,
    ds2.term AS stage_off_term,
    CASE bp.term
        WHEN NULL THEN ''
        ELSE bp.term
    END AS biological_process_term,
    UCASE(ts.sex) AS sex_term
FROM PredictedCRM pcrm
LEFT JOIN triplestore_predicted_crm ts ON pcrm.predicted_crm_id = ts.predicted_crm_id
LEFT JOIN DevelopmentalStage ds ON ts.stage_on = ds.identifier
LEFT JOIN DevelopmentalStage ds2 ON ts.stage_off = ds2.identifier
LEFT OUTER JOIN BiologicalProcess bp ON ts.biological_process = bp.go_id
WHERE pcrm.state = 'approved';

DROP VIEW v_rc_audit;

CREATE VIEW v_reporter_construct_audit AS
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
    g.name AS gene_name,
    c.name AS chromosome,
    CONCAT(c.name, ' (', sfs.short_name, ')') AS chromosome_display,
    rc.current_start AS start,
    rc.current_end AS end,
    CONCAT(c.name, ':', rc.current_start, '..', rc.current_end) AS coordinates,
    rc.sequence,
    rc.fbtp,
    rc.figure_labels,
    e.term AS evidence,
    GROUP_CONCAT(DISTINCT et.term ORDER BY et.term ASC SEPARATOR ',') AS expressions,
    GROUP_CONCAT(DISTINCT et.identifier ORDER BY et.term ASC SEPARATOR ',') AS expression_identifiers,
    rc.notes,
    ss.term AS sequence_source,
    rc.date_added,
    rc.last_update,
    rc.last_audit
FROM ReporterConstruct rc
LEFT JOIN Users curator ON rc.curator_id = curator.user_id
LEFT OUTER JOIN Users auditor ON rc.auditor_id = auditor.user_id
LEFT JOIN Species sfs ON rc.sequence_from_species_id = sfs.species_id
LEFT JOIN Species ais ON rc.assayed_in_species_id = ais.species_id
LEFT JOIN Gene g ON rc.gene_id = g.gene_id
LEFT JOIN Chromosome c ON rc.chromosome_id = c.chromosome_id
LEFT JOIN EvidenceTerm e ON rc.evidence_id = e.evidence_id
LEFT JOIN SequenceSourceTerm ss ON rc.sequence_source_id = ss.source_id
LEFT OUTER JOIN RC_has_ExprTerm ON rc.rc_id = RC_has_ExprTerm.rc_id
LEFT OUTER JOIN ExpressionTerm et ON RC_has_ExprTerm.term_id = et.term_id
WHERE rc.state IN ('approval' , 'approved', 'deleted', 'editing')
GROUP BY rc.rc_id;

DROP VIEW v_rc_feature_location;

CREATE VIEW v_reporter_construct_feature_location AS
SELECT rc.rc_id AS id,
    f.type,
    f.name,
    f.parent,
    f.feature_id,
    f.identifier,
    rc.current_start AS start,
    rc.current_end AS end,
    f.start AS f_start,
    f.end AS f_end,
    f.strand,
    IF(f.strand = '+',
        IF(rc.current_start < f.start + 5,
            5,
            IF(rc.current_start > f.end + 5, 3, 0)),
        IF(rc.current_end < f.start + 5,
            3,
            IF(rc.current_end > f.end + 5, 5, 0))) AS relative_start,
    IF(f.strand = '+',
        IF(rc.current_end < f.start + 5,
            5,
            IF(rc.current_end > f.end + 5, 3, 0)),
        IF(rc.current_start < f.start + 5,
            3,
            IF(rc.current_start > f.end + 5, 5, 0))) AS relative_end,
    IF(f.strand = '+',
        IF(rc.current_start < f.start + 5,
            ABS(f.start - rc.current_start),
            IF(rc.current_start > f.end + 5,
                ABS(rc.current_start - f.end),
                0)),
        IF(rc.current_end < f.start + 5,
            ABS(f.start - rc.current_end),
            IF(rc.current_end > f.end + 5,
                ABS(rc.current_end - f.end),
                0))) AS start_dist,
    IF(f.strand = '+',
        IF(rc.current_end < f.start + 5,
            ABS(f.start - rc.current_end),
            IF(rc.current_end > f.end + 5,
                ABS(rc.current_end - f.end),
                0)),
        IF(rc.current_start < f.start + 5,
            ABS(f.start - rc.current_start),
            IF(rc.current_start > f.end + 5,
                ABS(f.end - rc.current_start),
                0))) AS end_dist
FROM Features f
LEFT JOIN ReporterConstruct rc ON (f.gene_id = rc.gene_id)
WHERE rc.state = 'current'
ORDER BY rc.rc_id,
    f.feature_id,
    f.parent;

DROP VIEW v_rc_minimalization;

CREATE VIEW v_reporter_construct_minimalization AS
SELECT rc.rc_id,
    rc.name,
    rc.current_start AS start,
    rc.current_end AS end,
    rc.is_override,
    g.name AS gene,
    g.identifier AS gene_identifier,
    c.name AS chr,
    c.chromosome_id,
    GROUP_CONCAT(e.identifier ORDER BY e.identifier ASC separator ',') AS expr_terms
FROM ReporterConstruct rc
JOIN Gene g ON rc.gene_id = g.gene_id
JOIN Chromosome c ON rc.chromosome_id = c.chromosome_id
JOIN RC_has_ExprTerm emap ON rc.rc_id = emap.rc_id
JOIN ExpressionTerm e ON emap.term_id = e.term_id
WHERE rc.state = 'current'
GROUP BY rc.rc_id
ORDER BY g.name,
    c.name,
    rc.current_start,
    rc.current_end;

DROP VIEW v_reporter_construct_search;

CREATE VIEW v_reporter_construct_no_ts_audit AS
SELECT sfs.scientific_name AS sequence_from_species_scientific_name,
    ais.scientific_name AS assayed_in_species_scientific_name,
    CONCAT(rc.name, ' ') AS name,
    CONCAT(curator.first_name, ' ', curator.last_name) AS curator_full_name,
    rc.state,
    '' AS pubmed_id,
    c.name AS chromosome,
    CONCAT(c.name, ' (', sfs.short_name, ')') AS chromosome_display,
    rc.current_start AS start,
    rc.current_end AS end,
    g.name AS gene_name,
    et.term AS expression_term,
    '' AS stage_on_term,
    '' AS stage_off_term,
    '' AS biological_process_term,
    '' AS sex,
    '' AS ectopic
FROM ReporterConstruct rc
JOIN Users curator ON rc.curator_id = curator.user_id
JOIN Species sfs ON rc.sequence_from_species_id = sfs.species_id
JOIN Gene g ON rc.gene_id = g.gene_id
JOIN Chromosome c ON rc.chromosome_id = c.chromosome_id
JOIN Species ais ON rc.assayed_in_species_id = ais.species_id
JOIN RC_has_ExprTerm map ON rc.rc_id = map.rc_id
JOIN ExpressionTerm et ON map.term_id = et.term_id
WHERE rc.state IN ('approval' , 'approved', 'deleted', 'editing') AND
    et.term <> '' AND
    NOT EXISTS (SELECT ts.ts_id
                FROM triplestore_rc ts
                WHERE map.rc_id = ts.rc_id AND
                    map.term_id = et.term_id AND
                    et.identifier = ts.expression)
ORDER BY rc.name,
	et.term;

DROP VIEW v_rc_ts_audit;

CREATE VIEW v_reporter_construct_ts_audit AS
SELECT sfs.scientific_name AS sequence_from_species_scientific_name,
    ais.scientific_name AS assayed_in_species_scientific_name,
    CONCAT(rc.name, ' ') AS name,
    CONCAT(curator.first_name, ' ', curator.last_name) AS curator_full_name,
    rc.state,
    CASE ts.pubmed_id
        WHEN NULL THEN ''
        ELSE ts.pubmed_id
    END AS pubmed_id,
    c.name AS chromosome,
    CONCAT(c.name, ' (', sfs.short_name, ')') AS chromosome_display,
    rc.current_start AS start,
    rc.current_end AS end,
    g.name AS gene_name,
    et.term AS expression_term,
    CASE ds_on.term
        WHEN NULL THEN ''
        ELSE ds_on.term
    END AS stage_on_term,
    CASE ds_off.term
        WHEN NULL THEN ''
        ELSE ds_off.term
    END AS stage_off_term,
    CASE bp.term
        WHEN NULL THEN ''
        ELSE bp.term
    END AS biological_process_term,
    CASE ts.sex
        WHEN NULL THEN ''
        ELSE ts.sex
    END AS sex,
    CASE ts.ectopic
        WHEN NULL THEN ''
        ELSE ts.ectopic
    END AS ectopic
FROM ReporterConstruct rc
JOIN Users curator ON rc.curator_id = curator.user_id
JOIN Species sfs ON rc.sequence_from_species_id = sfs.species_id
JOIN Gene g ON rc.gene_id = g.gene_id
JOIN Chromosome c ON rc.chromosome_id = c.chromosome_id
JOIN Species ais ON rc.assayed_in_species_id = ais.species_id
JOIN triplestore_rc ts ON rc.rc_id = ts.rc_id
JOIN ExpressionTerm et ON ts.expression = et.identifier
JOIN DevelopmentalStage ds_on ON ais.species_id = ds_on.species_id AND
    ts.stage_on = ds_on.identifier
JOIN DevelopmentalStage ds_off ON ais.species_id = ds_off.species_id AND
    ts.stage_off = ds_off.identifier
LEFT OUTER JOIN BiologicalProcess bp ON ts.biological_process = bp.go_id
WHERE rc.state IN ('approval' , 'approved', 'deleted', 'editing') AND
    et.term <> ''
ORDER BY rc.name,
	et.term;

DROP VIEW v_rc_ts_notify_author;

CREATE VIEW v_reporter_construct_ts_notify_author AS
SELECT ts.rc_id,
    ts.expression AS expression_identifier,
    ds.term AS stage_on_term,
    ds2.term AS stage_off_term,
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
LEFT JOIN triplestore_rc ts ON rc.rc_id = ts.rc_id
LEFT JOIN DevelopmentalStage ds ON ts.stage_on = ds.identifier
LEFT JOIN DevelopmentalStage ds2 ON ts.stage_off = ds2.identifier
LEFT OUTER JOIN BiologicalProcess bp ON ts.biological_process = bp.go_id
WHERE rc.state = 'approved';

CREATE OR REPLACE VIEW v_reporter_construct_file AS
SELECT CONCAT('RFRC:', LPAD(rc.entity_id, 10, '0'), '.', LPAD(rc.version, 3, '0')) AS redfly_id,
    CONCAT('RFRC:', LPAD(rc.entity_id, 10, '0')) AS redfly_id_unversioned,
    rc.rc_id,
    rc.pubmed_id,
    rc.fbtp,
    CASE 
        WHEN rc.is_crm = 1 THEN 'REDfly_CRM'
        WHEN rc.cell_culture_only = 1 THEN 'REDfly_RC_CLO'
        ELSE 'REDfly_RC'
    END AS label,
    rc.is_crm,
    rc.cell_culture_only,
    rc.name,
    sfs.scientific_name AS sequence_from_species_scientific_name,
    ais.scientific_name AS assayed_in_species_scientific_name,
    g.name AS gene_name,
    g.identifier AS gene_identifier,
    rc.sequence AS sequence,
    e.term AS evidence_term,
    c.name AS chromosome,
    rc.current_start AS start,
    rc.current_end AS end,
    IFNULL(GROUP_CONCAT(DISTINCT tfbs.name ORDER BY tfbs.name SEPARATOR ','), '') AS associated_tfbs,
    IFNULL(GROUP_CONCAT(DISTINCT et.identifier ORDER BY et.identifier SEPARATOR ','), '') AS ontology_term
FROM ReporterConstruct rc
LEFT JOIN Species sfs ON rc.sequence_from_species_id = sfs.species_id
LEFT JOIN Species ais ON rc.assayed_in_species_id = ais.species_id
LEFT JOIN Gene g ON rc.gene_id = g.gene_id
LEFT JOIN Chromosome c ON rc.chromosome_id = c.chromosome_id
LEFT JOIN EvidenceTerm e ON rc.evidence_id = e.evidence_id
LEFT JOIN RC_associated_BS ON rc.rc_id = RC_associated_BS.rc_id
LEFT JOIN BindingSite tfbs ON RC_associated_BS.tfbs_id = tfbs.tfbs_id
LEFT JOIN RC_has_ExprTerm ON rc.rc_id = RC_has_ExprTerm.rc_id
LEFT JOIN ExpressionTerm et ON RC_has_ExprTerm.term_id = et.term_id
WHERE rc.state = 'current'  
GROUP BY rc.rc_id;

CREATE OR REPLACE VIEW v_reporter_construct_staging_data_file AS
SELECT 'RFRC' AS entity_type,
    rc.rc_id AS parent_id,
    rc.pubmed_id AS parent_pubmed_id,
    rc.name,
    ts.expression AS expression_identifier,
    ts.pubmed_id,
    ts.stage_on AS stage_on_identifier,
    ts.stage_off AS stage_off_identifier,
    ts.biological_process AS biological_process_identifier,
    ts.sex,
    ts.ectopic
FROM ReporterConstruct rc 
JOIN triplestore_rc ts ON rc.rc_id = ts.rc_id
WHERE rc.state = 'current';

DROP VIEW v_tfbs_audit;

CREATE VIEW v_transcription_factor_binding_site_audit AS
SELECT tfbs.tfbs_id AS id,
    tfbs.state,
    tfbs.name,
    tfbs.pubmed_id,
    tfbs.curator_id,
    CONCAT(u.first_name, ' ', u.last_name) AS curator_full_name,
    sfs.scientific_name AS sequence_from_species_scientific_name,
    ais.scientific_name AS assayed_in_species_scientific_name,
    g.name AS gene_name,
    c.name AS chromosome,
    CONCAT(c.name, ' (', sfs.short_name, ')') AS chromosome_display,
    tfbs.current_start AS start,
    tfbs.current_end AS end,
    CONCAT(c.name, ':', tfbs.current_start, '..', tfbs.current_end) AS coordinates,
    tfbs.notes,
    tfbs.date_added,
    tfbs.last_update
FROM BindingSite tfbs
LEFT JOIN Species sfs ON tfbs.sequence_from_species_id = sfs.species_id
LEFT JOIN Species ais ON tfbs.assayed_in_species_id = ais.species_id
LEFT JOIN Users u ON tfbs.curator_id = u.user_id
LEFT JOIN Gene g ON tfbs.gene_id = g.gene_id
LEFT JOIN Chromosome c ON tfbs.chromosome_id = c.chromosome_id
WHERE tfbs.state IN ('approval' , 'approved', 'editing', 'rejected')
ORDER BY tfbs.tfbs_id;

DROP VIEW v_tfbs_feature_location;

CREATE VIEW v_transcription_factor_binding_site_feature_location AS
SELECT tfbs.tfbs_id AS id,
    f.type,
    f.name,
    f.parent,
    f.feature_id,
    f.identifier,
    tfbs.current_start,
    tfbs.current_end,
    f.start AS f_start,
    f.end AS f_end,
    f.strand,
    IF(f.strand = '+',
        IF(tfbs.current_start < f.start + 5,
            5,
            IF(tfbs.current_start > f.end + 5, 3, 0)),
        IF(tfbs.current_end < f.start + 5,
            3,
            IF(tfbs.current_end > f.end + 5, 5, 0))) AS relative_start,
    IF(f.strand = '+',
        IF(tfbs.current_end < f.start + 5,
            5,
            IF(tfbs.current_end > f.end + 5, 3, 0)),
        IF(tfbs.current_start < f.start + 5,
            3,
            IF(tfbs.current_start > f.end + 5, 5, 0))) AS relative_end,
    IF(f.strand = '+',
        IF(tfbs.current_start < f.start + 5,
            ABS(f.start - tfbs.current_start),
            IF(tfbs.current_start > f.end + 5,
                ABS(tfbs.current_start - f.end),
                0)),
        IF(tfbs.current_end < f.start + 5,
            ABS(f.start - tfbs.current_end),
            IF(tfbs.current_end > f.end + 5,
                ABS(tfbs.current_end - f.end),
                0))) AS start_dist,
    IF(f.strand = '+',
        IF(tfbs.current_end < f.start + 5,
            ABS(f.start - tfbs.current_end),
                IF(tfbs.current_end > f.end + 5,
                    ABS(tfbs.current_end - f.end),
                0)),
        IF(tfbs.current_start < f.start + 5,
            ABS(f.start - tfbs.current_start),
            IF(tfbs.current_start > f.end + 5,
                ABS(f.end - tfbs.current_start),
                0))) AS end_dist
FROM Features f
LEFT JOIN BindingSite tfbs ON (f.gene_id = tfbs.gene_id)
WHERE tfbs.state = 'current'
ORDER BY tfbs.tfbs_id,
    f.feature_id,
    f.parent;

CREATE OR REPLACE VIEW v_transcription_factor_binding_site_file AS
SELECT CONCAT('RFTF:', LPAD(tfbs.entity_id, 10, '0'), '.', LPAD(tfbs.version, 3, '0')) AS redfly_id,
    CONCAT('RFTF:', LPAD(tfbs.entity_id, 10, '0')) AS redfly_id_unversioned,
    tfbs.tfbs_id,
    tfbs.pubmed_id,
    'REDfly_TFBS' AS label,
    tfbs.name,
    sfs.scientific_name AS sequence_from_species_scientific_name,
    ais.scientific_name AS assayed_in_species_scientific_name,
    g.name AS gene_name,
    tf.name AS tf_name,
    g.identifier AS gene_identifier,
    tf.identifier AS tf_identifier,
    tfbs.sequence,
    tfbs.sequence_with_flank,
    e.term AS evidence_term, 
    c.name AS chromosome,
    tfbs.current_start AS start,
    tfbs.current_end AS end,
    IFNULL(GROUP_CONCAT(DISTINCT rc.name ORDER BY rc.name SEPARATOR ','), '') AS associated_rc,
    IFNULL(GROUP_CONCAT(DISTINCT et.identifier ORDER BY et.identifier SEPARATOR ','), '') AS ontology_term
FROM BindingSite tfbs
LEFT JOIN Species sfs ON tfbs.sequence_from_species_id = sfs.species_id
LEFT JOIN Species ais ON tfbs.assayed_in_species_id = ais.species_id
LEFT JOIN Gene g ON tfbs.gene_id = g.gene_id
LEFT JOIN Gene tf ON tfbs.tf_id = tf.gene_id
LEFT JOIN EvidenceTerm e ON tfbs.evidence_id = e.evidence_id
LEFT JOIN Chromosome c ON tfbs.chromosome_id = c.chromosome_id
LEFT JOIN RC_associated_BS ON tfbs.tfbs_id = RC_associated_BS.tfbs_id
LEFT JOIN ReporterConstruct rc ON RC_associated_BS.rc_id = rc.rc_id
LEFT JOIN RC_has_ExprTerm ON RC_associated_BS.rc_id = RC_has_ExprTerm.rc_id
LEFT JOIN ExpressionTerm et ON RC_has_ExprTerm.term_id = et.term_id
WHERE tfbs.state = 'current'
GROUP BY tfbs.tfbs_id;

\! echo "Done!";

\! echo "Making staging table transformations..."; 

CREATE OR REPLACE TABLE staging_developmental_stage_update (
    species_short_name VARCHAR(255) NOT NULL,
    identifier         VARCHAR(255) NOT NULL,
    term               VARCHAR(255) NOT NULL
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4;

DROP TABLE staging_expr_term_update;

CREATE TABLE staging_expression_update (
    species_short_name VARCHAR(255) NOT NULL,
    identifier         VARCHAR(255) NOT NULL,
    term               VARCHAR(255) NOT NULL
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4;

CREATE OR REPLACE TABLE staging_feature_update (
  species_short_name VARCHAR(255) NOT NULL,
  type               ENUM('cds',
                          'exon',
                          'five_prime_utr',
                          'intron',
                          'mrna',
                          'ncrna',
                          'pseudogene',
                          'snorna',
                          'snrna',
                          'three_prime_utr',
                          'trna') NOT NULL,
  INDEX (type),
  start              INT NOT NULL,
  end                INT NOT NULL,
  strand             VARCHAR(1) NOT NULL,
  identifier         VARCHAR(32) NOT NULL,
  name               VARCHAR(64) NOT NULL,
  parent             VARCHAR(32) NOT NULL,
  INDEX (parent)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4;

CREATE OR REPLACE TABLE staging_gene_update (
  species_short_name              VARCHAR(255) NOT NULL,
  genome_assembly_id              INT(10) UNSIGNED,
  genome_assembly_release_version VARCHAR(255) NOT NULL,
  identifier                      VARCHAR(255) NOT NULL,
  name                            VARCHAR(255) NOT NULL,
  chromosome_id                   INT(10) UNSIGNED,
  chromosome_name                 VARCHAR(255) NOT NULL,
  start                           INT NOT NULL,
  end                             INT NOT NULL,
  strand                          VARCHAR(255) NOT NULL
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4;

\! echo "Done!";

\! echo "Building up new statistics functions...";

DELIMITER //

CREATE FUNCTION NumberOfCurrentReporterConstructsWithStagingData()
RETURNS INT DETERMINISTIC
BEGIN
	DECLARE number_of_current_reporter_constructs_with_staging_data INT;

	SELECT COUNT(rc.name)
	INTO number_of_current_reporter_constructs_with_staging_data 
	FROM ReporterConstruct rc
	WHERE rc.state = ('current') AND
		EXISTS (SELECT DISTINCT rc.rc_id
	            FROM triplestore_rc ts
		        WHERE rc.rc_id = ts.rc_id);
				
	RETURN number_of_current_reporter_constructs_with_staging_data;
END; //

DELIMITER ;

DELIMITER //

CREATE FUNCTION NumberOfCurrentReporterConstructsWithoutStagingData() RETURNS int(11)
    DETERMINISTIC
BEGIN
	DECLARE number_of_current_reporter_constructs_without_staging_data INT;

	SELECT COUNT(rc.name)
	INTO number_of_current_reporter_constructs_without_staging_data 
	FROM ReporterConstruct rc
	WHERE rc.state = ('current') AND
		NOT EXISTS (SELECT DISTINCT rc.rc_id
	                FROM triplestore_rc ts
			        WHERE rc.rc_id = ts.rc_id);
				
	RETURN number_of_current_reporter_constructs_without_staging_data;
END; //

DELIMITER ;

DELIMITER //

CREATE FUNCTION NumberOfCurrentCisRegulatoryModulesWithStagingData()
RETURNS INT DETERMINISTIC
BEGIN
	DECLARE number_of_current_cis_regulatory_modules_with_staging_data INT;

	SELECT COUNT(rc.name)
	INTO number_of_current_cis_regulatory_modules_with_staging_data 
	FROM ReporterConstruct rc
	WHERE rc.state = ('current') AND
        rc.is_crm = 1 AND  
		EXISTS (SELECT DISTINCT rc.rc_id
	            FROM triplestore_rc ts
		        WHERE rc.rc_id = ts.rc_id);
				
	RETURN number_of_current_cis_regulatory_modules_with_staging_data;
END; //

DELIMITER ;

DELIMITER //

CREATE FUNCTION NumberOfCurrentCisRegulatoryModulesWithoutStagingData()
RETURNS INT DETERMINISTIC
BEGIN
	DECLARE number_of_current_cis_regulatory_modules_without_staging_data INT;

	SELECT COUNT(rc.name)
	INTO number_of_current_cis_regulatory_modules_without_staging_data 
	FROM ReporterConstruct rc
	WHERE rc.state = ('current') AND
        rc.is_crm = 1 AND
		NOT EXISTS (SELECT DISTINCT rc.rc_id
	                FROM triplestore_rc ts
		            WHERE rc.rc_id = ts.rc_id);
				
	RETURN number_of_current_cis_regulatory_modules_without_staging_data;
END; //

DELIMITER ;

DELIMITER //

CREATE FUNCTION NumberOfCurrentNonCisRegulatoryModulesWithStagingData()
RETURNS INT DETERMINISTIC
BEGIN
	DECLARE number_of_current_non_cis_regulatory_modules_with_staging_data INT;

	SELECT COUNT(rc.name)
	INTO number_of_current_non_cis_regulatory_modules_with_staging_data 
	FROM ReporterConstruct rc
	WHERE rc.state = ('current') AND
        rc.is_crm = 0 AND
		EXISTS (SELECT DISTINCT rc.rc_id
	            FROM triplestore_rc ts
		        WHERE rc.rc_id = ts.rc_id);
				
	RETURN number_of_current_non_cis_regulatory_modules_with_staging_data;
END; //

DELIMITER ;

DELIMITER //

CREATE FUNCTION NumberOfCurrentNonCisRegulatoryModulesWithoutStagingData()
RETURNS INT DETERMINISTIC
BEGIN
	DECLARE number_of_current_non_cis_regulatory_modules_without_staging_data INT;

	SELECT COUNT(rc.name)
	INTO number_of_current_non_cis_regulatory_modules_without_staging_data 
	FROM ReporterConstruct rc
	WHERE rc.state = ('current') AND
        rc.is_crm = 0 AND
		NOT EXISTS (SELECT DISTINCT rc.rc_id
	                FROM triplestore_rc ts
		            WHERE rc.rc_id = ts.rc_id);
				
	RETURN number_of_current_non_cis_regulatory_modules_without_staging_data;
END; //

DELIMITER ;

DELIMITER //

CREATE FUNCTION NumberOfCurrentPredictedCisRegulatoryModulesWithStagingData()
RETURNS INT DETERMINISTIC
BEGIN
	DECLARE number_of_current_predicted_cis_regulatory_modules_with_staging_data INT;

	SELECT COUNT(pcrm.name)
	INTO number_of_current_predicted_cis_regulatory_modules_with_staging_data 
	FROM PredictedCRM pcrm
	WHERE pcrm.state = ('current') AND
		EXISTS (SELECT DISTINCT pcrm.predicted_crm_id
	            FROM triplestore_predicted_crm tpc
		        WHERE pcrm.predicted_crm_id = tpc.predicted_crm_id);
				
	RETURN number_of_current_predicted_cis_regulatory_modules_with_staging_data;
END; //

DELIMITER ;

DELIMITER //

CREATE FUNCTION NumberOfCurrentPredictedCisRegulatoryModulesWithoutStagingData()
RETURNS INT DETERMINISTIC
BEGIN
	DECLARE number_of_current_predicted_cis_regulatory_modules_without_staging_data INT;

	SELECT COUNT(pcrm.name)
	INTO number_of_current_predicted_cis_regulatory_modules_without_staging_data 
	FROM PredictedCRM pcrm
	WHERE pcrm.state = ('current') AND
		NOT EXISTS (SELECT DISTINCT pcrm.predicted_crm_id
	                FROM triplestore_predicted_crm tpc
		            WHERE pcrm.predicted_crm_id = tpc.predicted_crm_id);
				
	RETURN number_of_current_predicted_cis_regulatory_modules_without_staging_data;
END; //

DELIMITER ;

DELIMITER //

CREATE FUNCTION NumberOfCurrentCisRegulatoryModuleSegmentsWithStagingData()
RETURNS INT DETERMINISTIC
BEGIN
	DECLARE number_of_current_cis_regulatory_module_segments_with_staging_data INT;

	SELECT COUNT(crms.name)
	INTO number_of_current_cis_regulatory_module_segments_with_staging_data 
	FROM CRMSegment crms
	WHERE crms.state = ('current') AND
		EXISTS (SELECT DISTINCT crms.crm_segment_id
	            FROM triplestore_crm_segment tcs
		        WHERE crms.crm_segment_id = tcs.crm_segment_id);
				
	RETURN number_of_current_cis_regulatory_module_segments_with_staging_data;
END; //

DELIMITER ;

DELIMITER //

CREATE FUNCTION NumberOfCurrentCisRegulatoryModuleSegmentsWithoutStagingData()
RETURNS INT DETERMINISTIC
BEGIN
	DECLARE number_of_current_cis_regulatory_module_segments_without_staging_data INT;

	SELECT COUNT(crms.name)
	INTO number_of_current_cis_regulatory_module_segments_without_staging_data 
	FROM CRMSegment crms
	WHERE crms.state = ('current') AND
		NOT EXISTS (SELECT DISTINCT crms.crm_segment_id
	                FROM triplestore_crm_segment tcs
		            WHERE crms.crm_segment_id = tcs.crm_segment_id);
				
	RETURN number_of_current_cis_regulatory_module_segments_without_staging_data;
END; //

DELIMITER ;

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
        current_end)
    VALUES (
        sequence_from_species_id,
        assayed_in_species_id,
        current_genome_assembly_release_version,
        chromosome_id,
        current_start,
        current_end);

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

DROP PROCEDURE update_expression_terms;

DELIMITER //

CREATE PROCEDURE update_anatomical_expressions(
    OUT identifiers TEXT,
    OUT old_terms TEXT,
    OUT new_terms TEXT,
    OUT updated_anatomical_expressions_number INT,
    OUT deleted_anatomical_expressions_number INT,
    OUT new_anatomical_expressions_number INT
)   MODIFIES SQL DATA
    SQL SECURITY INVOKER
    COMMENT 'Updates the anatomical expressions based on the data stored in staging'
BEGIN
    SET identifiers :=  (
	    SELECT GROUP_CONCAT(old.identifier ORDER BY old.identifier SEPARATOR '\t')
        FROM ExpressionTerm AS old
        JOIN Species AS s USING(species_id)
        JOIN staging_expression_update AS new ON old.identifier = new.identifier
        WHERE old.species_id = s.species_id AND
            s.short_name = new.species_short_name AND
            BINARY old.term != BINARY new.term
    );

    SET old_terms :=  (
	    SELECT GROUP_CONCAT(old.term ORDER BY old.identifier SEPARATOR '\t')
        FROM ExpressionTerm AS old
        JOIN Species AS s USING(species_id)
        JOIN staging_expression_update AS new ON old.identifier = new.identifier
        WHERE old.species_id = s.species_id AND
            s.short_name = new.species_short_name AND
            BINARY old.term != BINARY new.term
    );

    SET new_terms :=  (
	    SELECT GROUP_CONCAT(new.term ORDER BY old.identifier SEPARATOR '\t')
        FROM ExpressionTerm AS old
        JOIN Species AS s USING(species_id)
        JOIN staging_expression_update AS new ON old.identifier = new.identifier
        WHERE old.species_id = s.species_id AND
            s.short_name = new.species_short_name AND
            BINARY old.term != BINARY new.term
    );

    UPDATE ExpressionTerm AS old
    JOIN Species AS s USING(species_id)
    JOIN staging_expression_update AS new ON old.identifier = new.identifier
    SET old.term = new.term
    WHERE old.species_id = s.species_id AND
        s.short_name = new.species_short_name AND
        BINARY old.term != BINARY new.term;

    SELECT ROW_COUNT()
    INTO updated_anatomical_expressions_number;
   
    UPDATE ExpressionTerm AS old
    JOIN Species AS s USING(species_id)
    LEFT OUTER JOIN staging_expression_update AS new ON old.identifier = new.identifier
    SET old.is_deprecated = true
    WHERE old.species_id = s.species_id AND
        s.short_name = new.species_short_name AND
        new.identifier IS NULL;

    DELETE FROM ExpressionTerm
    WHERE term_id NOT IN (SELECT DISTINCT term_id
                          FROM RC_has_ExprTerm) AND
        term_id NOT IN (SELECT DISTINCT term_id
                        FROM icrm_has_expr_term) AND
        term_id NOT IN (SELECT DISTINCT term_id
                        FROM CRMSegment_has_Expression_Term) AND
        term_id NOT IN (SELECT DISTINCT term_id
                        FROM PredictedCRM_has_Expression_Term) AND                        
        is_deprecated = true;

    SELECT ROW_COUNT()
    INTO deleted_anatomical_expressions_number;
              
    INSERT INTO ExpressionTerm (
        species_id,
        term,
        identifier,
        is_deprecated)
    SELECT s.species_id,
        new.term,
        new.identifier,
        false
    FROM staging_expression_update AS new
    JOIN Species AS s ON new.species_short_name = s.short_name
    WHERE new.identifier NOT IN (SELECT identifier
                                 FROM ExpressionTerm);
                                
    SELECT ROW_COUNT()
    INTO new_anatomical_expressions_number;
END //

DELIMITER //

CREATE OR REPLACE PROCEDURE update_biological_processes(
    OUT go_ids TEXT,
    OUT old_terms TEXT,
    OUT new_terms TEXT,
    OUT updated_biological_processes_number_with_new_term INT,
    OUT deleted_biological_processes_number INT,
    OUT new_biological_processes_number INT)
    MODIFIES SQL DATA
    SQL SECURITY INVOKER
    COMMENT 'Updates the biological processes based on the data stored in staging'
BEGIN
    SET go_ids :=  (
	    SELECT GROUP_CONCAT(old.go_id ORDER BY old.go_id SEPARATOR '\t')
        FROM BiologicalProcess AS old
        JOIN staging_biological_process_update AS new USING (go_id)
        WHERE BINARY old.term != BINARY new.term
    );

    SET old_terms :=  (
	    SELECT GROUP_CONCAT(old.term ORDER BY old.go_id SEPARATOR '\t')
        FROM BiologicalProcess AS old
        JOIN staging_biological_process_update AS new USING (go_id)
        WHERE BINARY old.term != BINARY new.term
    );

    SET new_terms :=  (
	    SELECT GROUP_CONCAT(new.term ORDER BY old.go_id SEPARATOR '\t')
        FROM BiologicalProcess AS old
        JOIN staging_biological_process_update AS new USING (go_id)
        WHERE BINARY old.term != BINARY new.term
    );

    UPDATE BiologicalProcess AS old
    JOIN staging_biological_process_update AS new USING (go_id)
    SET old.term = new.term
    WHERE BINARY old.term != BINARY new.term;
 
    SELECT ROW_COUNT()
    INTO updated_biological_processes_number_with_new_term; 

    UPDATE BiologicalProcess AS old
    LEFT OUTER JOIN staging_biological_process_update AS new USING (go_id)
    SET old.is_deprecated = true
    WHERE new.go_id IS NULL;

    DELETE FROM BiologicalProcess
    WHERE is_deprecated = true;

    SELECT ROW_COUNT()
    INTO deleted_biological_processes_number;
 
    INSERT INTO BiologicalProcess (
        term,
        go_id,
        is_deprecated)
    SELECT new.term,
        new.go_id,
        false
    FROM staging_biological_process_update AS new
    WHERE new.go_id NOT IN (SELECT go_id FROM BiologicalProcess);
 
    SELECT ROW_COUNT()
    INTO new_biological_processes_number; 
END //

DELIMITER //

CREATE OR REPLACE PROCEDURE update_developmental_stages(
    OUT identifiers TEXT,
    OUT old_terms TEXT,
    OUT new_terms TEXT,
    OUT updated_developmental_stages_number_with_new_term INT,
    OUT deleted_developmental_stages_number INT,
    OUT new_developmental_stages_number INT)
    MODIFIES SQL DATA
    SQL SECURITY INVOKER
    COMMENT 'Updates the developmental stages based on the data stored in staging'
BEGIN
	DECLARE finished INT DEFAULT 0;
	DECLARE id INT;
	DECLARE species_cursor CURSOR FOR 
		SELECT species_id FROM Species;
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET finished = 1;

    SET identifiers :=  (
	    SELECT GROUP_CONCAT(old.identifier ORDER BY old.identifier SEPARATOR '\t')
        FROM DevelopmentalStage AS old
        JOIN Species AS s USING(species_id)
        JOIN staging_developmental_stage_update AS new ON old.identifier = new.identifier
        WHERE old.species_id = s.species_id AND
            s.short_name = new.species_short_name AND
            BINARY old.term != BINARY new.term
    );

    SET old_terms :=  (
	    SELECT GROUP_CONCAT(old.term ORDER BY old.identifier SEPARATOR '\t')
        FROM DevelopmentalStage AS old
        JOIN Species AS s USING(species_id)
        JOIN staging_developmental_stage_update AS new ON old.identifier = new.identifier
        WHERE old.species_id = s.species_id AND
            s.short_name = new.species_short_name AND
            BINARY old.term != BINARY new.term
    );

    SET new_terms :=  (
	    SELECT GROUP_CONCAT(new.term ORDER BY old.identifier SEPARATOR '\t')
        FROM DevelopmentalStage AS old
        JOIN Species AS s USING(species_id)
        JOIN staging_developmental_stage_update AS new ON old.identifier = new.identifier
        WHERE old.species_id = s.species_id AND
            s.short_name = new.species_short_name AND
            BINARY old.term != BINARY new.term
    );

    UPDATE DevelopmentalStage AS old
    JOIN Species AS s USING(species_id)
    JOIN staging_developmental_stage_update AS new ON (old.identifier = new.identifier)
    SET old.term = new.term
    WHERE old.species_id = s.species_id AND
        s.short_name = new.species_short_name AND
        old.term != new.term;

    SELECT ROW_COUNT()
    INTO updated_developmental_stages_number_with_new_term;
   
    UPDATE DevelopmentalStage AS old
    JOIN Species AS s USING(species_id)
    LEFT OUTER JOIN staging_developmental_stage_update AS new ON (old.identifier = new.identifier)
    SET old.is_deprecated = true
    WHERE old.species_id = s.species_id AND
        s.short_name = new.species_short_name AND
        new.identifier IS NULL;

    DELETE FROM DevelopmentalStage
    WHERE is_deprecated = true;
   
    SELECT ROW_COUNT()
    INTO deleted_developmental_stages_number;   

    INSERT INTO DevelopmentalStage (
        species_id,
        term,
        identifier,
        is_deprecated)
    SELECT s.species_id,
        new.term,
        new.identifier,
        false
    FROM staging_developmental_stage_update AS new
    JOIN Species AS s ON (new.species_short_name = s.short_name)
    WHERE new.identifier NOT IN (SELECT identifier
                                 FROM DevelopmentalStage);

    SELECT ROW_COUNT()
    INTO new_developmental_stages_number;   
   
    OPEN species_cursor;

    species_id_insert: LOOP
        FETCH species_cursor INTO id;
        IF finished = 1 THEN
            LEAVE species_id_insert;
        END IF;
        IF (SELECT species_id
            FROM DevelopmentalStage
            WHERE species_id = id AND
            	term = 'none' AND
            	identifier = 'none') != id THEN
	        INSERT INTO DevelopmentalStage (
    	        species_id,
        	    term,
            	identifier,
	            is_deprecated) 
    	    VALUES (id,
        	    'none',
            	'none',
	            0);
	    END IF;
    END LOOP species_id_insert;

    CLOSE species_cursor;
END //

DELIMITER //

CREATE OR REPLACE PROCEDURE update_features(
    OUT new_mrna_features_number INT,
    OUT new_exon_and_intron_features_number INT)
    MODIFIES SQL DATA
    SQL SECURITY INVOKER
    COMMENT 'Updates the features based on the data stored in staging'
BEGIN
    DELETE FROM Features;

    INSERT INTO Features (
        species_id,
        gene_id,
        type,
        start,
        end,
        strand,
        identifier,
        name)
    SELECT s.species_id,
        g.gene_id,
        new.type,
        new.start,
        new.end,
        new.strand,
        new.identifier,
        new.name
    FROM staging_feature_update AS new
    JOIN Species AS s ON (new.species_short_name = s.short_name)
    JOIN Gene AS g ON (new.parent = g.identifier)
    WHERE new.type = 'mrna';

    SELECT ROW_COUNT()
    INTO new_mrna_features_number;   
   
    CREATE TEMPORARY TABLE tmp_locations AS
    SELECT s.species_id,
        new.type,
        new.start,
        new.end,
        new.strand,
        new.parent
    FROM staging_feature_update AS new
    JOIN Features AS f ON (new.parent = f.identifier)
    JOIN Species AS s ON (new.species_short_name = s.short_name)
    WHERE new.type IN ('exon', 'intron');

    INSERT INTO Features (
        species_id,
        type,
        start,
        end,
        strand,
        parent)
    SELECT species_id,
        type,
        start,
        end,
        strand,
        parent
    FROM tmp_locations;
   
	SELECT ROW_COUNT()
    INTO new_exon_and_intron_features_number;
END //

DELIMITER //

CREATE OR REPLACE PROCEDURE update_genes(
    OUT deleted_genes_number INT,
    OUT identifiers TEXT,
    OUT old_names TEXT,
    OUT new_names TEXT,
    OUT updated_genes_number_with_new_name INT,
    OUT old_identifiers TEXT,
    OUT new_identifiers TEXT,    
    OUT updated_genes_number_with_new_identifier INT,
    OUT renamed_reporter_construct_names TEXT,
    OUT renamed_crm_segment_names TEXT,
    OUT updated_reporter_constructs_number_with_new_gene_name INT,
    OUT updated_crm_segments_number_with_new_gene_name INT,
    OUT new_genes_number INT)
    MODIFIES SQL DATA
    SQL SECURITY INVOKER
    COMMENT 'Updates the genes based on the data stored in staging'
BEGIN
    DELETE FROM Features;

    DELETE FROM Gene
    WHERE gene_id NOT IN (SELECT DISTINCT gene_id FROM BindingSite) AND
        gene_id NOT IN (SELECT DISTINCT tf_id FROM BindingSite) AND
        gene_id NOT IN (SELECT DISTINCT gene_id FROM ReporterConstruct) AND
        gene_id NOT IN (SELECT DISTINCT gene_id FROM CRMSegment);

    SELECT ROW_COUNT()
    INTO deleted_genes_number;

    UPDATE staging_gene_update sgu
    JOIN Species s ON sgu.species_short_name = s.short_name
    JOIN GenomeAssembly ga ON sgu.genome_assembly_release_version = ga.release_version
    JOIN Chromosome c ON s.species_id = c.species_id AND
        ga.genome_assembly_id = c.genome_assembly_id
    SET sgu.genome_assembly_id = c.genome_assembly_id,
        sgu.chromosome_id = c.chromosome_id
    WHERE ga.is_deprecated = 0 AND
        sgu.chromosome_name = c.name;

    SET identifiers :=  (
	    SELECT GROUP_CONCAT(old.identifier ORDER BY old.identifier SEPARATOR '\t')
        FROM Gene AS old
        JOIN Species AS s USING(species_id)
        JOIN GenomeAssembly AS ga USING(species_id)
        JOIN staging_gene_update AS new ON old.identifier = new.identifier
        WHERE old.species_id = s.species_id AND
            s.short_name = new.species_short_name AND
            old.genome_assembly_id = ga.genome_assembly_id AND
            ga.release_version = new.genome_assembly_release_version AND
            BINARY old.name != BINARY new.name
    );

    SET old_names :=  (
	    SELECT GROUP_CONCAT(old.name ORDER BY old.identifier SEPARATOR '\t')
        FROM Gene AS old
        JOIN Species AS s USING(species_id)
        JOIN GenomeAssembly AS ga USING(species_id)
        JOIN staging_gene_update AS new ON old.identifier = new.identifier
        WHERE old.species_id = s.species_id AND
            s.short_name = new.species_short_name AND
            old.genome_assembly_id = ga.genome_assembly_id AND
            ga.release_version = new.genome_assembly_release_version AND
            BINARY old.name != BINARY new.name
    );

    SET new_names :=  (
	    SELECT GROUP_CONCAT(new.name ORDER BY old.identifier SEPARATOR '\t')
        FROM Gene AS old
        JOIN Species AS s USING(species_id)
        JOIN GenomeAssembly AS ga USING(species_id)
        JOIN staging_gene_update AS new ON old.identifier = new.identifier
        WHERE old.species_id = s.species_id AND
            s.short_name = new.species_short_name AND
            old.genome_assembly_id = ga.genome_assembly_id AND
            ga.release_version = new.genome_assembly_release_version AND
            BINARY old.name != BINARY new.name
    );

    UPDATE Gene AS old
    JOIN Species AS s USING(species_id)
    JOIN GenomeAssembly AS ga USING(species_id)
    JOIN staging_gene_update AS new ON old.identifier = new.identifier
    SET old.name = new.name,
        old.genome_assembly_id = new.genome_assembly_id,
        old.chrm_id = new.chromosome_id,
        old.start = new.start,
        old.stop = new.end,
        old.strand = new.strand
    WHERE old.species_id = s.species_id AND
        s.short_name = new.species_short_name AND
        old.genome_assembly_id = ga.genome_assembly_id AND
        ga.release_version = new.genome_assembly_release_version AND
        BINARY old.name != BINARY new.name;

    SELECT ROW_COUNT()
    INTO updated_genes_number_with_new_name;

    SET old_identifiers :=  (
	    SELECT GROUP_CONCAT(old.identifier ORDER BY old.identifier SEPARATOR '\t')
        FROM Gene AS old
        JOIN Species AS s USING(species_id)
        JOIN GenomeAssembly AS ga USING(species_id)
        JOIN staging_gene_update AS new ON BINARY old.name = BINARY new.name
        WHERE old.species_id = s.species_id AND
            s.short_name = new.species_short_name AND
            old.genome_assembly_id = ga.genome_assembly_id AND
            ga.release_version = new.genome_assembly_release_version AND
            old.identifier != new.identifier
    );

    SET new_identifiers :=  (
	    SELECT GROUP_CONCAT(new.identifier ORDER BY old.identifier SEPARATOR '\t')
        FROM Gene AS old
        JOIN Species AS s USING(species_id)
        JOIN GenomeAssembly AS ga USING(species_id)
        JOIN staging_gene_update AS new ON BINARY old.name = BINARY new.name
        WHERE old.species_id = s.species_id AND
            s.short_name = new.species_short_name AND
            old.genome_assembly_id = ga.genome_assembly_id AND
            ga.release_version = new.genome_assembly_release_version AND
            old.identifier != new.identifier
    );

    UPDATE Gene AS old
    JOIN Species AS s USING(species_id)
    JOIN GenomeAssembly AS ga USING(species_id)
    JOIN staging_gene_update AS new ON BINARY old.name = BINARY new.name
    SET old.identifier = new.identifier,
        old.genome_assembly_id = new.genome_assembly_id,
        old.chrm_id = new.chromosome_id,
        old.start = new.start,
        old.stop = new.end,
        old.strand = new.strand
    WHERE old.species_id = s.species_id AND
        s.short_name = new.species_short_name AND
        old.genome_assembly_id = ga.genome_assembly_id AND
        ga.release_version = new.genome_assembly_release_version AND
        old.identifier != new.identifier;

    SELECT ROW_COUNT()
    INTO updated_genes_number_with_new_identifier;
    
    SET renamed_reporter_construct_names :=  (
        SELECT GROUP_CONCAT(rc.name ORDER BY rc.name SEPARATOR '\t')
        FROM ReporterConstruct AS rc
        JOIN Gene AS g USING (gene_id)
        WHERE BINARY SUBSTRING_INDEX(rc.name, '_', 1) != BINARY g.name
    );

    UPDATE ReporterConstruct AS rc
    JOIN Gene AS g USING (gene_id)
    SET rc.name = CONCAT(g.name, '_', rc.name)
    WHERE BINARY SUBSTRING_INDEX(rc.name, '_', 1) != BINARY g.name;

    SELECT ROW_COUNT()
    INTO updated_reporter_constructs_number_with_new_gene_name;

    SET renamed_crm_segment_names :=  (
        SELECT GROUP_CONCAT(crm_segment.name ORDER BY crm_segment.name SEPARATOR '\t')
        FROM CRMSegment AS crm_segment
        JOIN Gene AS g USING (gene_id)
        WHERE BINARY SUBSTRING_INDEX(crm_segment.name, '_', 1) != BINARY g.name
    );

    UPDATE CRMSegment AS crm_segment
    JOIN Gene AS g USING (gene_id)
    SET crm_segment.name = CONCAT(g.name, '_', crm_segment.name)
    WHERE BINARY SUBSTRING_INDEX(crm_segment.name, '_', 1) != BINARY g.name;

    SELECT ROW_COUNT()
    INTO updated_crm_segments_number_with_new_gene_name;
   
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
        IF(new.name != '', new.name, new.identifier),
        new.identifier,
        ga.genome_assembly_id,
        c.chromosome_id,
        new.start,
        new.end,
        new.strand
    FROM staging_gene_update AS new
    JOIN Species AS s ON new.species_short_name = s.short_name
    JOIN GenomeAssembly ga ON new.genome_assembly_release_version = ga.release_version
    JOIN Chromosome AS c ON s.species_id = c.species_id AND
        ga.genome_assembly_id = c.genome_assembly_id AND
        new.chromosome_name = c.name
    WHERE new.identifier NOT IN (SELECT identifier FROM Gene);
    
    SELECT ROW_COUNT()
    INTO new_genes_number;
END //

\! echo "Done!";