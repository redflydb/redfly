-- Find all the entities where the version of the current record is the same as the
-- maximum archived version.

-- Cis-Regulatory Module Segment
CREATE TEMPORARY TABLE tmp_crms_with_bad_current_version AS
SELECT current.entity_id
FROM CRMSegment current
JOIN (SELECT entity_id,
          MAX(version) AS version
      FROM CRMSegment
      WHERE state = 'archived'
      GROUP BY entity_id) max_archive ON current.entity_id = max_archive.entity_id
WHERE current.state = 'current' AND
    max_archive.version = current.version;

UPDATE CRMSegment e
JOIN tmp_crms_with_bad_current_version tmp ON tmp.entity_id = e.entity_id
SET e.version = e.version + 1
WHERE state = 'current';

DROP TABLE tmp_crms_with_bad_current_version;

-- Reporter Construct
CREATE TEMPORARY TABLE tmp_rc_with_bad_current_version AS
SELECT current.entity_id
FROM ReporterConstruct current
JOIN (SELECT entity_id,
          MAX(version) AS version
      FROM ReporterConstruct
      WHERE state = 'archived'
      GROUP BY entity_id) max_archive ON current.entity_id = max_archive.entity_id
WHERE current.state = 'current' AND
    max_archive.version = current.version;

UPDATE ReporterConstruct e
JOIN tmp_rc_with_bad_current_version tmp ON tmp.entity_id = e.entity_id
SET e.version = e.version + 1
WHERE state = 'current';

DROP TABLE tmp_rc_with_bad_current_version;

-- Transcription Factor Binding Site
CREATE TEMPORARY TABLE tmp_tfbs_with_bad_current_version AS
SELECT current.entity_id
FROM BindingSite current
JOIN (SELECT entity_id,
          MAX(version) AS version
      FROM BindingSite
      WHERE state = 'archived'
      GROUP BY entity_id) max_archive ON current.entity_id = max_archive.entity_id
WHERE current.state = 'current' AND
    max_archive.version = current.version;

UPDATE BindingSite c
JOIN tmp_tfbs_with_bad_current_version tmp ON tmp.entity_id = c.entity_id
SET c.version = c.version + 1
WHERE state = 'current';

DROP TABLE tmp_tfbs_with_bad_current_version;