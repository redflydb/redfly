-- WITH currentRC AS (SELECT rc_id, entity_id, version FROM ReporterConstruct WHERE state="current")
-- SELECT rc_id FROM currentRC AS currentRC0 WHERE EXISTS (SELECT 1 FROM currentRC AS currentRC1 where currentRC0.entity_id=currentRC1.entity_id and currentRC0.version=currentRC1.version and currentRC0.rc_id<currentRC1.rc_id);

-- WITH currentRC AS (SELECT rc_id, entity_id, version FROM ReporterConstruct WHERE state="current")
-- SELECT rc_id FROM currentRC AS currentRC0 WHERE EXISTS (SELECT 1 FROM currentRC AS currentRC1 where currentRC0.entity_id=currentRC1.entity_id and currentRC0.version=currentRC1.version and currentRC0.rc_id>currentRC1.rc_id);

UPDATE ReporterConstruct SET state="archived" WHERE rc_id IN (76476, 77617);
UPDATE ReporterConstruct SET version=version+1 WHERE rc_id IN (127132, 154075);
