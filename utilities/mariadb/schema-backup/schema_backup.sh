#!/bin/sh

set -e;

mkdir -p /db;
mysqldump \
    --host=${MYSQL_HOST} \
    --port=${MYSQL_PORT} \
    --user=${MYSQL_BACKUP_USER} \
    --password=${MYSQL_BACKUP_PASSWORD} \
    --single-transaction \
    --routines \
    --triggers \
    --no-data \
    ${MYSQL_DATABASE} > /db/schema.sql;