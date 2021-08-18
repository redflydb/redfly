(cd ./go/termlookupserver/assets && sh ./download-external-assets.sh)
mv ./go/termlookupserver/assets/cache.db ./go/termlookupserver/assets/cache-backup.db
docker-compose exec termlookup_server chown $(id -u):$(id -g) /go/src/assets/cache-backup.db
# After executing the following command line, you should wait two minutes, at least,
# so that its internal cache, cache.db, can be finally built
docker-compose restart termlookup_server
docker-compose exec termlookup_server chown $(id -u):$(id -g) /go/src/assets/cache.db
make $(cat .env | xargs) update 
make $(cat .env | xargs) release
make $(cat .env | xargs) datadumps
# After executing the following command line, you should update the file, ./config/settings.yml)
cat go/termlookupserver/assets/releases.log
make $(cat .env | xargs) statistics
make $(cat .env | xargs) ubir-backup
make $(cat .env | xargs) mariadb-audit-plugin-start
