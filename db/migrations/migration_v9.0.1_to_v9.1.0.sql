\! echo "Making table transformations..."; 

UPDATE Chromosome
SET length = 22422827
WHERE chromosome_id = 17;

UPDATE Chromosome
SET length = 21146708
WHERE chromosome_id = 18;

UPDATE Chromosome
SET length = 23011544
WHERE chromosome_id = 19;

UPDATE Chromosome
SET length = 27905053
WHERE chromosome_id = 20;

UPDATE Chromosome
SET length = 24543557
WHERE chromosome_id = 21;

UPDATE Chromosome
SET length = 1351857
WHERE chromosome_id = 22;

UPDATE Chromosome
SET length = 10049037
WHERE chromosome_id = 23;

UPDATE Chromosome
SET length = 19517
WHERE chromosome_id = 24;

ALTER TABLE BindingSite
MODIFY COLUMN pubmed_id VARCHAR(64) NOT NULL
AFTER name;

CREATE INDEX pubmed_id ON BindingSite(pubmed_id);

ALTER TABLE CRMSegment
MODIFY COLUMN pubmed_id VARCHAR(64) NOT NULL
AFTER name;

CREATE INDEX pubmed_id ON CRMSegment(pubmed_id);

ALTER TABLE PredictedCRM
MODIFY COLUMN pubmed_id VARCHAR(64) NOT NULL
AFTER name;

CREATE INDEX pubmed_id ON PredictedCRM(pubmed_id);

ALTER TABLE ReporterConstruct
MODIFY COLUMN pubmed_id VARCHAR(64) NOT NULL
AFTER name;

CREATE INDEX pubmed_id ON ReporterConstruct(pubmed_id);

ALTER TABLE triplestore_crm_segment
MODIFY COLUMN pubmed_id VARCHAR(64) NOT NULL;

CREATE INDEX pubmed_id ON triplestore_crm_segment(pubmed_id);

ALTER TABLE triplestore_predicted_crm
MODIFY COLUMN pubmed_id VARCHAR(64) NOT NULL;

CREATE INDEX pubmed_id ON triplestore_predicted_crm(pubmed_id);

ALTER TABLE triplestore_rc
MODIFY COLUMN pubmed_id VARCHAR(64) NOT NULL;

CREATE INDEX pubmed_id ON triplestore_rc(pubmed_id);

\! echo "Done!";

\! echo "Making function transformations..."; 

DELIMITER //

CREATE OR REPLACE FUNCTION NumberOfCurrentReporterConstructs(species_id INT)
RETURNS INT DETERMINISTIC
BEGIN
	DECLARE number_of_current_reporter_constructs INT;

    IF species_id = 0
    THEN
        SELECT COUNT(rc_id)
	    INTO number_of_current_reporter_constructs
        FROM ReporterConstruct
        WHERE state = 'current';
    ELSE
        SELECT COUNT(rc_id)
	    INTO number_of_current_reporter_constructs
        FROM ReporterConstruct
        WHERE sequence_from_species_id = species_id AND
            state = 'current';
    END IF;
				
	RETURN number_of_current_reporter_constructs;
END; //

DELIMITER ;

\! echo "Done!";
