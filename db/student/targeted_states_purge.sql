DELETE FROM CRMSegment
WHERE state IN ('approval', 'approved', 'deleted', 'editing');
DELETE FROM ReporterConstruct
WHERE state IN ('approval', 'approved', 'deleted', 'editing');
DELETE FROM BindingSite
WHERE state IN ('approval', 'approved', 'deleted', 'editing');