FROM golang:1.17

WORKDIR /go/src/
RUN apt-get update && \
    apt-get install -y --no-install-recommends \
        librdf0 \
        librdf0-dev \
        redland-utils && \
    go get -u github.com/gorilla/mux && \
    go get -u github.com/lib/pq && \
    go get -u github.com/mattn/go-sqlite3

ADD . .
RUN CGO_ENABLED=1 GOOS=linux GOARCH=amd64 go build -a ./cmd/main.go && \
    mv /go/src/main ./cmd/termlookupserver

EXPOSE 8080
VOLUME ["/go/src/assets"]

WORKDIR /go/src/cmd
CMD ["./termlookupserver"]
