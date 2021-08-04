\! echo "Making table transformations..."; 

UPDATE Species
SET public_database_names = 'VectorBase',
    public_database_links = 'https://vectorbase.org/vectorbase/app/search?q=gene_identifier',
    public_browser_names = 'JBrowse',
    public_browser_links = 'https://vectorbase.org/vectorbase/app/jbrowse?data=%2Fa%2Fservice%2Fjbrowse%2Ftracks%2FaaegLVP_AGWG&loc=AaegL5_chromosome%3Astart..end&tracks=gene&highlight='
WHERE species_id = 4;

\! echo "Done!";
