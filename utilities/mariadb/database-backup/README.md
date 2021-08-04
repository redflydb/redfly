The REDfly MariaDB backup utility
---------------------------------

This is a Docker image that creates, compresses, and saves a MariaDB database dump to a targeted directory in `.sql.gz` format.
The filename will be a timestamp based on the ISO-8601 standard (yyyy-mm-ddThh:mm:ss).

The dump file (which will be named `dump.sql.gz`) is placed into ./db/dumps/ which is a volume mount point that should be mounted locally or in another container.

To build the image, simply run, e.g.

```
docker build . -t mariadb_database_backup
```

To run the image, the following environment variables must be set:
  - `MYSQL_DATABASE` The MariaDB database to back up.
  - `MYSQL_BACKUP_USER` The MariaDB user.
  - `MYSQL_BACKUP_PASSWORD` The MariaDB password.
  - `MYSQL_HOST` The host of the MariaDB server.
  - `MYSQL_PORT` The port to use to connect to the MariaDB server -- usually `3306`.

A command to run the image would look like:

```
docker run \
    -e MYSQL_DATABASE=database \
    -e MYSQL_BACKUP_USER=user \
    -e MYSQL_BACKUP_PASSWORD=password \
    -e MYSQL_HOST=host \
    -e MYSQL_PORT=3306 \
    -v ./db/dumps:/db/dumps
    --rm mariadb_database_backup
```

Note that this image is intended to be a short-lived one -- it starts up, does its work, and exits. The `--rm` above tells Docker to remove the container once it exits.
