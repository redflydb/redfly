-- Find and fix all the citations having the contents empty.

SELECT *
FROM Citation
WHERE contents = '';

-- The variables, USER, PASSWORD, and HOSTNAME, must be given to the command line embedded in the SQL consult
SELECT CONCAT('curl -u USER:PASSWORD \'http://HOSTNAME/api/rest/jsonstore/citation/list?&force_update=1&external_id=', external_id, '\' && sleep 3')
FROM Citation
WHERE contents = '';