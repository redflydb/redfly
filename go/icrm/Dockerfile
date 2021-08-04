FROM golang:1.16

WORKDIR /go/src/
COPY . .
VOLUME ["/go/src/icrm"]
CMD ["sh", "/go/src/docker_build.sh"]