-- Find all the different entities having the same coordinates.

-- Cis-Regulatory Module Segment
SELECT crms1.entity_id,
	crms1.name,
	crms1.chromosome_id,
	crms1.current_start,
	crms1.current_end,
	crms2.entity_id,
	crms2.name,
	crms2.chromosome_id,
	crms2.current_start,
	crms2.current_end
FROM (SELECT entity_id,
	      name,
		  chromosome_id,
		  current_start,
		  current_end
	  FROM CRMSegment
	  WHERE state <> 'archived') AS crms1,
	 (SELECT entity_id,
	      name,
		  chromosome_id,
		  current_start,
		  current_end
	  FROM CRMSegment
	  WHERE state <> 'archived') AS crms2
WHERE crms1.entity_id <> crms2.entity_id AND
	crms1.chromosome_id = crms2.chromosome_id AND
	crms1.current_start = crms2.current_start AND
	crms1.current_end = crms2.current_end
ORDER BY crms1.chromosome_id,
	crms1.current_start,
	crms1.current_end,
	crms1.name;

-- Reporter Construct
-- Its execution time is estimated about a bit more than an hour
SELECT rc1.entity_id,
	rc1.name,
	rc1.chromosome_id,
	rc1.current_start,
	rc1.current_end,
	rc2.entity_id,
	rc2.name,
	rc2.chromosome_id,
	rc2.current_start,
	rc2.current_end
FROM (SELECT entity_id,
	      name,
		  chromosome_id,
		  current_start,
		  current_end
	  FROM ReporterConstruct
	  WHERE state <> 'archived') AS rc1,
	 (SELECT entity_id,
	      name,
		  chromosome_id,
		  current_start,
		  current_end
	  FROM ReporterConstruct
	  WHERE state <> 'archived') AS rc2
WHERE rc1.entity_id <> rc2.entity_id AND
	rc1.chromosome_id = rc2.chromosome_id AND
	rc1.current_start = rc2.current_start AND
	rc1.current_end = rc2.current_end
ORDER BY rc1.chromosome_id,
	rc1.current_start,
	rc1.current_end,
	rc1.name;

-- Transcription Factor Binding Site
SELECT tfbs1.entity_id,
	tfbs1.name,
	tfbs1.chromosome_id,
	tfbs1.current_start,
	tfbs1.current_end,
	tfbs2.entity_id,
	tfbs2.name,
	tfbs2.chromosome_id,
	tfbs2.current_start,
	tfbs2.current_end
FROM (SELECT entity_id,
	      name,
		  chromosome_id,
		  current_start,
		  current_end
	  FROM BindingSite
	  WHERE state <> 'archived') AS tfbs1,
	 (SELECT entity_id,
	      name,
		  chromosome_id,
		  current_start,
		  current_end
	  FROM BindingSite
	  WHERE state <> 'archived') AS tfbs2
WHERE tfbs1.entity_id <> tfbs2.entity_id AND
	tfbs1.chromosome_id = tfbs2.chromosome_id AND
	tfbs1.current_start = tfbs2.current_start AND
	tfbs1.current_end = tfbs2.current_end
ORDER BY tfbs1.chromosome_id,
	tfbs1.current_start,
	tfbs1.current_end,
	tfbs1.name;