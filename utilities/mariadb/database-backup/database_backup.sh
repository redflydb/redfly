#!/bin/sh

set -e;

mkdir -p /db/dumps;
mysqldump \
    --host=${MYSQL_HOST} \
    --port=${MYSQL_PORT} \
    --user=${MYSQL_BACKUP_USER} \
    --password=${MYSQL_BACKUP_PASSWORD} \
    --single-transaction \
    --routines \
    --triggers \
    ${MYSQL_DATABASE} | gzip -c > /db/dumps/$(date +"%Y-%m-%dT%H:%M:%S").sql.gz;