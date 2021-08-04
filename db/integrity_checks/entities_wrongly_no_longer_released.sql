-- Find all the entities wrongly no longer released.
-- Do not mistake them as entities having their state as "deleted" and 
-- going to be archived forever in the next REDfly release!

-- Cis-regulatory Module Segment
SELECT crms.entity_id,
    MAX(IF(crms.state = 'archived', crms.version, 0)) AS max_archive
FROM CRMSegment crms
JOIN (SELECT crms.entity_id,
          SUM(IF(crms.state = 'current', 1, 0)) AS current
      FROM CRMSegment crms
      JOIN (SELECT entity_id
            FROM CRMSegment
            WHERE state = 'archived'
            GROUP BY entity_id) archived ON crms.entity_id = archived.entity_id
      GROUP BY crms.entity_id) a ON crms.entity_id = a.entity_id
WHERE a.current = 0
GROUP BY crms.entity_id;

-- Reporter Construct
SELECT rc.entity_id,
    MAX(IF(rc.state = 'archived', rc.version, 0)) AS max_archive
FROM ReporterConstruct rc
JOIN (SELECT rc.entity_id,
          SUM(IF(rc.state = 'current', 1, 0)) AS current
      FROM ReporterConstruct rc
      JOIN (SELECT entity_id
            FROM ReporterConstruct
            WHERE state = 'archived'
            GROUP BY entity_id) archived ON rc.entity_id = archived.entity_id
      GROUP BY rc.entity_id) a ON rc.entity_id = a.entity_id
WHERE a.current = 0
GROUP BY rc.entity_id;

-- Transcription Factor Binding Site
SELECT tfbs.entity_id,
    MAX(IF(tfbs.state = 'archived', tfbs.version, 0)) AS max_archive
FROM BindingSite tfbs
JOIN (SELECT tfbs.entity_id,
          SUM(IF(tfbs.state = 'current', 1, 0)) AS current
      FROM BindingSite tfbs
      JOIN (SELECT entity_id
            FROM BindingSite
            WHERE state = 'archived'
            GROUP BY entity_id) archived ON tfbs.entity_id = archived.entity_id
      GROUP BY tfbs.entity_id) a ON tfbs.entity_id = a.entity_id
WHERE a.current = 0
GROUP BY tfbs.entity_id;