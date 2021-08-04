-- There is a special (and very computationally expensive,
-- about 22 minutes for each species) case which both gene name and
-- identifier do NOT match.
-- It is recommended to execute it in one from both test or development
-- environments after each new REDfly release.
-- If only an "Unspecified" term is printed for each species, you are all set.
-- Otherwise, follow the instructions in the Gene update caveat section
-- of the file placed in ./README.md.
-- Hint: most, if not all, matches can be found using the coordinates
-- and/or aliaeses of the gene(s) affected.
CALL search_genes_which_both_name_and_identifier_do_not_match ('aaeg');
CALL search_genes_which_both_name_and_identifier_do_not_match ('agam');
CALL search_genes_which_both_name_and_identifier_do_not_match ('dmel');
CALL search_genes_which_both_name_and_identifier_do_not_match ('tcas');