-- Allow to make tests about notifying authors which must be yourself 
-- in any development or test environment but the production environments

UPDATE Citation
SET author_email = 'your_email@ccr.buffalo.edu',
    author_contacted = 0,
    author_contact_date = NULL;