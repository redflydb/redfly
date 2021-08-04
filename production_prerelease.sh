make $(cat .env | xargs) database-backup
git pull --rebase
make $(cat .env | xargs) vendor-update \
    php-libraries-reload \
    extjs-libraries-reload \
    help-update
cat ./db/migrations/migration_v9.4.0_to_v9.4.1.sql | env $(cat .env) bash -c 'docker exec -i $(docker ps -a | grep "mariadb:10.5" | cut -d" " -f1) mysql $MYSQL_DATABASE -u $MYSQL_USER -p$MYSQL_PASSWORD'
