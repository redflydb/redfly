\! echo "Making table transformations..."; 

UPDATE triplestore_crm_segment 
SET stage_on = 'dmel:none'
WHERE stage_on = 'none' AND
	crm_segment_id IN (SELECT crm_segment_id
	                   FROM CRMSegment
	                   WHERE assayed_in_species_id = 1);

UPDATE triplestore_crm_segment 
SET stage_off = 'dmel:none'
WHERE stage_off = 'none' AND
	crm_segment_id IN (SELECT crm_segment_id
	                   FROM CRMSegment
	                   WHERE assayed_in_species_id = 1);
	          
UPDATE triplestore_crm_segment 
SET stage_on = 'agam:none'
WHERE stage_on = 'none' AND
	crm_segment_id IN (SELECT crm_segment_id
	                   FROM CRMSegment
	                   WHERE assayed_in_species_id = 2);

UPDATE triplestore_crm_segment 
SET stage_off = 'agam:none'
WHERE stage_off = 'none' AND
	crm_segment_id IN (SELECT crm_segment_id
	                   FROM CRMSegment
	                   WHERE assayed_in_species_id = 2);
	          
UPDATE triplestore_crm_segment 
SET stage_on = 'tcas:none'
WHERE stage_on = 'none' AND
	crm_segment_id IN (SELECT crm_segment_id
	                   FROM CRMSegment
	                   WHERE assayed_in_species_id = 3);

UPDATE triplestore_crm_segment 
SET stage_off = 'tcas:none'
WHERE stage_off = 'none' AND
	crm_segment_id IN (SELECT crm_segment_id
	                   FROM CRMSegment
	                   WHERE assayed_in_species_id = 3);
	          
UPDATE triplestore_crm_segment 
SET stage_on = 'aaeg:none'
WHERE stage_on = 'none' AND
	crm_segment_id IN (SELECT crm_segment_id
	                   FROM CRMSegment
	                   WHERE assayed_in_species_id = 4);

UPDATE triplestore_crm_segment 
SET stage_off = 'aaeg:none'
WHERE stage_off = 'none' AND
	crm_segment_id IN (SELECT crm_segment_id
	                   FROM CRMSegment
	                   WHERE assayed_in_species_id = 4);

\! echo "Done!";