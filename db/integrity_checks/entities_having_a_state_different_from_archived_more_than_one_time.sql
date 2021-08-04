-- Find all the entities having more same (and different from archived) state occurrences than one.

-- Cis-Regulatory Module Segment
SELECT crms.entity_id, crms.state, COUNT(crms.state)
FROM CRMSegment crms 
WHERE crms.entity_id IS NOT NULL AND
	crms.state <> 'archived'
GROUP BY crms.entity_id, crms.state
HAVING COUNT(crms.state) > 1
ORDER BY crms.entity_id;

-- Reporter Construct
SELECT rc.entity_id, rc.state, COUNT(rc.state)
FROM ReporterConstruct rc 
WHERE rc.entity_id IS NOT NULL AND
	rc.state <> 'archived'
GROUP BY rc.entity_id, rc.state
HAVING COUNT(rc.state) > 1
ORDER BY rc.entity_id;

-- Transcription Factor Binding Site
SELECT tfbs.entity_id, tfbs.state, COUNT(tfbs.state)
FROM BindingSite tfbs 
WHERE tfbs.entity_id IS NOT NULL AND
	tfbs.state <> 'archived'
GROUP BY tfbs.entity_id, tfbs.state
HAVING COUNT(tfbs.state) > 1
ORDER BY tfbs.entity_id;