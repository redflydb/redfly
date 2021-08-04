-- Find all the entities whose element name does not begin by the name of its gene associated
-- with the exception of transcription factor binding sites needing two searches about
-- gene names.

-- Cis-Regulatory Module Segment
SELECT g.name,
	crms.name,
	crms.state 
FROM CRMSegment crms
JOIN Gene g ON crms.gene_id = g.gene_id
WHERE SUBSTRING(crms.name, 1, LOCATE('_', crms.name) - 1) <> g.name
ORDER BY crms.name;

-- Reporter Construct
SELECT g.name,
	rc.name,
	rc.state 
FROM ReporterConstruct rc
JOIN Gene g ON rc.gene_id = g.gene_id
WHERE SUBSTRING(rc.name, 1, LOCATE('_', rc.name) - 1) <> g.name
ORDER BY rc.name;

-- Transcription Factor Binding Site
-- a) Transcription factor name
SELECT g.name AS tf_name,
	tfbs.name,
	tfbs.state 
FROM BindingSite tfbs,
	Gene g
WHERE tfbs.state NOT IN ('archived') AND
	tfbs.tf_id = g.gene_id AND
	SUBSTRING(tfbs.name, 1, LOCATE('_', tfbs.name) - 1) <> g.name
ORDER BY tfbs.name;
-- b) Gene name
SELECT g.name AS gene_name,
	tfbs.name,
	tfbs.state 
FROM BindingSite tfbs,
	Gene g
WHERE tfbs.state NOT IN ('archived') AND
	tfbs.gene_id = g.gene_id AND
	SUBSTRING(SUBSTRING(tfbs.name, 1, LOCATE(':REDFLY:', tfbs.name) - 1), LOCATE('_', tfbs.name) + 1) <> g.name
ORDER BY tfbs.name;