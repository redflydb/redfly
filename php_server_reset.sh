docker stop $(docker ps -a | grep "redfly_php_server" | cut -d" " -f1)
docker rm $(docker ps -a | grep "redfly_php_server" | cut -d" " -f1)
docker rmi $(docker images | grep "redfly_php_server" | cut -d" " -f1)
docker-compose up -d php_server
docker image prune --force
docker volume prune --force
