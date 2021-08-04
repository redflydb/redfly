#!/bin/sh

set -e
go get -u github.com/go-sql-driver/mysql
(cd ./cmd && go build -o icrm_calculation)
useradd redfly
chown redfly:redfly ./cmd/icrm_calculation
