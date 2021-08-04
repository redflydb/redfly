#!/bin/sh

set -e
go get -u github.com/go-sql-driver/mysql
(cd ./cmd && go build -o statistics_report)
