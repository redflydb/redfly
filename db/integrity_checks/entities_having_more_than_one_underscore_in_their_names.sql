-- Find all the entities where the element name has more than one underscore which is wrong.

-- Cis-Regulatory Module Segment
SELECT crms.name 
FROM CRMSegment crms 
WHERE crms.name REGEXP '_{2,}'
ORDER BY crms.name;

-- Predicted Cis-Regulatory Module
SELECT pcrm.name 
FROM PredictedCRM pcrm 
WHERE pcrm.name REGEXP '_{2,}'
ORDER BY pcrm.name;

-- Reporter Construct
SELECT rc.name 
FROM ReporterConstruct rc
WHERE rc.name REGEXP '_{2,}'
ORDER BY rc.name;

-- Transcription Factor Binding Site
SELECT bs.name 
FROM BindingSite bs 
WHERE bs.name REGEXP '_{2,}'
ORDER BY bs.name;