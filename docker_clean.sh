docker stop $(docker ps --filter "name=mariadb_database_backup_1" \
    --filter "name=mariadb_schema_backup_1" \
    --filter "name=redfly_blat_server_1" \
    --filter "name=redfly_mariadb_server_1" \
    --filter "name=redfly_php_server_1" \
    --filter "name=redfly_termlookup_server_1" \
    -q)
docker rm mariadb_database_backup_1 \
    mariadb_schema_backup_1 \
    redfly_blat_server_1 \
    redfly_mariadb_server_1 \
    redfly_php_server_1 \
    redfly_termlookup_server_1
docker rmi mariadb_database_backup
docker rmi mariadb_schema_backup 
docker rmi redfly_blat_server
docker rmi redfly_php_server
docker rmi redfly_termlookup_server
docker rmi $(docker images -f "dangling=true" -q)
docker volume prune -f
