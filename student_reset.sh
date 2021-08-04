make $(cat .env | xargs) configuration \
    vendor-update \
    php-libraries-reload \
    extjs-libraries-reload \
    help-update \
    docker-restart \
    mariadb-access \
    redfly-database-curator-users
cat ./db/student/targeted_states_purge.sql | env $(cat .env) bash -c 'docker exec -i $(docker ps -a | grep "mariadb:10.5" | cut -d" " -f1) mysql $MYSQL_DATABASE -u $MYSQL_USER -p$MYSQL_PASSWORD'
cat ./db/student/curator_mshalfon.sql | env $(cat .env) bash -c 'docker exec -i $(docker ps -a | grep "mariadb:10.5" | cut -d" " -f1) mysql $MYSQL_DATABASE -u $MYSQL_USER -p$MYSQL_PASSWORD'
cp ./assets/.student.class_number .student
make $(cat .env | xargs) student-reset \
    mariadb-audit-plugin-start
