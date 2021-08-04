\! echo "Making table transformations..."; 

ALTER TABLE ExternalReference 
DROP FOREIGN KEY  `fk_{F2A7CFAF-2DC4-4AB8-804B-B3F2A1E33451}`;

ALTER TABLE ExternalReference 
DROP FOREIGN KEY  `fk_{BE9C7D2A-44C6-4EB3-B167-950FFDCDCFFD}`;

DROP TABLE ExternalReferenceSource;

ALTER TABLE BS_has_ExtRef
DROP FOREIGN KEY  `BS_has_ExtRef_ibfk_1`;

ALTER TABLE BS_has_ExtRef 
DROP FOREIGN KEY  `BS_has_ExtRef_ibfk_2`;

DROP TABLE ExternalReference;

DROP TABLE BS_has_ExtRef;

INSERT INTO Gene (species_id, genome_assembly_id, name, identifier, start, stop, strand, chrm_id)
VALUES (2, 5, 'Unspecified', 'Unspecified', 0, 0, '?', 34);

\! echo "Done!";

\! echo "Making view transformations..."; 

CREATE OR REPLACE VIEW v_cis_regulatory_module_overlaps AS
SELECT ro.rc_id,
    ro.overlap_id,
    ro.sequence_from_species_id,
    ro.current_genome_assembly_release_version,
    ro.chromosome_id,
    ro.current_start AS start,
    ro.current_end AS end,
    ro.assayed_in_species_id,
    GROUP_CONCAT(rt.term_id separator ',') AS terms
FROM (SELECT r.rc_id AS rc_id,
          o.rc_id AS overlap_id,
          r.sequence_from_species_id,
          r.current_genome_assembly_release_version,
          r.chromosome_id AS chromosome_id,
          GREATEST(r.current_start, o.current_start) AS current_start,
          LEAST(r.current_end, o.current_end) AS current_end,
          r.assayed_in_species_id
      FROM redfly.ReporterConstruct r
      JOIN redfly.ReporterConstruct o ON r.state = o.state AND
          r.current_genome_assembly_release_version = o.current_genome_assembly_release_version AND
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

CREATE OR REPLACE VIEW v_transcription_factor_binding_site_audit AS
SELECT tfbs.tfbs_id AS id,
    tfbs.state,
    tfbs.name,
    tfbs.pubmed_id,
    tfbs.curator_id,
    CONCAT(u.first_name, ' ', u.last_name) AS curator_full_name,
    sfs.scientific_name AS sequence_from_species_scientific_name,
    ais.scientific_name AS assayed_in_species_scientific_name,
    g.name AS gene_name,
    g2.name AS tf_name,
    c.name AS chromosome,
    CONCAT(c.name, ' (', sfs.short_name, ')') AS chromosome_display,
    tfbs.current_start AS start,
    tfbs.current_end AS end,
    CONCAT(c.name, ':', tfbs.current_start, '..', tfbs.current_end) AS coordinates,
    tfbs.notes,
    tfbs.date_added,
    tfbs.last_update
FROM BindingSite tfbs
JOIN Species sfs ON tfbs.sequence_from_species_id = sfs.species_id
JOIN Species ais ON tfbs.assayed_in_species_id = ais.species_id
JOIN Users u ON tfbs.curator_id = u.user_id
JOIN Gene g ON tfbs.gene_id = g.gene_id
JOIN Gene g2 ON tfbs.tf_id = g2.gene_id
JOIN Chromosome c ON tfbs.chromosome_id = c.chromosome_id
WHERE tfbs.state IN ('approval' , 'approved', 'editing', 'rejected')
ORDER BY tfbs.tfbs_id;

\! echo "Done!";

\! echo "Making procedure transformations..."; 

DELIMITER //

CREATE OR REPLACE PROCEDURE update_genes(
    OUT deleted_genes_number INT,
    OUT identifiers MEDIUMTEXT,
    OUT old_names MEDIUMTEXT,
    OUT new_names MEDIUMTEXT,
    OUT updated_genes_number_with_new_name INT,
    OUT old_identifiers MEDIUMTEXT,
    OUT new_identifiers MEDIUMTEXT,    
    OUT updated_genes_number_with_new_identifier INT,
    OUT renamed_reporter_construct_names MEDIUMTEXT,
    OUT renamed_crm_segment_names MEDIUMTEXT,
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
        new.name,
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