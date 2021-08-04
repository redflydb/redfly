#cd cmd && go build -a ./main.go
docker stop $(docker ps -a | grep "redfly_termlookup_server" | cut -d" " -f1)
docker rm $(docker ps -a | grep "redfly_termlookup_server" | cut -d" " -f1)
docker rmi $(docker images | grep "redfly_termlookup_server" | cut -d" " -f1)
mv -f ./assets/cache.db ./assets/cache-backup.db
# After executing the following command line, you should wait two minutes, at least,
# so that its internal cache, cache.db, can be finally built
docker-compose up -d termlookup_server
docker image prune --force
docker volume prune --force
sleep 2m
docker-compose exec termlookup_server chown $(id -u):$(id -g) /go/src/assets/cache.db
#docker-compose exec termlookup_server pwd
# After such two minutes, you can check the new cache contents with the following command lines:
#docker-compose exec php_server curl termlookup_server:8080/anatomical_expressions > anatomical_expressions.json
#docker-compose exec php_server curl termlookup_server:8080/biological_processes > biological_processes.json
#docker-compose exec php_server curl termlookup_server:8080/developmental_stages > developmental_stages.json
#docker-compose exec php_server curl termlookup_server:8080/genes > genes.json
#docker-compose exec php_server curl termlookup_server:8080/transgenes > transgenes.json
