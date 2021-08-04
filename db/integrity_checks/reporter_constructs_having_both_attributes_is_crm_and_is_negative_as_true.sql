-- Find and fix all the reporter constructs having both attributes "is_crm" and "is_negative" as TRUE.

SELECT entity_id, 
	name,
	state,
	is_crm,
	is_negative 
FROM ReporterConstruct
WHERE state = 'current' AND
	is_crm = 1 AND
	is_negative = 1
ORDER By name;

UPDATE ReporterConstruct
SET is_crm = 0
WHERE state = 'current' AND
	is_crm = 1 AND
	is_negative = 1;