-- Find all the different entities having the same element name.

-- Cis-Regulatory Module Segment
SELECT crms1.entity_id,
	crms1.name,
	crms1.state,
	crms2.entity_id,
	crms2.name,
	crms2.state	
FROM (SELECT entity_id,
	      name,
	      state
	  FROM CRMSegment
	  WHERE state <> 'archived') AS crms1,
	 (SELECT entity_id,
	      name,
	      state
	  FROM CRMSegment
	  WHERE state <> 'archived') AS crms2
WHERE crms1.entity_id <> crms2.entity_id AND
	crms1.name = crms2.name
ORDER BY crms1.name;

-- Reporter Construct
SELECT rc1.entity_id,
	rc1.name,
	rc1.state,
	rc2.entity_id,
	rc2.name,
	rc2.state	
FROM (SELECT entity_id,
	      name,
	      state
	  FROM ReporterConstruct
	  WHERE state <> 'archived') AS rc1,
	 (SELECT entity_id,
	      name,
	      state
	  FROM ReporterConstruct
	  WHERE state <> 'archived') AS rc2
WHERE rc1.entity_id <> rc2.entity_id AND
	rc1.name = rc2.name
ORDER BY rc1.name;

-- Transcription Factor Binding Site
SELECT tfbs1.entity_id,
	tfbs1.name,
	tfbs1.state,
	tfbs2.entity_id,
	tfbs2.name,
	tfbs2.state	
FROM (SELECT entity_id,
	      name,
	      state
	  FROM BindingSite
	  WHERE state <> 'archived') AS tfbs1,
	 (SELECT entity_id,
	      name,
	      state
	  FROM BindingSite
	  WHERE state <> 'archived') AS tfbs2
WHERE tfbs1.entity_id <> tfbs2.entity_id AND
	tfbs1.name = tfbs2.name
ORDER BY tfbs1.name;