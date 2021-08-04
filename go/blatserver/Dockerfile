FROM golang:1.16

WORKDIR /go/src/
RUN apt-get update && \
    apt-get install -y --no-install-recommends \
        libkrb5-dev \
        libpng-dev \
        patch \
        unzip && \
    go get -u github.com/gorilla/mux

ADD . .
RUN CGO_ENABLED=0 GOOS=linux GOARCH=amd64 go build -a ./cmd/main.go

# Build the BLAT commandline tool as a static binary so it can be run within
# Alpine without worrying about dependencies. Credit goes to Alex Reynolds
# (https://www.biostars.org/p/261839/#261983) for the patch.

RUN mkdir /tmp/blat && \
    unzip -q ./assets/blat.zip -d /tmp/blat/ && \
    cp /tmp/blat/blatSrc/blat/makefile /tmp/blat/blatSrc/blat/makefile.original && \
    patch /tmp/blat/blatSrc/blat/makefile.original -i ./assets/static-makefile.patch -o /tmp/blat/blatSrc/blat/makefile && \
    mkdir -p /root/bin/x86_64 && \
    (cd /tmp/blat/blatSrc && MACHTYPE=x86_64 make) && \
    mv /root/bin/x86_64/blat ./

FROM alpine:3.14

COPY --from=0 /go/src/main ./cmd/blatserver
COPY --from=0 /go/src/blat /usr/local/bin
EXPOSE 8080
VOLUME ["/assets"]

WORKDIR /cmd
CMD ["./blatserver"]
