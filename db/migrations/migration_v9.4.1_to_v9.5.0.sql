\! echo "Making procedure transformations...";

DELIMITER //

CREATE OR REPLACE PROCEDURE release_approved_records(
    OUT new_current_rcs_number INT,
    OUT new_archived_rcs_number INT,
	OUT new_current_tfbss_number INT,
    OUT new_archived_tfbss_number INT,
    OUT new_current_crm_segments_number INT,
    OUT new_archived_crm_segments_number INT,
    OUT new_current_predicted_crms_number INT,
    OUT new_archived_predicted_crms_number INT)
    MODIFIES SQL DATA
    SQL SECURITY INVOKER
    COMMENT 'Sets all approved entities to current.'
BEGIN
    CREATE TEMPORARY TABLE tmp_release_staging (record_id INT UNSIGNED);

    INSERT INTO tmp_release_staging (record_id)
    SELECT rc_id
    FROM ReporterConstruct AS rc
         INNER JOIN (SELECT entity_id
                     FROM ReporterConstruct
                     WHERE state = 'approved' AND
                        entity_id IS NOT NULL) AS a
         ON rc.entity_id = a.entity_id
    WHERE rc.state = 'current';

    UPDATE ReporterConstruct AS rc
           LEFT OUTER JOIN (SELECT entity_id,
                                MAX(version) AS latest
                            FROM ReporterConstruct
                            WHERE state = 'current'
                            GROUP BY entity_id) AS v
           ON rc.entity_id = v.entity_id
    SET rc.state = 'current',
        rc.version = IF(latest IS NOT NULL, latest + 1, 0),
        rc.last_update = CURRENT_TIMESTAMP
    WHERE rc.state = 'approved';
   
    SELECT ROW_COUNT()
  	INTO new_current_rcs_number; 

    SET @entity_id = (SELECT IFNULL(MAX(entity_id), 0)
                      FROM ReporterConstruct);

    UPDATE ReporterConstruct
    SET entity_id = (@entity_id := @entity_id + 1)
    WHERE state = 'current' AND
        entity_id IS NULL;

    UPDATE ReporterConstruct
    SET state = 'archived',
        archive_date = CURRENT_TIMESTAMP
    WHERE rc_id IN (SELECT record_id
                    FROM tmp_release_staging);

    SELECT ROW_COUNT()
  	INTO new_archived_rcs_number;     
    
    TRUNCATE TABLE tmp_release_staging;

    INSERT INTO tmp_release_staging (record_id)
    SELECT tfbs_id
    FROM BindingSite AS bs
         INNER JOIN (SELECT entity_id
                     FROM BindingSite
                     WHERE state = 'approved' AND
                        entity_id IS NOT NULL) AS a
         ON bs.entity_id = a.entity_id
    WHERE bs.state = 'current';

    UPDATE BindingSite AS bs
           LEFT OUTER JOIN (SELECT entity_id,
                                MAX(version) AS latest
                            FROM BindingSite
                            WHERE state = 'current'
                            GROUP BY entity_id) AS v
           ON bs.entity_id = v.entity_id
    SET bs.state = 'current',
        bs.version = IF(latest IS NOT NULL, latest + 1, 0),
        bs.last_update = CURRENT_TIMESTAMP
    WHERE bs.state = 'approved';

    SELECT ROW_COUNT()
  	INTO new_current_tfbss_number; 
   
    SET @entity_id = (SELECT IFNULL(MAX(entity_id), 0)
                      FROM BindingSite);

    UPDATE BindingSite
    SET entity_id = (@entity_id := @entity_id + 1)
    WHERE state = 'current' AND
        entity_id IS NULL;

    UPDATE BindingSite
    SET name = REPLACE(name, 'REDFLY:XXX', CONCAT('REDFLY:TF', LPAD(entity_id, 6, '0')))
    WHERE name LIKE '%XXX' AND
        state = 'current';

    UPDATE BindingSite
    SET state = 'archived',
        archive_date = CURRENT_TIMESTAMP
    WHERE tfbs_id IN (SELECT record_id
                      FROM tmp_release_staging);

    SELECT ROW_COUNT()
  	INTO new_archived_tfbss_number;     
    
    TRUNCATE TABLE tmp_release_staging;

    INSERT INTO tmp_release_staging (record_id)
    SELECT crm_segment_id
    FROM CRMSegment AS crmsegment
         INNER JOIN (SELECT entity_id
                     FROM CRMSegment
                     WHERE state = 'approved' AND
                        entity_id IS NOT NULL) AS a
         ON crmsegment.entity_id = a.entity_id
    WHERE crmsegment.state = 'current';

    UPDATE CRMSegment AS crmsegment
           LEFT OUTER JOIN (SELECT entity_id,
                                MAX(version) AS latest
                            FROM CRMSegment
                            WHERE state = 'current'
                            GROUP BY entity_id) AS v
           ON crmsegment.entity_id = v.entity_id
    SET crmsegment.state = 'current',
        crmsegment.version = IF(latest IS NOT NULL, latest + 1, 0),
        crmsegment.last_update = CURRENT_TIMESTAMP
    WHERE crmsegment.state = 'approved';

    SELECT ROW_COUNT()
  	INTO new_current_crm_segments_number; 
   
    SET @entity_id = (SELECT IFNULL(MAX(entity_id), 0)
                      FROM CRMSegment);

    UPDATE CRMSegment
    SET entity_id = (@entity_id := @entity_id + 1)
    WHERE state = 'current' AND
       entity_id IS NULL;

    UPDATE CRMSegment
    SET state = 'archived',
        archive_date = CURRENT_TIMESTAMP
    WHERE crm_segment_id IN (SELECT record_id
                             FROM tmp_release_staging);

    SELECT ROW_COUNT()
  	INTO new_archived_crm_segments_number;    
    
    TRUNCATE TABLE tmp_release_staging;
   
    INSERT INTO tmp_release_staging (record_id)
    SELECT predicted_crm_id
    FROM PredictedCRM AS pcrm
         INNER JOIN (SELECT entity_id
                     FROM PredictedCRM
                     WHERE state = 'approved' AND
                        entity_id IS NOT NULL) AS a
         ON pcrm.entity_id = a.entity_id
    WHERE pcrm.state = 'current';

    UPDATE PredictedCRM AS pcrm
           LEFT OUTER JOIN (SELECT entity_id,
                                MAX(version) AS latest
                            FROM PredictedCRM
                            WHERE state = 'current'
                            GROUP BY entity_id) AS v
            ON pcrm.entity_id = v.entity_id
    SET pcrm.state = 'current',
        pcrm.version = IF(latest IS NOT NULL, latest + 1, 0),
        pcrm.last_update = CURRENT_TIMESTAMP
    WHERE pcrm.state = 'approved';

    SELECT ROW_COUNT()
  	INTO new_current_predicted_crms_number;    
   
    SET @entity_id = (SELECT IFNULL(MAX(entity_id), 0)
                      FROM PredictedCRM);

    UPDATE PredictedCRM
    SET entity_id = (@entity_id := @entity_id + 1)
    WHERE state = 'current' AND
       entity_id IS NULL;

    UPDATE PredictedCRM
    SET state = 'archived',
        archive_date = CURRENT_TIMESTAMP
    WHERE predicted_crm_id IN (SELECT record_id
                               FROM tmp_release_staging);

    SELECT ROW_COUNT()
  	INTO new_archived_predicted_crms_number;    
    
    DROP TEMPORARY TABLE IF EXISTS tmp_release_staging;
END //

\! echo "Done!";