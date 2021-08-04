make $(cat .env | xargs) vendor-update \
    php-libraries-reload \
    extjs-libraries-reload \
    help-update \
    docker-restart \
    mariadb-access \
    redfly-database-curator-users \
    redfly-database-development-user
docker-compose exec php_server docker-php-ext-enable xdebug
make $(cat .env | xargs) xdebug
#cat ./db/migrations/migration_v9.4.0_to_v9.4.1.sql | env $(cat .env) bash -c 'docker exec -i $(docker ps -a | grep "mariadb:10.5" | cut -d" " -f1) mysql $MYSQL_DATABASE -u $MYSQL_USER -p$MYSQL_PASSWORD'
cat ./db/development/author_myself.sql | env $(cat .env) bash -c 'docker exec -i $(docker ps -a | grep "mariadb:10.5" | cut -d" " -f1) mysql $MYSQL_DATABASE -u $MYSQL_USER -p$MYSQL_PASSWORD'
make $(cat .env | xargs) mariadb-audit-plugin-start
