-- Find all the entities whose current version is less than the maximum archived version.

-- Cis-Regulatory Module Segment
CREATE TEMPORARY TABLE tmp_crms_with_bad_current_version AS
SELECT current.entity_id,
    max_archive.version,
    max_archive.expected
FROM CRMSegment current
JOIN (SELECT entity_id,
          MAX(version) AS version,
          COUNT(entity_id) - 1 AS expected
      FROM CRMSegment
      WHERE state = 'archived'
      GROUP BY entity_id) max_archive ON current.entity_id = max_archive.entity_id
WHERE current.state = 'current' AND
    max_archive.version > current.version;

UPDATE CRMSegment e
JOIN tmp_crms_with_bad_current_version tmp ON tmp.entity_id = e.entity_id
SET e.version = tmp.expected + 1
WHERE state = 'current';

UPDATE CRMSegment e
JOIN tmp_crms_with_bad_current_version tmp ON tmp.entity_id = e.entity_id
SET e.version = tmp.expected
WHERE e.state = 'archived' AND
    e.version = tmp.version;

DROP TABLE tmp_crms_with_bad_current_version;

-- Reporter Construct
CREATE TEMPORARY TABLE tmp_rc_with_bad_current_version AS
SELECT current.entity_id,
    max_archive.version,
    max_archive.expected
FROM ReporterConstruct current
JOIN (SELECT entity_id,
          MAX(version) AS version,
          COUNT(entity_id) - 1 AS expected
      FROM ReporterConstruct
      WHERE state = 'archived'
      GROUP BY entity_id) max_archive ON current.entity_id = max_archive.entity_id
WHERE current.state = 'current' AND
    max_archive.version > current.version;

UPDATE ReporterConstruct e
JOIN tmp_rc_with_bad_current_version tmp ON tmp.entity_id = e.entity_id
SET e.version = tmp.expected + 1
WHERE state = 'current';

UPDATE ReporterConstruct e
JOIN tmp_rc_with_bad_current_version tmp ON tmp.entity_id = e.entity_id
SET e.version = tmp.expected
WHERE e.state = 'archived' AND
    e.version = tmp.version;

DROP TABLE tmp_rc_with_bad_current_version;

-- Transcription Factor Binding Site
CREATE TEMPORARY TABLE tmp_tfbs_with_bad_current_version AS
SELECT current.entity_id,
    max_archive.version,
    max_archive.expected
FROM BindingSite current
JOIN (SELECT entity_id,
          MAX(version) AS version,
          COUNT(entity_id) - 1 AS expected
      FROM BindingSite
      WHERE state = 'archived'
      GROUP BY entity_id) max_archive ON current.entity_id = max_archive.entity_id
WHERE current.state = 'current' AND
    max_archive.version > current.version;

UPDATE BindingSite e
JOIN tmp_tfbs_with_bad_current_version tmp ON tmp.entity_id = e.entity_id
SET e.version = tmp.expected + 1
WHERE state = 'current';

UPDATE BindingSite e
JOIN tmp_tfbs_with_bad_current_version tmp ON tmp.entity_id = e.entity_id
SET e.version = tmp.expected
WHERE e.state = 'archived' AND
    e.version = tmp.version;

DROP TABLE tmp_tfbs_with_bad_current_version;