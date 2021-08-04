\! echo "Making table transformations..."; 

INSERT INTO Species (scientific_name, short_name, public_database_names, public_database_links, public_browser_names, public_browser_links)
VALUES ('Tribolium castaneum', 'tcas', '', '', '', '');

INSERT INTO GenomeAssembly (species_id, release_version, is_deprecated)
VALUES (3, 'tcas5.2', 0);

INSERT INTO Chromosome (species_id, genome_assembly_id, name, length)
VALUES (3, 6, 'NC_007416.3', 8676460);

INSERT INTO Chromosome (species_id, genome_assembly_id, name, length)
VALUES (3, 6, 'NC_007417.3', 15265516);

INSERT INTO Chromosome (species_id, genome_assembly_id, name, length)
VALUES (3, 6, 'NC_007418.3', 31381287);

INSERT INTO Chromosome (species_id, genome_assembly_id, name, length)
VALUES (3, 6, 'NC_007419.2', 12290766);

INSERT INTO Chromosome (species_id, genome_assembly_id, name, length)
VALUES (3, 6, 'NC_007420.3', 15459558);

INSERT INTO Chromosome (species_id, genome_assembly_id, name, length)
VALUES (3, 6, 'NC_007421.3', 10086398);

INSERT INTO Chromosome (species_id, genome_assembly_id, name, length)
VALUES (3, 6, 'NC_007422.5', 16482863);

INSERT INTO Chromosome (species_id, genome_assembly_id, name, length)
VALUES (3, 6, 'NC_007423.3', 14581690);

INSERT INTO Chromosome (species_id, genome_assembly_id, name, length)
VALUES (3, 6, 'NC_007424.3', 16184580);

INSERT INTO Chromosome (species_id, genome_assembly_id, name, length)
VALUES (3, 6, 'NC_007425.3', 7222678);

INSERT INTO Chromosome (species_id, genome_assembly_id, name, length)
VALUES (3, 6, 'NC_003081.2', 15881);

INSERT INTO Chromosome (species_id, genome_assembly_id, name, length)
VALUES (3, 6, 'Unspecified', 0);

INSERT INTO DevelopmentalStage (species_id, term, identifier, is_deprecated)
VALUES (3, 'none', 'none', 0);

INSERT INTO Species (scientific_name, short_name, public_database_names, public_database_links, public_browser_names, public_browser_links)
VALUES ('Aedes aegypti', 'aaeg', '', '', '', '');

INSERT INTO GenomeAssembly (species_id, release_version, is_deprecated)
VALUES (4, 'aaeg5', 0);

INSERT INTO Chromosome (species_id, genome_assembly_id, name, length)
VALUES (4, 7, '1', 310827022);

INSERT INTO Chromosome (species_id, genome_assembly_id, name, length)
VALUES (4, 7, '2', 474425716);

INSERT INTO Chromosome (species_id, genome_assembly_id, name, length)
VALUES (4, 7, '3', 409777670);

INSERT INTO Chromosome (species_id, genome_assembly_id, name, length)
VALUES (4, 7, 'MT', 16790);

INSERT INTO Chromosome (species_id, genome_assembly_id, name, length)
VALUES (4, 7, 'Unspecified', 0);

INSERT INTO DevelopmentalStage (species_id, term, identifier, is_deprecated)
VALUES (4, 'none', 'none', 0);

\! echo "Done!";

\! echo "Making procedure transformations..."; 

DELIMITER //

CREATE OR REPLACE PROCEDURE update_anatomical_expressions(
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
                                 FROM ExpressionTerm et
                                 WHERE s.species_id = et.species_id);
                                
    SELECT ROW_COUNT()
    INTO new_anatomical_expressions_number;
END //

\! echo "Done!";