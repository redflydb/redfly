\! echo "Making table transformations..."; 

ALTER TABLE inferred_crm_read_model
ADD CONSTRAINT FOREIGN KEY (chromosome_id) REFERENCES Chromosome (chromosome_id);

ALTER TABLE triplestore_crm_segment
ADD COLUMN silencer ENUM ('enhancer', 'silencer') NOT NULL DEFAULT 'enhancer';

ALTER TABLE triplestore_predicted_crm
ADD COLUMN silencer ENUM ('enhancer', 'silencer') NOT NULL DEFAULT 'enhancer';

ALTER TABLE triplestore_rc
ADD COLUMN silencer ENUM ('enhancer', 'silencer') NOT NULL DEFAULT 'enhancer';

\! echo "Done!";