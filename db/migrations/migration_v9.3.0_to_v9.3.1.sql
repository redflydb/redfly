\! echo "Making procedure transformations..."; 

DELIMITER //

CREATE PROCEDURE search_genes_which_both_name_and_identifier_do_not_match(IN species_short_name VARCHAR(32))
    MODIFIES SQL DATA
    SQL SECURITY INVOKER
BEGIN

SELECT g.name 
FROM Gene g
JOIN Species s ON (s.short_name = species_short_name AND
    g.species_id = s.species_id)
WHERE g.gene_id NOT IN (SELECT old.gene_id
                        FROM Gene old
                        JOIN Species s ON (s.short_name = species_short_name AND
                        	old.species_id = s.species_id) 
                        JOIN staging_gene_update new ON (BINARY old.name = BINARY new.name AND
                            old.identifier = new.identifier AND
                            s.short_name = new.species_short_name)) AND
      g.gene_id NOT IN (SELECT old.gene_id
                        FROM Gene old
                        JOIN Species s ON (s.short_name = species_short_name AND
                        	old.species_id = s.species_id)
                        JOIN staging_gene_update new ON (BINARY old.name = BINARY new.name AND
                            old.identifier != new.identifier AND
                            s.short_name = new.species_short_name)) AND
      g.gene_id NOT IN (SELECT old.gene_id
                        FROM Gene old
                        JOIN Species s ON (s.short_name = species_short_name AND
                        	old.species_id = s.species_id)
                        JOIN staging_gene_update new ON (BINARY old.name != BINARY new.name AND
                            old.identifier = new.identifier AND
                            s.short_name = new.species_short_name));

END //

DELIMITER ;

\! echo "Done!";